<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makePost(User $user, array $attributes = []): Post
{
    return Post::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Sample Post',
        'category' => 'marvel',
        'content' => 'Sample content',
        'slug' => fake()->unique()->slug(),
        'image' => null,
        'is_published' => true,
        'published_at' => now(),
    ], $attributes));
}

test('authenticated users can view their own post', function () {
    $user = User::factory()->create();
    $post = makePost($user, ['title' => 'My Own Post']);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.show', $post));

    $response->assertOk();
    $response->assertSeeText('My Own Post');
});

test('users cannot view posts owned by others', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = makePost($otherUser);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.show', $post));

    $response->assertNotFound();
});

test('authenticated users can open the edit page for their own post', function () {
    $user = User::factory()->create();
    $post = makePost($user, ['title' => 'Editable Post']);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.edit', $post));

    $response->assertOk();
    $response->assertSeeText('Editable Post');
});

test('users can update their own posts', function () {
    $user = User::factory()->create();
    $post = makePost($user, [
        'title' => 'Old Title',
        'content' => 'Old content',
        'is_published' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('posts.update', $post), [
            'title' => 'New Title',
            'category' => 'dc',
            'content' => 'New content',
            'is_published' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('posts.index'));

    $post->refresh();

    expect($post->title)->toBe('New Title');
    expect($post->category)->toBe('dc');
    expect($post->content)->toBe('New content');
    expect($post->slug)->toBe('new-title');
    expect($post->is_published)->toBe(1);
});

test('users can delete their own posts', function () {
    $user = User::factory()->create();
    $post = makePost($user);

    $response = $this
        ->actingAs($user)
        ->delete(route('posts.destroy', $post));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('posts.index'));

    expect($post->fresh())->toBeNull();
});

test('users cannot delete posts owned by others', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = makePost($otherUser);

    $response = $this
        ->actingAs($user)
        ->delete(route('posts.destroy', $post));

    $response->assertNotFound();
    expect($post->fresh())->not->toBeNull();
});

test('index can be filtered by search keyword', function () {
    $user = User::factory()->create();

    makePost($user, [
        'title' => 'Laravel Inertia Guide',
        'content' => 'All about Inertia pages',
    ]);

    makePost($user, [
        'title' => 'Vue Basics',
        'content' => 'Component lifecycle',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('posts.index', ['search' => 'Inertia']));

    $response->assertOk();
    $response->assertSeeText('Laravel Inertia Guide');
    $response->assertDontSeeText('Vue Basics');
});

test('updating post image replaces old image file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Storage::disk('public')->put('posts/old-cover.jpg', 'old-image');

    $post = makePost($user, [
        'image' => 'posts/old-cover.jpg',
    ]);

    $newImage = UploadedFile::fake()->image('new-cover.jpg');

    $response = $this
        ->actingAs($user)
        ->put(route('posts.update', $post), [
            'title' => 'Updated with image',
            'category' => 'marvel',
            'content' => 'Post with a new image',
            'is_published' => true,
            'image' => $newImage,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('posts.index'));

    $post->refresh();

    expect($post->image)->not->toBe('posts/old-cover.jpg');
    Storage::disk('public')->assertMissing('posts/old-cover.jpg');
    Storage::disk('public')->assertExists($post->image);
});

test('deleting a post removes its image file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Storage::disk('public')->put('posts/delete-me.jpg', 'image-content');

    $post = makePost($user, [
        'image' => 'posts/delete-me.jpg',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('posts.destroy', $post));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('posts.index'));

    expect($post->fresh())->toBeNull();
    Storage::disk('public')->assertMissing('posts/delete-me.jpg');
});

test('index is paginated with page metadata', function () {
    $user = User::factory()->create();

    foreach (range(1, 12) as $number) {
        makePost($user, [
            'title' => sprintf('Page Test %03d', $number),
            'created_at' => now()->addSeconds($number),
        ]);
    }

    $firstPage = $this
        ->actingAs($user)
        ->get(route('posts.index'));

    $firstPage->assertOk();
    $firstPage->assertSeeText('Page 1 / 2');
    $firstPage->assertSeeText('Showing 1 to 10 of 12 posts');
    $firstPage->assertSeeText('Page Test 001');
});
