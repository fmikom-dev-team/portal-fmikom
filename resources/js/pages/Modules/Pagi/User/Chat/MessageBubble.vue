<script setup lang="ts">
import {
	Check,
	CheckCheck,
	Clock,
	Copy,
	Edit3,
	MoreVertical,
	Reply,
	Smile,
	Trash2,
} from "lucide-vue-next";
import { ref } from "vue";
import type { AuthUser, Message } from "./types";
import { avatarUrl, formatTime, isWithin20Minutes } from "./utils";

const props = defineProps<{
	msg: Message;
	authUser: AuthUser;
	activePartnerId: number;
	onlineUsers: Set<number>;
	openMenuMessageId: number | null;
	openReactionMessageId: number | null;
	selectedMobileMessageId: number | null;
}>();

const emit = defineEmits<{
	(e: "reply", msg: Message): void;
	(e: "copy", body: string): void;
	(e: "react", msgId: number, emoji: string): void;
	(e: "delete", msgId: number, isSender: boolean): void;
	(e: "edit", msg: Message): void;
	(e: "scroll-to-message", msgId: number): void;
	(e: "update:openMenuMessageId", id: number | null): void;
	(e: "update:openReactionMessageId", id: number | null): void;
	(e: "update:selectedMobileMessageId", id: number | null): void;
}>();

const swipeOffset = ref(0);
let touchStartX = 0;
let touchStartY = 0;
let isSwiping = false;
let isTouchHold = false;
let longPressTimer: any = null;

const dropdownPosition = ref<"up" | "down">("up");
const reactionPosition = ref<"up" | "down">("up");

function handleTouchStart(e: TouchEvent) {
	if (longPressTimer) clearTimeout(longPressTimer);

	if (props.openMenuMessageId || props.openReactionMessageId) {
		emit("update:openMenuMessageId", null);
		emit("update:openReactionMessageId", null);
		emit("update:selectedMobileMessageId", null);
		return;
	}

	const touch = e.touches[0];
	touchStartX = touch.clientX;
	touchStartY = touch.clientY;
	isSwiping = false;
	isTouchHold = true;

	longPressTimer = setTimeout(() => {
		if (isTouchHold && !isSwiping) {
			emit("update:selectedMobileMessageId", props.msg.id);
			emit("update:openMenuMessageId", props.msg.id);
			if (navigator.vibrate) {
				navigator.vibrate(50);
			}
		}
	}, 500);
}

function handleTouchMove(e: TouchEvent) {
	const touch = e.touches[0];
	const diffX = touch.clientX - touchStartX;
	const diffY = touch.clientY - touchStartY;

	if (Math.abs(diffY) > 10 && !isSwiping) {
		isTouchHold = false;
		if (longPressTimer) clearTimeout(longPressTimer);
		return;
	}

	if (diffX > 15 && Math.abs(diffY) < 15) {
		if (longPressTimer) clearTimeout(longPressTimer);
		isTouchHold = false;
		isSwiping = true;

		if (e.cancelable) e.preventDefault();

		swipeOffset.value = Math.min(diffX * 0.6, 80);
	}
}

function handleTouchEnd() {
	isTouchHold = false;
	if (longPressTimer) clearTimeout(longPressTimer);

	if (isSwiping && swipeOffset.value >= 50) {
		emit("reply", props.msg);
		if (navigator.vibrate) {
			navigator.vibrate(30);
		}
	}

	isSwiping = false;
	swipeOffset.value = 0;
}

function toggleMenu(event: MouseEvent) {
	if (props.openMenuMessageId === props.msg.id) {
		emit("update:openMenuMessageId", null);
	} else {
		const button = event.currentTarget as HTMLElement;
		if (button) {
			const scrollContainer = button.closest(".overflow-y-auto");
			if (scrollContainer) {
				const containerRect = scrollContainer.getBoundingClientRect();
				const buttonRect = button.getBoundingClientRect();
				const distanceToTop = buttonRect.top - containerRect.top;
				// If message bubble options are within 220px of the container's top boundary, drop down
				if (distanceToTop < 220) {
					dropdownPosition.value = "down";
				} else {
					dropdownPosition.value = "up";
				}
			} else {
				const rect = button.getBoundingClientRect();
				if (rect.top < 260) {
					dropdownPosition.value = "down";
				} else {
					dropdownPosition.value = "up";
				}
			}
		}
		emit("update:openMenuMessageId", props.msg.id);
		emit("update:openReactionMessageId", null);
	}
}

function toggleReaction(event: MouseEvent) {
	if (props.openReactionMessageId === props.msg.id) {
		emit("update:openReactionMessageId", null);
	} else {
		const button = event.currentTarget as HTMLElement;
		if (button) {
			const scrollContainer = button.closest(".overflow-y-auto");
			if (scrollContainer) {
				const containerRect = scrollContainer.getBoundingClientRect();
				const buttonRect = button.getBoundingClientRect();
				const distanceToTop = buttonRect.top - containerRect.top;
				if (distanceToTop < 100) {
					reactionPosition.value = "down";
				} else {
					reactionPosition.value = "up";
				}
			} else {
				const rect = button.getBoundingClientRect();
				if (rect.top < 260) {
					reactionPosition.value = "down";
				} else {
					reactionPosition.value = "up";
				}
			}
		}
		emit("update:openReactionMessageId", props.msg.id);
		emit("update:openMenuMessageId", null);
	}
}

function handleReactAction(emoji: string) {
	emit("react", props.msg.id, emoji);
}
</script>

<template>
    <div
        :id="'msg-' + msg.id"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
        @touchcancel="handleTouchEnd"
        :class="[
            'flex gap-2.5 group relative transition-all duration-200 rounded-2xl p-1 w-full',
            msg.sender_id === authUser.id ? 'justify-end' : 'justify-start',
            selectedMobileMessageId === msg.id ? 'bg-blue-100/20 dark:bg-blue-900/10 scale-[0.98] ring-1 ring-blue-500/30' : ''
        ]"
        :style="swipeOffset > 0 ? { transform: `translateX(${swipeOffset}px)`, transition: isSwiping ? 'none' : 'transform 0.2s ease' } : {}"
    >
        <!-- Swipe reply icon indicator behind -->
        <div 
            v-if="swipeOffset > 10" 
            class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center justify-center p-2 rounded-full bg-slate-100 dark:bg-zinc-800 text-indigo-500 transition-opacity pointer-events-none"
            :style="{ opacity: Math.min(swipeOffset / 50, 1), transform: `scale(${Math.min(swipeOffset / 50, 1)})` }"
        >
            <Reply class="w-4 h-4" />
        </div>

        <!-- Incoming avatar -->
        <div v-if="msg.sender_id !== authUser.id" class="h-7 w-7 rounded-full overflow-hidden bg-slate-200 dark:bg-zinc-700 shrink-0 self-end flex items-center justify-center pointer-events-none">
            <img v-if="avatarUrl(msg.sender.foto_path)" :src="avatarUrl(msg.sender.foto_path)!" class="w-full h-full object-cover" />
            <span v-else class="text-[10px] font-bold text-slate-500">{{ msg.sender.name.charAt(0) }}</span>
        </div>

        <div :class="['flex flex-col max-w-[75%] sm:max-w-[60%]', msg.sender_id === authUser.id ? 'items-end' : 'items-start']">
            <div :class="[
                msg.parent ? 'rounded-2xl text-sm leading-relaxed shadow-xs relative' : 'px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-xs relative',
                msg.sender_id === authUser.id
                    ? (msg.parent 
                        ? 'bg-[#d9ecff] dark:bg-[#182a3d] text-[#111b21] dark:text-[#e9edef] border border-[#c1e0ff]/65 dark:border-[#223a54]/50 rounded-br-sm p-1 pb-1.5 shadow-2xs' 
                        : 'bg-[#d9ecff] dark:bg-[#182a3d] text-[#111b21] dark:text-[#e9edef] border border-[#c1e0ff]/65 dark:border-[#223a54]/50 rounded-br-sm pl-4 pr-14 pt-2.5 pb-1.5 shadow-2xs')
                    : (msg.parent 
                        ? 'bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-200/70 dark:border-zinc-700 rounded-bl-sm p-1 pb-1.5 shadow-2xs' 
                        : 'bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-200/70 dark:border-zinc-700 rounded-bl-sm pl-4 pr-11 pt-2.5 pb-1.5')
            ]">
                <!-- Message Options / Context Menu Trigger -->
                <div 
                    :class="[
                        'absolute top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center gap-1 select-none',
                        msg.sender_id === authUser.id 
                            ? 'left-0 -translate-x-[calc(100%+12px)]' 
                            : 'right-0 translate-x-[calc(100%+12px)]',
                        { 'opacity-100': openMenuMessageId === msg.id || openReactionMessageId === msg.id }
                    ]"
                >
                    <!-- Opsi (MoreVertical Icon) -->
                    <button 
                        @click.stop="toggleMenu($event)"
                        class="p-1 rounded-full hover:bg-slate-150 dark:hover:bg-zinc-800 text-slate-400 dark:text-zinc-500 hover:text-slate-700 dark:hover:text-zinc-250 transition-all hover:scale-110 active:scale-95"
                        :class="{ 'bg-slate-150 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200': openMenuMessageId === msg.id }"
                        title="Opsi"
                    >
                        <MoreVertical class="w-4 h-4" />
                    </button>

                    <!-- Balas (Reply Icon) -->
                    <button 
                        @click.stop="$emit('reply', msg)"
                        class="p-1 rounded-full hover:bg-slate-150 dark:hover:bg-zinc-800 text-slate-400 dark:text-zinc-500 hover:text-slate-700 dark:hover:text-zinc-250 transition-all hover:scale-110 active:scale-95"
                        title="Balas"
                    >
                        <Reply class="w-4 h-4" />
                    </button>

                    <!-- Tanggapi (Smile Icon) -->
                    <button 
                        @click.stop="toggleReaction($event)"
                        class="p-1 rounded-full hover:bg-slate-150 dark:hover:bg-zinc-800 text-slate-400 dark:text-zinc-500 hover:text-amber-500 dark:hover:text-amber-400 transition-all hover:scale-110 active:scale-95"
                        :class="{ 'bg-slate-150 dark:bg-zinc-800 text-amber-500 dark:text-amber-400': openReactionMessageId === msg.id }"
                        title="Tanggapi"
                    >
                        <Smile class="w-4 h-4" />
                    </button>

                    <!-- Dropdown Menu -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div 
                            v-if="openMenuMessageId === msg.id && !msg.is_deleted && !msg.is_deleted_for_me" 
                            class="absolute z-50 w-48 bg-white dark:bg-[#233138] rounded-xl shadow-[0_4px_20px_rgba(11,20,26,0.12),_0_2px_8px_rgba(11,20,26,0.12)] py-1.5 flex flex-col border border-slate-100/50 dark:border-zinc-800/20"
                            :class="[
                                msg.sender_id === authUser.id ? 'right-0' : 'left-0',
                                dropdownPosition === 'up' ? 'bottom-full mb-2' : 'top-full mt-2'
                            ]"
                        >
                            <!-- Salin -->
                            <button @click="$emit('copy', msg.body)" class="w-full text-left px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 hover:bg-[#f5f6f6] dark:hover:bg-[#182229] flex items-center gap-3 transition-colors">
                                <Copy class="w-4 h-4 text-slate-500 dark:text-zinc-400 shrink-0" stroke-width="2" />
                                <span>Salin</span>
                            </button>
                            <!-- Edit Pesan (sender only, dalam 20 menit) -->
                            <button
                                v-if="msg.sender_id === authUser.id && isWithin20Minutes(msg.created_at)"
                                @click="$emit('edit', msg)"
                                class="w-full text-left px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 hover:bg-[#f5f6f6] dark:hover:bg-[#182229] flex items-center gap-3 transition-colors"
                            >
                                <Edit3 class="w-4 h-4 text-slate-500 dark:text-zinc-400 shrink-0" stroke-width="2" />
                                <span>Edit Pesan</span>
                            </button>
                            <!-- Hapus Pesan -->
                            <button
                                @click="$emit('delete', msg.id, msg.sender_id === authUser.id)"
                                class="w-full text-left px-4 py-2.5 text-[13px] font-medium text-red-650 dark:text-red-400 hover:bg-red-50/50 dark:hover:bg-red-955/10 flex items-center gap-3 transition-colors"
                            >
                                <Trash2 class="w-4 h-4 text-red-500 dark:text-red-400 shrink-0" stroke-width="2" />
                                <span>Hapus Pesan</span>
                            </button>
                        </div>
                    </Transition>

                    <!-- Reaction Quick Popup -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div 
                            v-if="openReactionMessageId === msg.id" 
                            class="absolute z-55 bg-white dark:bg-[#233138] border border-slate-200/50 dark:border-zinc-800 rounded-full shadow-[0_4px_20px_rgba(11,20,26,0.12),_0_2px_8px_rgba(11,20,26,0.12)] px-2.5 py-1 flex items-center gap-1.5 animate-in fade-in slide-in-from-bottom-2 duration-150"
                            :class="[
                                msg.sender_id === authUser.id ? 'right-0' : 'left-0',
                                reactionPosition === 'up' ? 'bottom-full mb-2' : 'top-full mt-2'
                            ]"
                        >
                            <button v-for="emoji in ['👍', '❤️', '😂', '😮', '😢', '🙏']" :key="emoji" @click="handleReactAction(emoji)" class="hover:scale-125 transition-transform text-lg p-1">
                                {{ emoji }}
                            </button>
                        </div>
                    </Transition>
                </div>

                <!-- Parent message quote in bubble -->
                <div 
                    v-if="msg.parent" 
                    @click="$emit('scroll-to-message', msg.parent.id)" 
                    class="w-full block rounded-t-xl rounded-b-lg mb-1.5 px-3 py-2 text-left text-xs cursor-pointer transition-all border-l-4"
                    :class="[
                        msg.sender_id === authUser.id
                            ? 'bg-[#cde4fc]/60 dark:bg-[#11202e] border-blue-500 hover:bg-[#c1ddfa]/70 dark:hover:bg-[#152738]'
                            : 'bg-slate-100/70 dark:bg-zinc-900/60 border-indigo-500 dark:border-indigo-400 hover:bg-slate-200/60 dark:hover:bg-zinc-850'
                    ]"
                >
                    <span 
                        class="font-extrabold block text-[10.5px] mb-1"
                        :class="msg.sender_id === authUser.id ? 'text-blue-700 dark:text-blue-400' : 'text-indigo-600 dark:text-indigo-400'"
                    >
                        {{ msg.parent.sender.id === authUser.id ? 'Anda' : msg.parent.sender.name }}
                    </span>
                    <span 
                        class="block w-full font-medium"
                        style="display: -webkit-box !important; -webkit-line-clamp: 1 !important; -webkit-box-orient: vertical !important; overflow: hidden !important; text-overflow: ellipsis !important;"
                        :class="msg.sender_id === authUser.id ? 'text-[#1a2e44] dark:text-zinc-300' : 'text-slate-650 dark:text-zinc-400'"
                    >
                        {{ msg.parent.body || 'Pesan tidak tersedia' }}
                    </span>
                </div>

                <!-- Message Body -->
                <!-- DELETED FOR ME PLACEHOLDER -->
                <div
                    v-if="msg.is_deleted_for_me"
                    class="flex items-center gap-1.5 italic py-0.5 break-words"
                    :class="[
                        msg.parent 
                            ? (msg.sender_id === authUser.id ? 'pl-2.5 pr-16 pb-0.5 pt-1' : 'pl-2.5 pr-12 pb-0.5 pt-1')
                            : '',
                        msg.sender_id === authUser.id ? 'text-[#8baec8] dark:text-[#6a90a8]' : 'text-slate-400 dark:text-zinc-500'
                    ]"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    Anda menghapus pesan ini
                </div>
                <!-- DELETED FOR EVERYONE PLACEHOLDER -->
                <div
                    v-else-if="msg.is_deleted"
                    class="flex items-center gap-1.5 italic py-0.5 break-words"
                    :class="[
                        msg.parent 
                            ? (msg.sender_id === authUser.id ? 'pl-2.5 pr-16 pb-0.5 pt-1' : 'pl-2.5 pr-12 pb-0.5 pt-1')
                            : '',
                        msg.sender_id === authUser.id ? 'text-[#8baec8] dark:text-[#6a90a8]' : 'text-slate-400 dark:text-zinc-500'
                    ]"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    {{ msg.sender_id === authUser.id ? 'Anda menghapus pesan ini' : 'Pesan ini telah dihapus' }}
                </div>
                <!-- NORMAL BODY (with reply context) -->
                <div
                    v-else-if="msg.parent"
                    class="whitespace-pre-wrap text-left break-words pt-1"
                    :class="msg.sender_id === authUser.id ? 'pl-2.5 pr-12 pb-0.5' : 'pl-2.5 pr-10 pb-0.5'"
                >
                    {{ msg.body }}
                    <span v-if="msg.edited_at" class="text-[9px] opacity-55 ml-1 font-medium not-italic">(diedit)</span>
                </div>
                <!-- NORMAL BODY (no reply) -->
                <div v-else class="whitespace-pre-wrap text-left break-words">
                    {{ msg.body }}<span v-if="msg.edited_at" class="text-[9px] opacity-55 ml-1 font-medium">(diedit)</span>
                </div>

                <!-- Timestamp & Ticks (WhatsApp Style) -->
                <div 
                    class="absolute bottom-1 right-2 flex items-center gap-1 text-[9.5px] select-none pointer-events-none"
                    :class="msg.sender_id === authUser.id ? 'text-slate-500/85 dark:text-[#a6bacc]/85' : 'text-slate-400 dark:text-zinc-500'"
                >
                    <span>{{ formatTime(msg.created_at) }}</span>
                    <template v-if="msg.sender_id === authUser.id">
                        <Clock v-if="msg.sending" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                        <Check v-else-if="!msg.read_at && !onlineUsers.has(activePartnerId)" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                        <CheckCheck v-else-if="!msg.read_at && onlineUsers.has(activePartnerId)" class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-500 shrink-0" />
                        <CheckCheck v-else-if="msg.read_at" class="w-3.5 h-3.5 text-sky-500 dark:text-sky-400 shrink-0" />
                    </template>
                </div>
            </div>

            <!-- Reactions Display -->
            <div v-if="msg.reactions && Object.keys(msg.reactions).length > 0" class="flex flex-wrap gap-1 mt-1.5">
                <button v-for="(userIds, emoji) in msg.reactions" :key="emoji" 
                    @click.stop="handleReactAction(emoji)"
                    :class="[
                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs border shadow-2xs transition-all active:scale-90',
                        userIds.includes(authUser.id)
                            ? 'bg-indigo-50 dark:bg-indigo-955/40 border-indigo-200 dark:border-indigo-850 text-indigo-600 dark:text-indigo-400'
                            : 'bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400'
                    ]"
                >
                    <span>{{ emoji }}</span>
                    <span class="font-extrabold">{{ userIds.length }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
