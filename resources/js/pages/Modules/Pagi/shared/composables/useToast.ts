import { ref } from "vue";

export interface Toast {
	id: number;
	message: string;
	type: string;
}

export function useToast() {
	const toasts = ref<Toast[]>([]);

	const addToast = (message: string, type = "success") => {
		const id = Date.now();
		toasts.value.push({ id, message, type });
		setTimeout(() => {
			removeToast(id);
		}, 5000);
	};

	const removeToast = (id: number) => {
		toasts.value = toasts.value.filter((t) => t.id !== id);
	};

	return {
		toasts,
		addToast,
		removeToast,
	};
}
