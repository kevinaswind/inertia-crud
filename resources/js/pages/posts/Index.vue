<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import {
    SearchIcon,
    EllipsisVerticalIcon,
    ViewIcon,
    PencilLineIcon,
    Trash2Icon,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { CardContent } from '@/components/ui/card';
import Card from '@/components/ui/card/Card.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
const myPosts = computed<Post[]>(() => {
    const payload = page.props.posts as { data?: Post[] } | Post[] | undefined;

    if (Array.isArray(payload)) {
        return payload;
    }

    return payload?.data ?? [];
});

const pagination = computed(() => {
    const payload = page.props.posts as
        | {
              current_page?: number;
              last_page?: number;
              total?: number;
              from?: number | null;
              to?: number | null;
          }
        | undefined;

    return {
        currentPage: payload?.current_page ?? 1,
        lastPage: payload?.last_page ?? 1,
        total: payload?.total ?? myPosts.value.length,
        from: payload?.from ?? (myPosts.value.length ? 1 : null),
        to: payload?.to ?? (myPosts.value.length ? myPosts.value.length : null),
    };
});

const paginationItems = computed<Array<number | string>>(() => {
    const current = pagination.value.currentPage;
    const last = pagination.value.lastPage;

    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    if (current <= 4) {
        return [1, 2, 3, 4, 5, '...', last];
    }

    if (current >= last - 3) {
        return [1, '...', last - 4, last - 3, last - 2, last - 1, last];
    }

    return [1, '...', current - 1, current, current + 1, '...', last];
});
const search = ref<string>(
    (page.props.filters as { search?: string } | undefined)?.search ?? '',
);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const runSearch = () => {
    router.get(
        posts.index().url,
        {
            search: search.value || undefined,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const handleSearch = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        runSearch();
    }, 300);
};

const clearSearch = () => {
    if (!search.value) {
        return;
    }

    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }

    search.value = '';
    runSearch();
};

const goToPage = (pageNumber: number) => {
    if (
        pageNumber < 1 ||
        pageNumber > pagination.value.lastPage ||
        pageNumber === pagination.value.currentPage
    ) {
        return;
    }

    router.get(
        posts.index().url,
        {
            search: search.value || undefined,
            page: pageNumber,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(
    () => (page.props.filters as { search?: string } | undefined)?.search,
    (value) => {
        search.value = value ?? '';
    },
);

const handleDelete = (postId: string) => {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    router.delete(posts.destroy(postId).url, {
        preserveScroll: true,
    });
};
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
                        v-model="search"
                        aria-label="Search"
                        placeholder="Search"
                        type="search"
                        @input="handleSearch"
                        @keydown.esc.prevent="clearSearch"
                    />
                    <InputGroupAddon v-if="search" align="inline-end">
                        <button
                            type="button"
                            class="text-xs text-muted-foreground hover:text-foreground"
                            @click="clearSearch"
                        >
                            Clear
                        </button>
                    </InputGroupAddon>
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
                        <TableRow
                            v-for="(post, index) in myPosts"
                            :key="post.id"
                        >
                            <TableCell>
                                {{ (pagination.from ?? 1) + index }}
                            </TableCell>
                            <TableCell>
                                <img
                                    :src="post.image ?? ''"
                                    alt="Post cover"
                                    class="h-auto w-16 rounded-sm object-cover"
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
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            class="h-8 w-8 p-0"
                                        >
                                            <EllipsisVerticalIcon />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        class="w-56"
                                        align="start"
                                    >
                                        <DropdownMenuLabel
                                            >My Post</DropdownMenuLabel
                                        >
                                        <DropdownMenuGroup>
                                            <DropdownMenuItem :as-child="true">
                                                <Link
                                                    :href="
                                                        posts.show(post.id).url
                                                    "
                                                    class="block w-full cursor-pointer"
                                                >
                                                    <ViewIcon
                                                        class="me-2 h-4 w-4"
                                                    />
                                                    View Post
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem :as-child="true">
                                                <Link
                                                    :href="
                                                        posts.edit(post.id).url
                                                    "
                                                    class="block w-full cursor-pointer"
                                                >
                                                    <PencilLineIcon
                                                        class="me-2 h-4 w-4"
                                                    />
                                                    Edit Post
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuGroup>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="text-rose-500"
                                            @click="handleDelete(post.id)"
                                        >
                                            <Trash2Icon class="me-2 h-4 w-4" />
                                            Delete Post
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="myPosts.length === 0">
                            <TableCell
                                colspan="7"
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                <p>No posts found.</p>
                                <Button
                                    v-if="search"
                                    variant="link"
                                    class="mt-2 h-auto p-0"
                                    @click="clearSearch"
                                >
                                    Clear search and show all posts
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div
                    v-if="pagination.total > 0"
                    class="mt-4 flex flex-col gap-3 border-t pt-4 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
                >
                    <p>
                        Showing
                        {{ pagination.from ?? 0 }}
                        to
                        {{ pagination.to ?? 0 }}
                        of
                        {{ pagination.total }}
                        posts
                    </p>

                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="pagination.currentPage <= 1"
                            @click="goToPage(pagination.currentPage - 1)"
                        >
                            Previous
                        </Button>

                        <div class="flex items-center gap-1">
                            <template
                                v-for="(item, itemIndex) in paginationItems"
                                :key="`${item}-${itemIndex}`"
                            >
                                <span
                                    v-if="item === '...'"
                                    class="px-1 text-xs text-muted-foreground"
                                >
                                    ...
                                </span>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    :class="
                                        item === pagination.currentPage
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : ''
                                    "
                                    @click="goToPage(item as number)"
                                >
                                    {{ item }}
                                </Button>
                            </template>
                        </div>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                pagination.currentPage >= pagination.lastPage
                            "
                            @click="goToPage(pagination.currentPage + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
