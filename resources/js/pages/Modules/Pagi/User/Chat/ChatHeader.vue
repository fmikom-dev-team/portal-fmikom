<script setup lang="ts">
import { ChevronLeft, MoreVertical } from "lucide-vue-next";
import { avatarUrl, formatLastSeen, isVerifiedUser } from "./utils";

defineProps<{
	partner: {
		id: number;
		name: string;
		foto_path: string | null;
		last_seen_at?: string | null;
		metadata?: any;
	} | null;
	isOnline: boolean;
	isPartnerTyping: boolean;
	isBlockedByMe: boolean;
	showDropdown: boolean;
}>();

const emit = defineEmits<{
	(e: "back"): void;
	(e: "clear-chat"): void;
	(e: "toggle-block"): void;
	(e: "update:showDropdown", value: boolean): void;
}>();
</script>

<template>
    <div class="flex items-center gap-3 px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0">
        <!-- Back button (mobile) -->
        <button @click="$emit('back')" class="md:hidden p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 transition-colors">
            <ChevronLeft class="h-5 w-5" />
        </button>

        <!-- Avatar -->
        <div class="relative shrink-0 pointer-events-none">
            <div class="h-9 w-9 rounded-full border border-slate-200 dark:border-zinc-700 overflow-hidden bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <img v-if="partner && avatarUrl(partner.foto_path)" :src="avatarUrl(partner!.foto_path)!" :alt="partner?.name || 'Avatar'" class="w-full h-full object-cover" />
                <span v-else class="text-xs font-black text-slate-500">{{ partner?.name?.charAt(0) }}</span>
            </div>
            <span v-if="isOnline" class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-zinc-900"></span>
        </div>

        <!-- Name + status -->
        <div class="flex-1 min-w-0 text-left pointer-events-none">
            <div class="flex items-center gap-1.5">
                <p class="text-sm font-black text-slate-900 dark:text-zinc-100 truncate leading-none">{{ partner?.name }}</p>
                <!-- Verified Seal Badge -->
                <img v-if="isVerifiedUser(partner)" src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Akun Terverifikasi" alt="Verified Badge" />
            </div>
            <p class="text-[10px] font-semibold mt-0.5 leading-none transition-all duration-300" :class="isPartnerTyping ? 'text-emerald-600 dark:text-emerald-400 animate-pulse' : (isOnline ? 'text-green-600' : 'text-slate-400')">
                {{ isPartnerTyping ? 'Sedang mengetik...' : (isOnline ? 'Online' : (partner?.last_seen_at ? 'Terakhir dilihat ' + formatLastSeen(partner.last_seen_at) : 'Offline')) }}
            </p>
        </div>

        <!-- Actions dropdown -->
        <div class="relative">
            <button @click.stop="$emit('update:showDropdown', !showDropdown)" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors" :class="{ 'bg-slate-100 dark:bg-zinc-800': showDropdown }">
                <MoreVertical class="h-4.5 w-4.5" />
            </button>
            <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div v-if="showDropdown" class="absolute right-0 mt-2.5 z-50 w-52 rounded-2xl border border-slate-200/60 dark:border-zinc-800/80 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl p-1.5 shadow-2xl animate-in fade-in slide-in-from-top-2 duration-150">
                    <button @click="$emit('clear-chat')" class="w-full text-left px-3.5 py-3 text-xs font-bold text-slate-700 dark:text-zinc-350 hover:bg-slate-100/60 dark:hover:bg-zinc-900/60 rounded-xl flex items-center gap-2.5 transition-colors">
                        <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Hapus Obrolan
                    </button>
                    <button @click="$emit('toggle-block')" class="w-full text-left px-3.5 py-3 text-xs font-bold rounded-xl flex items-center gap-2.5 transition-colors" :class="isBlockedByMe ? 'text-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20' : 'text-red-655 hover:bg-red-550/10 dark:hover:bg-red-950/20'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path v-if="isBlockedByMe" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        {{ isBlockedByMe ? 'Buka Blokir' : 'Blokir Pengguna' }}
                    </button>
                </div>
            </Transition>
        </div>
    </div>
</template>
