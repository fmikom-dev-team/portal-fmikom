import axios from "axios";
import { nextTick, type Ref, ref } from "vue";
import type { AuthUser, Conversation, Message } from "../types";
import { formatConversationTime } from "../utils";

interface UseMessageActionsParams {
	authUser: AuthUser;
	activePartnerId: Ref<number | null>;
	activeConversationId: Ref<string | null>;
	conversationsList: Ref<Conversation[]>;
	messages: Ref<Message[]>;
	newMessageText: Ref<string>;
	isSending: Ref<boolean>;
	isEditingSending: Ref<boolean>;
	replyingToMessage: Ref<Message | null>;
	editTargetMessage: Ref<{ id: number; body: string } | null>;
	deleteTargetMessageId: Ref<number | null>;
	showDeleteMessageModal: Ref<boolean>;
	openMenuMessageId: Ref<number | null>;
	openReactionMessageId: Ref<number | null>;
	encryptText: (text: string) => Promise<string>;
	decryptText: (
		text: string,
		customKey?: CryptoKey | null,
		partnerId?: number,
	) => Promise<string>;
	typingTimeout: Ref<any>;
	isTypingLocal: Ref<boolean>;
	echoChannel: Ref<any>;
	onlineChannel: Ref<any>;
	chatWindowRef: Ref<any>;
	triggerToast: (msg: string, type?: "success" | "info" | "error") => void;
	updateConversation: (
		partnerId: number,
		updater: (conv: Conversation) => Partial<Conversation> | undefined,
	) => void;
}

export function useMessageActions({
	authUser,
	activePartnerId,
	activeConversationId,
	conversationsList,
	messages,
	newMessageText,
	isSending,
	isEditingSending,
	replyingToMessage,
	editTargetMessage,
	deleteTargetMessageId,
	showDeleteMessageModal,
	openMenuMessageId,
	openReactionMessageId,
	encryptText,
	decryptText,
	typingTimeout,
	isTypingLocal,
	echoChannel,
	onlineChannel,
	chatWindowRef,
	triggerToast,
	updateConversation,
}: UseMessageActionsParams) {
	function sanitizeInput(text: string): string {
		if (!text) return "";
		const scriptRegex = new RegExp(
			"<script\\b[^<]*(?:(?!" + "<\\/script>)<[^<]*)*" + "<\\/script>",
			"gi",
		);
		let clean = text.replace(scriptRegex, "");
		clean = clean.replace(/<[^>]*>/g, "");
		return clean;
	}

	async function sendMessage() {
		if (editTargetMessage.value) {
			await executeEditMessage();
			return;
		}

		const rawBody = newMessageText.value.trim();
		const body = sanitizeInput(rawBody);
		if (!body || !activePartnerId.value || isSending.value) return;

		isSending.value = true;
		const optimisticId = Date.now();
		const repliedMsg = replyingToMessage.value;

		const optimistic = {
			id: optimisticId,
			sender_id: authUser.id,
			body,
			parent_id: repliedMsg ? repliedMsg.id : null,
			parent: repliedMsg
				? {
						id: repliedMsg.id,
						body: repliedMsg.body,
						sender: {
							id: repliedMsg.sender.id,
							name: repliedMsg.sender.name,
						},
					}
				: null,
			read_at: null,
			created_at: new Date().toISOString(),
			sender: authUser as any,
			sending: true,
		};
		messages.value.push(optimistic);
		newMessageText.value = "";
		replyingToMessage.value = null;

		const conv = conversationsList.value.find(
			(c) => c.id === activePartnerId.value,
		);
		if (conv) {
			conv.last_message = body;
			conv.last_message_at = new Date().toISOString();
			conv.formatted_time = formatConversationTime(conv.last_message_at);
			conv.last_message_sender_id = authUser.id;
			conv.last_message_read_at = null;
			conv.last_message_sending = true;
			conv.last_message_id = optimisticId;
			conv.last_message_is_deleted = false;
			conv.last_message_is_deleted_for_me = false;

			const idx = conversationsList.value.findIndex(
				(c) => c.id === activePartnerId.value,
			);
			if (idx !== -1) {
				conversationsList.value.splice(idx, 1);
				conversationsList.value.unshift(conv);
			}
		}

		if (typingTimeout.value) clearTimeout(typingTimeout.value);
		isTypingLocal.value = false;
		if (echoChannel.value) {
			echoChannel.value.whisper("typing", {
				user_id: authUser.id,
				typing: false,
			});
		}
		if (onlineChannel.value) {
			onlineChannel.value.whisper("typing", {
				user_id: authUser.id,
				receiver_id: activePartnerId.value,
				typing: false,
			});
		}

		await nextTick();
		chatWindowRef.value?.scrollToBottom();

		try {
			const bodyEncrypted = await encryptText(body);
			const res = await axios.post("/pagi/messages", {
				receiver_id: activePartnerId.value,
				parent_id: optimistic.parent_id,
				body: bodyEncrypted,
			});

			res.data.body = await decryptText(
				res.data.body,
				undefined,
				activePartnerId.value || undefined,
			);
			if (res.data.parent) {
				res.data.parent.body = await decryptText(
					res.data.parent.body,
					undefined,
					activePartnerId.value || undefined,
				);
			}

			const idx = messages.value.findIndex((m) => m.id === optimisticId);
			if (idx !== -1) messages.value[idx] = res.data;

			updateConversation(activePartnerId.value, (c) => {
				c.last_message = body;
				c.last_message_at = res.data.created_at;
				c.formatted_time = formatConversationTime(res.data.created_at);
				c.last_message_sender_id = authUser.id;
				c.last_message_read_at = res.data.read_at;
				if (c.last_message_sending) delete c.last_message_sending;
				c.last_message_id = res.data.id;
				c.last_message_is_deleted = false;
				c.last_message_is_deleted_for_me = false;
			});
		} catch (err) {
			messages.value = messages.value.filter((m) => m.id !== optimisticId);
			updateConversation(activePartnerId.value, (c) => {
				if (c.last_message_sending) delete c.last_message_sending;
			});
			console.error("Failed to send message", err);
		} finally {
			isSending.value = false;
		}
	}

	async function executeDeleteForMe() {
		const messageId = deleteTargetMessageId.value;
		if (!messageId) return;
		showDeleteMessageModal.value = false;
		try {
			await axios.delete(`/pagi/messages/${messageId}`, {
				data: { delete_type: "for_me" },
			});
			const msg = messages.value.find((m) => m.id === messageId);
			if (msg) {
				msg.is_deleted_for_me = true;
			}
			const activeConv = conversationsList.value.find(
				(c) => c.conversation_id === activeConversationId.value,
			);
			if (activeConv && activeConv.last_message_id === messageId) {
				updateConversation(activeConv.id, (c) => {
					c.last_message_is_deleted_for_me = true;
					c.last_message_is_deleted = false;
					c.last_message = "";
				});
			}
			triggerToast("Pesan dihapus untuk Anda", "success");
		} catch (err) {
			triggerToast("Gagal menghapus pesan", "error");
		}
		deleteTargetMessageId.value = null;
		openMenuMessageId.value = null;
	}

	async function executeDeleteForEveryone() {
		const messageId = deleteTargetMessageId.value;
		if (!messageId) return;
		showDeleteMessageModal.value = false;
		try {
			await axios.delete(`/pagi/messages/${messageId}`, {
				data: { delete_type: "for_everyone" },
			});
			const msg = messages.value.find((m) => m.id === messageId);
			if (msg) {
				msg.is_deleted = true;
				msg.body = "";
				msg.edited_at = null;
			}
			const activeConv = conversationsList.value.find(
				(c) => c.conversation_id === activeConversationId.value,
			);
			if (activeConv && activeConv.last_message_id === messageId) {
				updateConversation(activeConv.id, (c) => {
					c.last_message_is_deleted = true;
					c.last_message_is_deleted_for_me = false;
					c.last_message = "";
				});
			}
			triggerToast("Pesan dihapus untuk semua orang", "success");
		} catch (err: any) {
			const errMsg = err?.response?.data?.error ?? "Gagal menghapus pesan";
			triggerToast(errMsg, "error");
		}
		deleteTargetMessageId.value = null;
		openMenuMessageId.value = null;
	}

	async function executeEditMessage() {
		const target = editTargetMessage.value;
		const body = newMessageText.value.trim();
		if (!target || !body) return;
		isEditingSending.value = true;
		try {
			const res = await axios.patch(`/pagi/messages/${target.id}`, { body });
			const msg = messages.value.find((m) => m.id === target.id);
			if (msg) {
				msg.body = res.data.body;
				msg.edited_at = res.data.edited_at;
			}
			triggerToast("Pesan berhasil diedit", "success");
			editTargetMessage.value = null;
			newMessageText.value = "";
		} catch (err: any) {
			triggerToast(
				err?.response?.data?.error ?? "Gagal mengedit pesan",
				"error",
			);
		}
		isEditingSending.value = false;
	}

	async function handleReact(messageId: number, emoji: string) {
		try {
			const res = await axios.post(`/pagi/messages/${messageId}/react`, {
				emoji,
			});
			const msg = messages.value.find((m) => m.id === messageId);
			if (msg) {
				msg.reactions = res.data.reactions;
			}
		} catch (err) {
			console.error("Failed to react to message", err);
		}
		openMenuMessageId.value = null;
		openReactionMessageId.value = null;
	}

	return {
		sendMessage,
		executeDeleteForMe,
		executeDeleteForEveryone,
		executeEditMessage,
		handleReact,
	};
}
