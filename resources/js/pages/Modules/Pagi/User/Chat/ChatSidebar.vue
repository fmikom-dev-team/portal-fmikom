<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
	Archive,
	Check,
	CheckCheck,
	ChevronDown,
	ChevronLeft,
	Clock,
	MessageSquare,
	Pin,
	Search,
	Trash2,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import type { AuthUser, Contact, Conversation } from "./types";
import { avatarUrl, formatConversationTime, isVerifiedUser } from "./utils";

const props = defineProps<{
	conversationsList: Conversation[];
	followedContacts: Contact[];
	authUser: AuthUser;
	activePartnerId: number | null;
	onlineUsers: Set<number>;
	typingUsers: Record<number, boolean>;
	mobileShowChat: boolean;
	showArchivedOnly: boolean;
	activeDropdownConvId: number | null;
}>();

const emit = defineEmits<{
	(e: "open-conversation", partnerId: number): void;
	(e: "toggle-read-status", conv: Conversation): void;
	(e: "toggle-archive", conv: Conversation): void;
	(e: "toggle-pin", conv: Conversation): void;
	(e: "delete-conversation", conv: Conversation): void;
	(e: "long-press", conv: Conversation): void;
	(e: "update:showArchivedOnly", value: boolean): void;
	(e: "update:activeDropdownConvId", value: number | null): void;
}>();

const searchQuery = ref("");

const sanitizeSearchQuery = (text: string): string => {
	if (!text) return "";
	let clean = text.replace(/<[^>]*>/g, "");
	clean = clean.replace(/[^\w\s\-.,@]/gi, "");
	return clean.trim();
};

const sanitizedSearchQuery = computed(() => {
	return sanitizeSearchQuery(searchQuery.value);
});

// Swipe states
const swipeActiveId = ref<number | null>(null);
const swipeX = ref(0);
let startSwipeX = 0;
let startSwipeY = 0;
let isSwipingConv = false;

// Long press state
let longPressTimerConv: any = null;
let touchMovedConv = false;

function onLongPressStart(conv: any) {
	touchMovedConv = false;
	longPressTimerConv = setTimeout(() => {
		if (window.innerWidth < 768) {
			emit("long-press", conv);
			if (navigator.vibrate) navigator.vibrate(50);
		}
	}, 600);
}

function onLongPressEnd() {
	clearTimeout(longPressTimerConv);
}

function onSwipeStart(e: any, conv: any) {
	if (conv.is_followed_placeholder) return;
	const target = e.target as HTMLElement;
	if (target.closest(".z-55") || target.closest(".conv-dropdown-btn")) return;

	touchMovedConv = false;
	startSwipeX = e.type === "touchstart" ? e.touches[0].clientX : e.clientX;
	startSwipeY = e.type === "touchstart" ? e.touches[0].clientY : e.clientY;
	isSwipingConv = false;
	swipeActiveId.value = conv.id;
	swipeX.value = 0;

	if (e.type === "touchstart") {
		onLongPressStart(conv);
	}

	if (e.type === "touchstart") {
		window.addEventListener("touchmove", onSwipeMove, { passive: false });
		window.addEventListener("touchend", onSwipeEnd);
	} else {
		window.addEventListener("mousemove", onSwipeMove);
		window.addEventListener("mouseup", onSwipeEnd);
	}
}

function onSwipeMove(e: any) {
	const currentX = e.type === "touchmove" ? e.touches[0].clientX : e.clientX;
	const currentY = e.type === "touchmove" ? e.touches[0].clientY : e.clientY;
	const diffX = currentX - startSwipeX;
	const diffY = currentY - startSwipeY;

	if (!isSwipingConv) {
		if (Math.abs(diffX) > 10 && Math.abs(diffX) > Math.abs(diffY)) {
			isSwipingConv = true;
			clearTimeout(longPressTimerConv);
			if (e.type === "touchmove") {
				e.preventDefault();
			}
		} else if (Math.abs(diffY) > 10) {
			onSwipeEnd();
			return;
		}
	}

	if (isSwipingConv) {
		const maxDrag = 140;
		if (diffX > 0) {
			swipeX.value = Math.min(diffX, maxDrag);
		} else {
			swipeX.value = Math.max(diffX, -maxDrag);
		}
		if (e.type === "touchmove") {
			e.preventDefault();
		}
	}
}

async function onSwipeEnd() {
	window.removeEventListener("touchmove", onSwipeMove);
	window.removeEventListener("touchend", onSwipeEnd);
	window.removeEventListener("mousemove", onSwipeMove);
	window.removeEventListener("mouseup", onSwipeEnd);

	clearTimeout(longPressTimerConv);

	const x = swipeX.value;
	const convId = swipeActiveId.value;

	swipeX.value = 0;

	setTimeout(() => {
		if (swipeActiveId.value === convId && swipeX.value === 0) {
			swipeActiveId.value = null;
		}
	}, 150);

	if (isSwipingConv && convId) {
		const conv = props.conversationsList.find((c) => c.id === convId);
		if (conv) {
			if (x > 80) {
				emit("toggle-read-status", conv);
			} else if (x < -80) {
				emit("toggle-archive", conv);
			}
		}
	}
	isSwipingConv = false;
}

function handleConvClick(conv: any) {
	if (touchMovedConv || isSwipingConv) {
		return;
	}
	emit("open-conversation", conv.id);
}

function toggleConvDropdown(convId: number) {
	if (props.activeDropdownConvId === convId) {
		emit("update:activeDropdownConvId", null);
	} else {
		emit("update:activeDropdownConvId", convId);
	}
}

const filteredConversations = computed(() => {
	let list = [...props.conversationsList];

	// Filter by archive state
	list = list.filter((c) =>
		props.showArchivedOnly ? c.is_archived : !c.is_archived,
	);

	// Search query
	if (sanitizedSearchQuery.value) {
		const q = sanitizedSearchQuery.value.toLowerCase();

		// 1. Filter active conversations
		let matchedActive = list.filter((c) => c.name.toLowerCase().includes(q));

		// 2. Filter followed contacts that are NOT already in the active list matching the query
		let matchedFollowed = props.followedContacts
			.filter((c) => c.name.toLowerCase().includes(q))
			.filter(
				(c) => !props.conversationsList.some((active) => active.id === c.id),
			)
			.map((c) => ({
				id: c.id,
				name: c.name,
				foto_path: c.foto_path,
				last_message: "Klik untuk mulai percakapan baru",
				last_message_at: null,
				unread_count: 0,
				conversation_id: [props.authUser.id, c.id]
					.sort((a, b) => a - b)
					.join("_"),
				metadata: {},
				is_pinned: false,
				is_archived: false,
				is_manual_unread: false,
				is_followed_placeholder: true,
			}));

		list = [...matchedActive, ...matchedFollowed];
	}

	// Sort: pinned first, then preserve relative order
	return list.sort((a, b) => {
		const pinA = a.is_pinned ? 1 : 0;
		const pinB = b.is_pinned ? 1 : 0;
		if (pinA !== pinB) {
			return pinB - pinA;
		}
		return 0; // preserve relative order
	});
});
</script>

<template>
    <aside
        :class="[
            'flex flex-col border-r border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 w-full md:w-80 lg:w-96 shrink-0',
            mobileShowChat ? 'hidden md:flex md:w-80 lg:w-96' : 'flex w-full md:w-80 lg:w-96'
        ]"
    >
        <!-- Sidebar Header -->
        <div class="px-5 pt-5 pb-3 border-b border-slate-100 dark:border-zinc-800/70 shrink-0">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <!-- Back button to dashboard on mobile -->
                    <Link href="/pagi" class="md:hidden p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 mr-1 shrink-0 transition-colors">
                        <ChevronLeft class="h-5 w-5" />
                    </Link>
                    <MessageSquare class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <h1 class="text-base font-black text-slate-900 dark:text-zinc-100 tracking-tight">Pesan Masuk</h1>
                </div>
            </div>
            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 dark:text-zinc-500" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari kontak/nama..."
                    class="w-full bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl pl-9 pr-3 py-2 text-xs font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 dark:placeholder-zinc-600 outline-none focus:border-indigo-400 dark:focus:border-indigo-500 transition-colors"
                />
            </div>
        </div>

        <!-- Archived Filter Header -->
        <div 
            v-if="showArchivedOnly || conversationsList.some(c => c.is_archived)" 
            class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-900/40 border-b border-slate-150 dark:border-zinc-800/80 flex items-center shrink-0"
        >
            <button 
                @click="$emit('update:showArchivedOnly', !showArchivedOnly)" 
                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2 hover:underline transition-all"
            >
                <Archive class="w-3.5 h-3.5" />
                <span>{{ showArchivedOnly ? 'Kembali ke Obrolan Utama' : `Arsip Obrolan (${conversationsList.filter(c => c.is_archived).length})` }}</span>
            </button>
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto py-1" style="scrollbar-width:thin;">
            <!-- Empty state -->
            <div v-if="filteredConversations.length === 0" class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-zinc-600 gap-3">
                <MessageSquare class="h-10 w-10 opacity-30" />
                <p class="text-sm font-semibold">Belum ada percakapan</p>
            </div>

            <div 
                v-for="conv in filteredConversations"
                :key="conv.id"
                class="relative w-full border-b border-slate-100 dark:border-zinc-900/50"
                :class="swipeActiveId === conv.id ? 'overflow-hidden' : 'overflow-visible'"
            >
                <!-- Swipe Action: Right (Green bg, checkmark) -->
                <div 
                    v-if="swipeActiveId === conv.id && swipeX > 0"
                    class="absolute inset-0 bg-emerald-500 flex items-center pl-6 text-white text-xs font-bold transition-all select-none"
                >
                    <div class="flex items-center gap-2">
                        <CheckCheck class="w-4 h-4" />
                        <span>Tandai Dibaca</span>
                    </div>
                </div>

                <!-- Swipe Action: Left (Purple/indigo bg, archive box) -->
                <div 
                    v-if="swipeActiveId === conv.id && swipeX < 0"
                    class="absolute inset-0 bg-indigo-600 flex items-center justify-end pr-6 text-white text-xs font-bold transition-all select-none"
                >
                    <div class="flex items-center gap-2">
                        <Archive class="w-4 h-4" />
                        <span>{{ conv.is_archived ? 'Buka Arsip' : 'Arsipkan' }}</span>
                    </div>
                </div>

                <button
                    @click="handleConvClick(conv)"
                    @dragstart.prevent
                    @mousedown="onSwipeStart($event, conv)"
                    @touchstart="onSwipeStart($event, conv)"
                    @mouseleave="onLongPressEnd"
                    @touchend="onLongPressEnd"
                    :class="[
                        'group w-full text-left flex items-center gap-3 pl-4 py-3.5 relative transition-transform duration-100 select-none touch-pan-y',
                        activePartnerId === conv.id
                            ? 'bg-slate-100/90 dark:bg-zinc-800/80 font-bold border-r-4 border-indigo-600 dark:border-indigo-500 pr-3 shadow-xs'
                            : 'bg-white dark:bg-zinc-955 hover:bg-slate-50 dark:hover:bg-zinc-900/50 pr-4'
                    ]"
                    :style="{ transform: swipeActiveId === conv.id ? `translateX(${swipeX}px)` : 'none' }"
                >
                    <!-- Avatar -->
                    <div class="relative shrink-0 pointer-events-none">
                        <div class="h-11 w-11 rounded-full border border-slate-200 dark:border-zinc-700 overflow-hidden bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <img v-if="avatarUrl(conv.foto_path)" :src="avatarUrl(conv.foto_path)!" :alt="conv.name" class="w-full h-full object-cover" />
                            <span v-else class="text-sm font-black text-slate-500 dark:text-zinc-400">{{ conv.name.charAt(0) }}</span>
                        </div>
                        <!-- Online green dot in sidebar -->
                        <span v-if="onlineUsers.has(conv.id)" class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-zinc-950"></span>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1 min-w-0 pointer-events-none">
                                <span :class="['text-sm truncate', (conv.unread_count > 0 || conv.is_manual_unread) ? 'font-black text-slate-900 dark:text-zinc-100' : 'font-semibold text-slate-700 dark:text-zinc-300']">
                                    {{ conv.name }}
                                </span>
                                <!-- Verified Seal Badge -->
                                <img v-if="isVerifiedUser(conv)" src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Akun Terverifikasi" alt="Verified Badge" />
                            </div>
                            
                            <!-- Time & Hover Dropdown Container -->
                            <div class="relative flex items-center justify-end h-5 w-20 shrink-0 select-none">
                                <!-- Time Span -->
                                <span 
                                    class="absolute right-0 text-[10px] text-slate-400 dark:text-zinc-500 transition-all duration-300 transform group-hover:-translate-x-6 pointer-events-none"
                                >
                                    {{ conv.formatted_time || formatConversationTime(conv.last_message_at) }}
                                </span>
                                
                                <!-- Dropdown Button wrapper -->
                                <div v-if="!conv.is_followed_placeholder" class="absolute right-[-24px] group-hover:right-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <button 
                                        @click.stop="toggleConvDropdown(conv.id)"
                                        class="conv-dropdown-btn flex items-center justify-center w-5 h-5 rounded-full bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-450 shadow-sm"
                                    >
                                        <ChevronDown class="w-3 h-3" />
                                    </button>
                                    
                                    <!-- Desktop Action Dropdown Menu -->
                                    <div 
                                        v-if="activeDropdownConvId === conv.id"
                                        class="absolute right-0 top-full mt-1.5 w-48 bg-white dark:bg-zinc-955 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl py-1 z-55 text-slate-800 dark:text-zinc-200"
                                    >
                                        <button 
                                            @click.stop="$emit('toggle-read-status', conv)"
                                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-zinc-900/60 flex items-center gap-2"
                                        >
                                            <CheckCheck class="w-3.5 h-3.5 text-slate-400" />
                                            <span>{{ (conv.unread_count > 0 || conv.is_manual_unread) ? 'Tandai sudah dibaca' : 'Tandai belum dibaca' }}</span>
                                        </button>
                                        <button 
                                            @click.stop="$emit('toggle-archive', conv)"
                                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-zinc-900/60 flex items-center gap-2"
                                        >
                                            <Archive class="w-3.5 h-3.5 text-slate-400" />
                                            <span>{{ conv.is_archived ? 'Buka arsip' : 'Arsipkan' }}</span>
                                        </button>
                                        <button 
                                            @click.stop="$emit('toggle-pin', conv)"
                                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-zinc-900/60 flex items-center gap-2"
                                        >
                                            <Pin class="w-3.5 h-3.5 text-slate-400 rotate-45" />
                                            <span>{{ conv.is_pinned ? 'Lepas sematan' : 'Sematkan' }}</span>
                                        </button>
                                        <hr class="my-1 border-slate-100 dark:border-zinc-800" />
                                        <button 
                                            @click.stop="$emit('delete-conversation', conv)"
                                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-red-50 dark:hover:bg-red-950/20 text-red-655 dark:text-red-400 flex items-center gap-2"
                                        >
                                            <Trash2 class="w-3.5 h-3.5 text-red-500" />
                                            <span>Hapus obrolan</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p 
                                :class="[
                                    'text-xs flex items-center gap-1 min-w-0 flex-1 pointer-events-none',
                                    typingUsers[conv.id]
                                        ? 'text-green-500 dark:text-green-400 font-semibold animate-pulse'
                                        : 'text-slate-500 dark:text-zinc-500'
                                ]"
                            >
                                <span v-if="typingUsers[conv.id]">Sedang mengetik...</span>
                                <template v-else>
                                    <!-- Sidebar last message preview with deleted states -->
                                    <template v-if="conv.last_message_is_deleted_for_me">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        <span class="truncate flex-1 italic text-slate-400 dark:text-zinc-500">Anda menghapus pesan ini</span>
                                    </template>
                                    <template v-else-if="conv.last_message_is_deleted">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        <span class="truncate flex-1 italic text-slate-400 dark:text-zinc-500">{{ conv.last_message_sender_id === authUser.id ? 'Anda menghapus pesan ini' : 'Pesan ini telah dihapus' }}</span>
                                    </template>
                                    <template v-else>
                                        <template v-if="conv.last_message && conv.last_message_sender_id == authUser.id">
                                            <Clock v-if="conv.last_message_sending" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                                            <Check v-else-if="!conv.last_message_read_at && !onlineUsers.has(conv.id)" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                                            <CheckCheck v-else-if="!conv.last_message_read_at && onlineUsers.has(conv.id)" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                                            <CheckCheck v-else-if="conv.last_message_read_at" class="w-3.5 h-3.5 text-sky-500 dark:text-sky-400 shrink-0" />
                                        </template>
                                        <span class="truncate flex-1">{{ conv.last_message }}</span>
                                    </template>
                                </template>
                            </p>
                            
                            <div class="flex items-center gap-2 shrink-0 relative">
                                <!-- Pinned state indicator -->
                                <Pin v-if="conv.is_pinned" class="w-3 h-3 text-slate-400 dark:text-zinc-500 rotate-45 pointer-events-none" />
                                
                                <!-- Unread indicator badge -->
                                <span v-if="conv.unread_count > 0 || conv.is_manual_unread" class="min-w-[14px] h-[14px] px-1 rounded-full bg-green-500 text-white text-[8px] font-black flex items-center justify-center pointer-events-none">
                                    {{ conv.is_manual_unread ? '' : conv.unread_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </aside>
</template>
