<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
	AlertTriangle,
	ChevronRight,
	Edit,
	Send,
	ShieldAlert,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import Modal from "./Modal.vue";

const props = defineProps<{
	show: boolean;
	notification: any;
}>();

const emit = defineEmits(["close"]);

const appealText = ref("");
const isSubmitting = ref(false);
const showAppealForm = ref(false);
const appealSuccess = ref(false);
const errorMessage = ref("");

// Safely extract notification payload
const payload = computed(() => {
	if (!props.notification) return {};
	return (
		props.notification.data || props.notification.extra || props.notification
	);
});

const isTakedown = computed(() => {
	return (
		props.notification?.type === "admin_takedown" ||
		payload.value?.type === "admin_takedown"
	);
});

const workId = computed(() => {
	return payload.value?.work_id;
});

const workTitle = computed(() => {
	return payload.value?.work_title || payload.value?.title || "Karya Anda";
});

const adminReason = computed(() => {
	return (
		payload.value?.message ||
		payload.value?.reason ||
		"Konten Anda terdeteksi melanggar panduan komunitas kami."
	);
});

const adminId = computed(() => {
	return payload.value?.admin_id || 1; // Fallback to user ID 1 (admin)
});

const editUrl = computed(() => {
	return (
		payload.value?.edit_url ||
		(workId.value ? `/pagi/editor?id=${workId.value}` : null)
	);
});

const handleEdit = () => {
	if (editUrl.value) {
		emit("close");
		router.visit(editUrl.value);
	}
};

const submitAppeal = async () => {
	if (!appealText.value.trim()) {
		errorMessage.value = "Pesan banding tidak boleh kosong.";
		return;
	}

	isSubmitting.value = ref(true).value;
	errorMessage.value = "";

	try {
		const res = await fetch("/pagi/messages", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-TOKEN": (document.querySelector("meta[name=csrf-token]") as any)
					?.content,
				Accept: "application/json",
			},
			body: JSON.stringify({
				receiver_id: adminId.value,
				body: `[AJUAN BANDING - ${isTakedown.value ? "TAKEDOWN" : "PERINGATAN"}]\n\nKarya: "${workTitle.value}" (ID: ${workId.value || "N/A"})\nAlasan Moderasi: "${adminReason.value}"\n\nPesan Banding:\n"${appealText.value.trim()}"`,
			}),
		});

		const data = await res.json();
		if (res.ok) {
			appealSuccess.value = true;
			appealText.value = "";
		} else {
			errorMessage.value =
				data.error ||
				data.message ||
				"Gagal mengirim banding. Silakan coba lagi.";
		}
	} catch (e) {
		console.error(e);
		errorMessage.value =
			"Terjadi kesalahan koneksi. Silakan coba beberapa saat lagi.";
	} finally {
		isSubmitting.value = false;
	}
};
</script>

<template>
	<Modal :show="show" :title="isTakedown ? 'Tindakan Takedown Admin' : 'Peringatan Moderasi Admin'" max-width="md" @close="emit('close')">
		<div class="flex flex-col gap-5">
			
			<!-- Severity Banner -->
			<div 
				class="flex items-start gap-3.5 p-4 rounded-2xl border"
				:class="isTakedown 
					? 'bg-rose-50/70 border-rose-100 dark:bg-rose-950/20 dark:border-rose-900/30 text-rose-800 dark:text-rose-300' 
					: 'bg-amber-50/70 border-amber-100 dark:bg-amber-950/20 dark:border-amber-900/30 text-amber-800 dark:text-amber-300'"
			>
				<div 
					class="p-2 rounded-xl"
					:class="isTakedown ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white'"
				>
					<component :is="isTakedown ? ShieldAlert : AlertTriangle" class="w-5 h-5 shrink-0" />
				</div>
				<div class="flex-1 min-w-0">
					<h4 class="text-[13px] font-black leading-snug">
						{{ isTakedown ? 'Karya Diturunkan Sementara' : 'Peringatan Pelanggaran Konten' }}
					</h4>
					<p class="text-[11px] mt-0.5 opacity-90 leading-relaxed">
						{{ isTakedown 
							? 'Karya Anda telah disembunyikan dari galeri publik untuk mematuhi pedoman komunitas.' 
							: 'Akun Anda mendapat peringatan moderasi karena konten yang tidak sesuai.' }}
					</p>
				</div>
			</div>

			<!-- Moderation Details Card -->
			<div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80 flex flex-col gap-3">
				<div class="flex flex-col gap-0.5">
					<span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-zinc-500">Nama Karya</span>
					<span class="text-[12px] font-bold text-slate-800 dark:text-zinc-200">
						{{ workTitle }}
					</span>
				</div>
				<div class="h-px bg-slate-200/60 dark:bg-slate-800/60" />
				<div class="flex flex-col gap-0.5">
					<span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-zinc-500">Keterangan / Alasan Admin</span>
					<p class="text-[11px] leading-relaxed text-slate-600 dark:text-zinc-400 italic">
						"{{ adminReason }}"
					</p>
				</div>
			</div>

			<!-- Primary Actions -->
			<div class="flex items-center gap-2.5">
				<button 
					v-if="editUrl"
					@click="handleEdit" 
					class="flex-1 flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-[12px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-200 dark:shadow-none hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer"
				>
					<Edit class="w-3.5 h-3.5" />
					Perbaiki / Edit Karya
				</button>
				<button 
					@click="showAppealForm = !showAppealForm; appealSuccess = false" 
					class="flex-1 flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-[12px] font-bold border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer"
					:class="{ 'bg-slate-100 dark:bg-slate-800': showAppealForm }"
				>
					Ajukan Banding
				</button>
			</div>

			<!-- Appeal Form -->
			<Transition
				enter-active-class="transition duration-200 ease-out"
				enter-from-class="opacity-0 -translate-y-2"
				enter-to-class="opacity-100 translate-y-0"
				leave-active-class="transition duration-150 ease-in"
				leave-from-class="opacity-100 translate-y-0"
				leave-to-class="opacity-0 -translate-y-2"
			>
				<div v-if="showAppealForm" class="flex flex-col gap-3.5 pt-3 border-t border-slate-100 dark:border-slate-800">
					
					<div v-if="appealSuccess" class="flex flex-col items-center justify-center py-4 text-center bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl p-4">
						<span class="text-[12px] font-bold text-emerald-800 dark:text-emerald-300">Ajuan Banding Terkirim!</span>
						<p class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1">
							Pesan banding Anda telah dikirim langsung ke chat admin. Admin akan meninjau ajuan Anda secepatnya.
						</p>
					</div>

					<div v-else class="flex flex-col gap-3">
						<div class="flex flex-col gap-1">
							<label class="text-[11px] font-bold text-slate-700 dark:text-zinc-300">Alasan Banding Anda</label>
							<textarea 
								v-model="appealText"
								placeholder="Tuliskan argumen atau penjelasan banding Anda secara rinci..." 
								rows="4"
								class="w-full text-[11px] p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
								:disabled="isSubmitting"
							></textarea>
						</div>

						<div v-if="errorMessage" class="text-[10px] text-rose-600 font-bold">
							{{ errorMessage }}
						</div>

						<button 
							@click="submitAppeal" 
							:disabled="isSubmitting"
							class="w-full h-10 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white text-[12px] font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-55"
						>
							<span v-if="isSubmitting">Mengirim...</span>
							<template v-else>
								<Send class="w-3.5 h-3.5" />
								Kirim Ajuan Banding
							</template>
						</button>
					</div>
				</div>
			</Transition>
		</div>
	</Modal>
</template>
