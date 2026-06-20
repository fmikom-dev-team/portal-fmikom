import axios from "axios";
import { type Ref, ref } from "vue";
import type { AuthUser, Conversation, Message } from "../types";
import { formatConversationTime } from "../utils";

interface UseMessagesRealtimeParams {
	authUser: AuthUser;
	onlineUsers: Ref<Set<number>>;
	typingUsers: Ref<Record<number, boolean>>;
	isPartnerTyping: Ref<boolean>;
	activePartnerId: Ref<number | null>;
	activeConversationId: Ref<string | null>;
	messages: Ref<Message[]>;
	conversationsList: Ref<Conversation[]>;
	decryptText: (
		text: string,
		customKey?: CryptoKey | null,
		partnerId?: number,
	) => Promise<string>;
	decryptMessageForPartner: (
		text: string,
		pubKey: string | null | undefined,
	) => Promise<string>;
	updateGlobalUnreadCount: () => void;
	chatWindowRef: Ref<any>;
	echoChannel: Ref<any>;
	userChannel: Ref<any>;
	onlineChannel: Ref<any>;
}

export function useMessagesRealtime({
	authUser,
	onlineUsers,
	typingUsers,
	isPartnerTyping,
	activePartnerId,
	activeConversationId,
	messages,
	conversationsList,
	decryptText,
	decryptMessageForPartner,
	updateGlobalUnreadCount,
	chatWindowRef,
	echoChannel,
	userChannel,
	onlineChannel,
}: UseMessagesRealtimeParams) {
	const subscribedChannelName = ref<string | null>(null);

	function joinOnlinePresence() {
		if (onlineChannel.value) return;
		onlineChannel.value = window.Broadcaster.join("pagi.online")
			.here((users: Array<{ id: number; name: string }>) => {
				users.forEach((u) => {
					onlineUsers.value.add(u.id);
				});
				onlineUsers.value = new Set(onlineUsers.value);
			})
			.joining((user: { id: number; name: string }) => {
				onlineUsers.value.add(user.id);
				onlineUsers.value = new Set(onlineUsers.value);
			})
			.leaving((user: { id: number; name: string }) => {
				onlineUsers.value.delete(user.id);
				onlineUsers.value = new Set(onlineUsers.value);
			})
			.listenForWhisper("typing", (e: any) => {
				if (e.receiver_id === authUser.id) {
					typingUsers.value[e.user_id] = e.typing;
					if (e.user_id === activePartnerId.value) {
						isPartnerTyping.value = e.typing;
					}
				}
			});
	}

	function subscribeToChannel(conversationId: string) {
		if (subscribedChannelName.value) {
			window.Broadcaster.leave(subscribedChannelName.value);
			echoChannel.value = null;
		}
		subscribedChannelName.value = `pagi.chat.${conversationId}`;
		echoChannel.value = window.Broadcaster.private(
			`pagi.chat.${conversationId}`,
		)
			.listen(".message.sent", async (data: any) => {
				if (data.sender_id !== authUser.id) {
					if (messages.value.some((m) => m.id === data.id)) return;
					const bodyDecrypted = await decryptText(
						data.body,
						undefined,
						activePartnerId.value || undefined,
					);
					const parentDecrypted = data.parent
						? {
								...data.parent,
								body: await decryptText(
									data.parent.body,
									undefined,
									activePartnerId.value || undefined,
								),
							}
						: null;

					messages.value.push({
						id: data.id,
						sender_id: data.sender_id,
						body: bodyDecrypted,
						parent_id: data.parent_id,
						parent: parentDecrypted,
						read_at: null,
						created_at: data.created_at,
						sender: data.sender,
					});
					chatWindowRef.value?.scrollToBottom();

					const conv = conversationsList.value.find(
						(c) => c.conversation_id === conversationId,
					);
					if (conv) {
						conv.last_message = bodyDecrypted;
						conv.last_message_at = new Date().toISOString();
						conv.formatted_time = formatConversationTime(conv.last_message_at);
						conv.last_message_sender_id = data.sender_id;
						conv.last_message_read_at =
							activePartnerId.value === data.sender_id
								? new Date().toISOString()
								: null;
						if (conv.last_message_sending) delete conv.last_message_sending;
						conv.last_message_id = data.id;
						conv.last_message_is_deleted = false;
						conv.last_message_is_deleted_for_me = false;
					}

					if (activePartnerId.value === data.sender_id) {
						await axios.post("/pagi/messages/read", {
							partner_id: data.sender_id,
						});
					}
				}
			})
			.listen(".messages.read", (data: any) => {
				if (data.receiver_id !== authUser.id) {
					messages.value.forEach((m) => {
						if (m.sender_id === authUser.id && !m.read_at) {
							m.read_at = data.read_at;
						}
					});

					const conv = conversationsList.value.find(
						(c) => c.conversation_id === conversationId,
					);
					if (conv && conv.last_message_sender_id === authUser.id) {
						conv.last_message_read_at = data.read_at;
						const idx = conversationsList.value.findIndex(
							(c) => c.id === conv.id,
						);
						if (idx !== -1) {
							conversationsList.value[idx] = { ...conv };
						}
					}
				}
			})
			.listen(".message.deleted", (data: any) => {
				const msg = messages.value.find((m) => m.id === data.id);
				if (msg) {
					msg.is_deleted = true;
					msg.body = "";
					msg.edited_at = null;
				}
				const activeConv = conversationsList.value.find(
					(c) => c.conversation_id === activeConversationId.value,
				);
				if (activeConv) {
					if (activeConv.last_message_id === data.id) {
						activeConv.last_message_is_deleted = true;
						activeConv.last_message_is_deleted_for_me = false;
						activeConv.last_message = "";
					}
				}
			})
			.listen(".message.edited", (data: any) => {
				const msg = messages.value.find((m) => m.id === data.id);
				if (msg) {
					msg.body = data.body;
					msg.edited_at = data.edited_at;
				}
			})
			.listen(".message.reacted", (data: any) => {
				const msg = messages.value.find((m) => m.id === data.id);
				if (msg) {
					msg.reactions = data.reactions;
				}
			})
			.listenForWhisper("typing", (e: any) => {
				if (e.user_id === activePartnerId.value) {
					isPartnerTyping.value = e.typing;
				}
			});
	}

	function subscribeToUserChannel() {
		if (userChannel.value) return;

		userChannel.value = window.Broadcaster.private(
			`App.Models.User.${authUser.id}`,
		)
			.listen(".message.sent", async (data: any) => {
				if (data.sender_id !== authUser.id) {
					const partnerPubKey =
						data.sender?.metadata?.pagi_e2e_pubkey ||
						conversationsList.value.find((c) => c.id === data.sender_id)
							?.metadata?.pagi_e2e_pubkey;

					const bodyDecrypted = await decryptMessageForPartner(
						data.body,
						partnerPubKey,
					);
					const isFromActivePartner = activePartnerId.value === data.sender_id;

					if (isFromActivePartner) {
						if (!messages.value.some((m) => m.id === data.id)) {
							const parentDecrypted = data.parent
								? {
										...data.parent,
										body: await decryptMessageForPartner(
											data.parent.body,
											partnerPubKey,
										),
									}
								: null;

							messages.value.push({
								id: data.id,
								sender_id: data.sender_id,
								body: bodyDecrypted,
								parent_id: data.parent_id,
								parent: parentDecrypted,
								read_at: null,
								created_at: data.created_at,
								sender: data.sender,
							});
							chatWindowRef.value?.scrollToBottom();

							await axios.post("/pagi/messages/read", {
								partner_id: data.sender_id,
							});
						}
					}

					const convIndex = conversationsList.value.findIndex(
						(c) => c.id === data.sender_id,
					);
					if (convIndex !== -1) {
						const conv = conversationsList.value[convIndex];
						conv.last_message = bodyDecrypted;
						conv.last_message_at = new Date().toISOString();
						conv.formatted_time = formatConversationTime(conv.last_message_at);
						if (!isFromActivePartner) {
							conv.unread_count = (conv.unread_count || 0) + 1;
						}
						conv.last_message_sender_id = data.sender_id;
						conv.last_message_read_at = isFromActivePartner
							? new Date().toISOString()
							: null;
						if (conv.last_message_sending) delete conv.last_message_sending;
						conv.last_message_id = data.id;
						conv.last_message_is_deleted = false;
						conv.last_message_is_deleted_for_me = false;

						conversationsList.value.splice(convIndex, 1);
						conversationsList.value.unshift(conv);
					} else {
						const newTime = new Date().toISOString();
						conversationsList.value.unshift({
							id: data.sender.id,
							name: data.sender.name,
							foto_path: data.sender.foto_path,
							last_message: bodyDecrypted,
							last_message_at: newTime,
							formatted_time: formatConversationTime(newTime),
							unread_count: 1,
							conversation_id: data.conversation_id,
							metadata: data.sender.metadata || {},
							last_message_sender_id: data.sender_id,
							last_message_read_at: isFromActivePartner ? newTime : null,
							last_message_id: data.id,
							last_message_is_deleted: false,
							last_message_is_deleted_for_me: false,
						});
					}

					if (!isFromActivePartner) {
						updateGlobalUnreadCount();
					}
				}
			})
			.listen(".messages.read", (data: any) => {
				if (data.receiver_id !== authUser.id) {
					if (activePartnerId.value === data.receiver_id) {
						messages.value.forEach((m) => {
							if (m.sender_id === authUser.id && !m.read_at) {
								m.read_at = data.read_at;
							}
						});
					}

					const conv = conversationsList.value.find(
						(c) =>
							c.id === data.receiver_id ||
							c.conversation_id === data.conversation_id,
					);
					if (conv && conv.last_message_sender_id === authUser.id) {
						conv.last_message_read_at = data.read_at;
						const idx = conversationsList.value.findIndex(
							(c) => c.id === conv.id,
						);
						if (idx !== -1) {
							conversationsList.value[idx] = { ...conv };
						}
					}
				}
			})
			.listen(".message.deleted", (data: any) => {
				if (activeConversationId.value === data.conversation_id) {
					const msg = messages.value.find((m) => m.id === data.id);
					if (msg) {
						msg.is_deleted = true;
						msg.body = "";
						msg.edited_at = null;
					}
				}
				const conv = conversationsList.value.find(
					(c) => c.conversation_id === data.conversation_id,
				);
				if (conv) {
					if (conv.last_message_id === data.id) {
						conv.last_message_is_deleted = true;
						conv.last_message_is_deleted_for_me = false;
						conv.last_message = "";
					}
				}
			});
	}

	return {
		echoChannel,
		userChannel,
		onlineChannel,
		subscribedChannelName,
		joinOnlinePresence,
		subscribeToChannel,
		subscribeToUserChannel,
	};
}
