export type Post = {
    id: string;
    title: string;
    category: string;
    content: string;
    slug: string;
    image: string | null;
    is_published: boolean;
};
