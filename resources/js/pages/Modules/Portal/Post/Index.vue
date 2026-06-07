<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { Search } from "lucide-vue-next";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

const props = defineProps({
	posts: Object,
});

const formatDate = (dateString) => {
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
};

const extractText = (content) => {
	if (!content) return "";
	try {
		const parsed = JSON.parse(content);
		if (parsed?.blocks && Array.isArray(parsed.blocks)) {
			const textContent = parsed.blocks
				.filter(
					(b) =>
						b.type === "paragraph" || b.type === "header" || b.type === "list",
				)
				.map((b) => {
					if (b.type === "list" && b.data?.items) {
						return b.data.items
							.map((item) =>
								typeof item === "string" ? item : item.content || "",
							)
							.join(" ");
					}
					return b.data?.text || "";
				})
				.join(" ");
			return textContent.replace(/<[^>]*>?/gm, "").trim();
		}
	} catch (_e) {
		// Not JSON
	}
	return content.replace(/<[^>]*>?/gm, "").trim();
};

const getAvatarUrl = (user) => {
	if (!user?.foto_path) return null;
	return user.foto_path.startsWith("http")
		? user.foto_path
		: `/storage/${user.foto_path}`;
};
</script>

<template>
    <Head title="Berita Terkini - Portal FMIKOM"></Head>

    <div class="min-h-screen bg-white font-sans antialiased text-slate-900">
        <!-- Navigation -->
        <PublicNavbar />

        <main class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-16 text-center max-w-3xl mx-auto">
                    <h1 class="text-4xl lg:text-6xl font-black text-slate-900 mb-6 tracking-tight">Kabar Terbaru FMIKOM</h1>
                    <p class="text-lg text-slate-500 leading-relaxed font-medium">Temukan informasi akademik, prestasi mahasiswa, dan update kegiatan kampus dalam satu wadah.</p>
                </div>

                <!-- Posts Grid -->
                <div v-if="posts.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-x-8 sm:gap-y-12">
                    <Link v-for="post in posts.data" :key="post.id" :href="'/berita/' + post.slug" class="group flex flex-col bg-white transition-all duration-300">
                        <!-- Thumbnail -->
                        <div class="w-full aspect-[16/11] rounded-xl overflow-hidden bg-slate-100 border border-slate-100/60 relative mb-4">
                            <img v-if="post.thumbnail" :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400">
                                <svg class="w-12 h-12 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm-8 12v-3l4-4 4.5 4.5L19 14v7H6z"/></svg>
                            </div>
                        </div>
                        
                        <!-- Card Body -->
                        <div class="flex-grow flex flex-col justify-between space-y-4">
                            <div class="space-y-3">
                                <!-- Category Badge/Pill -->
                                <div v-if="post.category?.name">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 transition-colors">
                                        {{ post.category.name }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h4 class="text-[17px] sm:text-[19px] font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2 tracking-tight text-pretty">
                                    {{ post.title }}
                                </h4>

                                <!-- Description -->
                                <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed font-normal">
                                    {{ post.meta_description || extractText(post.content).substring(0, 150) + '...' }}
                                </p>
                            </div>
                            
                            <!-- Author Info Footer -->
                            <div class="flex items-center gap-3 pt-2">
                                <Avatar class="h-9 w-9 border border-slate-200 shrink-0">
                                    <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                                    <AvatarFallback>{{ (post.user?.name || 'Admin').charAt(0).toUpperCase() }}</AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col text-left">
                                    <span class="text-xs font-bold text-slate-800 leading-none mb-1">{{ post.user?.name || 'Admin' }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ formatDate(post.published_at || post.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-32 bg-white rounded-[3rem] border border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                        <Search class="w-10 h-10"></Search>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2 tracking-tight">Belum Ada Berita</h3>
                    <p class="text-slate-400">Silakan kembali lagi nanti untuk mendapatkan update terbaru.</p>
                </div>

                <!-- Pagination -->
                <div v-if="posts.links.length > 3" class="mt-20 flex justify-center gap-2">
                    <template v-for="(link, k) in posts.links" :key="k">
                        <div v-if="link.url === null" class="px-4 py-2 text-sm font-bold text-slate-300 bg-white border border-slate-50 rounded-xl cursor-default" v-html="link.label"></div>
                        <Link v-else :href="link.url" 
                            class="px-4 py-2 text-sm font-bold rounded-xl transition-all border"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-100' : 'bg-white text-slate-500 border-slate-100 hover:border-blue-600 hover:text-blue-600'">
                            <span v-html="link.label"></span>
                        </Link>
                    </template>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <PublicFooter />
    </div>
</template>
