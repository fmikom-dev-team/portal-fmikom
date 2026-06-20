import { type Ref, ref } from "vue";
import type { AuthUser } from "../types";

export function useTypingIndicator(
	authUser: AuthUser,
	activePartnerId: Ref<number | null>,
	echoChannel: Ref<any>,
	onlineChannel: Ref<any>,
	typingTimeout: Ref<any>,
	isTypingLocal: Ref<boolean>,
	isPartnerTyping: Ref<boolean>,
	typingUsers: Ref<Record<number, boolean>>,
) {
	function handleTyping() {
		if (!activePartnerId.value) return;

		if (!isTypingLocal.value) {
			isTypingLocal.value = true;
			if (echoChannel.value) {
				echoChannel.value.whisper("typing", {
					user_id: authUser.id,
					typing: true,
				});
			}
			if (onlineChannel.value) {
				onlineChannel.value.whisper("typing", {
					user_id: authUser.id,
					receiver_id: activePartnerId.value,
					typing: true,
				});
			}
		}

		if (typingTimeout.value) clearTimeout(typingTimeout.value);

		typingTimeout.value = setTimeout(() => {
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
		}, 2000);
	}

	return {
		typingTimeout,
		isTypingLocal,
		isPartnerTyping,
		typingUsers,
		handleTyping,
	};
}
