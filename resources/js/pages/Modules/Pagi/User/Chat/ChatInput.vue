<script setup lang="ts">
import { Keyboard, Reply, Send, Smile, X } from "lucide-vue-next";
import { nextTick, ref } from "vue";
import type { AuthUser, Message } from "./types";

const props = defineProps<{
	modelValue: string;
	isBlockedByMe: boolean;
	hasBlockedMe: boolean;
	editTargetMessage: { id: number; body: string } | null;
	replyingToMessage: Message | null;
	authUser: AuthUser;
	isSending: boolean;
	isEditingSending: boolean;
}>();

const emit = defineEmits<{
	(e: "update:modelValue", value: string): void;
	(e: "send"): void;
	(e: "typing"): void;
	(e: "cancel-edit"): void;
	(e: "cancel-reply"): void;
	(e: "toggle-block"): void;
}>();

const showEmojiPicker = ref(false);
const activeEmojiTab = ref("Smileys");

const emojiCategories = [
	{
		name: "Smileys",
		emojis: [
			"😀",
			"😃",
			"😄",
			"😁",
			"😆",
			"😅",
			"😂",
			"🤣",
			"😊",
			"😇",
			"🙂",
			"🙃",
			"😉",
			"😌",
			"😍",
			"🥰",
			"😘",
			"😗",
			"😙",
			"😚",
			"😋",
			"😛",
			"😝",
			"😜",
			"🤪",
			"🤨",
			"🧐",
			"🤓",
			"😎",
			"🤩",
			"🥳",
			"😏",
			"😒",
			"😞",
			"😔",
			"😟",
		],
	},
	{
		name: "Gestures",
		emojis: [
			"👍",
			"👎",
			"👌",
			"🤝",
			"✌️",
			"🤞",
			"🤟",
			"🤘",
			"👋",
			"👏",
			"🙌",
			"👐",
			"🙏",
			"✍️",
			"🤳",
			"💪",
		],
	},
	{
		name: "Hearts & Symbols",
		emojis: [
			"❤️",
			"🧡",
			"💛",
			"💚",
			"💙",
			"💜",
			"🖤",
			"🤍",
			"🤎",
			"💔",
			"❣️",
			"💕",
			"💞",
			"💓",
			"💗",
			"💖",
			"💘",
			"💝",
			"🔥",
			"✨",
			"🌟",
			"⭐",
			"🎉",
			"🎈",
		],
	},
];

const addEmoji = (emoji: string) => {
	emit("update:modelValue", props.modelValue + emoji);
	emit("typing");
};

const toggleEmojiPicker = () => {
	if (showEmojiPicker.value) {
		showEmojiPicker.value = false;
		nextTick(() => {
			const textarea = document.querySelector("textarea");
			if (textarea) textarea.focus();
		});
	} else {
		showEmojiPicker.value = true;
		const textarea = document.querySelector("textarea");
		if (textarea) textarea.blur();
	}
};

const handleTextareaFocus = () => {
	showEmojiPicker.value = false;
};

function handleKeydown(e: KeyboardEvent) {
	if (e.key === "Enter" && !e.shiftKey) {
		e.preventDefault();
		emit("send");
	}
}

function handleInput(e: Event) {
	const target = e.target as HTMLTextAreaElement;
	emit("update:modelValue", target.value);
	emit("typing");
}
</script>

<template>
    <div class="border-t border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0 relative">
        <!-- Blocked Banner -->
        <div v-if="isBlockedByMe || hasBlockedMe" class="px-6 py-5 bg-slate-50 dark:bg-zinc-900/40 text-center text-xs font-semibold text-slate-500 dark:text-zinc-400 select-none flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" /></svg>
            <span v-if="isBlockedByMe">
                Anda memblokir pengguna ini. 
                <button @click.stop="$emit('toggle-block')" class="text-indigo-600 dark:text-indigo-400 font-extrabold hover:underline ml-1">Buka Blokir</button>
                untuk mengirim pesan.
            </span>
            <span v-else-if="hasBlockedMe">
                Anda tidak dapat mengirim pesan ke pengguna ini karena Anda telah diblokir.
            </span>
        </div>

        <template v-else>
            <!-- Edit Mode Bar (WhatsApp-style) -->
            <div v-if="editTargetMessage" class="px-5 py-3 border-b border-slate-150 dark:border-zinc-850 bg-indigo-50/80 dark:bg-indigo-950/20 flex items-center justify-between shrink-0 backdrop-blur-xs transition-all duration-300">
                <div class="border-l-4 border-indigo-500 dark:border-indigo-400 pl-3 text-left min-w-0 flex-1 mr-4">
                    <p class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 leading-none mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                        <span>Mengedit Pesan</span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-zinc-405 w-full block leading-relaxed truncate">{{ editTargetMessage.body }}</p>
                </div>
                <button @click="$emit('cancel-edit')" class="p-1.5 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-slate-450 hover:text-slate-700 dark:text-zinc-500 dark:hover:text-zinc-300 shrink-0 transition-colors" title="Batal edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Reply Mode Bar -->
            <div v-if="replyingToMessage" class="px-5 py-3 border-b border-slate-150 dark:border-zinc-850 bg-slate-50/80 dark:bg-zinc-900/60 flex items-center justify-between shrink-0 backdrop-blur-xs transition-all duration-300">
                <div class="border-l-4 border-indigo-600 dark:border-indigo-500 pl-3 text-left min-w-0 flex-1 mr-4">
                    <p class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 leading-none mb-1.5 flex items-center gap-1.5">
                        <Reply class="w-3 h-3 text-indigo-500 dark:text-indigo-400" />
                        <span>Membalas {{ replyingToMessage.sender.id === authUser.id ? 'Anda' : replyingToMessage.sender.name }}</span>
                    </p>
                    <p class="text-xs text-slate-650 dark:text-zinc-405 w-full block leading-relaxed" style="display: -webkit-box !important; -webkit-line-clamp: 1 !important; -webkit-box-orient: vertical !important; overflow: hidden !important; text-overflow: ellipsis !important;">
                        {{ replyingToMessage.body }}
                    </p>
                </div>
                <button @click="$emit('cancel-reply')" class="p-1.5 rounded-full hover:bg-slate-200/60 dark:hover:bg-zinc-800/80 text-slate-450 hover:text-slate-700 dark:text-zinc-500 dark:hover:text-zinc-300 shrink-0 transition-colors" title="Batal membalas">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- WhatsApp-Style Message Input Area -->
            <div class="px-4 sm:px-6 py-3 flex items-end gap-2.5 bg-white dark:bg-zinc-900">
                <!-- Input Box (Pill Wrapper) -->
                <div class="flex-1 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl px-3 py-1 flex items-end gap-2 focus-within:border-indigo-400 dark:focus-within:border-indigo-500 transition-colors min-h-[46px]">
                    <!-- Emoji/Keyboard toggle button on the left inside -->
                    <button @click="toggleEmojiPicker" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors shrink-0 self-end mb-[2px]">
                        <Keyboard v-if="showEmojiPicker" class="h-5 w-5" />
                        <Smile v-else class="h-5 w-5" />
                    </button>

                    <!-- Textarea -->
                    <textarea
                        :value="modelValue"
                        @keydown="handleKeydown"
                        @input="handleInput"
                        @focus="handleTextareaFocus"
                        placeholder="Tulis pesan Anda..."
                        rows="1"
                        class="flex-1 bg-transparent border-0 p-0 text-sm text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-600 outline-none resize-none max-h-32 overflow-y-auto leading-relaxed py-2.5 min-h-[20px] focus:ring-0"
                        style="scrollbar-width:thin;"
                    ></textarea>
                </div>

                <!-- Send / Save Edit Button -->
                <button
                    @click="$emit('send')"
                    :disabled="!modelValue.trim() || isSending || isEditingSending"
                    :class="[
                        'p-3 rounded-full transition-all shrink-0 self-end shadow-sm flex items-center justify-center min-h-[46px] min-w-[46px]',
                        modelValue.trim() && !isSending && !isEditingSending
                            ? (editTargetMessage ? 'bg-emerald-600 dark:bg-emerald-500 text-white hover:bg-emerald-700 dark:hover:bg-emerald-400 active:scale-95' : 'bg-slate-900 dark:bg-indigo-600 text-white hover:bg-indigo-700 dark:hover:bg-indigo-500 active:scale-95')
                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-600 cursor-not-allowed'
                    ]"
                >
                    <!-- Checkmark when editing, Send when composing -->
                    <template v-if="editTargetMessage">
                        <svg v-if="!isEditingSending" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    </template>
                    <Send v-else class="h-4.5 w-4.5" :class="isSending ? 'opacity-50' : ''" />
                </button>
            </div>

            <!-- WhatsApp-Style Bottom Inline Emoji Picker (Occupies keyboard space) -->
            <div v-if="showEmojiPicker" class="w-full border-t border-slate-150 dark:border-zinc-900 bg-white dark:bg-zinc-900 p-4 select-none flex flex-col h-72 shrink-0 animate-in slide-in-from-bottom duration-200">
                <!-- Category tabs -->
                <div class="flex gap-2 border-b border-slate-100 dark:border-zinc-800 pb-2 mb-2 overflow-x-auto shrink-0" style="scrollbar-width:none;">
                    <button v-for="cat in emojiCategories" :key="cat.name"
                        @click="activeEmojiTab = cat.name"
                        :class="['text-[10px] font-black px-2.5 py-1 rounded-full shrink-0 transition-all',
                            activeEmojiTab === cat.name
                                ? 'bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-sm'
                                : 'bg-slate-50 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-700'
                        ]"
                    >
                        {{ cat.name }}
                    </button>
                </div>
                <!-- Emoji grid -->
                <div class="flex-1 overflow-y-auto grid grid-cols-6 gap-2 p-0.5" style="scrollbar-width:thin;">
                    <button v-for="emoji in emojiCategories.find(c => c.name === activeEmojiTab)?.emojis" :key="emoji"
                        @click="addEmoji(emoji)"
                        class="text-xl p-1 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg active:scale-90 transition-transform"
                    >
                        {{ emoji }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
