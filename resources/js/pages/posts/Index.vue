<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import { SearchIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { CardContent } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
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
import Badge from '@/components/ui/badge/Badge.vue';

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
const myPosts = computed<Post[]>(() => (page.props.posts as Post[]) ?? []);
console.log(page.flash.message);
</script>

<template>
    <Head title="Posts" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-8"
    >
        <div class="mb-6 flex items-center justify-between">
            <div class="w-1/3">
                <InputGroup class="h-10">
                    <InputGroupAddon>
                        <SearchIcon aria-hidden="true" />
                    </InputGroupAddon>
                    <InputGroupInput
                        aria-label="Search"
                        placeholder="Search"
                        type="search"
                    />
                </InputGroup>
            </div>

            <Button as-child>
                <Link :href="posts.create()">Create Post</Link>
            </Button>
        </div>
        <Card class="w-full">
            <CardContent>
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
                            <TableCell>
                                <img
                                    :src="post.image"
                                    alt="Post cover"
                                    class="h-auto w-40 object-cover"
                                />
                            </TableCell>
                            <TableCell>{{ post.title }}</TableCell>
                            <TableCell>{{ post.content }}</TableCell>
                            <TableCell>{{
                                post.category.toUpperCase()
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :class="
                                        post.is_published
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    "
                                >
                                    {{
                                        post.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell>Action</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
