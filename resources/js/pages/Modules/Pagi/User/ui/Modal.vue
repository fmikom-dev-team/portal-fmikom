<script setup lang="ts">
import { X } from "lucide-vue-next";
import { onMounted, onUnmounted, watch } from "vue";

const props = withDefaults(
	defineProps<{
		show: boolean;
		title?: string;
		maxWidth?: "sm" | "md" | "lg" | "xl" | "2xl" | "3xl" | "4xl" | "5xl";
		preventClose?: boolean;
	}>(),
	{
		maxWidth: "md",
		preventClose: false,
	},
);

const emit = defineEmits(["close"]);

const close = () => {
	if (props.preventClose) return;
	emit("close");
};

// Handle Escape key
const handleEscape = (e: KeyboardEvent) => {
	if (e.key === "Escape" && props.show && !props.preventClose) {
		close();
	}
};

// ── Shared scroll lock using DOM as counter ────────────────────────────────────
// document.body.dataset.modalCount is shared across ALL Modal instances,
// so overflow is only restored when every modal is closed.
function lockScroll() {
	const count = parseInt(document.body.dataset.modalCount || "0", 10) + 1;
	document.body.dataset.modalCount = String(count);
	document.body.style.overflow = "hidden";
}

function unlockScroll() {
	const count = Math.max(
		0,
		parseInt(document.body.dataset.modalCount || "0", 10) - 1,
	);
	document.body.dataset.modalCount = String(count);
	if (count === 0) {
		document.body.style.overflow = "";
		delete document.body.dataset.modalCount;
	}
}
// ─────────────────────────────────────────────────────────────────────────────

watch(
	() => props.show,
	(value, oldValue) => {
		if (value && !oldValue) {
			lockScroll();
		} else if (!value && oldValue) {
			unlockScroll();
		}
	},
);

onMounted(() => {
	window.addEventListener("keydown", handleEscape);
	if (props.show) {
		lockScroll();
	}
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleEscape);
	if (props.show) {
		// Modal was destroyed while still open — release its lock
		unlockScroll();
	}
});

const maxWidthClass = {
	sm: "max-w-sm",
	md: "max-w-md",
	lg: "max-w-lg",
	xl: "max-w-xl",
	"2xl": "max-w-2xl",
	"3xl": "max-w-3xl",
	"4xl": "max-w-4xl",
	"5xl": "max-w-5xl",
}[props.maxWidth];
</script>

<template>
	<Teleport to="body">
		<Transition name="modal-fade">
			<div
				v-if="show"
				class="fixed inset-0 z-[1000] flex items-center justify-center p-4 bg-slate-950/25 dark:bg-slate-950/40 overflow-y-auto"
				@click.self="close"
			>
				<div
					class="modal-panel relative bg-white dark:bg-slate-900 w-full rounded-[24px] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col"
					:class="maxWidthClass"
				>
					<!-- Modal Header -->
					<div
						v-if="title || $slots['header-subtitle']"
						class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/20"
					>
						<div>
							<h3
								v-if="title"
								class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white"
							>
								{{ title }}
							</h3>
							<slot name="header-subtitle"></slot>
						</div>
						<button
							v-if="!preventClose"
							@click="close"
							class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition-colors cursor-pointer"
						>
							<X class="w-4 h-4" />
						</button>
					</div>

					<!-- Modal Body -->
					<div class="p-6 overflow-y-auto max-h-[70vh]">
						<slot></slot>
					</div>

					<!-- Modal Footer (Optional) -->
					<div
						v-if="$slots.footer"
						class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 bg-slate-50/50 dark:bg-slate-950/20"
					>
						<slot name="footer"></slot>
					</div>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>

<style scoped>
/* Outer backdrop fade transition */
.modal-fade-enter-active {
	transition: opacity 300ms ease-out;
}
.modal-fade-leave-active {
	transition: opacity 200ms ease-in;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
	opacity: 0 !important;
}

/* Inner panel slide/scale/fade transition coordinated with the outer backdrop */
.modal-fade-enter-active .modal-panel {
	transition: transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 300ms ease-out;
}
.modal-fade-leave-active .modal-panel {
	transition: transform 200ms ease-in, opacity 200ms ease-in;
}
.modal-fade-enter-from .modal-panel,
.modal-fade-leave-to .modal-panel {
	opacity: 0 !important;
	transform: scale(0.95) translateY(16px);
}
</style>
