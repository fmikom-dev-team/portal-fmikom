<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	CheckCircle,
	MessageCircle,
	Trash2,
	User,
	XCircle,
} from "lucide-vue-next";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	comments: {
		type: Array as () => any[],
		default: () => [],
	},
});

const updateStatus = (id: number, status: string) => {
	router.put(`/portal-admin/comments/${id}`, { status });
};

const deleteComment = (id: number) => {
	if (confirm("Apakah Anda yakin ingin menghapus komentar ini?")) {
		router.delete(`/portal-admin/comments/${id}`);
	}
};
</script>

<template>
    <PortalAdminLayout title="Moderasi Komentar">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[20px] sm:text-[24px] font-black text-slate-900 dark:text-white tracking-tight">Komentar & Moderasi</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Moderasi komentar yang masuk dari pengunjung portal.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[1.25rem] shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <caption class="sr-only">Daftar Komentar</caption>
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                            <th class="py-4 px-4 sm:px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase">Penulis & Isi</th>
                            <th class="hidden sm:table-cell py-4 px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase w-32">Status</th>
                            <th class="py-4 px-4 sm:px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase text-right w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in comments" :key="c.id" class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                        <User class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[13px] font-black text-slate-800 dark:text-slate-200 truncate">{{ c.author_name }}</p>
                                            <span v-if="c.status === 'approved'" class="sm:hidden bg-emerald-100 text-emerald-700 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase">Approved</span>
                                            <span v-else-if="c.status === 'pending'" class="sm:hidden bg-amber-100 text-amber-700 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase">Pending</span>
                                            <span v-else class="sm:hidden bg-rose-100 text-rose-700 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase">Spam</span>
                                        </div>
                                        <p class="text-[12px] text-slate-600 dark:text-slate-400 leading-relaxed mt-1 break-words line-clamp-3">{{ c.content }}</p>
                                        <p class="text-[11px] font-bold text-blue-500 mt-2 truncate">Post: {{ c.post?.title }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell py-4 px-6">
                                <span v-if="c.status === 'approved'" class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Disetujui</span>
                                <span v-else-if="c.status === 'pending'" class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Menunggu</span>
                                <span v-else class="bg-rose-100 text-rose-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Spam</span>
                            </td>
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex justify-end gap-1 sm:gap-2">
                                    <button v-if="c.status !== 'approved'" @click="updateStatus(c.id, 'approved')" class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Setujui">
                                        <CheckCircle class="w-4 h-4" />
                                    </button>
                                    <button v-if="c.status !== 'spam'" @click="updateStatus(c.id, 'spam')" class="p-2 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" title="Tandai Spam">
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteComment(c.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="comments.length === 0">
                            <td colspan="4" class="py-16 text-center">
                                <MessageCircle class="w-10 h-10 text-slate-200 mx-auto mb-3" />
                                <p class="text-slate-500 text-[14px] font-bold">Belum ada komentar</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PortalAdminLayout>
</template>
