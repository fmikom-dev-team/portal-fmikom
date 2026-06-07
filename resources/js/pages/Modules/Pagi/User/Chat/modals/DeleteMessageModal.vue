<script setup lang="ts">
defineProps<{
	show: boolean;
	isSender: boolean;
}>();

defineEmits<{
	(e: "delete-for-everyone"): void;
	(e: "delete-for-me"): void;
	(e: "close"): void;
}>();
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-[999] flex items-center justify-center p-4" @click.self="$emit('close')">
            <!-- Backdrop blur subtle -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" @click="$emit('close')"></div>
            <!-- Modal card -->
            <div class="relative bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-800/80 rounded-2xl w-full max-w-xs overflow-hidden shadow-2xl select-none z-10">
                <!-- Header -->
                <div class="px-5 pt-5 pb-4 text-center border-b border-slate-100 dark:border-zinc-800/80">
                    <h3 class="text-[15px] font-black text-slate-900 dark:text-zinc-100 tracking-tight">Hapus Pesan?</h3>
                </div>
                <!-- Action Buttons — vertical WhatsApp style -->
                <div class="flex flex-col divide-y divide-slate-100 dark:divide-zinc-800/80">
                    <!-- Hapus untuk saya -->
                    <button
                        @click="$emit('delete-for-me')"
                        class="group w-full px-5 py-4 text-left flex items-center gap-3.5 hover:bg-slate-50 dark:hover:bg-zinc-900/60 transition-colors"
                    >
                        <span class="flex-shrink-0 w-9 h-9 rounded-full bg-slate-100 dark:bg-zinc-800/70 flex items-center justify-center text-slate-600 dark:text-zinc-300 group-hover:bg-slate-200 dark:group-hover:bg-zinc-800 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-black text-slate-800 dark:text-zinc-100 leading-tight">Hapus untuk saya</span>
                            <span class="block text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 font-medium leading-tight">Hanya dihapus dari tampilan Anda</span>
                        </span>
                    </button>
                    <!-- Hapus untuk semua orang — only sender can do this -->
                    <button
                        v-if="isSender"
                        @click="$emit('delete-for-everyone')"
                        class="group w-full px-5 py-4 text-left flex items-center gap-3.5 hover:bg-red-50/70 dark:hover:bg-red-950/20 transition-colors"
                    >
                        <span class="flex-shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 group-hover:bg-red-200 dark:group-hover:bg-red-950/60 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 19l-7-7 7-7"/></svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-black text-red-600 dark:text-red-400 leading-tight">Hapus untuk semua orang</span>
                            <span class="block text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 font-medium leading-tight">Pesan dihapus di semua perangkat</span>
                        </span>
                    </button>
                    <!-- Batal -->
                    <button
                        @click="$emit('close')"
                        class="w-full px-5 py-4 text-center text-sm font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-900/60 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
