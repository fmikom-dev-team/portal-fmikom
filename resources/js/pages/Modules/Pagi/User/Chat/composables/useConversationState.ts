import axios from "axios";
import { nextTick, type Ref, ref } from "vue";
import type { AuthUser, Conversation, Message } from "../types";

interface UseConversationStateParams {
	authUser: AuthUser;
	conversationsList: Ref<Conversation[]>;
	messages: Ref<Message[]>;
	replyingToMessage: Ref<Message | null>;
	isPartnerTyping: Ref<boolean>;
	hasMoreMessages: Ref<boolean>;
	isLoadingMessages: Ref<boolean>;
	isBlockedByMe: Ref<boolean>;
	hasBlockedMe: Ref<boolean>;
	establishSharedKey: (pubKey: string | null | undefined) => Promise<void>;
	decryptText: (
		text: string,
		customKey?: CryptoKey | null,
		partnerId?: number,
	) => Promise<string>;
	subscribeToChannel: (conversationId: string) => void;
	updateGlobalUnreadCount: () => void;
	chatWindowRef: Ref<any>;
	activeConversationId: Ref<string | null>;
	activePartner: Ref<any>;
	activePartnerId: Ref<number | null>;
	mobileShowChat: Ref<boolean>;
	typingTimeout: Ref<any>;
	isTypingLocal: Ref<boolean>;
}

export function useConversationState({
	authUser,
	conversationsList,
	messages,
	replyingToMessage,
	isPartnerTyping,
	hasMoreMessages,
	isLoadingMessages,
	isBlockedByMe,
	hasBlockedMe,
	establishSharedKey,
	decryptText,
	subscribeToChannel,
	updateGlobalUnreadCount,
	chatWindowRef,
	activeConversationId,
	activePartner,
	activePartnerId,
	mobileShowChat,
	typingTimeout,
	isTypingLocal,
}: UseConversationStateParams) {
	function updateConversation(
		partnerId: number,
		updater: (conv: Conversation) => Partial<Conversation> | undefined,
	) {
		const idx = conversationsList.value.findIndex((c) => c.id === partnerId);
		if (idx !== -1) {
			const updated = { ...conversationsList.value[idx] };
			const changes = updater(updated);
			if (changes) {
				Object.assign(updated, changes);
			}
			conversationsList.value[idx] = updated;
		}
	}

	function closeConversation() {
		mobileShowChat.value = false;
		activePartnerId.value = null;
		sessionStorage.removeItem("pagi_active_chat");
		window.history.replaceState({}, "", "/pagi/messages");
		if (typingTimeout.value) {
			clearTimeout(typingTimeout.value);
			typingTimeout.value = null;
		}
		isTypingLocal.value = false;
	}

	async function openConversation(partnerId: number) {
		sessionStorage.setItem("pagi_active_chat", partnerId.toString());
		window.history.replaceState({}, "", "/pagi/messages");
		if (activePartnerId.value === partnerId) {
			mobileShowChat.value = true;
			return;
		}

		if (typingTimeout.value) {
			clearTimeout(typingTimeout.value);
			typingTimeout.value = null;
		}
		isTypingLocal.value = false;

		isLoadingMessages.value = true;
		activePartnerId.value = partnerId;
		mobileShowChat.value = true;
		messages.value = [];
		replyingToMessage.value = null;
		isPartnerTyping.value = false;
		hasMoreMessages.value = true;

		try {
			const res = await axios.get(`/pagi/messages/${partnerId}`);
			activePartner.value = res.data.partner;
			activeConversationId.value = res.data.conversation_id;
			isBlockedByMe.value = res.data.is_blocked_by_me;
			hasBlockedMe.value = res.data.has_blocked_me;

			const partnerPub = res.data.partner?.metadata?.pagi_e2e_pubkey;
			await establishSharedKey(partnerPub);

			const decryptedMsgs = await Promise.all(
				res.data.messages.map(async (msg: any) => {
					msg.body = await decryptText(msg.body, undefined, partnerId);
					if (msg.parent) {
						msg.parent.body = await decryptText(
							msg.parent.body,
							undefined,
							partnerId,
						);
					}
					return msg;
				}),
			);
			messages.value = decryptedMsgs;
			hasMoreMessages.value = decryptedMsgs.length >= 30;

			subscribeToChannel(res.data.conversation_id);

			const conv = conversationsList.value.find((c) => c.id === partnerId);
			if (conv) {
				conv.unread_count = 0;
				conv.is_manual_unread = false;
				if (conv.last_message_sender_id !== authUser.id) {
					conv.last_message_read_at = new Date().toISOString();
				}
				const idx = conversationsList.value.findIndex(
					(c) => c.id === partnerId,
				);
				if (idx !== -1) {
					conversationsList.value[idx] = { ...conv };
				}
			} else if (res.data.partner) {
				conversationsList.value.unshift({
					id: res.data.partner.id,
					name: res.data.partner.name,
					foto_path: res.data.partner.foto_path,
					last_message: null,
					last_message_at: null,
					formatted_time: "",
					unread_count: 0,
					conversation_id: res.data.conversation_id,
					metadata: res.data.partner.metadata,
				});
			}
			updateGlobalUnreadCount();

			await axios.post("/pagi/messages/read", { partner_id: partnerId });
		} catch (err) {
			console.error("Failed to load messages", err);
		} finally {
			isLoadingMessages.value = false;
			await nextTick();
			chatWindowRef.value?.scrollToBottom();
		}
	}

	return {
		activeConversationId,
		activePartner,
		activePartnerId,
		mobileShowChat,
		updateConversation,
		closeConversation,
		openConversation,
	};
}
