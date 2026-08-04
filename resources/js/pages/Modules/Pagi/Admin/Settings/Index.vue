<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	Activity,
	AlertTriangle,
	Bell,
	Globe,
	MessageCircle,
	MessageSquare,
	Settings2,
	Shield,
	Trash2,
	Upload,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	settings: {
		siteName: string;
		maxUploadSizeMb: number;
		allowPublicWork: boolean;
		requireEmailVerification: boolean;
		autoModeration: boolean;
		maxWarningsBeforeSuspend: number;
		rateLimitPerMinute: number;
		enableActivityLog: boolean;
		notifyOnReport: boolean;
		notifyOnNewUser: boolean;
		notifyOnTakedown: boolean;
		enableComments: boolean;
		commentAudience: string;
		commentCensorMode?: string;
		customBannedWords?: string[];
		customImageRules?: string[];
		enableLocalEngine?: boolean;
		enableGoogleAi?: boolean;
		enableVisionAi?: boolean;
		googleAiApiKey?: string;
		googleAiModel?: string;
	};
	adminRole?: string;
}>();

const isSuperAdmin = computed(() => props.adminRole === "super-admin");

const activeSection = ref<
	"general" | "moderation" | "security" | "notifications"
>("general");

const sections = [
	{ key: "general", label: "Umum", icon: Settings2 },
	{ key: "moderation", label: "Moderasi", icon: Shield },
	{ key: "security", label: "Keamanan", icon: AlertTriangle },
	{ key: "notifications", label: "Notifikasi", icon: Bell },
] as const;

const form = useForm({
	siteName: props.settings?.siteName ?? "PAGI – Works & Gallery",
	maxUploadSizeMb: props.settings?.maxUploadSizeMb ?? 10,
	allowPublicWork: props.settings?.allowPublicWork ?? true,
	requireEmailVerification: props.settings?.requireEmailVerification ?? true,
	autoModeration: props.settings?.autoModeration ?? false,
	maxWarningsBeforeSuspend: props.settings?.maxWarningsBeforeSuspend ?? 3,
	rateLimitPerMinute: props.settings?.rateLimitPerMinute ?? 60,
	enableActivityLog: props.settings?.enableActivityLog ?? true,
	notifyOnReport: props.settings?.notifyOnReport ?? true,
	notifyOnNewUser: props.settings?.notifyOnNewUser ?? false,
	notifyOnTakedown: props.settings?.notifyOnTakedown ?? true,
	enableChat: props.settings?.enableChat ?? true,
	enableComments: props.settings?.enableComments ?? true,
	commentAudience: props.settings?.commentAudience ?? "mahasiswa_mitra",
	commentCensorMode: props.settings?.commentCensorMode ?? "reject",
	customBannedWords: props.settings?.customBannedWords ?? [],
	customImageRules: props.settings?.customImageRules ?? [],
	enableLocalEngine: props.settings?.enableLocalEngine ?? true,
	enableGoogleAi: props.settings?.enableGoogleAi ?? false,
	enableVisionAi: props.settings?.enableVisionAi ?? true,
	googleAiApiKey: props.settings?.googleAiApiKey ?? "",
	googleAiModel: props.settings?.googleAiModel ?? "gemini-flash-latest",
});

const newSettingsBannedWord = ref("");
const addSettingsBannedWord = () => {
	const word = newSettingsBannedWord.value.trim().toLowerCase();
	if (word && !form.customBannedWords.includes(word)) {
		form.customBannedWords.push(word);
		newSettingsBannedWord.value = "";
	}
};
const removeSettingsBannedWord = (index: number) => {
	form.customBannedWords.splice(index, 1);
};

const newCustomImageRule = ref("");
const addCustomImageRule = () => {
	const rule = newCustomImageRule.value.trim();
	if (rule && !form.customImageRules.includes(rule)) {
		form.customImageRules.push(rule);
		newCustomImageRule.value = "";
	}
};
const removeCustomImageRule = (index: number) => {
	form.customImageRules.splice(index, 1);
};

const isTestingAi = ref(false);
const isFetchingModels = ref(false);
const aiTestResult = ref<{ success?: boolean; message?: string } | null>(null);

const availableGeminiModels = ref<Array<{ id: string; name: string }>>([
	{
		id: "gemini-flash-latest",
		name: "gemini-flash-latest ⭐ (Dokploy Default - Otomatis Best Flash)",
	},
	{
		id: "gemini-pro-latest",
		name: "gemini-pro-latest (Dokploy Default - Otomatis Best Pro)",
	},
	{ id: "gemini-2.0-flash", name: "gemini-2.0-flash (Model Terbaru)" },
	{ id: "gemini-1.5-flash", name: "gemini-1.5-flash (Stabil)" },
]);

const handleFetchGoogleModels = async () => {
	if (!form.googleAiApiKey) {
		aiTestResult.value = {
			success: false,
			message: "Harap masukkan Google Gemini API Key terlebih dahulu.",
		};
		return;
	}

	isFetchingModels.value = true;
	try {
		const response = await fetch(
			"/pagi/admin/settings/fetch-google-ai-models",
			{
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					"X-CSRF-TOKEN":
						(
							document.querySelector(
								'meta[name="csrf-token"]',
							) as HTMLMetaElement
						)?.content || "",
				},
				body: JSON.stringify({
					apiKey: form.googleAiApiKey,
				}),
			},
		);

		const data = await response.json();
		if (response.ok && data.models && data.models.length > 0) {
			availableGeminiModels.value = data.models;
			aiTestResult.value = {
				success: true,
				message:
					data.message ||
					`${data.models.length} model resmi berhasil dimuat dari Google AI Studio!`,
			};
		} else {
			aiTestResult.value = {
				success: false,
				message: data.message || "Gagal memuat model dari Google.",
			};
		}
	} catch (e: any) {
		aiTestResult.value = {
			success: false,
			message: `Kesalahan jaringan: ${e.message}`,
		};
	} finally {
		isFetchingModels.value = false;
	}
};

const handleTestGoogleAi = async () => {
	if (!form.googleAiApiKey) {
		aiTestResult.value = {
			success: false,
			message: "Harap masukkan Google Gemini API Key terlebih dahulu.",
		};
		return;
	}

	isTestingAi.value = true;
	aiTestResult.value = null;

	try {
		const response = await fetch("/pagi/admin/test-google-ai", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-TOKEN":
					(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
						?.content || "",
			},
			body: JSON.stringify({
				apiKey: form.googleAiApiKey,
				model: form.googleAiModel,
			}),
		});

		const data = await response.json();
		aiTestResult.value = {
			success: response.ok,
			message:
				data.message ||
				(response.ok ? "Koneksi API Berhasil!" : "Gagal terhubung ke API."),
		};
	} catch (e: any) {
		aiTestResult.value = {
			success: false,
			message: `Kesalahan jaringan: ${e.message}`,
		};
	} finally {
		isTestingAi.value = false;
	}
};

const submitSettings = () => {
	form.post("/pagi/admin/settings", {
		preserveScroll: true,
	});
};

// ─── Danger Zone State ────────────────────────────────────────────────────────
const showDangerModal = ref(false);
const dangerPassword = ref("");
const dangerConfirmation = ref("");
const dangerProcessing = ref(false);
const dangerPasswordError = ref("");
const dangerConfirmationError = ref("");

const confirmationText = "HAPUS SEMUA KARYA";
const isConfirmationValid = computed(
	() => dangerConfirmation.value === confirmationText,
);
const isFormComplete = computed(
	() => dangerPassword.value.length >= 1 && isConfirmationValid.value,
);

const openDangerModal = () => {
	dangerPassword.value = "";
	dangerConfirmation.value = "";
	dangerPasswordError.value = "";
	dangerConfirmationError.value = "";
	showDangerModal.value = true;
};

const closeDangerModal = () => {
	showDangerModal.value = false;
};

const submitDangerReset = () => {
	if (!isFormComplete.value) return;
	dangerPasswordError.value = "";
	dangerConfirmationError.value = "";
	dangerProcessing.value = true;

	router.delete("/pagi/admin/reset-all-works", {
		data: {
			password: dangerPassword.value,
			confirmation: dangerConfirmation.value,
		},
		preserveScroll: true,
		onSuccess: () => {
			showDangerModal.value = false;
			dangerPassword.value = "";
			dangerConfirmation.value = "";
			toast.success(
				"Seluruh karya portofolio berhasil dihapus. Database kini bersih!",
			);
		},
		onError: (errors) => {
			if (errors.password) dangerPasswordError.value = errors.password;
			if (errors.confirmation)
				dangerConfirmationError.value = errors.confirmation;
			toast.error(
				"Verifikasi gagal. Periksa kembali password dan teks konfirmasi Anda.",
			);
		},
		onFinish: () => {
			dangerProcessing.value = false;
		},
	});
};
</script>

<template>
    <PagiAdminLayout title="Pengaturan">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Pengaturan Modul</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500 font-medium">Konfigurasi sistem dan preferensi admin PAGI</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

            <!-- Sidebar Nav -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-2 h-fit">
                <button
                    v-for="s in sections"
                    :key="s.key"
                    @click="activeSection = s.key"
                    :class="[
                        'w-full flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-[13px] font-semibold text-left transition-all',
                        activeSection === s.key
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                    ]"
                >
                    <component :is="s.icon" :class="['h-4 w-4 shrink-0', activeSection === s.key ? 'text-white' : 'text-slate-400 dark:text-zinc-500']" />
                    {{ s.label }}
                </button>
            </div>

            <!-- Settings Panel -->
            <div class="lg:col-span-3 space-y-4">

                <!-- GENERAL -->
                <template v-if="activeSection === 'general'">

                    <!-- Platform Config -->
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Konfigurasi Platform</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Nama dan batas upload file</p>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Nama Platform</label>
                                <input v-model="form.siteName" type="text" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Ukuran Maks Upload (MB)</label>
                                <input v-model="form.maxUploadSizeMb" type="number" min="1" max="100" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Feature Cards: Komunikasi -->
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Fitur Komunikasi</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Aktifkan atau nonaktifkan fitur interaksi antar pengguna. Klik kartu untuk toggle.</p>
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">

                            <!-- Chat Card -->
                            <button
                                type="button"
                                @click="form.enableChat = !form.enableChat"
                                :class="[
                                    'w-full text-left rounded-xl border-2 p-4 transition-all duration-200 cursor-pointer hover:shadow-md',
                                    form.enableChat
                                        ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/10'
                                        : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/30'
                                ]"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div :class="['h-9 w-9 rounded-xl flex items-center justify-center transition-colors duration-200', form.enableChat ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'bg-slate-100 dark:bg-zinc-700']">
                                        <MessageCircle :class="['h-[17px] w-[17px] transition-colors duration-200', form.enableChat ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-zinc-500']" />
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold', form.enableChat ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-zinc-700 text-slate-400 dark:text-zinc-500']">
                                            {{ form.enableChat ? '\u25cf Aktif' : '\u25cb Nonaktif' }}
                                        </span>
                                        <div :class="['relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200', form.enableChat ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-600']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.enableChat ? 'translate-x-4' : 'translate-x-0']" />
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Pesan Langsung (Chat)</p>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                    Izinkan mahasiswa dan mitra mengirim pesan pribadi secara realtime.
                                </p>
                                <div v-if="!form.enableChat" class="mt-2.5 flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                    <AlertTriangle class="h-3 w-3 shrink-0" />
                                    Akses /messages akan diblokir otomatis
                                </div>
                            </button>

                            <!-- Comments Card — with Audience Control -->
                            <div
                                :class="[
                                    'w-full rounded-xl border-2 p-4 transition-all duration-200',
                                    form.enableComments
                                        ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/10'
                                        : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/30'
                                ]"
                            >
                                <!-- Header Row: icon + toggle -->
                                <button type="button" @click="form.enableComments = !form.enableComments" class="w-full text-left cursor-pointer">
                                    <div class="flex items-start justify-between mb-3">
                                        <div :class="['h-9 w-9 rounded-xl flex items-center justify-center transition-colors duration-200', form.enableComments ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'bg-slate-100 dark:bg-zinc-700']">
                                            <MessageSquare :class="['h-[17px] w-[17px] transition-colors duration-200', form.enableComments ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-zinc-500']" />
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold', form.enableComments ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-zinc-700 text-slate-400 dark:text-zinc-500']">
                                                {{ form.enableComments ? '● Aktif' : '○ Nonaktif' }}
                                            </span>
                                            <div :class="['relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200', form.enableComments ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-600']">
                                                <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.enableComments ? 'translate-x-4' : 'translate-x-0']" />
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Komentar Karya</p>
                                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                        Izinkan pengguna memberikan komentar pada karya portfolio mahasiswa.
                                    </p>
                                    <div v-if="!form.enableComments" class="mt-2.5 flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                        <AlertTriangle class="h-3 w-3 shrink-0" />
                                        Form komentar tersembunyi di semua karya
                                    </div>
                                </button>

                                <!-- Audience Control (only when comments enabled) -->
                                <div v-if="form.enableComments" class="mt-3 pt-3 border-t border-indigo-100 dark:border-indigo-900/30">
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-widest mb-1.5">Siapa yang boleh berkomentar?</label>
                                    <select
                                        v-model="form.commentAudience"
                                        class="w-full rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer"
                                        @click.stop
                                    >
                                        <option value="all">🌐 Semua pengguna login</option>
                                        <option value="mahasiswa_mitra">🎓 Mahasiswa &amp; Mitra saja</option>
                                        <option value="mahasiswa_only">🔒 Mahasiswa saja</option>
                                    </select>
                                    <p class="mt-1 text-[10px] text-slate-400 dark:text-zinc-500">
                                        <template v-if="form.commentAudience === 'mahasiswa_only'">Hanya mahasiswa aktif yang dapat berkomentar. Mitra, dosen, dan tamu tidak bisa.</template>
                                        <template v-else-if="form.commentAudience === 'mahasiswa_mitra'">Mahasiswa dan mitra dapat berkomentar. Dosen dan tamu tidak bisa.</template>
                                        <template v-else>Semua pengguna yang sudah login dapat berkomentar.</template>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Visibilitas Card -->
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Visibilitas &amp; Akses</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Kontrol siapa yang bisa melihat konten galeri</p>
                        </div>
                        <div class="p-4">
                            <button
                                type="button"
                                @click="form.allowPublicWork = !form.allowPublicWork"
                                :class="[
                                    'w-full text-left rounded-xl border-2 p-4 transition-all duration-200 cursor-pointer hover:shadow-md',
                                    form.allowPublicWork
                                        ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/10'
                                        : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/30'
                                ]"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div :class="['h-9 w-9 rounded-xl flex items-center justify-center transition-colors duration-200', form.allowPublicWork ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'bg-slate-100 dark:bg-zinc-700']">
                                        <Globe :class="['h-[17px] w-[17px] transition-colors duration-200', form.allowPublicWork ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-zinc-500']" />
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold', form.allowPublicWork ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-zinc-700 text-slate-400 dark:text-zinc-500']">
                                            {{ form.allowPublicWork ? '\u25cf Aktif' : '\u25cb Nonaktif' }}
                                        </span>
                                        <div :class="['relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200', form.allowPublicWork ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-600']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.allowPublicWork ? 'translate-x-4' : 'translate-x-0']" />
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Karya Publik</p>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                    Izinkan halaman karya dilihat oleh publik tanpa perlu login ke portal.
                                </p>
                            </button>
                        </div>
                    </div>

                    <!-- ⚠️ Danger Zone — only visible to Super Admin -->
                    <div v-if="isSuperAdmin" class="rounded-2xl border-2 border-rose-200 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-950/10 overflow-hidden">
                        <div class="px-6 py-4 border-b border-rose-200 dark:border-rose-900/40 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center shrink-0">
                                <Trash2 class="h-4 w-4 text-rose-600 dark:text-rose-400" />
                            </div>
                            <div>
                                <h3 class="text-[13px] font-black text-rose-700 dark:text-rose-400">⚠️ Zona Berbahaya (Danger Zone)</h3>
                                <p class="text-[11px] text-rose-500 dark:text-rose-500 mt-0.5">Tindakan di bawah ini berdampak permanen. Tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                        <div class="px-6 py-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Reset &amp; Hapus Semua Postingan Karya</p>
                                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                        Menghapus seluruh karya portofolio, laporan, peringatan, dan tag yang terkait secara permanen dari database.
                                    </p>
                                </div>
                                <button
                                    @click="openDangerModal"
                                    type="button"
                                    class="shrink-0 rounded-xl border border-rose-300 dark:border-rose-800 bg-white dark:bg-zinc-900 px-4 py-2 text-[12px] font-black text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 active:scale-95 transition-all flex items-center gap-2"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Reset Semua Karya
                                </button>
                            </div>
                        </div>
                    </div>

                </template>

                <!-- MODERATION -->
                <template v-if="activeSection === 'moderation'">
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Moderasi Konten &amp; Anti-Toxic Engine</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Atur kebijakan otomatisasi moderasi, penanganan komentar, dan kata terlarang</p>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                            <!-- TOGGLE 1: Engine Lokal -->
                            <div class="py-3.5 rounded-xl px-4 bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-700/50 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center shrink-0 text-emerald-600 font-bold text-xs">
                                        🛡️
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">1. Filter Engine Lokal (Kamus &amp; Leetspeak Normalizer)</p>
                                            <span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Instan 2ms (Gratis)
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Pemindaian kata kasar, slot/judi online, dan plesetan leetspeak langsung di server lokal.</p>
                                    </div>
                                </div>
                                <button @click="form.enableLocalEngine = !form.enableLocalEngine" type="button" :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ml-3', form.enableLocalEngine ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-zinc-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.enableLocalEngine ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>

                            <!-- TOGGLE 2: AI Konteks Chat & Komentar Teks -->
                            <div class="py-3.5 rounded-xl px-4 bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 text-white font-bold text-xs">
                                            💬
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">2. AI Konteks Chat &amp; Komentar Teks (Google Gemini Engine)</p>
                                                <span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                    Cloud Real AI
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Analisis kecerdasan buatan dari cloud server Google untuk sentimen, bullying terselubung, dan ancaman obrolan.</p>
                                        </div>
                                    </div>
                                    <button @click="form.enableGoogleAi = !form.enableGoogleAi" type="button" :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ml-3', form.enableGoogleAi ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']">
                                        <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.enableGoogleAi ? 'translate-x-4' : 'translate-x-0']" />
                                    </button>
                                </div>

                                <!-- CONDITIONAL CONFIGURATION PANEL (Tampil saat Toggle 2 AKTIF) -->
                                <div v-if="form.enableGoogleAi" class="pt-3 border-t border-indigo-100 dark:border-indigo-900/40 space-y-3">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                        <label class="text-[12px] font-bold text-slate-700 dark:text-zinc-200">Google Gemini API Key (Gratis Google AI Studio):</label>
                                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                            🔑 Ambil API Key Gratis di Google AI Studio &rarr;
                                        </a>
                                    </div>

                                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2">
                                        <input
                                            v-model="form.googleAiApiKey"
                                            type="password"
                                            placeholder="Masukkan Google Gemini API Key (misal: AIzaSy...)"
                                            class="flex-1 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3.5 py-2 text-[12px] font-medium text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        />
                                        <select
                                            v-model="form.googleAiModel"
                                            class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[12px] font-bold text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-xs"
                                        >
                                            <option v-for="m in availableGeminiModels" :key="m.id" :value="m.id">
                                                {{ m.name }}
                                            </option>
                                        </select>
                                        <button
                                            type="button"
                                            @click="handleFetchGoogleModels"
                                            :disabled="isFetchingModels"
                                            class="rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-50 px-3 py-2 text-[11.5px] font-bold text-white transition-all active:scale-95 shadow-xs shrink-0 flex items-center gap-1.5"
                                            title="Tarik daftar model resmi terbaru langsung dari Google AI Studio API"
                                        >
                                            <span>{{ isFetchingModels ? '⏳' : '🔄' }}</span>
                                            <span>{{ isFetchingModels ? 'Memuat...' : 'Fetch List Model' }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="handleTestGoogleAi"
                                            :disabled="isTestingAi"
                                            class="rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 px-3.5 py-2 text-[12px] font-bold text-white transition-all active:scale-95 shadow-sm shrink-0"
                                        >
                                            {{ isTestingAi ? 'Menguji...' : '🧪 Uji Koneksi API' }}
                                        </button>
                                    </div>

                                    <div v-if="aiTestResult" :class="['p-2.5 rounded-xl text-[11.5px] font-bold flex items-center gap-2 border', aiTestResult.success ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800']">
                                        <span>{{ aiTestResult.success ? '✅' : '❌' }}</span>
                                        <span>{{ aiTestResult.message }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- TOGGLE 3: Moderasi Gambar & Postingan Karya (Google Gemini Vision AI) -->
                            <div class="py-3.5 rounded-xl px-4 bg-purple-50/40 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/40 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shrink-0 text-white font-bold text-xs">
                                        🖼️
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">3. Moderasi Gambar &amp; Postingan Karya (Google Gemini Vision AI)</p>
                                            <span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                Vision AI
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Pemindaian gambar cover karya/postingan (NSFW, poster judi online/slot OCR, kekerasan) via Google Vision AI.</p>
                                    </div>
                                </div>
                                <button @click="form.enableVisionAi = !form.enableVisionAi" type="button" :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ml-3', form.enableVisionAi ? 'bg-purple-600' : 'bg-slate-200 dark:bg-zinc-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', form.enableVisionAi ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>

                            <!-- SHORTCUTS KELOLA KAMUS TERPISAH -->
                            <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 space-y-3">
                                <p class="text-[12px] font-bold text-slate-700 dark:text-zinc-200">
                                    📚 Pintasan Kelola Kamus Moderasi Terpisah:
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <Link
                                        href="/pagi/admin/text-dictionary"
                                        class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 hover:border-indigo-400 transition-all text-slate-800 dark:text-zinc-100 font-bold text-[13px] group"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">📝</span>
                                            <span>Kelola Kamus Kata Teks</span>
                                        </div>
                                        <span class="text-indigo-600 dark:text-indigo-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                                    </Link>

                                    <Link
                                        href="/pagi/admin/image-dictionary"
                                        class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 hover:border-purple-400 transition-all text-slate-800 dark:text-zinc-100 font-bold text-[13px] group"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">🖼️</span>
                                            <span>Kelola Kamus Gambar Visual</span>
                                        </div>
                                        <span class="text-purple-600 dark:text-purple-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Max Warnings -->
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Maks Peringatan Sebelum Suspend Otomatis</label>
                                <input v-model="form.maxWarningsBeforeSuspend" type="number" min="1" max="10" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1.5">Akun mahasiswa akan otomatis ditangguhkan setelah melampaui batas ini.</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- SECURITY -->
                <template v-if="activeSection === 'security'">
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Keamanan Sistem</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Rate limiting dan pencatatan aktivitas</p>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Rate Limit (request/menit)</label>
                                <input v-model="form.rateLimitPerMinute" type="number" min="10" max="300" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
                            </div>
                            <div v-for="item in [
                                { field: 'enableActivityLog', label: 'Log Aktivitas', desc: 'Simpan log semua aksi admin dan moderasi' },
                                { field: 'requireEmailVerification', label: 'Verifikasi Email Wajib', desc: 'Akun baru harus verifikasi email sebelum aktif' },
                            ]" :key="item.field" class="flex items-center justify-between py-3 rounded-xl px-4 bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-700/50">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                        <Activity class="h-4 w-4 text-slate-500" />
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">{{ item.label }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.desc }}</p>
                                    </div>
                                </div>
                                <button @click="(form as any)[item.field] = !(form as any)[item.field]" :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200', (form as any)[item.field] ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', (form as any)[item.field] ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- NOTIFICATIONS -->
                <template v-if="activeSection === 'notifications'">
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Notifikasi Admin</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Pilih peristiwa yang memicu notifikasi ke admin</p>
                        </div>
                        <div class="px-6 py-5 space-y-3">
                            <div v-for="item in [
                                { key: 'notifyOnReport', label: 'Laporan Konten Baru', desc: 'Terima notifikasi saat ada laporan masuk dari pengguna' },
                                { key: 'notifyOnNewUser', label: 'Mahasiswa Baru Bergabung', desc: 'Terima notifikasi saat akun mahasiswa baru mendaftar' },
                                { key: 'notifyOnTakedown', label: 'Konten Di-takedown', desc: 'Terima notifikasi saat konten disembunyikan atau dihapus' },
                            ]" :key="item.key" class="flex items-center justify-between py-3 px-4 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-700/50">
                                <div>
                                    <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">{{ item.label }}</p>
                                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.desc }}</p>
                                </div>
                                <button @click="(form as any)[item.key] = !(form as any)[item.key]" :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ml-4', (form as any)[item.key] ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', (form as any)[item.key] ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Save Footer -->
                <div class="flex items-center justify-between rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 px-6 py-4">
                    <p v-if="form.wasSuccessful" class="text-[12px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Pengaturan berhasil disimpan
                    </p>
                    <div v-else></div>
                    <button
                        @click="submitSettings"
                        :disabled="form.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-[13px] font-bold text-white hover:bg-indigo-700 active:scale-95 transition-all shadow-sm shadow-indigo-200 dark:shadow-none disabled:opacity-55 flex items-center justify-center gap-2 min-w-[140px]"
                    >
                        <svg v-if="form.processing" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- ─── Danger Zone Modal ───────────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showDangerModal"
                    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                    @click.self="closeDangerModal"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeDangerModal" />

                    <!-- Modal -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showDangerModal"
                            class="relative z-10 w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border-2 border-rose-200 dark:border-rose-900/60 shadow-2xl overflow-hidden"
                        >
                            <!-- Modal Header -->
                            <div class="px-6 py-5 border-b border-rose-100 dark:border-rose-900/40 bg-rose-50/60 dark:bg-rose-950/20">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center shrink-0 mt-0.5">
                                        <Trash2 class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-[15px] font-black text-rose-700 dark:text-rose-400">Konfirmasi Penghapusan Permanen</h3>
                                        <p class="text-[12px] text-rose-500 dark:text-rose-500 mt-0.5 leading-relaxed">
                                            Tindakan ini tidak dapat dibatalkan. Seluruh karya, laporan, dan peringatan akan dihapus permanen dari database.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 py-5 space-y-5">

                                <!-- Warning Banner -->
                                <div class="rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 px-4 py-3 flex items-start gap-2.5">
                                    <AlertTriangle class="h-4 w-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                                    <p class="text-[11px] text-amber-700 dark:text-amber-300 font-semibold leading-relaxed">
                                        Hanya <strong>Super Admin</strong> yang dapat mengeksekusi ini. Aksi ini akan tercatat di log keamanan sistem.
                                    </p>
                                </div>

                                <!-- Lapis 2: Password -->
                                <div>
                                    <label class="block text-[12px] font-bold text-slate-700 dark:text-zinc-200 mb-1.5">
                                        1. Masukkan Password Admin Anda
                                    </label>
                                    <input
                                        v-model="dangerPassword"
                                        type="password"
                                        placeholder="Password akun admin Anda"
                                        autocomplete="current-password"
                                        class="w-full rounded-xl border px-4 py-2.5 text-[13px] font-medium focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all"
                                        :class="dangerPasswordError
                                            ? 'border-rose-400 dark:border-rose-600 bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-300'
                                            : 'border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200'"
                                    />
                                    <p v-if="dangerPasswordError" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                        <AlertTriangle class="h-3 w-3 shrink-0" />
                                        {{ dangerPasswordError }}
                                    </p>
                                </div>

                                <!-- Lapis 3: Text Confirmation -->
                                <div>
                                    <label class="block text-[12px] font-bold text-slate-700 dark:text-zinc-200 mb-1.5">
                                        2. Ketik <code class="bg-slate-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded-md text-rose-600 dark:text-rose-400 font-mono text-[11px]">HAPUS SEMUA KARYA</code> untuk mengonfirmasi
                                    </label>
                                    <input
                                        v-model="dangerConfirmation"
                                        type="text"
                                        placeholder="HAPUS SEMUA KARYA"
                                        autocomplete="off"
                                        class="w-full rounded-xl border px-4 py-2.5 text-[13px] font-mono focus:outline-none focus:ring-2 transition-all"
                                        :class="dangerConfirmation.length > 0
                                            ? isConfirmationValid
                                                ? 'border-emerald-400 dark:border-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 focus:ring-emerald-500'
                                                : 'border-rose-300 dark:border-rose-700 bg-rose-50/60 dark:bg-rose-950/10 text-rose-700 dark:text-rose-300 focus:ring-rose-500'
                                            : 'border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 focus:ring-rose-500'"
                                    />
                                    <p v-if="dangerConfirmationError" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                        <AlertTriangle class="h-3 w-3 shrink-0" />
                                        {{ dangerConfirmationError }}
                                    </p>
                                    <p v-else-if="isConfirmationValid" class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                        ✓ Teks konfirmasi cocok
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30">
                                <button
                                    @click="closeDangerModal"
                                    type="button"
                                    class="rounded-xl px-4 py-2 text-[13px] font-semibold text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="submitDangerReset"
                                    :disabled="!isFormComplete || dangerProcessing"
                                    type="button"
                                    class="rounded-xl px-5 py-2 text-[13px] font-black text-white flex items-center gap-2 transition-all active:scale-95"
                                    :class="isFormComplete && !dangerProcessing
                                        ? 'bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-300/50'
                                        : 'bg-rose-300 dark:bg-rose-900/40 cursor-not-allowed opacity-60'"
                                >
                                    <svg v-if="dangerProcessing" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <Trash2 v-else class="h-3.5 w-3.5" />
                                    {{ dangerProcessing ? 'Menghapus...' : 'Konfirmasi Hapus' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </PagiAdminLayout>
</template>
