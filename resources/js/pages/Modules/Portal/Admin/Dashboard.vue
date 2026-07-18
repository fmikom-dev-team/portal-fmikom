<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
	ArrowDown,
	ArrowUp,
	ChevronDown,
	ExternalLink,
	FileText,
	Folder,
	Image as ImageIcon,
	MessageCircle,
	MoreHorizontal,
	Calendar,
} from "lucide-vue-next";
import { computed } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const page = usePage();
const userName = computed(() => (page.props as any).auth?.user?.name || "Admin");

const greetingMessage = computed(() => {
	const hour = new Date().getHours();
	if (hour >= 5 && hour < 11) return "Selamat Pagi";
	if (hour >= 11 && hour < 15) return "Selamat Siang";
	if (hour >= 15 && hour < 18) return "Selamat Sore";
	return "Selamat Malam";
});

const currentFormattedDate = computed(() => {
	return new Intl.DateTimeFormat("id-ID", {
		weekday: "long",
		day: "numeric",
		month: "long",
		year: "numeric"
	}).format(new Date());
});

const props = defineProps({
	stats: {
		type: Object,
		default: () => ({
			totalPosts: 0,
			totalEvents: 0,
			totalMedia: 0,
			pendingComments: 0,
		}),
	},
	latestPosts: {
		type: Array as () => Array<any>,
		default: () => [],
	},
	recentComments: {
		type: Array as () => Array<any>,
		default: () => [],
	},
});

const formatDate = (dateStr: string) => {
	if (!dateStr) return "-";
	return new Intl.DateTimeFormat("id-ID", {
		day: "numeric",
		month: "short",
	}).format(new Date(dateStr));
};

const statusColor = (status: string) => {
	const map: Record<string, string> = {
		published: "text-emerald-500",
		draft: "text-amber-500",
		pending: "text-amber-500",
		approved: "text-emerald-500",
		spam: "text-rose-500",
		rejected: "text-rose-500",
	};
	return map[status?.toLowerCase()] || "text-slate-400";
};

const statusLabel = (status: string) => {
	const map: Record<string, string> = {
		published: "Published",
		draft: "Draft",
		pending: "Pending",
		approved: "Approved",
		spam: "Spam",
		rejected: "Rejected",
	};
	return map[status?.toLowerCase()] || status;
};
</script>

<template>
    <PortalAdminLayout title="Dashboard">

        <!-- Welcome Greeting -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ greetingMessage }}, {{ userName }}!
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 font-medium">
                    Selamat datang kembali di Panel Admin Portal FMIKOM. Berikut ringkasan performa situs Anda hari ini.
                </p>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-[12px] font-bold bg-blue-50 dark:bg-blue-500/10 text-[#2563EB] dark:text-blue-400 border border-blue-100/50 dark:border-blue-500/20">
                    {{ currentFormattedDate }}
                </span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-8">
            <!-- Total Posts -->
            <Link href="/portal-admin/posts" class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                <div class="w-9 h-9 bg-blue-50 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mb-4 text-[#2563EB]">
                    <FileText class="w-4 h-4"/>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-[12px] font-bold mb-1">Total Posts</p>
                <h2 class="text-slate-900 dark:text-white text-[28px] font-black mb-4 tracking-tight">{{ stats.totalPosts }}</h2>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-[11px] font-bold">Semua waktu</span>
                    <span class="text-[#2563EB] text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        Lihat <ExternalLink class="w-3 h-3"/>
                    </span>
                </div>
            </Link>

            <!-- Total Events -->
            <Link href="/portal-admin/events" class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl flex items-center justify-center mb-4 text-indigo-500">
                    <Calendar class="w-4 h-4"/>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-[12px] font-bold mb-1">Total Event</p>
                <h2 class="text-slate-900 dark:text-white text-[28px] font-black mb-4 tracking-tight">{{ stats.totalEvents }}</h2>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-[11px] font-bold">Semua waktu</span>
                    <span class="text-indigo-500 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        Lihat <ExternalLink class="w-3 h-3"/>
                    </span>
                </div>
            </Link>

            <!-- Total Media -->
            <Link href="/portal-admin/media" class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                <div class="w-9 h-9 bg-violet-50 dark:bg-violet-500/10 rounded-xl flex items-center justify-center mb-4 text-violet-500">
                    <ImageIcon class="w-4 h-4"/>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-[12px] font-bold mb-1">Total Media</p>
                <h2 class="text-slate-900 dark:text-white text-[28px] font-black mb-4 tracking-tight">{{ stats.totalMedia }}</h2>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-[11px] font-bold">Semua waktu</span>
                    <span class="text-violet-500 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        Lihat <ExternalLink class="w-3 h-3"/>
                    </span>
                </div>
            </Link>

            <!-- Pending Comments -->
            <Link href="/portal-admin/comments" class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                <div class="w-9 h-9 bg-amber-50 dark:bg-amber-500/10 rounded-xl flex items-center justify-center mb-4 text-amber-500">
                    <MessageCircle class="w-4 h-4"/>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-[12px] font-bold mb-1">Komentar Pending</p>
                <h2 class="text-slate-900 dark:text-white text-[28px] font-black mb-4 tracking-tight">{{ stats.pendingComments }}</h2>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-[11px] font-bold">Menunggu moderasi</span>
                    <span v-if="stats.pendingComments > 0" class="bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-black px-2 py-0.5 rounded-full">
                        Perlu ditinjau
                    </span>
                </div>
            </Link>
        </div>

        <!-- Tables Area -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-6">

            <!-- Latest Posts -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-[16px] font-black text-slate-900 dark:text-white">Postingan Terbaru</h3>
                    <Link href="/portal-admin/posts" class="text-[#2563EB] text-[12px] font-bold hover:underline">Lihat semua →</Link>
                </div>

                <div v-if="latestPosts.length === 0" class="flex flex-col items-center justify-center py-14 text-slate-400">
                    <FileText class="w-10 h-10 mb-3 opacity-30"/>
                    <p class="text-[13px] font-bold">Belum ada postingan</p>
                    <Link href="/portal-admin/posts/create" class="mt-3 text-[#2563EB] text-[12px] font-bold hover:underline">+ Buat postingan pertama</Link>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <caption class="sr-only">Tabel Postingan Terbaru</caption>
                        <thead>
                            <tr class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-6 py-3 font-black">Judul</th>
                                <th class="px-3 py-3 font-black">Status</th>
                                <th class="px-3 py-3 font-black">Tanggal</th>
                                <th class="px-3 py-3 font-black text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="post in latestPosts" :key="post.id"
                                class="border-t border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/60 dark:hover:bg-slate-700/20 transition-colors">
                                <td class="px-6 py-3.5 text-[13px] font-bold text-slate-800 dark:text-slate-200 max-w-[180px] truncate">
                                    {{ post.title }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <span :class="['text-[12px] font-bold', post.is_published ? 'text-emerald-500' : 'text-amber-500']">
                                        {{ post.is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ formatDate(post.created_at) }}
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <Link :href="`/portal-admin/posts/${post.id}/edit`" class="text-slate-400 hover:text-[#2563EB] transition-colors">
                                        <MoreHorizontal class="w-4 h-4 inline-block"/>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Comments -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-[16px] font-black text-slate-900 dark:text-white">Komentar Terbaru</h3>
                    <Link href="/portal-admin/comments" class="text-[#2563EB] text-[12px] font-bold hover:underline">Lihat semua →</Link>
                </div>

                <div v-if="recentComments.length === 0" class="flex flex-col items-center justify-center py-14 text-slate-400">
                    <MessageCircle class="w-10 h-10 mb-3 opacity-30"/>
                    <p class="text-[13px] font-bold">Belum ada komentar</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <caption class="sr-only">Tabel Komentar Terbaru</caption>
                        <thead>
                            <tr class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-6 py-3 font-black">Pengirim</th>
                                <th class="px-3 py-3 font-black">Komentar</th>
                                <th class="px-3 py-3 font-black">Tanggal</th>
                                <th class="px-3 py-3 font-black text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="comment in recentComments" :key="comment.id"
                                class="border-t border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/60 dark:hover:bg-slate-700/20 transition-colors">
                                <td class="px-6 py-3.5 text-[13px] font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                    {{ comment.author_name || 'Anonymous' }}
                                </td>
                                <td class="px-3 py-3.5 text-[12px] font-medium text-slate-500 dark:text-slate-400 max-w-[180px] truncate">
                                    "{{ comment.content }}"
                                </td>
                                <td class="px-3 py-3.5 text-[12px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ formatDate(comment.created_at) }}
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <Link href="/portal-admin/comments" class="bg-blue-50 hover:bg-[#2563EB] hover:text-white dark:bg-blue-500/10 dark:hover:bg-blue-500 text-[#2563EB] dark:text-blue-400 text-[11px] font-black px-3 py-1.5 rounded-full transition-colors inline-block">
                                        Tinjau
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </PortalAdminLayout>
</template>
