<script setup lang="ts">
import { MessageSquare } from "lucide-vue-next";
import { nextTick, ref, watch } from "vue";
import ChatHeader from "./ChatHeader.vue";
import ChatInput from "./ChatInput.vue";
import MessageBubble from "./MessageBubble.vue";
import type { AuthUser, Message } from "./types";
import { formatMessageDate } from "./utils";

const props = defineProps<{
	modelValue: string;
	messages: Message[];
	activePartnerId: number | null;
	activePartner: {
		id: number;
		name: string;
		foto_path: string | null;
		last_seen_at?: string | null;
		metadata?: any;
	} | null;
	authUser: AuthUser;
	isOnline: boolean;
	isPartnerTyping: boolean;
	isBlockedByMe: boolean;
	hasBlockedMe: boolean;
	isSending: boolean;
	isEditingSending: boolean;
	isLoadingMessages: boolean;
	isLoadingMore: boolean;
	hasMoreMessages: boolean;
	editTargetMessage: { id: number; body: string } | null;
	replyingToMessage: Message | null;
	showDropdown: boolean;
	openMenuMessageId: number | null;
	openReactionMessageId: number | null;
	selectedMobileMessageId: number | null;
	onlineUsers: Set<number>;
	mobileShowChat: boolean;
}>();

const emit = defineEmits<{
	(e: "update:modelValue", value: string): void;
	(e: "update:showDropdown", value: boolean): void;
	(e: "update:openMenuMessageId", id: number | null): void;
	(e: "update:openReactionMessageId", id: number | null): void;
	(e: "update:selectedMobileMessageId", id: number | null): void;
	(e: "back"): void;
	(e: "clear-chat"): void;
	(e: "toggle-block"): void;
	(e: "send"): void;
	(e: "typing"): void;
	(e: "cancel-edit"): void;
	(e: "cancel-reply"): void;
	(e: "reply", msg: Message): void;
	(e: "copy", body: string): void;
	(e: "react", msgId: number, emoji: string): void;
	(e: "delete", msgId: number, isSender: boolean): void;
	(e: "edit", msg: Message): void;
	(e: "load-more"): void;
}>();

const messagesContainer = ref<HTMLElement | null>(null);

function scrollToBottom() {
	nextTick(() => {
		if (messagesContainer.value) {
			messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
		}
	});
}

function scrollToMessage(id: number) {
	nextTick(() => {
		const el = document.getElementById(`msg-${id}`);
		if (el) {
			el.scrollIntoView({ behavior: "smooth", block: "center" });
			el.classList.add("bg-indigo-50", "dark:bg-indigo-950/40");
			setTimeout(() => {
				el.classList.remove("bg-indigo-50", "dark:bg-indigo-950/40");
			}, 1000);
		}
	});
}

function handleMessagesScroll(e: Event) {
	const target = e.target as HTMLElement;
	if (!target) return;

	if (target.scrollTop <= 50) {
		emit("load-more");
	}
}

// Watch messages length to scroll to bottom on new messages
watch(
	() => props.messages.length,
	(newVal, oldVal) => {
		if (newVal > oldVal && !props.isLoadingMore) {
			scrollToBottom();
		}
	},
);

// Expose DOM container scroll methods to parent orchestrator
defineExpose({
	scrollToBottom,
	scrollToMessage,
	messagesContainer,
});
</script>

<template>
    <div
        :class="[
            'flex-1 flex flex-col min-w-0 bg-slate-50/20 dark:bg-zinc-900/10',
            mobileShowChat ? 'flex' : 'hidden md:flex'
        ]"
    >
        <!-- Empty state (no conversation selected) -->
        <div v-if="!activePartnerId" class="flex-1 flex flex-col items-center justify-center text-slate-400 dark:text-zinc-650 gap-4">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-zinc-900 flex items-center justify-center pointer-events-none">
                <MessageSquare class="h-10 w-10 opacity-40" />
            </div>
            <p class="text-sm font-semibold select-none">Pilih percakapan untuk memulai</p>
        </div>

        <template v-else>
            <!-- Chat Header -->
            <ChatHeader
                :partner="activePartner"
                :is-online="isOnline"
                :is-partner-typing="isPartnerTyping"
                :is-blocked-by-me="isBlockedByMe"
                :show-dropdown="showDropdown"
                @update:show-dropdown="$emit('update:showDropdown', $event)"
                @back="$emit('back')"
                @clear-chat="$emit('clear-chat')"
                @toggle-block="$emit('toggle-block')"
            />

            <!-- Messages Container -->
            <div
                ref="messagesContainer"
                @scroll="handleMessagesScroll"
                class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 bg-slate-50/40 dark:bg-zinc-900/30"
                style="scrollbar-width:thin;"
            >
                <!-- Loading -->
                <div v-if="isLoadingMessages" class="flex items-center justify-center py-10">
                    <div class="w-6 h-6 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <template v-else>
                    <!-- Mini loader for scroll pagination -->
                    <div v-if="isLoadingMore" class="flex justify-center py-2 select-none">
                        <div class="w-4.5 h-4.5 border-2 border-indigo-500/80 border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <template v-for="(msg, index) in messages" :key="msg.id">
                        <!-- Date Divider (Centered) -->
                        <div 
                            v-if="index === 0 || formatMessageDate(msg.created_at) !== formatMessageDate(messages[index - 1].created_at)" 
                            class="flex justify-center my-4 w-full select-none"
                        >
                            <span class="px-3.5 py-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 text-[10.5px] font-bold rounded-xl border border-slate-200/50 dark:border-zinc-700/50 shadow-2xs">
                                {{ formatMessageDate(msg.created_at) }}
                            </span>
                        </div>

                        <MessageBubble
                            :msg="msg"
                            :auth-user="authUser"
                            :active-partner-id="activePartnerId"
                            :online-users="onlineUsers"
                            :open-menu-message-id="openMenuMessageId"
                            :open-reaction-message-id="openReactionMessageId"
                            :selected-mobile-message-id="selectedMobileMessageId"
                            @update:open-menu-message-id="$emit('update:openMenuMessageId', $event)"
                            @update:open-reaction-message-id="$emit('update:openReactionMessageId', $event)"
                            @update:selected-mobile-message-id="$emit('update:selectedMobileMessageId', $event)"
                            @reply="$emit('reply', $event)"
                            @copy="$emit('copy', $event)"
                            @react="(msgId, emoji) => $emit('react', msgId, emoji)"
                            @delete="(msgId, isSender) => $emit('delete', msgId, isSender)"
                            @edit="$emit('edit', $event)"
                            @scroll-to-message="scrollToMessage"
                        />
                    </template>
                </template>
            </div>

            <!-- Message Input Area -->
            <ChatInput
                :model-value="modelValue"
                @update:model-value="$emit('update:modelValue', $event)"
                :is-blocked-by-me="isBlockedByMe"
                :has-blocked-me="hasBlockedMe"
                :edit-target-message="editTargetMessage"
                :replying-to-message="replyingToMessage"
                :auth-user="authUser"
                :is-sending="isSending"
                :is-editing-sending="isEditingSending"
                @send="$emit('send')"
                @typing="$emit('typing')"
                @cancel-edit="$emit('cancel-edit')"
                @cancel-reply="$emit('cancel-reply')"
                @toggle-block="$emit('toggle-block')"
            />
        </template>
    </div>
</template>
