<script setup lang="ts">
import { Loader2 } from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import Modal from "../../ui/Modal.vue";

const props = defineProps<{
	show: boolean;
	form: any;
	user: any;
}>();

const emit = defineEmits(["close", "submit"]);

const usernameCheckStatus = ref<
	"idle" | "checking" | "available" | "taken" | "invalid"
>("idle");
const usernameSuggestions = ref<string[]>([]);
const usernameErrorMsg = ref("");
let usernameDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const usernameChangesCount = computed(
	() => props.user.metadata?.username_changes_count || 0,
);
const lastUsernameChangedAt = computed(
	() => props.user.metadata?.last_username_changed_at || null,
);

const canChangeUsername = computed(() => {
	if (!props.user.pagi_username) return { allowed: true };

	if (usernameChangesCount.value >= 3) {
		return {
			allowed: false,
			reason: "Batas perubahan username Anda telah habis (Maksimal 3 kali).",
		};
	}

	if (lastUsernameChangedAt.value) {
		const lastDate = new Date(lastUsernameChangedAt.value);
		const diffTime = Math.abs(Date.now() - lastDate.getTime());
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
		if (diffDays <= 30) {
			const daysLeft = 30 - Math.floor(diffTime / (1000 * 60 * 60 * 24));
			return {
				allowed: false,
				reason: `Anda baru saja mengubah username. Silakan tunggu ${daysLeft} hari lagi untuk mengubahnya kembali.`,
			};
		}
	}

	return { allowed: true };
});

const usernameEditBlock = computed(() => {
	const currentUsernameClean = (props.user.pagi_username || "")
		.toLowerCase()
		.trim();
	const inputUsernameClean = (props.form.pagi_username || "")
		.toLowerCase()
		.trim();
	if (inputUsernameClean === currentUsernameClean) {
		return null;
	}
	const status = canChangeUsername.value;
	if (!status.allowed) {
		return status.reason;
	}
	return null;
});

const formatDateString = (dateStr: any) => {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	return date.toLocaleDateString("id-ID", {
		year: "numeric",
		month: "long",
		day: "numeric",
	});
};

const checkUsernameAvailability = async (val: string) => {
	if (!val || val.length < 3) {
		usernameCheckStatus.value = val ? "invalid" : "idle";
		usernameErrorMsg.value = val ? "Minimal 3 karakter." : "";
		usernameSuggestions.value = [];
		return;
	}
	const formatted = val.toLowerCase();
	if (!/^[a-z0-9._]+$/.test(formatted)) {
		usernameCheckStatus.value = "invalid";
		usernameErrorMsg.value =
			"Hanya boleh huruf kecil, angka, titik (.), dan underscore (_).";
		usernameSuggestions.value = [];
		return;
	}
	if (formatted === (props.user.pagi_username || "").toLowerCase()) {
		usernameCheckStatus.value = "available";
		usernameErrorMsg.value = "";
		usernameSuggestions.value = [];
		return;
	}
	usernameCheckStatus.value = "checking";
	try {
		const res = await fetch(
			`/pagi/username/check?username=${encodeURIComponent(formatted)}`,
			{
				headers: { Accept: "application/json" },
			},
		);
		const data = await res.json();
		if (data.available) {
			usernameCheckStatus.value = "available";
			usernameErrorMsg.value = "";
			usernameSuggestions.value = [];
		} else {
			usernameCheckStatus.value = "taken";
			usernameErrorMsg.value = data.error || "Username sudah digunakan.";
			usernameSuggestions.value = data.suggestions || [];
		}
	} catch {
		usernameCheckStatus.value = "idle";
	}
};

watch(
	() => props.form.pagi_username,
	(val) => {
		if (usernameDebounceTimer) clearTimeout(usernameDebounceTimer);
		usernameDebounceTimer = setTimeout(
			() => checkUsernameAvailability(val),
			500,
		);
	},
);

onMounted(() => {
	if (props.form.pagi_username) {
		checkUsernameAvailability(props.form.pagi_username);
	}
});
</script>

<template>
	<Modal :show="show" title="Username PAGI" maxWidth="sm" @close="emit('close')">
		<form @submit.prevent="emit('submit')" class="space-y-5 text-left">
			<div>
				<label for="modal_pagi_username" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Username PAGI</label>
				<p class="text-xs text-slate-550 dark:text-slate-400 mb-3 leading-relaxed">Digunakan untuk pencarian dan identitas komentar di modul PAGI. Hanya boleh huruf kecil, angka, titik (.) dan underscore (_). Minimal 3 karakter.</p>

				<!-- Input with @ prefix -->
				<div class="relative flex items-center">
					<span class="absolute left-3.5 text-sm font-black text-slate-405 dark:text-slate-500 select-none pointer-events-none">@</span>
					<input
						id="modal_pagi_username"
						v-model="form.pagi_username"
						type="text"
						maxlength="30"
						autocomplete="off"
						autocorrect="off"
						spellcheck="false"
						placeholder="username_mu"
						class="w-full pl-8 pr-10 py-3 rounded-xl border text-sm font-semibold bg-transparent placeholder-slate-400 focus:outline-hidden focus:ring-1 transition-all shadow-2xs"
						:class="{
							'border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800': (usernameCheckStatus === 'idle' || usernameCheckStatus === 'checking') && !usernameEditBlock,
							'border-emerald-450 dark:border-emerald-500 text-slate-800 dark:text-white focus:ring-emerald-400': usernameCheckStatus === 'available' && !usernameEditBlock,
							'border-red-450 dark:border-red-500 text-slate-800 dark:text-white focus:ring-red-400': usernameCheckStatus === 'taken' || usernameCheckStatus === 'invalid' || !!usernameEditBlock,
						}"
					/>
					<!-- Status icon -->
					<div class="absolute right-3 flex items-center justify-center w-5 h-5">
						<svg v-if="usernameCheckStatus === 'checking'" class="w-4 h-4 animate-spin text-slate-405" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
						<svg v-else-if="usernameCheckStatus === 'available' && !usernameEditBlock" class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
						<svg v-else-if="usernameCheckStatus === 'taken' || usernameCheckStatus === 'invalid' || !!usernameEditBlock" class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
					</div>
				</div>

				<!-- Status message -->
				<p v-if="usernameCheckStatus === 'available' && !usernameEditBlock" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-1.5 flex items-center gap-1">
					<svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
					Username tersedia!
				</p>
				<p v-else-if="usernameEditBlock || form.errors.pagi_username" class="text-[11px] font-bold text-red-500 dark:text-red-400 mt-1.5">
					{{ usernameEditBlock || form.errors.pagi_username }}
				</p>
				<p v-else-if="usernameErrorMsg" class="text-[11px] font-bold text-red-500 dark:text-red-400 mt-1.5">{{ usernameErrorMsg }}</p>

				<!-- Suggestions when taken -->
				<div v-if="usernameCheckStatus === 'taken' && usernameSuggestions.length > 0 && !usernameEditBlock" class="mt-3">
					<p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Saran username:</p>
					<div class="flex flex-wrap gap-2">
						<button
							v-for="s in usernameSuggestions"
							:key="s"
							type="button"
							@click="form.pagi_username = s"
							class="px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-bold text-blue-600 dark:text-blue-400 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all cursor-pointer"
						>@{{ s }}</button>
					</div>
				</div>

				<!-- Changes Count & Info Box -->
				<div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-150 dark:border-slate-800 text-[10px] font-semibold text-slate-550 dark:text-slate-400 space-y-1">
					<div class="flex justify-between">
						<span>Perubahan Terpakai:</span>
						<span class="font-bold text-slate-700 dark:text-slate-300">{{ usernameChangesCount }}/3</span>
					</div>
					<div v-if="lastUsernameChangedAt" class="flex justify-between">
						<span>Terakhir Diubah:</span>
						<span class="font-bold text-slate-700 dark:text-slate-300">{{ formatDateString(lastUsernameChangedAt) }}</span>
					</div>
					<div class="text-[9px] text-slate-400 dark:text-slate-500 leading-normal pt-1 border-t border-slate-200/50 dark:border-slate-800/50 mt-1">
						* Username hanya dapat diubah maksimal 3 kali, dengan jeda minimal 1 bulan (30 hari) sejak perubahan terakhir.
					</div>
				</div>
			</div>

			<!-- Actions -->
			<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
				<button
					type="button"
					@click="emit('close')"
					class="h-11 px-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-xs cursor-pointer"
				>Batal</button>
				<button
					type="submit"
					:disabled="form.processing || usernameCheckStatus === 'taken' || usernameCheckStatus === 'invalid' || usernameCheckStatus === 'checking' || !!usernameEditBlock"
					class="h-11 px-6 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-xs flex items-center gap-2 cursor-pointer"
				>
					<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
					Simpan Username
				</button>
			</div>
		</form>
	</Modal>
</template>
