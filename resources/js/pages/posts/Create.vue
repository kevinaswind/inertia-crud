<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { CardContent } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import posts from '@/routes/posts';

const imagePreviewUrl = ref<string | null>(null);

const handleImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }

    if (file && file.type.startsWith('image/')) {
        imagePreviewUrl.value = URL.createObjectURL(file);
    } else {
        imagePreviewUrl.value = null;
    }
};

onUnmounted(() => {
    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }
});
</script>

<template>
    <Head title="Create a new post" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-8"
    >
        <div class="mb-6 flex items-center justify-between">
            <div class="w-1/3">
                <h2 class="text-xl font-medium text-slate-600">
                    Create a new post
                </h2>
            </div>
            <Button as-child>
                <Link :href="posts.index()">Back to Posts</Link>
            </Button>
        </div>
        <Card class="w-full">
            <CardContent>
                <Form
                    :action="posts.store()"
                    method="post"
                    class="flex flex-col gap-4"
                    #default="{ processing, isDirty }"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="col-span-2">
                            <Label for="title" class="mb-2"> Title </Label>
                            <Input id="title" name="title" type="text" />
                            <InputError
                                :message="$page.props.errors.title"
                                class="mt-2"
                            />
                        </div>
                        <div>
                            <Label for="category" class="mb-2">
                                Category
                            </Label>
                            <Select name="category" id="category">
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select a category"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Categories</SelectLabel>
                                        <SelectItem value="marvel">
                                            Marvel
                                        </SelectItem>
                                        <SelectItem value="dc"> DC </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="$page.props.errors.category"
                                class="mt-2"
                            />
                        </div>
                        <div>
                            <Label for="is_published" class="mb-2">
                                Status
                            </Label>
                            <Select name="is_published" id="is_published">
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select a status"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Status</SelectLabel>
                                        <SelectItem value="1">
                                            Published
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Unpublished
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="$page.props.errors.is_published"
                                class="mt-2"
                            />
                        </div>
                        <div class="col-span-2">
                            <Label for="content" class="mb-2"> Content </Label>
                            <Textarea id="content" name="content" type="text" />
                            <InputError
                                :message="$page.props.errors.content"
                                class="mt-2"
                            />
                        </div>
                        <div class="col-span-2">
                            <Label for="image" class="mb-2">
                                Cover Image
                            </Label>
                            <Input
                                id="image"
                                name="image"
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                            />
                            <div
                                v-if="imagePreviewUrl"
                                class="mt-3 overflow-hidden rounded-md border"
                            >
                                <img
                                    :src="imagePreviewUrl"
                                    alt="Selected cover preview"
                                    class="h-auto w-40 object-cover"
                                />
                            </div>
                            <InputError
                                :message="$page.props.errors.image"
                                class="mt-2"
                            />
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <Button type="submit" :disabled="processing">
                                {{ processing ? 'Creating...' : 'Create Post' }}
                            </Button>
                            <span
                                v-if="isDirty"
                                class="text-sm text-muted-foreground"
                                >Unsaved changes</span
                            >
                        </div>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
