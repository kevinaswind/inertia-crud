<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Table,
    TableBody,
    TableCaption,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import TableCell from '@/components/ui/table/TableCell.vue';
import posts from '@/routes/posts';
import type { Post } from '@/types/app';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Posts',
                href: posts.index(),
            },
        ],
    },
});

const page = usePage();
const myPosts = computed<Post[]>(() => page.props.posts);
</script>

<template>
    <Head title="Posts" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div
            class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 p-5 md:min-h-min dark:border-sidebar-border"
        >
            <Table>
                <TableCaption>A list of your recent posts.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>Image</TableHead>
                        <TableHead>Title</TableHead>
                        <TableHead>Content</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="post in myPosts" :key="post.id">
                        <TableCell>{{ post.id }}</TableCell>
                        <TableCell>Image</TableCell>
                        <TableCell>{{ post.title }}</TableCell>
                        <TableCell>{{ post.content }}</TableCell>
                        <TableCell>{{ post.category }}</TableCell>
                        <TableCell>{{
                            post.is_published ? 'Published' : 'Draft'
                        }}</TableCell>
                        <TableCell>Action</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
