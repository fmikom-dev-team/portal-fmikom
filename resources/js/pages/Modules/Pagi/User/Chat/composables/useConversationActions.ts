import axios from "axios";
import type { Ref } from "vue";
import type { Conversation, Message } from "../types";

interface UseConversationActionsParams {
	activePartnerId: Ref<number | null>;
	conversationsList: Ref<Conversation[]>;
	messages: Ref<Message[]>;
	isBlockedByMe: Ref<boolean>;
	deleteTargetConv: Ref<Conversation | null>;
	showConfirmModal: Ref<boolean>;
	confirmModalType: Ref<"clear" | "block" | "unblock" | "delete-conversation">;
	showMobileContextMenu: Ref<boolean>;
	activeDropdownConvId: Ref<number | null>;
	triggerToast: (msg: string, type?: "success" | "info" | "error") => void;
	closeConversation: () => void;
	updateGlobalUnreadCount: () => void;
	updateConversation: (
		partnerId: number,
		updater: (conv: Conversation) => Partial<Conversation> | undefined,
	) => void;
}

export function useConversationActions({
	activePartnerId,
	conversationsList,
	messages,
	isBlockedByMe,
	deleteTargetConv,
	showConfirmModal,
	confirmModalType,
	showMobileContextMenu,
	activeDropdownConvId,
	triggerToast,
	closeConversation,
	updateGlobalUnreadCount,
	updateConversation,
}: UseConversationActionsParams) {
	async function togglePin(conv: Conversation) {
		try {
			const res = await axios.post("/pagi/messages/pin", {
				partner_id: conv.id,
			});
			if (res.data.success) {
				updateConversation(conv.id, (c) => {
					c.is_pinned = res.data.is_pinned;
				});
				showMobileContextMenu.value = false;
				activeDropdownConvId.value = null;
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
				updateConversation(conv.id, (c) => {
					c.is_archived = res.data.is_archived;
				});
				showMobileContextMenu.value = false;
				activeDropdownConvId.value = null;

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
				updateConversation(conv.id, (c) => {
					if (res.data.status === "read") {
						c.unread_count = 0;
						c.is_manual_unread = false;
					} else {
						c.is_manual_unread = true;
						c.unread_count = 0;
					}
				});
				updateGlobalUnreadCount();
				showMobileContextMenu.value = false;
				activeDropdownConvId.value = null;
			}
		} catch (err) {
			console.error("Failed to toggle read status:", err);
		}
	}

	async function clearConversation() {
		try {
			await axios.delete("/pagi/messages/clear-all/conversation", {
				data: { partner_id: activePartnerId.value },
			});
			messages.value = [];
			if (activePartnerId.value !== null) {
				updateConversation(activePartnerId.value, (c) => {
					c.last_message = null;
					c.last_message_at = null;
					c.last_message_is_deleted = false;
					c.last_message_is_deleted_for_me = false;
				});
			}
			triggerToast("Obrolan dibersihkan", "success");
		} catch (err) {
			console.error("Failed to clear chat", err);
			triggerToast("Gagal membersihkan obrolan", "error");
		}
	}

	async function blockUser() {
		try {
			await axios.post("/pagi/messages/block", {
				partner_id: activePartnerId.value,
			});
			isBlockedByMe.value = true;
			triggerToast("Pengguna diblokir", "success");
			if (activePartnerId.value !== null) {
				updateConversation(activePartnerId.value, (c) => {
					c.is_blocked_by_me = true;
				});
			}
		} catch (err) {
			console.error("Failed to block user", err);
			triggerToast("Gagal memblokir pengguna", "error");
		}
	}

	async function unblockUser() {
		try {
			await axios.post("/pagi/messages/unblock", {
				partner_id: activePartnerId.value,
			});
			isBlockedByMe.value = false;
			triggerToast("Pengguna dibuka blokirnya", "success");
			if (activePartnerId.value !== null) {
				updateConversation(activePartnerId.value, (c) => {
					c.is_blocked_by_me = false;
				});
			}
		} catch (err) {
			console.error("Failed to unblock user", err);
			triggerToast("Gagal membuka blokir", "error");
		}
	}

	async function deleteConversation() {
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

	async function executeConfirmAction() {
		showConfirmModal.value = false;
		if (confirmModalType.value === "clear") {
			await clearConversation();
		} else if (confirmModalType.value === "block") {
			await blockUser();
		} else if (confirmModalType.value === "unblock") {
			await unblockUser();
		} else if (confirmModalType.value === "delete-conversation") {
			await deleteConversation();
		}
	}

	return {
		togglePin,
		toggleArchive,
		toggleReadStatus,
		clearConversation,
		blockUser,
		unblockUser,
		deleteConversation,
		executeConfirmAction,
	};
}
