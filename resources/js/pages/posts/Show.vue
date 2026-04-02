<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { CardContent } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import posts from '@/routes/posts';
import type { Post } from '@/types/app';

defineProps<{
    post: Post;
}>();
</script>

<template>
    <Head title="View Post" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-8"
    >
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-medium text-slate-600">Post detail</h2>

            <div class="flex items-center gap-2">
                <Button as-child variant="outline">
                    <Link :href="posts.index()">Back to Posts</Link>
                </Button>
                <Button as-child>
                    <Link :href="posts.edit(post.id).url">Edit Post</Link>
                </Button>
            </div>
        </div>

        <Card class="w-full">
            <CardContent class="space-y-6">
                <img
                    v-if="post.image"
                    :src="post.image"
                    alt="Post cover"
                    class="h-auto w-full max-w-md rounded-md object-cover"
                />

                <div class="space-y-2">
                    <p class="text-sm text-muted-foreground">Title</p>
                    <h3 class="text-2xl font-semibold">{{ post.title }}</h3>
                </div>

                <div class="flex items-center gap-3">
                    <Badge class="bg-slate-100 text-slate-800">
                        {{ post.category.toUpperCase() }}
                    </Badge>
                    <Badge
                        :class="
                            post.is_published
                                ? 'bg-green-100 text-green-800'
                                : 'bg-gray-100 text-gray-800'
                        "
                    >
                        {{ post.is_published ? 'Published' : 'Draft' }}
                    </Badge>
                </div>

                <div class="space-y-2">
                    <p class="text-sm text-muted-foreground">Content</p>
                    <p class="whitespace-pre-wrap text-slate-800">
                        {{ post.content }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
