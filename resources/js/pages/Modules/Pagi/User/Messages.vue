<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";
// ── Components ──────────────────────────────────────────────────────────────
import ChatSidebar from "./Chat/ChatSidebar.vue";
import ChatWindow from "./Chat/ChatWindow.vue";
// ── Composables & Types ──────────────────────────────────────────────────────
import { useE2EE } from "./Chat/composables/useE2EE";
import ConfirmActionModal from "./Chat/modals/ConfirmActionModal.vue";
import DeleteMessageModal from "./Chat/modals/DeleteMessageModal.vue";
import NewChatModal from "./Chat/modals/NewChatModal.vue";
import type { AuthUser, Contact, Conversation, Message } from "./Chat/types";
import {
	avatarUrl,
	formatConversationTime,
	formatLastSeen,
} from "./Chat/utils";
import Navbar from "./ui/Navbar.vue";

// ── Props from PagiChatController::index() ────────────────────────────────────
const props = defineProps<{
	moduleName: string;
	roleName: string;
	conversations: Array<any>;
	authUser: AuthUser;
}>();

// ── State ─────────────────────────────────────────────────────────────────────
const page = usePage();
const mobileShowChat = ref(false);
const newMessageText = ref("");
const isSending = ref(false);
const isLoadingMessages = ref(false);
const isLoadingMore = ref(false);
const hasMoreMessages = ref(true);

// Active conversation
const activePartnerId = ref<number | null>(null);
const activePartner = ref<{
	id: number;
	name: string;
	foto_path: string | null;
	last_seen_at?: string | null;
	metadata?: any;
} | null>(null);
const activeConversationId = ref<string | null>(null);

// Local lists
const messages = ref<Message[]>([]);
const conversationsList = ref<Conversation[]>([...props.conversations]);
const followedContacts = ref<Contact[]>([]);

// Online presence & typing states
const onlineUsers = ref<Set<number>>(new Set());
const typingUsers = ref<Record<number, boolean>>({});
const isPartnerTyping = ref(false);
const isOnline = computed(() =>
	activePartnerId.value ? onlineUsers.value.has(activePartnerId.value) : false,
);

// Blocking states
const isBlockedByMe = ref(false);
const hasBlockedMe = ref(false);

// UI Dropdowns & Modals
const showHeaderDropdown = ref(false);
const showConfirmModal = ref(false);
const confirmModalType = ref<
	"clear" | "block" | "unblock" | "delete-conversation"
>("clear");
const deleteTargetConv = ref<Conversation | null>(null);

const showDeleteMessageModal = ref(false);
const deleteTargetMessageId = ref<number | null>(null);
const deleteTargetIsSender = ref(false);
const editTargetMessage = ref<{ id: number; body: string } | null>(null);
const isEditingSending = ref(false);

// Touch Context Menu & Reactions
const showMobileContextMenu = ref(false);
const mobileMenuConv = ref<Conversation | null>(null);
const openMenuMessageId = ref<number | null>(null);
const openReactionMessageId = ref<number | null>(null);
const selectedMobileMessageId = ref<number | null>(null);

// New Chat Modal state
const showNewChatModal = ref(false);
const contactsList = ref<Contact[]>([]);
const isLoadingContacts = ref(false);

// Toast System
const toastMessage = ref("");
const toastType = ref<"success" | "info" | "error">("success");
const showToast = ref(false);
let toastTimeout: any = null;

// Reply state
const replyingToMessage = ref<Message | null>(null);

// Filter states for Sidebar
const showArchivedOnly = ref(false);
const activeDropdownConvId = ref<number | null>(null);

// Timestamps update interval
const nowRef = ref(Date.now());
let timeUpdateInterval: any = null;

// ChatWindow template ref
const chatWindowRef = ref<any>(null);

// ── E2EE Cryptography Composable ─────────────────────────────────────────────
const {
	initE2EKeys,
	establishSharedKey,
	encryptText,
	decryptText,
	decryptMessageForPartner,
	decryptSidebarPreviews,
} = useE2EE(props.authUser);

// ── Helpers & Action Callbacks ────────────────────────────────────────────────
function triggerToast(
	msg: string,
	type: "success" | "info" | "error" = "success",
) {
	toastMessage.value = msg;
	toastType.value = type;
	showToast.value = true;
	if (toastTimeout) clearTimeout(toastTimeout);
	toastTimeout = setTimeout(() => {
		showToast.value = false;
	}, 3000);
}

function updateGlobalUnreadCount() {
	const total = conversationsList.value.reduce((acc, conv) => {
		const count = conv.unread_count || (conv.is_manual_unread ? 1 : 0);
		return acc + count;
	}, 0);
	if (page.props) {
		page.props.unread_messages_count = total;
	}
	window.dispatchEvent(
		new CustomEvent("pagi:unread_messages_count", { detail: total }),
	);
}

function updateAllConversationTimes() {
	conversationsList.value.forEach((conv) => {
		conv.formatted_time = formatConversationTime(conv.last_message_at);
	});
}

function closeConversation() {
	mobileShowChat.value = false;
	activePartnerId.value = null;
	sessionStorage.removeItem("pagi_active_chat");
	window.history.replaceState({}, "", "/pagi/messages");
}

function openDeleteModal(messageId: number, isSender: boolean) {
	deleteTargetMessageId.value = messageId;
	deleteTargetIsSender.value = isSender;
	showDeleteMessageModal.value = true;
	openMenuMessageId.value = null;
}

function openEditModal(msg: Message) {
	editTargetMessage.value = { id: msg.id, body: msg.body };
	newMessageText.value = msg.body;
	replyingToMessage.value = null;
	openMenuMessageId.value = null;
	nextTick(() => {
		const textarea = document.querySelector("textarea");
		if (textarea) {
			textarea.focus();
			textarea.setSelectionRange(textarea.value.length, textarea.value.length);
		}
	});
}

function cancelEdit() {
	editTargetMessage.value = null;
	newMessageText.value = "";
	openMenuMessageId.value = null;
}

function openConfirmModal(
	type: "clear" | "block" | "unblock" | "delete-conversation",
) {
	confirmModalType.value = type;
	showConfirmModal.value = true;
	showHeaderDropdown.value = false;
}

function confirmDeleteConversation(conv: Conversation) {
	deleteTargetConv.value = conv;
	showMobileContextMenu.value = false;
	openConfirmModal("delete-conversation");
}

function handleReply(msg: Message) {
	replyingToMessage.value = msg;
	openMenuMessageId.value = null;
	openReactionMessageId.value = null;
	nextTick(() => {
		const textarea = document.querySelector("textarea");
		if (textarea) textarea.focus();
	});
}

async function handleCopy(body: string) {
	try {
		await navigator.clipboard.writeText(body);
		triggerToast("Pesan disalin ke clipboard", "success");
	} catch (err) {
		triggerToast("Gagal menyalin pesan", "error");
	}
	openMenuMessageId.value = null;
	openReactionMessageId.value = null;
}

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

let typingTimeout: any = null;
const isTypingLocal = ref(false);

function handleTyping() {
	if (!activePartnerId.value) return;

	if (!isTypingLocal.value) {
		isTypingLocal.value = true;
		if (echoChannel) {
			echoChannel.whisper("typing", {
				user_id: props.authUser.id,
				typing: true,
			});
		}
		if (onlineChannel) {
			onlineChannel.whisper("typing", {
				user_id: props.authUser.id,
				receiver_id: activePartnerId.value,
				typing: true,
			});
		}
	}

	if (typingTimeout) clearTimeout(typingTimeout);

	typingTimeout = setTimeout(() => {
		isTypingLocal.value = false;
		if (echoChannel) {
			echoChannel.whisper("typing", {
				user_id: props.authUser.id,
				typing: false,
			});
		}
		if (onlineChannel) {
			onlineChannel.whisper("typing", {
				user_id: props.authUser.id,
				receiver_id: activePartnerId.value,
				typing: false,
			});
		}
	}, 2000);
}

// ── Presence & Online Channel ────────────────────────────────────────────────
let onlineChannel: any = null;

function joinOnlinePresence() {
	onlineChannel = window.Echo.join("pagi.online")
		.here((users: Array<{ id: number; name: string }>) => {
			users.forEach((u) => {
				onlineUsers.value.add(u.id);
			});
		})
		.joining((user: { id: number; name: string }) => {
			onlineUsers.value.add(user.id);
		})
		.leaving((user: { id: number; name: string }) => {
			onlineUsers.value.delete(user.id);
		})
		.listenForWhisper("typing", (e: any) => {
			if (e.receiver_id === props.authUser.id) {
				typingUsers.value[e.user_id] = e.typing;
				if (e.user_id === activePartnerId.value) {
					isPartnerTyping.value = e.typing;
				}
			}
		});
}

// ── Echo Channel Subscription ─────────────────────────────────────────────────
let echoChannel: any = null;

function subscribeToChannel(conversationId: string) {
	if (echoChannel) {
		window.Echo.leave(`pagi.chat.${activeConversationId.value}`);
		echoChannel = null;
	}
	echoChannel = window.Echo.private(`pagi.chat.${conversationId}`)
		.listen(".message.sent", async (data: any) => {
			if (data.sender_id !== props.authUser.id) {
				const bodyDecrypted = await decryptText(data.body);
				const parentDecrypted = data.parent
					? {
							...data.parent,
							body: await decryptText(data.parent.body),
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
			if (data.receiver_id !== props.authUser.id) {
				messages.value.forEach((m) => {
					if (m.sender_id === props.authUser.id && !m.read_at) {
						m.read_at = data.read_at;
					}
				});

				const conv = conversationsList.value.find(
					(c) => c.conversation_id === conversationId,
				);
				if (conv && conv.last_message_sender_id === props.authUser.id) {
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

// ── User private channel subscription ─────────────────────────────────────────
let userChannel: any = null;

function subscribeToUserChannel() {
	if (userChannel) return;

	userChannel = window.Echo.private(`App.Models.User.${props.authUser.id}`)
		.listen(".message.sent", async (data: any) => {
			if (data.sender_id !== props.authUser.id) {
				const partnerPubKey =
					data.sender?.metadata?.pagi_e2e_pubkey ||
					conversationsList.value.find((c) => c.id === data.sender_id)?.metadata
						?.pagi_e2e_pubkey;

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

				let convIndex = conversationsList.value.findIndex(
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
			if (data.receiver_id !== props.authUser.id) {
				if (activePartnerId.value === data.receiver_id) {
					messages.value.forEach((m) => {
						if (m.sender_id === props.authUser.id && !m.read_at) {
							m.read_at = data.read_at;
						}
					});
				}

				const conv = conversationsList.value.find(
					(c) =>
						c.id === data.receiver_id ||
						c.conversation_id === data.conversation_id,
				);
				if (conv && conv.last_message_sender_id === props.authUser.id) {
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

// ── Open a Conversation ───────────────────────────────────────────────────────
async function openConversation(partnerId: number) {
	sessionStorage.setItem("pagi_active_chat", partnerId.toString());
	window.history.replaceState({}, "", "/pagi/messages");
	if (activePartnerId.value === partnerId) {
		mobileShowChat.value = true;
		return;
	}

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

		const decryptedMsgs = [];
		for (const msg of res.data.messages) {
			msg.body = await decryptText(msg.body);
			if (msg.parent) {
				msg.parent.body = await decryptText(msg.parent.body);
			}
			decryptedMsgs.push(msg);
		}
		messages.value = decryptedMsgs;
		hasMoreMessages.value = decryptedMsgs.length >= 30;

		subscribeToChannel(res.data.conversation_id);

		const conv = conversationsList.value.find((c) => c.id === partnerId);
		if (conv) {
			conv.unread_count = 0;
			conv.is_manual_unread = false;
			if (conv.last_message_sender_id !== props.authUser.id) {
				conv.last_message_read_at = new Date().toISOString();
			}
			const idx = conversationsList.value.findIndex((c) => c.id === partnerId);
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

// ── Send a Message ────────────────────────────────────────────────────────────
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
		sender_id: props.authUser.id,
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
		sender: props.authUser,
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
		conv.last_message_sender_id = props.authUser.id;
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

	if (typingTimeout) clearTimeout(typingTimeout);
	isTypingLocal.value = false;
	if (echoChannel) {
		echoChannel.whisper("typing", {
			user_id: props.authUser.id,
			typing: false,
		});
	}
	if (onlineChannel) {
		onlineChannel.whisper("typing", {
			user_id: props.authUser.id,
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

		res.data.body = await decryptText(res.data.body);
		if (res.data.parent) {
			res.data.parent.body = await decryptText(res.data.parent.body);
		}

		const idx = messages.value.findIndex((m) => m.id === optimisticId);
		if (idx !== -1) messages.value[idx] = res.data;

		const conv = conversationsList.value.find(
			(c) => c.id === activePartnerId.value,
		);
		if (conv) {
			conv.last_message = body;
			conv.last_message_at = res.data.created_at;
			conv.formatted_time = formatConversationTime(conv.last_message_at);
			conv.last_message_sender_id = props.authUser.id;
			conv.last_message_read_at = res.data.read_at;
			if (conv.last_message_sending) delete conv.last_message_sending;
			conv.last_message_id = res.data.id;
			conv.last_message_is_deleted = false;
			conv.last_message_is_deleted_for_me = false;

			const convIdx = conversationsList.value.findIndex(
				(c) => c.id === activePartnerId.value,
			);
			if (convIdx !== -1) {
				conversationsList.value[convIdx] = { ...conv };
			}
		}
	} catch (err) {
		messages.value = messages.value.filter((m) => m.id !== optimisticId);
		const conv = conversationsList.value.find(
			(c) => c.id === activePartnerId.value,
		);
		if (conv) {
			if (conv.last_message_sending) delete conv.last_message_sending;
			const convIdx = conversationsList.value.findIndex(
				(c) => c.id === activePartnerId.value,
			);
			if (convIdx !== -1) {
				conversationsList.value[convIdx] = { ...conv };
			}
		}
		console.error("Failed to send message", err);
	} finally {
		isSending.value = false;
	}
}

// ── Sidebar List Actions ──────────────────────────────────────────────────────
async function togglePin(conv: Conversation) {
	try {
		const res = await axios.post("/pagi/messages/pin", { partner_id: conv.id });
		if (res.data.success) {
			conv.is_pinned = res.data.is_pinned;
			showMobileContextMenu.value = false;
			activeDropdownConvId.value = null;

			const idx = conversationsList.value.findIndex((c) => c.id === conv.id);
			if (idx !== -1) {
				conversationsList.value[idx] = { ...conv };
			}
		}
	} catch (err) {
		console.error("Failed to toggle pin:", err);
	}
}

async function toggleArchive(conv: Conversation) {
	try {
		const res = await axios.post("/pagi/messages/archive", {
			partner_id: conv.id,
		});
		if (res.data.success) {
			conv.is_archived = res.data.is_archived;
			showMobileContextMenu.value = false;
			activeDropdownConvId.value = null;

			const idx = conversationsList.value.findIndex((c) => c.id === conv.id);
			if (idx !== -1) {
				conversationsList.value[idx] = { ...conv };
			}

			if (activePartnerId.value === conv.id) {
				closeConversation();
			}
		}
	} catch (err) {
		console.error("Failed to toggle archive:", err);
	}
}

async function toggleReadStatus(conv: Conversation) {
	try {
		const res = await axios.post("/pagi/messages/unread", {
			partner_id: conv.id,
		});
		if (res.data.success) {
			if (res.data.status === "read") {
				conv.unread_count = 0;
				conv.is_manual_unread = false;
			} else {
				conv.is_manual_unread = true;
				conv.unread_count = 0;
			}
			updateGlobalUnreadCount();
			showMobileContextMenu.value = false;
			activeDropdownConvId.value = null;

			const idx = conversationsList.value.findIndex((c) => c.id === conv.id);
			if (idx !== -1) {
				conversationsList.value[idx] = { ...conv };
			}
		}
	} catch (err) {
		console.error("Failed to toggle read status:", err);
	}
}

// ── Message Actions ───────────────────────────────────────────────────────────
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
		const conv = conversationsList.value.find(
			(c) => c.conversation_id === activeConversationId.value,
		);
		if (conv) {
			if (conv.last_message_id === messageId) {
				conv.last_message_is_deleted_for_me = true;
				conv.last_message_is_deleted = false;
				conv.last_message = "";
			}
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
		const conv = conversationsList.value.find(
			(c) => c.conversation_id === activeConversationId.value,
		);
		if (conv) {
			if (conv.last_message_id === messageId) {
				conv.last_message_is_deleted = true;
				conv.last_message_is_deleted_for_me = false;
				conv.last_message = "";
			}
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
		triggerToast(err?.response?.data?.error ?? "Gagal mengedit pesan", "error");
	}
	isEditingSending.value = false;
}

// ── Dropdown Actions (Header Dropdown) ─────────────────────────────────────────
function handleClearChat() {
	openConfirmModal("clear");
}

function toggleBlockUser() {
	if (isBlockedByMe.value) {
		openConfirmModal("unblock");
	} else {
		openConfirmModal("block");
	}
}

async function executeConfirmAction() {
	showConfirmModal.value = false;
	if (confirmModalType.value === "clear") {
		try {
			await axios.delete("/pagi/messages/clear-all/conversation", {
				data: { partner_id: activePartnerId.value },
			});
			messages.value = [];
			const conv = conversationsList.value.find(
				(c) => c.id === activePartnerId.value,
			);
			if (conv) {
				conv.last_message = null;
				conv.last_message_at = null;
				conv.last_message_is_deleted = false;
				conv.last_message_is_deleted_for_me = false;
			}
			triggerToast("Obrolan dibersihkan", "success");
		} catch (err) {
			console.error("Failed to clear chat", err);
			triggerToast("Gagal membersihkan obrolan", "error");
		}
	} else if (confirmModalType.value === "block") {
		try {
			await axios.post("/pagi/messages/block", {
				partner_id: activePartnerId.value,
			});
			isBlockedByMe.value = true;
			triggerToast("Pengguna diblokir", "success");
			const conv = conversationsList.value.find(
				(c) => c.id === activePartnerId.value,
			);
			if (conv) {
				conv.is_blocked_by_me = true;
			}
		} catch (err) {
			console.error("Failed to block user", err);
			triggerToast("Gagal memblokir pengguna", "error");
		}
	} else if (confirmModalType.value === "unblock") {
		try {
			await axios.post("/pagi/messages/unblock", {
				partner_id: activePartnerId.value,
			});
			isBlockedByMe.value = false;
			triggerToast("Pengguna dibuka blokirnya", "success");
			const conv = conversationsList.value.find(
				(c) => c.id === activePartnerId.value,
			);
			if (conv) {
				conv.is_blocked_by_me = false;
			}
		} catch (err) {
			console.error("Failed to unblock user", err);
			triggerToast("Gagal membuka blokir", "error");
		}
	} else if (confirmModalType.value === "delete-conversation") {
		const conv = deleteTargetConv.value;
		if (!conv) return;
		try {
			const res = await axios.delete("/pagi/messages/conversation/delete", {
				data: { partner_id: conv.id },
			});
			if (res.data.success) {
				conversationsList.value = conversationsList.value.filter(
					(c) => c.id !== conv.id,
				);
				updateGlobalUnreadCount();
				if (activePartnerId.value === conv.id) {
					closeConversation();
				}
				showMobileContextMenu.value = false;
				activeDropdownConvId.value = null;
				triggerToast("Obrolan dihapus", "success");
			}
		} catch (err) {
			console.error("Failed to delete conversation:", err);
			triggerToast("Gagal menghapus obrolan", "error");
		}
		deleteTargetConv.value = null;
	}
}

// ── Contact Pick & New Chat ───────────────────────────────────────────────────
async function openNewChatModal() {
	showNewChatModal.value = true;
	if (contactsList.value.length === 0) {
		isLoadingContacts.value = true;
		try {
			const res = await axios.get("/pagi/messages/contacts");
			contactsList.value = res.data;
		} catch (err) {
			console.error("Failed to load contacts", err);
		} finally {
			isLoadingContacts.value = false;
		}
	}
}

function startChatWith(contact: Contact) {
	showNewChatModal.value = false;

	const exists = conversationsList.value.some((c) => c.id === contact.id);
	if (!exists) {
		conversationsList.value.unshift({
			id: contact.id,
			name: contact.name,
			foto_path: contact.foto_path,
			last_message: null,
			last_message_at: null,
			formatted_time: "",
			unread_count: 0,
			conversation_id: [props.authUser.id, contact.id]
				.sort((a, b) => a - b)
				.join("_"),
			metadata: {},
		});
	}

	openConversation(contact.id);
}

// ── Infinite Scroll Pagination ────────────────────────────────────────────────
async function loadMoreMessages() {
	if (isLoadingMore.value || !hasMoreMessages.value || !activePartnerId.value)
		return;

	const firstMsg = messages.value[0];
	if (!firstMsg) return;

	isLoadingMore.value = true;

	try {
		const res = await axios.get(
			`/pagi/messages/${activePartnerId.value}?cursor_id=${firstMsg.id}`,
		);
		const fetchedMessages = res.data.messages || [];

		if (fetchedMessages.length < 30) {
			hasMoreMessages.value = false;
		}

		if (fetchedMessages.length > 0) {
			const container = chatWindowRef.value?.messagesContainer;
			const previousScrollHeight = container ? container.scrollHeight : 0;
			const previousScrollTop = container ? container.scrollTop : 0;

			const decryptedMsgs = [];
			for (const msg of fetchedMessages) {
				msg.body = await decryptText(msg.body);
				if (msg.parent) {
					msg.parent.body = await decryptText(msg.parent.body);
				}
				decryptedMsgs.push(msg);
			}

			messages.value = [...decryptedMsgs, ...messages.value];

			await nextTick();
			if (container) {
				const newScrollHeight = container.scrollHeight;
				container.scrollTop =
					previousScrollTop + (newScrollHeight - previousScrollHeight);
			}
		} else {
			hasMoreMessages.value = false;
		}
	} catch (err) {
		console.error("Failed to load more messages", err);
	} finally {
		isLoadingMore.value = false;
	}
}

// ── Click Outside & Body Scroll Locking ─────────────────────────────────────────
const clickOutsideListener = () => {
	openMenuMessageId.value = null;
	showHeaderDropdown.value = false;
	activeDropdownConvId.value = null;
};

onMounted(async () => {
	updateAllConversationTimes();
	timeUpdateInterval = setInterval(() => {
		updateAllConversationTimes();
	}, 10000);

	document.documentElement.style.overflow = "hidden";
	document.documentElement.style.height = "100%";
	document.body.style.overflow = "hidden";
	document.body.style.height = "100%";
	document.body.style.position = "fixed";
	document.body.style.width = "100%";

	window.addEventListener("click", clickOutsideListener);

	await initE2EKeys();
	await decryptSidebarPreviews(conversationsList.value);
	joinOnlinePresence();
	subscribeToUserChannel();

	const params = new URLSearchParams(window.location.search);
	const chatPartnerId =
		params.get("chat") || sessionStorage.getItem("pagi_active_chat");
	if (chatPartnerId) {
		const parsedId = parseInt(chatPartnerId, 10);
		if (parsedId === props.authUser.id) {
			sessionStorage.removeItem("pagi_active_chat");
			window.history.replaceState({}, "", "/pagi/messages");
		} else {
			openConversation(parsedId);
		}
	}

	axios
		.get("/pagi/messages/contacts")
		.then((res) => {
			followedContacts.value = res.data;
		})
		.catch((err) => {
			console.error("Failed to load followed contacts:", err);
		});
});

onUnmounted(() => {
	if (timeUpdateInterval) {
		clearInterval(timeUpdateInterval);
	}
	window.removeEventListener("click", clickOutsideListener);
	if (echoChannel && activeConversationId.value) {
		window.Echo.leave(`pagi.chat.${activeConversationId.value}`);
	}
	if (userChannel) {
		window.Echo.leave(`App.Models.User.${props.authUser.id}`);
	}
	if (onlineChannel) {
		window.Echo.leave("pagi.online");
	}

	document.documentElement.style.overflow = "";
	document.documentElement.style.height = "";
	document.body.style.overflow = "";
	document.body.style.height = "";
	document.body.style.position = "";
	document.body.style.width = "";
});
</script>

<template>
    <Head title="Pesan | PAGI" />

    <!-- Full-screen layout — no footer, no bottom padding -->
    <div class="fixed inset-0 flex flex-col h-dvh w-full max-w-full overflow-hidden bg-white dark:bg-zinc-950">
        <Navbar :roleName="roleName" />

        <!-- Main chat area fills remaining height -->
        <div class="flex flex-1 overflow-hidden min-h-0">

            <!-- ── Conversations Sidebar ─────────────────────────── -->
            <ChatSidebar
                :conversations-list="conversationsList"
                :followed-contacts="followedContacts"
                :auth-user="authUser"
                :active-partner-id="activePartnerId"
                :online-users="onlineUsers"
                :typing-users="typingUsers"
                :mobile-show-chat="mobileShowChat"
                v-model:show-archived-only="showArchivedOnly"
                v-model:active-dropdown-conv-id="activeDropdownConvId"
                @open-conversation="openConversation"
                @toggle-read-status="toggleReadStatus"
                @toggle-archive="toggleArchive"
                @toggle-pin="togglePin"
                @delete-conversation="confirmDeleteConversation"
                @long-press="(conv) => { mobileMenuConv = conv; showMobileContextMenu = true; }"
            />

            <!-- ── Chat Panel ───────────────────────────────────────── -->
            <ChatWindow
                ref="chatWindowRef"
                v-model="newMessageText"
                :messages="messages"
                :active-partner-id="activePartnerId"
                :active-partner="activePartner"
                :auth-user="authUser"
                :is-online="isOnline"
                :is-partner-typing="isPartnerTyping"
                :is-blocked-by-me="isBlockedByMe"
                :has-blocked-me="hasBlockedMe"
                :is-sending="isSending"
                :is-editing-sending="isEditingSending"
                :is-loading-messages="isLoadingMessages"
                :is-loading-more="isLoadingMore"
                :has-more-messages="hasMoreMessages"
                :edit-target-message="editTargetMessage"
                :replying-to-message="replyingToMessage"
                :show-dropdown="showHeaderDropdown"
                :open-menu-message-id="openMenuMessageId"
                :open-reaction-message-id="openReactionMessageId"
                :selected-mobile-message-id="selectedMobileMessageId"
                :online-users="onlineUsers"
                :mobile-show-chat="mobileShowChat"
                @update:show-dropdown="showHeaderDropdown = $event"
                @update:open-menu-message-id="openMenuMessageId = $event"
                @update:open-reaction-message-id="openReactionMessageId = $event"
                @update:selected-mobile-message-id="selectedMobileMessageId = $event"
                @back="closeConversation"
                @clear-chat="handleClearChat"
                @toggle-block="toggleBlockUser"
                @send="sendMessage"
                @typing="handleTyping"
                @cancel-edit="cancelEdit"
                @cancel-reply="replyingToMessage = null"
                @reply="handleReply"
                @copy="handleCopy"
                @react="handleReact"
                @delete="openDeleteModal"
                @edit="openEditModal"
                @load-more="loadMoreMessages"
            />
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Obrolan / Blokir Pengguna -->
    <ConfirmActionModal
        :show="showConfirmModal"
        :type="confirmModalType"
        @confirm="executeConfirmAction"
        @close="showConfirmModal = false"
    />

    <!-- Ultra-Premium Toast Notification System -->
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showToast" class="fixed bottom-6 right-6 z-[9999] max-w-sm bg-slate-900/95 dark:bg-zinc-900/95 text-white backdrop-blur-md border border-slate-800 dark:border-zinc-800 py-2.5 px-4 rounded-xl shadow-2xl flex items-center gap-3">
            <div :class="[
                'p-1.5 rounded-lg shrink-0',
                toastType === 'success' ? 'bg-emerald-500/10 text-emerald-400' :
                toastType === 'error' ? 'bg-rose-500/10 text-rose-400' :
                'bg-indigo-500/10 text-indigo-400'
            ]">
                <svg v-if="toastType === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <svg v-else-if="toastType === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.085 1.085l-.04.04m-1.085 1.085h.008v.008h-.008v-.008zM12 3a9 9 0 100 18 9 9 0 000-18z" /></svg>
            </div>
            <p class="text-[13px] font-semibold text-slate-100 pr-1">{{ toastMessage }}</p>
        </div>
    </Transition>

    <!-- ── Modal Hapus Pesan (WhatsApp Style) ────────────────────────────────── -->
    <DeleteMessageModal
        :show="showDeleteMessageModal"
        :is-sender="deleteTargetIsSender"
        @delete-for-everyone="executeDeleteForEveryone"
        @delete-for-me="executeDeleteForMe"
        @close="showDeleteMessageModal = false"
    />

    <!-- Modal Mulai Obrolan Baru (New Chat) -->
    <NewChatModal
        :show="showNewChatModal"
        :contacts="contactsList"
        :is-loading="isLoadingContacts"
        @start-chat="startChatWith"
        @close="showNewChatModal = false"
    />

    <!-- Mobile Context Menu (Bottom Sheet) -->
    <div v-if="showMobileContextMenu && mobileMenuConv" class="fixed inset-0 z-[999] flex items-end justify-center">
        <!-- Overlay (no blur) -->
        <div class="fixed inset-0 bg-black/60" @click="showMobileContextMenu = false"></div>
        
        <!-- Bottom Sheet content -->
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-t-2xl overflow-hidden shadow-2xl py-2 animate-in slide-in-from-bottom duration-250 select-none pb-safe">
            <!-- Drag handle indicator -->
            <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-800 rounded-full mx-auto my-2"></div>
            
            <div class="px-4 py-2.5 border-b border-slate-150 dark:border-zinc-800 flex items-center gap-3">
                <div class="h-8 w-8 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-850 flex items-center justify-center">
                    <img v-if="avatarUrl(mobileMenuConv.foto_path)" :src="avatarUrl(mobileMenuConv.foto_path)!" class="w-full h-full object-cover" />
                    <span v-else class="text-xs font-black text-slate-500">{{ mobileMenuConv.name.charAt(0) }}</span>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate">{{ mobileMenuConv.name }}</h4>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-500 truncate">{{ mobileMenuConv.last_message }}</p>
                </div>
            </div>

            <div class="flex flex-col py-1">
                <button 
                    @click="toggleReadStatus(mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <CheckCheck class="w-4 h-4 text-slate-400" />
                    <span>{{ (mobileMenuConv.unread_count > 0 || mobileMenuConv.is_manual_unread) ? 'Tandai sudah dibaca' : 'Tandai belum dibaca' }}</span>
                </button>
                
                <button 
                    @click="toggleArchive(mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <Archive class="w-4 h-4 text-slate-400" />
                    <span>{{ mobileMenuConv.is_archived ? 'Buka arsip' : 'Arsipkan' }}</span>
                </button>
                
                <button 
                    @click="togglePin(mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-zinc-300 active:bg-slate-50 dark:active:bg-zinc-800 transition-colors"
                >
                    <Pin class="w-4 h-4 text-slate-400 rotate-45" />
                    <span>{{ mobileMenuConv.is_pinned ? 'Lepas sematan' : 'Sematkan' }}</span>
                </button>
                
                <hr class="my-1.5 border-slate-100 dark:border-zinc-850" />
                
                <button 
                    @click="confirmDeleteConversation(mobileMenuConv)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-black text-red-650 dark:text-red-450 active:bg-red-50 dark:active:bg-red-950/20 transition-colors"
                >
                    <Trash2 class="w-4 h-4 text-red-500" />
                    <span>Hapus obrolan</span>
                </button>
            </div>
            
            <div class="px-4 py-2 mt-1 border-t border-slate-100 dark:border-zinc-850">
                <button 
                    @click="showMobileContextMenu = false"
                    class="w-full py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-850 active:scale-95 transition-all"
                >
                    Batal
                </button>
            </div>
        </div>
    </div>
</template>
