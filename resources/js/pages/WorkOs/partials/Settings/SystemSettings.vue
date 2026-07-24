<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import {
	AlertCircle,
	Building2,
	Camera,
	CheckCircle,
	Cpu,
	Globe,
	Info,
	Layers,
	LayoutGrid,
	Loader2,
	Lock,
	RefreshCw,
	Save,
	ShieldAlert,
	Sparkles,
	UploadCloud,
	X,
} from "lucide-vue-next";
import { computed, reactive, ref } from "vue";

const props = defineProps<{
	settings: Record<string, string>;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors || {}) as Record<string, string>);

const activeTab = ref("branding");
const isProcessing = ref(false);
const isSuccess = ref(false);

const form = reactive({
	brand_name: props.settings.brand_name || "Portal FMIKOM",
	brand_subtitle: props.settings.brand_subtitle || "Fakultas Matematika dan Ilmu Komputer",
	brand_description: props.settings.brand_description || "Sistem informasi terpadu untuk civitas akademika FMIKOM.",
	brand_logo_file: null as File | null,
	brand_favicon_file: null as File | null,
	primary_color: props.settings.primary_color || "#2563eb",
	maintenance_mode: props.settings.maintenance_mode === "1" ? "1" : "0",
	maintenance_message: props.settings.maintenance_message || "Sistem sedang dalam pemeliharaan. Silakan kembali beberapa saat lagi.",
	public_registration: props.settings.public_registration !== "0" ? "1" : "0",
});

// For previews
const defaultLogo = "/asset/brand-logo.webp";
const defaultFavicon = "/asset/brand-logo.webp";

const logoPreview = ref(props.settings.brand_logo || defaultLogo);
const faviconPreview = ref(props.settings.brand_favicon || defaultFavicon);

const isModalOpen = ref(false);
const modalType = ref<"logo" | "favicon">("logo");
const modalError = ref("");
const tempFile = ref<File | null>(null);
const tempPreview = ref("");
const dragActive = ref(false);
const modalFileInput = ref<HTMLInputElement | null>(null);

const openUploadModal = (type: "logo" | "favicon") => {
	modalType.value = type;
	modalError.value = "";
	tempFile.value = null;
	tempPreview.value = "";
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
	tempFile.value = null;
	tempPreview.value = "";
	modalError.value = "";
};

const validateAndSetFile = (file: File) => {
	modalError.value = "";
	
	const isLogo = modalType.value === "logo";
	const maxSize = isLogo ? 2 * 1024 * 1024 : 512 * 1024;
	const maxSizeLabel = isLogo ? "2MB" : "512KB";
	
	if (file.size > maxSize) {
		modalError.value = `Ukuran file melebihi batas maks ${maxSizeLabel}.`;
		return;
	}
	
	const allowedTypes = isLogo 
		? ["image/png", "image/jpeg", "image/jpg", "image/svg+xml"] 
		: ["image/x-icon", "image/vnd.microsoft.icon", "image/png", "image/x-ico", "image/ico"];
		
	const ext = file.name.split(".").pop()?.toLowerCase();
	const allowedExts = isLogo ? ["png", "jpg", "jpeg", "svg"] : ["ico", "png"];
	
	const isTypeAllowed = allowedTypes.includes(file.type) || (ext && allowedExts.includes(ext));
	
	if (!isTypeAllowed) {
		modalError.value = isLogo 
			? "Format file tidak didukung. Gunakan PNG, SVG, atau JPG." 
			: "Format file tidak didukung. Gunakan ICO atau PNG.";
		return;
	}
	
	tempFile.value = file;
	tempPreview.value = URL.createObjectURL(file);
};

const onDragOver = (e: DragEvent) => {
	e.preventDefault();
	dragActive.value = true;
};

const onDragLeave = () => {
	dragActive.value = false;
};

const onDrop = (e: DragEvent) => {
	e.preventDefault();
	dragActive.value = false;
	const file = e.dataTransfer?.files?.[0];
	if (file) {
		validateAndSetFile(file);
	}
};

const onFileSelect = (e: Event) => {
	const target = e.target as HTMLInputElement;
	const file = target.files?.[0];
	if (file) {
		validateAndSetFile(file);
	}
};

const applyUploadedFile = () => {
	if (!tempFile.value) return;
	
	if (modalType.value === "logo") {
		form.brand_logo_file = tempFile.value;
		logoPreview.value = tempPreview.value;
	} else {
		form.brand_favicon_file = tempFile.value;
		faviconPreview.value = tempPreview.value;
	}
	
	closeModal();
};

const submitSettings = () => {
	isProcessing.value = true;
	router.post("/workos/settings/update", form, {
		preserveScroll: true,
		onSuccess: () => {
			isProcessing.value = false;
			isSuccess.value = true;
			form.brand_logo_file = null;
			form.brand_favicon_file = null;
			setTimeout(() => {
				isSuccess.value = false;
			}, 3000);
		},
		onError: () => {
			isProcessing.value = false;
		},
	});
};

const isFlushing = ref(false);
const flushMessage = ref("");

const flushCache = async () => {
	isFlushing.value = true;
	flushMessage.value = "";
	try {
		const response = await fetch("/workos/settings/flush-cache", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || "",
			},
		});
		const result = await response.json();
		if (result.success) {
			flushMessage.value = "Cache berhasil dibersihkan!";
		} else {
			flushMessage.value = "Gagal: " + result.message;
		}
	} catch (e: any) {
		flushMessage.value = "Terjadi kesalahan: " + e.message;
	} finally {
		isFlushing.value = false;
		setTimeout(() => {
			flushMessage.value = "";
		}, 4000);
	}
};
</script>

<template>
	<div class="w-full px-4 py-6 sm:px-8 sm:pt-8 sm:pb-12 space-y-6" style="font-family: var(--wos-font)">
		<!-- Page Header -->
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
			<div>
				<h2 class="text-xl font-bold text-[#111827] dark:text-zinc-100 tracking-tight">System & Branding Settings</h2>
				<p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1">Konfigurasi nama aplikasi, identitas visual, mode pemeliharaan, dan preferensi sistem.</p>
			</div>
			
			<button
				@click="submitSettings"
				:disabled="isProcessing"
				class="inline-flex items-center justify-center gap-2 bg-[#111827] hover:bg-black disabled:opacity-50 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white px-4 py-2.5 rounded-lg text-[13px] font-semibold transition-all shadow-sm active:scale-[0.98] w-full sm:w-auto cursor-pointer border-0 dark:shadow-none"
			>
				<Loader2 v-if="isProcessing" class="w-4 h-4 animate-spin" />
				<CheckCircle v-else-if="isSuccess" class="w-4 h-4 text-emerald-400" />
				<Save v-else class="w-4 h-4" />
				{{ isProcessing ? 'Menyimpan...' : isSuccess ? 'Tersimpan!' : 'Simpan Perubahan' }}
			</button>
		</div>

		<!-- Validation Errors Alert -->
		<div v-if="Object.keys(errors).length > 0" class="p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 rounded-xl flex items-start gap-3">
			<AlertCircle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
			<div class="text-[12.5px] text-red-800 dark:text-red-400 space-y-1">
				<p class="font-bold">Gagal Menyimpan Perubahan:</p>
				<ul class="list-disc pl-4 space-y-0.5">
					<li v-for="(err, field) in errors" :key="field">{{ err }}</li>
				</ul>
			</div>
		</div>

		<!-- Main Workspace Grid -->
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
			<!-- Settings Tabs & Forms -->
			<div class="lg:col-span-12 space-y-6">
				<!-- Tab Navigation -->
				<div class="grid grid-cols-2 lg:flex border border-gray-150 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-1 rounded-xl shadow-xs gap-1">
					<button
						v-for="tab in [
							{ id: 'branding', label: 'Branding & Visual', icon: Sparkles },
							{ id: 'system', label: 'Sistem & Maintenance', icon: ShieldAlert },
							{ id: 'access', label: 'Akses & Registrasi', icon: Lock },
							{ id: 'utility', label: 'Server & Utility', icon: Cpu },
						]"
						:key="tab.id"
						@click="activeTab = tab.id"
						:class="[
							'flex items-center gap-2 px-4 py-2.5 text-[12.5px] font-semibold rounded-lg transition-all justify-center w-full cursor-pointer border-0',
							activeTab === tab.id
								? 'bg-[#111827] text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm'
								: 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800 bg-transparent'
						]"
					>
						<component :is="tab.icon" class="w-4 h-4 shrink-0" />
						{{ tab.label }}
					</button>
				</div>

				<!-- Form Container -->
				<div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs min-h-[400px]">
					
					<!-- TAB 1: BRANDING & VISUAL -->
					<div v-show="activeTab === 'branding'" class="space-y-6">
						<h3 class="text-[14.5px] font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
							<Sparkles class="w-4 h-4 text-amber-500" /> Identitas Visual Aplikasi
						</h3>
						
						<!-- App/Brand Name & Subtitle -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="space-y-2">
								<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Nama Aplikasi</label>
								<input
									v-model="form.brand_name"
									type="text"
									placeholder="Contoh: Portal FMIKOM"
									class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-2.5 text-[13px] font-medium text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-black dark:focus:ring-zinc-600 focus:border-transparent outline-none transition-all"
								/>
							</div>

							<div class="space-y-2">
								<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Sub-Nama Aplikasi (Fakultas / Instansi)</label>
								<input
									v-model="form.brand_subtitle"
									type="text"
									placeholder="Contoh: Fakultas Matematika dan Ilmu Komputer"
									class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-2.5 text-[13px] font-medium text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-black dark:focus:ring-zinc-600 focus:border-transparent outline-none transition-all"
								/>
							</div>
						</div>

						<!-- App/Brand Short Description -->
						<div class="space-y-2">
							<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Deskripsi Singkat</label>
							<textarea
								v-model="form.brand_description"
								rows="3"
								placeholder="Deskripsi singkat yang tampil pada halaman luar/landing page."
								class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-2.5 text-[13px] font-medium text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-black dark:focus:ring-zinc-600 focus:border-transparent outline-none transition-all resize-none"
							></textarea>
						</div>

						<!-- Uploaders Grid -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
							
							<!-- Logo Uploader -->
							<div class="space-y-2">
								<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Logo Aplikasi (Navbar)</label>
								<div class="flex items-center gap-4">
									<div class="w-16 h-16 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800/50 flex items-center justify-center p-2 overflow-hidden shadow-xs shrink-0">
										<img :src="logoPreview" class="w-full h-full object-contain" alt="Logo Preview" />
									</div>
									<div class="space-y-2">
										<button
											type="button"
											@click="openUploadModal('logo')"
											class="px-3.5 py-2 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg text-[12px] font-semibold text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 shadow-xs transition-colors cursor-pointer"
										>
											Pilih Logo
										</button>
										<p class="text-[10px] text-gray-400 dark:text-zinc-500">PNG/SVG/JPG. Maks 2MB.</p>
									</div>
								</div>
							</div>

							<!-- Favicon Uploader -->
							<div class="space-y-2">
								<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Favicon (Browser Tab Icon)</label>
								<div class="flex items-center gap-4">
									<div class="w-16 h-16 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800/50 flex items-center justify-center p-3.5 overflow-hidden shadow-xs shrink-0">
										<img :src="faviconPreview" class="w-full h-full object-contain" alt="Favicon Preview" />
									</div>
									<div class="space-y-2">
										<button
											type="button"
											@click="openUploadModal('favicon')"
											class="px-3.5 py-2 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg text-[12px] font-semibold text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 shadow-xs transition-colors cursor-pointer"
										>
											Pilih Favicon
										</button>
										<p class="text-[10px] text-gray-400 dark:text-zinc-500">ICO/PNG. Maks 512KB.</p>
									</div>
								</div>
							</div>

						</div>

						<!-- Primary Theme Accent Color -->
						<div class="space-y-3 pt-4 border-t border-gray-100 dark:border-zinc-800">
							<div>
								<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Warna Aksen Aplikasi</label>
								<p class="text-[11px] text-gray-400 dark:text-zinc-400 mt-0.5">Warna utama untuk tombol, tautan, dan elemen interaktif di portal publik.</p>
							</div>
							<div class="flex flex-wrap items-center gap-3">
								<div class="flex items-center gap-2">
									<input
										type="color"
										v-model="form.primary_color"
										class="w-12 h-10 rounded-lg border border-gray-200 dark:border-zinc-700 cursor-pointer overflow-hidden p-0 bg-transparent"
									/>
									<input
										type="text"
										v-model="form.primary_color"
										class="bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-2 text-[12.5px] font-bold text-gray-800 dark:text-zinc-100 w-28 focus:ring-2 focus:ring-black dark:focus:ring-zinc-600 outline-none"
									/>
								</div>
								<div class="flex flex-wrap gap-1.5">
									<button
										v-for="color in ['#2563eb', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#111827']"
										:key="color"
										type="button"
										@click="form.primary_color = color"
										class="w-6 h-6 rounded-full border border-white dark:border-zinc-800 shadow-xs transition-transform hover:scale-110 active:scale-95 cursor-pointer"
										:style="{ backgroundColor: color }"
									></button>
								</div>
							</div>
						</div>

						<!-- Informational Card -->
						<div class="p-4 bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-800 rounded-xl flex items-start gap-2.5 mt-4">
							<Sparkles class="w-4.5 h-4.5 text-emerald-500 mt-0.5 shrink-0" />
							<p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
								Perubahan pada <strong>Nama Aplikasi</strong>, <strong>Logo</strong>, <strong>Favicon</strong>, dan <strong>Warna Aksen</strong> akan langsung diterapkan secara instan ke seluruh sistem setelah Anda menyimpan perubahan.
							</p>
						</div>
					</div>

					<!-- TAB 2: SYSTEM & MAINTENANCE -->
					<div v-show="activeTab === 'system'" class="space-y-6">
						<h3 class="text-[14.5px] font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
							<ShieldAlert class="w-4 h-4 text-red-500" /> Mode Pemeliharaan (Maintenance)
						</h3>

						<!-- Maintenance Toggle Switch -->
						<div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-800 rounded-xl">
							<div class="space-y-1 pr-4">
								<p class="text-[13px] font-bold text-gray-800 dark:text-zinc-200">Mode Maintenance</p>
								<p class="text-[11px] text-gray-500 dark:text-zinc-400">Tampilkan halaman pemeliharaan sistem ke publik. Super Admin tetap dapat mengakses dasbor.</p>
							</div>
							<button
								type="button"
								@click="form.maintenance_mode = form.maintenance_mode === '1' ? '0' : '1'"
								:class="[
									'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 border-0 cursor-pointer',
									form.maintenance_mode === '1' ? 'bg-[#111827] dark:bg-zinc-100' : 'bg-gray-200 dark:bg-zinc-700'
								]"
							>
								<span :class="['inline-block h-4 w-4 transform rounded-full bg-white dark:bg-zinc-900 shadow transition-transform duration-200', form.maintenance_mode === '1' ? 'translate-x-6' : 'translate-x-1']"></span>
							</button>
						</div>

						<!-- Maintenance Message Textarea -->
						<div v-show="form.maintenance_mode === '1'" class="space-y-2 transition-all duration-300">
							<label class="block text-[12px] font-bold text-gray-700 dark:text-zinc-300">Pesan Maintenance Publik</label>
							<textarea
								v-model="form.maintenance_message"
								rows="4"
								placeholder="Tulis pesan penjelasan mengapa situs sedang dalam pemeliharaan..."
								class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-2.5 text-[13px] font-medium text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-black dark:focus:ring-zinc-600 focus:border-transparent outline-none transition-all resize-none"
							></textarea>
							<p class="text-[10px] text-gray-400 dark:text-zinc-500">Pesan ini akan dibaca oleh pengunjung umum yang mengakses halaman luar.</p>
						</div>

						<div class="p-4 bg-blue-50/70 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 rounded-xl flex items-start gap-3">
							<Info class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
							<div class="text-[11.5px] text-blue-800 dark:text-blue-400">
								<p class="font-bold">Bypass Super Admin Aktif</p>
								<p class="mt-0.5">Sebagai Super Admin, Anda tidak akan terblokir oleh halaman maintenance ini dan dapat melakukan pengetesan sistem seperti biasa.</p>
							</div>
						</div>
					</div>

					<!-- TAB 3: ACCESS & REGISTRATION -->
					<div v-show="activeTab === 'access'" class="space-y-6">
						<h3 class="text-[14.5px] font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
							<Lock class="w-4 h-4 text-blue-500" /> Kontrol Pendaftaran & Hak Akses
						</h3>

						<!-- Toggle: Registrasi Terbuka -->
						<div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-800 rounded-xl">
							<div class="space-y-1 pr-4">
								<p class="text-[13px] font-bold text-gray-800 dark:text-zinc-200">Registrasi Terbuka (Public Signup)</p>
								<p class="text-[11px] text-gray-500 dark:text-zinc-400">Izinkan pendaftaran akun mahasiswa/user baru melalui halaman depan publik secara mandiri.</p>
							</div>
							<button
								type="button"
								@click="form.public_registration = form.public_registration === '1' ? '0' : '1'"
								:class="[
									'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 border-0 cursor-pointer',
									form.public_registration === '1' ? 'bg-[#111827] dark:bg-zinc-100' : 'bg-gray-200 dark:bg-zinc-700'
								]"
							>
								<span :class="['inline-block h-4 w-4 transform rounded-full bg-white dark:bg-zinc-900 shadow transition-transform duration-200', form.public_registration === '1' ? 'translate-x-6' : 'translate-x-1']"></span>
							</button>
						</div>

						<div class="p-4 bg-gray-50/80 dark:bg-zinc-800/20 border border-gray-100 dark:border-zinc-800 rounded-xl">
							<p class="text-[12px] font-bold text-gray-800 dark:text-zinc-200">Kebijakan Registrasi</p>
							<p class="text-[11.5px] text-gray-500 dark:text-zinc-400 mt-1">Jika pendaftaran dimatikan, pendaftaran hanya bisa dilakukan secara internal oleh administrator melalui tab <strong>Users Management</strong>.</p>
						</div>
					</div>

					<!-- TAB 4: SERVER UTILITY & STATUS -->
					<div v-show="activeTab === 'utility'" class="space-y-6">
						<h3 class="text-[14.5px] font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
							<Cpu class="w-4 h-4 text-violet-500" /> Utilitas & Kesehatan Server
						</h3>

						<!-- Cache Flusher -->
						<div class="border border-gray-200 dark:border-zinc-800 rounded-xl p-5 space-y-4">
							<div class="space-y-1">
								<p class="text-[13px] font-bold text-gray-800 dark:text-zinc-200">Flush System Cache</p>
								<p class="text-[11.5px] text-gray-500 dark:text-zinc-400">Bersihkan semua cache konfigurasi, cache view template, dan cache database internal agar konfigurasi termuat ulang seketika.</p>
							</div>
							<div class="flex items-center gap-3">
								<button
									type="button"
									@click="flushCache"
									:disabled="isFlushing"
									class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 disabled:opacity-50 rounded-lg text-[12.5px] font-semibold text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 shadow-xs transition-colors cursor-pointer"
								>
									<RefreshCw class="w-4 h-4" :class="isFlushing ? 'animate-spin' : ''" />
									{{ isFlushing ? 'Membersihkan Cache...' : 'Bersihkan Cache Sistem' }}
								</button>
								<span v-if="flushMessage" class="text-[11.5px] font-bold text-emerald-600 animate-pulse">{{ flushMessage }}</span>
							</div>
						</div>

						<!-- System Health Stats -->
						<div class="border border-gray-200 dark:border-zinc-800 rounded-xl p-5 space-y-3">
							<p class="text-[12.5px] font-bold text-gray-800 dark:text-zinc-200">Informasi Lingkungan Server</p>
							<div class="grid grid-cols-2 gap-4 text-[11.5px] text-gray-600 dark:text-zinc-400">
								<div class="p-3 bg-gray-50 dark:bg-zinc-800/40 rounded-lg border border-gray-100 dark:border-zinc-800">
									<span class="text-gray-400 dark:text-zinc-500 block mb-0.5">Framework</span>
									<strong class="dark:text-zinc-200">Laravel v12.0</strong>
								</div>
								<div class="p-3 bg-gray-50 dark:bg-zinc-800/40 rounded-lg border border-gray-100 dark:border-zinc-800">
									<span class="text-gray-400 dark:text-zinc-500 block mb-0.5">PHP Version</span>
									<strong class="dark:text-zinc-200">v8.3+</strong>
								</div>
								<div class="p-3 bg-gray-50 dark:bg-zinc-800/40 rounded-lg border border-gray-100 dark:border-zinc-800">
									<span class="text-gray-400 dark:text-zinc-500 block mb-0.5">Environment</span>
									<span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/40 text-[10px] rounded font-bold uppercase tracking-wider">Local</span>
								</div>
								<div class="p-3 bg-gray-50 dark:bg-zinc-800/40 rounded-lg border border-gray-100 dark:border-zinc-800">
									<span class="text-gray-400 dark:text-zinc-500 block mb-0.5">Database Driver</span>
									<strong class="dark:text-zinc-200">MySQL (Relational)</strong>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- Modern Upload Modal -->
		<Transition
			enter-active-class="transition duration-200 ease-out"
			enter-from-class="opacity-0"
			leave-active-class="transition duration-150 ease-in"
			leave-to-class="opacity-0"
		>
			<div
				v-if="isModalOpen"
				class="fixed inset-0 z-50 overflow-y-auto"
				aria-labelledby="modal-title"
				role="dialog"
				aria-modal="true"
			>
				<!-- Backdrop -->
				<div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity animate-fade-in" @click="closeModal"></div>

				<!-- Modal Wrapper -->
				<div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
					<Transition
						enter-active-class="transition duration-200 ease-out"
						enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
						leave-active-class="transition duration-150 ease-in"
						leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
					>
						<div
							class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 text-left shadow-xl transition-all my-8 w-full max-w-md p-6 dark:shadow-none"
							@click.stop
						>
							<!-- Header -->
							<div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
								<div>
									<h3 id="modal-title" class="text-base font-bold text-gray-900 dark:text-zinc-100">
										Upload {{ modalType === 'logo' ? 'Logo Aplikasi' : 'Favicon Situs' }}
									</h3>
									<p class="text-[12px] text-gray-500 dark:text-zinc-400 mt-0.5">
										Pilih atau seret file visual untuk aplikasi Anda.
									</p>
								</div>
								<button
									type="button"
									@click="closeModal"
									class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-800 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors bg-transparent border-0 cursor-pointer"
								>
									<X class="w-4 h-4" />
								</button>
							</div>

							<!-- Body: Error Alert -->
							<div
								v-if="modalError"
								class="mt-4 p-3 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/40 rounded-xl flex items-start gap-2.5 animate-pulse"
							>
								<AlertCircle class="w-4.5 h-4.5 text-red-500 shrink-0 mt-0.5" />
								<p class="text-[11.5px] text-red-700 dark:text-red-400 leading-relaxed font-medium">
									{{ modalError }}
								</p>
							</div>

							<!-- Body: Drag & Drop Zone -->
							<div class="mt-4">
								<div
									@dragover="onDragOver"
									@dragleave="onDragLeave"
									@drop="onDrop"
									@click="modalFileInput?.click()"
									:class="[
										'border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center gap-3 transition-all cursor-pointer select-none',
										dragActive
											? 'border-black dark:border-zinc-500 bg-gray-50 dark:bg-zinc-800 scale-[0.99]'
											: tempFile
												? 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/10 dark:bg-emerald-950/10'
												: 'border-gray-200 dark:border-zinc-800 hover:border-gray-400 dark:hover:border-zinc-700 bg-gray-50/50 dark:bg-zinc-900/40'
									]"
								>
									<input
										ref="modalFileInput"
										type="file"
										class="hidden"
										:accept="modalType === 'logo' ? 'image/png, image/jpeg, image/jpg, image/svg+xml' : 'image/x-icon, image/vnd.microsoft.icon, image/png'"
										@change="onFileSelect"
									/>

									<!-- Preview if selected -->
									<template v-if="tempFile">
										<div class="w-20 h-20 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center p-2.5 overflow-hidden shadow-xs">
											<img :src="tempPreview" class="w-full h-full object-contain" alt="Temporary Preview" />
										</div>
										<div class="text-center">
											<p class="text-[12.5px] font-bold text-gray-800 dark:text-zinc-200 truncate max-w-[280px]">
												{{ tempFile.name }}
											</p>
											<p class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
												{{ (tempFile.size / 1024).toFixed(1) }} KB
											</p>
										</div>
										<button
											type="button"
											@click.stop="tempFile = null; tempPreview = ''; modalError = ''"
											class="text-[11px] font-bold text-red-500 hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/20 bg-transparent border-0 cursor-pointer"
										>
											Hapus / Ganti
										</button>
									</template>

									<!-- Upload state instruction -->
									<template v-else>
										<div class="w-12 h-12 rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 flex items-center justify-center shadow-xs text-gray-400 dark:text-zinc-500">
											<UploadCloud class="w-6 h-6" />
										</div>
										<div class="text-center">
											<p class="text-[12.5px] font-bold text-gray-800 dark:text-zinc-200">
												Tarik & lepas file di sini, atau klik untuk memilih
											</p>
											<p class="text-[10.5px] text-gray-400 dark:text-zinc-500 mt-1">
												Format: {{ modalType === 'logo' ? 'PNG, SVG, JPG (Maks 2MB)' : 'ICO, PNG (Maks 512KB)' }}
											</p>
										</div>
									</template>
								</div>
							</div>

							<!-- Footer Action Buttons -->
							<div class="mt-6 flex items-center justify-end gap-2.5">
								<button
									type="button"
									@click="closeModal"
									class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl text-[12.5px] font-semibold text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 shadow-xs transition-colors cursor-pointer"
								>
									Batal
								</button>
								<button
									type="button"
									@click="applyUploadedFile"
									:disabled="!tempFile"
									class="px-4 py-2.5 bg-[#111827] dark:bg-zinc-100 dark:text-zinc-900 hover:bg-black dark:hover:bg-white disabled:opacity-50 text-white rounded-xl text-[12.5px] font-semibold transition-all shadow-sm cursor-pointer disabled:cursor-not-allowed border-0 dark:shadow-none"
								>
									Terapkan
								</button>
							</div>
						</div>
					</Transition>
				</div>
			</div>
		</Transition>
	</div>
</template>
