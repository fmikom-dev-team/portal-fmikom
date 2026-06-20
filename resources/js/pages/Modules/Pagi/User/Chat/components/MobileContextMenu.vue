<script setup lang="ts">
import { Archive, CheckCheck, Pin, Trash2 } from "lucide-vue-next";
import type { Conversation } from "../types";
import { avatarUrl } from "../utils";

defineProps<{
	show: boolean;
	mobileMenuConv: Conversation | null;
}>();

defineEmits<{
	(e: "close"): void;
	(e: "toggle-read-status", conv: Conversation): void;
	(e: "toggle-archive", conv: Conversation): void;
	(e: "toggle-pin", conv: Conversation): void;
	(e: "delete-conversation", conv: Conversation): void;
}>();
</script>

<template>
    <!-- Mobile Context Menu (Bottom Sheet) -->
    <div v-if="show && mobileMenuConv" class="fixed inset-0 z-[999] flex items-end justify-center">
        <!-- Overlay (no blur) -->
        <div class="fixed inset-0 bg-black/60" @click="$emit('close')"></div>
        
        <!-- Bottom Sheet content -->
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-t-2xl overflow-hidden shadow-2xl py-2 animate-in slide-in-from-bottom duration-250 select-none pb-safe">
            <!-- Drag handle indicator -->
            <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-800 rounded-full mx-auto my-2"></div>
            
            <div class="px-4 py-2.5 border-b border-slate-150 dark:border-zinc-800 flex items-center gap-3">
                <div class="h-8 w-8 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-850 flex items-center justify-center">
                    <img v-if="avatarUrl(mobileMenuConv.foto_path)" :src="avatarUrl(mobileMenuConv.foto_path)!" :alt="mobileMenuConv.name" class="w-full h-full object-cover" />
                    <span v-else class="text-xs font-black text-slate-500">{{ mobileMenuConv.name.charAt(0) }}</span>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate">{{ mobileMenuConv.name }}</h4>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-500 truncate">{{ mobileMenuConv.last_message }}</p>
                </div>
            </div>

            <div class="flex flex-col py-1">
                <button 
                    @click="$emit('toggle-read-status', mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <CheckCheck class="w-4 h-4 text-slate-400" />
                    <span>{{ (mobileMenuConv.unread_count > 0 || mobileMenuConv.is_manual_unread) ? 'Tandai sudah dibaca' : 'Tandai belum dibaca' }}</span>
                </button>
                
                <button 
                    @click="$emit('toggle-archive', mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <Archive class="w-4 h-4 text-slate-400" />
                    <span>{{ mobileMenuConv.is_archived ? 'Buka arsip' : 'Arsipkan' }}</span>
                </button>
                
                <button 
                    @click="$emit('toggle-pin', mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <Pin class="w-4 h-4 text-slate-400 rotate-45" />
                    <span>{{ mobileMenuConv.is_pinned ? 'Lepas sematan' : 'Sematkan' }}</span>
                </button>
                
                <hr class="my-1.5 border-slate-100 dark:border-zinc-850" />
                
                <button 
                    @click="$emit('delete-conversation', mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-black text-red-650 dark:text-red-450 active:bg-red-50 dark:active:bg-red-955/20 transition-colors"
                >
                    <Trash2 class="w-4 h-4 text-red-500" />
                    <span>Hapus obrolan</span>
                </button>
            </div>
            
            <div class="px-4 py-2 mt-1 border-t border-slate-100 dark:border-zinc-850">
                <button 
                    @click="$emit('close')"
                    class="w-full py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-850 active:scale-95 transition-all"
                >
                    Batal
                </button>
            </div>
        </div>
    </div>
</template>
