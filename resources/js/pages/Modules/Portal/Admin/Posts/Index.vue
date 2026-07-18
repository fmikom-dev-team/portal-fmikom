<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import {
	Edit,
	Eye,
	FileText,
	Plus,
	Search,
	Trash2,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	posts: {
		type: Object,
		default: () => ({ data: [] }),
	},
	filters: {
		type: Object,
		default: () => ({ search: "" }),
	},
});

// Search and filter states
const localSearch = ref(props.filters.search || "");
const selectedStatus = ref("all");

// Map paginator list
const postsList = computed(() => props.posts.data || []);

import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue";

const isDeleteModalOpen = ref(false);
const deleteId = ref<number | null>(null);

const confirmDelete = (id: number) => {
	deleteId.value = id;
	isDeleteModalOpen.value = true;
};

const handleDeleteConfirm = () => {
	if (deleteId.value !== null) {
		router.delete(`/portal-admin/posts/${deleteId.value}`, {
			onFinish: () => {
				isDeleteModalOpen.value = false;
				deleteId.value = null;
			}
		});
	}
};

// Filtered posts logic
const filteredPosts = computed(() => {
	return postsList.value.filter((post: any) => {
		// Status filter
		if (
			selectedStatus.value !== "all" &&
			post.status !== selectedStatus.value
		) {
			return false;
		}
		return true;
	});
});

// Stats counters (based on current page)
const countAll = computed(() => postsList.value.length);
const countPublished = computed(
	() => postsList.value.filter((p: any) => p.status === "published").length,
);
const countScheduled = computed(
	() => postsList.value.filter((p: any) => p.status === "scheduled").length,
);
const countDraft = computed(
	() => postsList.value.filter((p: any) => p.status === "draft").length,
);

const getFormattedDate = (dateStr: string) => {
	if (!dateStr) return "Draf";
	const date = new Date(dateStr);
	return date.toLocaleDateString("id-ID", {
		day: "numeric",
		month: "short",
	});
};

// Watch search to reload from server live
watch(localSearch, (newSearch) => {
	router.get(
		"/portal-admin/posts",
		{ search: newSearch },
		{ preserveState: true, replace: true }
	);
});
</script>

<template>
    <PortalAdminLayout title="Manajemen Postingan">
        <!-- Compact Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    Postingan
                    <span class="text-xs font-bold text-slate-400">({{ posts.total || 0 }})</span>
                </h2>
            </div>
            
            <Link href="/portal-admin/posts/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2 active:scale-95 shrink-0 select-none">
                <Plus class="w-4 h-4" />
                Postingan Baru
            </Link>
        </div>

        <!-- Filter & Search Toolbar (Sleek and Narrow) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 border border-slate-150 dark:border-slate-800 shadow-sm mb-4 flex flex-col md:flex-row gap-3 items-center justify-between">
            <!-- Tabs (Filtering) -->
            <div class="flex items-center gap-1 w-full md:w-auto overflow-x-auto scrollbar-none">
                <button 
                    @click="selectedStatus = 'all'"
                    :class="[
                        selectedStatus === 'all' 
                            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap'
                    ]"
                >
                    Semua ({{ countAll }})
                </button>
                <button 
                    @click="selectedStatus = 'published'"
                    :class="[
                        selectedStatus === 'published' 
                            ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap'
                    ]"
                >
                    Terbit ({{ countPublished }})
                </button>
                <button 
                    @click="selectedStatus = 'scheduled'"
                    :class="[
                        selectedStatus === 'scheduled' 
                            ? 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap'
                    ]"
                >
                    Terjadwal ({{ countScheduled }})
                </button>
                <button 
                    @click="selectedStatus = 'draft'"
                    :class="[
                        selectedStatus === 'draft' 
                            ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap'
                    ]"
                >
                    Draft ({{ countDraft }})
                </button>
            </div>

            <!-- Compact Search -->
            <div class="relative w-full md:w-[260px] shrink-0">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <Search class="w-3.5 h-3.5" />
                </span>
                <input 
                    v-model="localSearch"
                    type="text" 
                    placeholder="Telusuri postingan..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-400 text-slate-700 dark:text-white"
                />
            </div>
        </div>

        <!-- Space-saving List Layout -->
        <div class="space-y-2">
            <div 
                v-for="post in filteredPosts" 
                :key="post.id" 
                class="group bg-white dark:bg-slate-800 rounded-xl p-3 border border-slate-150 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-sm transition-all flex items-center gap-4"
            >
                <!-- Compact Square Thumbnail -->
                <div class="shrink-0">
                    <div class="w-14 h-14 md:w-16 md:h-12 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center shrink-0">
                        <img 
                            v-if="post.thumbnail" 
                            :src="post.thumbnail" 
                            class="w-full h-full object-cover" 
                            :alt="post.title"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-blue-50 dark:bg-blue-900/10 text-blue-500/60 font-black text-sm uppercase">
                            {{ post.title ? post.title.charAt(0) : 'P' }}
                        </div>
                    </div>
                </div>

                <!-- Mid-row Details (Titles & Subtitle Info) -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white truncate leading-snug group-hover:text-blue-600 transition-colors">
                            {{ post.title || '(Tanpa judul)' }}
                        </h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                        <!-- Status and Date label -->
                        <span v-if="post.status === 'published'" class="text-emerald-600 dark:text-emerald-450">Dipublikasikan</span>
                        <span v-else-if="post.status === 'scheduled'" class="text-indigo-600 dark:text-indigo-400">Terjadwal</span>
                        <span v-else class="text-amber-600 dark:text-amber-400">Draf</span>

                        <span>•</span>
                        
                        <span>{{ getFormattedDate(post.published_at || post.created_at) }}</span>

                        <template v-if="post.category">
                            <span>•</span>
                            <span class="bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700 px-1.5 py-0.5 rounded text-[10px]">
                                {{ post.category.name }}
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Right Side: Author, Views Count & Simple Actions -->
                <div class="flex items-center gap-4 shrink-0">
                    <!-- Author Info (Compact) -->
                    <div class="hidden md:flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ post.user?.name || 'Admin' }}</span>
                        <img 
                            :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(post.user?.name || 'Admin')}&backgroundColor=2563eb&textColor=ffffff`" 
                            class="w-5 h-5 rounded-full border border-blue-50 dark:border-slate-700 shrink-0"
                            alt="author"
                        >
                    </div>

                    <!-- Metrics/Views & Compact Actions -->
                    <div class="flex items-center gap-2">
                        <!-- Stat Indicators -->
                        <div class="flex items-center gap-3 text-slate-400 mr-2">
                            <div class="flex items-center gap-1" title="Views">
                                <Eye class="w-3.5 h-3.5" />
                                <span class="text-[11px] font-bold">{{ post.views_count || 0 }}</span>
                            </div>
                        </div>

                        <!-- Hover/Direct Action Buttons -->
                        <div class="flex items-center gap-0.5">
                            <Link 
                                :href="`/portal-admin/posts/${post.id}/edit`" 
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all" 
                                title="Edit"
                            >
                                <Edit class="w-4 h-4" />
                            </Link>
                            <button 
                                @click="confirmDelete(post.id)" 
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all" 
                                title="Hapus"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-if="filteredPosts.length === 0" 
                class="py-12 text-center bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700"
            >
                <FileText class="w-8 h-8 text-slate-300 mx-auto mb-2 opacity-55" />
                <p class="text-slate-800 dark:text-white text-xs font-bold">Tidak ada postingan ditemukan</p>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div v-if="posts.links && posts.links.length > 3" class="mt-6 flex items-center justify-between border-t border-slate-150 dark:border-slate-700 px-4 py-3 sm:px-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm">
            <div class="flex flex-1 justify-between sm:hidden">
                <Link :href="posts.prev_page_url || '#'" :class="[!posts.prev_page_url ? 'opacity-50 pointer-events-none' : '', 'relative inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-750 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-250']">Previous</Link>
                <Link :href="posts.next_page_url || '#'" :class="[!posts.next_page_url ? 'opacity-50 pointer-events-none' : '', 'relative ml-3 inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-750 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-250']">Next</Link>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Showing
                        <span class="font-bold">{{ posts.from || 0 }}</span>
                        to
                        <span class="font-bold">{{ posts.to || 0 }}</span>
                        of
                        <span class="font-bold">{{ posts.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm bg-white dark:bg-slate-800" aria-label="Pagination">
                        <Link
                            v-for="(link, i) in posts.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-offset-0',
                                !link.url ? 'opacity-50 pointer-events-none' : '',
                                'relative inline-flex items-center px-3 py-2 text-xs font-bold ring-1 ring-inset ring-slate-200 dark:ring-slate-700 focus:z-20 focus:outline-offset-0 transition-all'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
        <DeleteConfirmModal
            :show="isDeleteModalOpen"
            title="Hapus Postingan"
            message="Apakah Anda yakin ingin menghapus postingan ini? Postingan akan dihapus secara permanen dari portal."
            @confirm="handleDeleteConfirm"
            @cancel="isDeleteModalOpen = false"
        />
    </PortalAdminLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
