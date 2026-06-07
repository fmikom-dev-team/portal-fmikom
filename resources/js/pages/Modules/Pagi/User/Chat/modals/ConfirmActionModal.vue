<script setup lang="ts">
defineProps<{
	show: boolean;
	type: "clear" | "block" | "unblock" | "delete-conversation";
}>();

defineEmits<{
	(e: "confirm"): void;
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
        <div v-if="show" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/60" @click.self="$emit('close')">
            <div class="bg-white dark:bg-zinc-955 border border-slate-200 dark:border-zinc-800 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl p-6 flex flex-col items-center text-center select-none animate-in fade-in zoom-in-95 duration-200">
                <!-- Icon box -->
                <div :class="[
                    'p-4 rounded-full mb-4 shadow-sm flex items-center justify-center',
                    type === 'unblock' 
                        ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 dark:text-emerald-450' 
                        : 'bg-red-50 dark:bg-red-950/30 text-red-500 dark:text-red-450'
                ]">
                    <!-- Clear Trash Icon / Delete Conversation -->
                    <svg v-if="type === 'clear' || type === 'delete-conversation'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <!-- Block Icon -->
                    <svg v-else-if="type === 'block'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <!-- Shield/Unblock Icon -->
                    <svg v-else-if="type === 'unblock'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                    </svg>
                </div>

                <!-- Text content -->
                <h3 class="text-base font-black text-slate-905 dark:text-zinc-100 mb-2">
                    {{ 
                        type === 'clear' ? 'Hapus Semua Pesan?' 
                        : type === 'block' ? 'Blokir Pengguna ini?' 
                        : type === 'delete-conversation' ? 'Hapus Obrolan?'
                        : 'Buka Blokir Pengguna?' 
                    }}
                </h3>
                <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400 mb-6 leading-relaxed">
                    {{ 
                        type === 'clear' ? 'Apakah Anda yakin ingin menghapus semua pesan dalam obrolan ini? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.' 
                        : type === 'block' ? 'Anda tidak akan menerima pesan dari pengguna ini. Pengguna ini juga tidak akan tahu jika Anda memblokirnya.' 
                        : type === 'delete-conversation' ? 'Apakah Anda yakin ingin menghapus obrolan ini? Tindakan ini akan menghapus riwayat obrolan Anda secara permanen.'
                        : 'Apakah Anda ingin membuka kembali obrolan Anda dan mengizinkan pengguna ini untuk mengirim pesan?' 
                    }}
                </p>

                <!-- Action buttons -->
                <div class="flex w-full gap-3">
                    <button @click="$emit('close')" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-900 transition-colors">
                        Batal
                    </button>
                    <button @click="$emit('confirm')" 
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-xl text-xs font-black text-white transition-all active:scale-95 shadow-sm',
                            type === 'unblock' 
                                ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-550 dark:hover:bg-emerald-500' 
                                : 'bg-red-600 hover:bg-red-750 dark:bg-red-550 dark:hover:bg-red-500'
                        ]"
                    >
                        {{ 
                            type === 'clear' ? 'Hapus' 
                            : type === 'block' ? 'Blokir' 
                            : type === 'delete-conversation' ? 'Hapus'
                            : 'Buka Blokir' 
                        }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
