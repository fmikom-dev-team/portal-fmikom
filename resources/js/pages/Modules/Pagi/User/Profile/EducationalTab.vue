<script setup lang="ts">
import {
	GraduationCap,
	Calendar,
	BookOpen,
	Plus,
	Pencil,
	Trash2,
	Loader2,
	AlertCircle,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import { Skeleton } from "@/components/ui/skeleton";
import Modal from "../ui/Modal.vue";
import MonthYearPickerPopover from "./MonthYearPickerPopover.vue";
import { useEducations } from "./composables/useEducations";

const props = withDefaults(
	defineProps<{
		isOwnProfile: boolean;
		educations: Array<{
			id: number;
			level: string;
			institution: string;
			major?: string;
			start_date: string;
			end_date: string;
			description?: string;
		}>;
		isLoading?: boolean;
	}>(),
	{
		isLoading: false,
	},
);

const emit = defineEmits<{
	(e: "add-toast", message: string, type: string): void;
	(e: "update-educations", list: any[]): void;
}>();

// Instantiate Composable
const {
	localEducations,
	showAddModal,
	showEditModal,
	educationForm,
	isSubmitting,
	openAddModal,
	openEditModal,
	storeEducation,
	updateEducation,
	deleteEducation,
} = useEducations(
	() => props.educations,
	(msg, type) => emit("add-toast", msg, type),
	(list) => emit("update-educations", list),
);

// Watch for props updates
watch(
	() => props.educations,
	(newVal) => {
		if (newVal) {
			localEducations.value = [...newVal];
		}
	},
	{ deep: true },
);

// Level badge color selector
const getLevelBadgeStyles = (level: string) => {
	const val = (level || "").toUpperCase();
	if (["S1", "S2", "S3"].includes(val)) {
		return "bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border-indigo-100/50 dark:border-indigo-900/30";
	}
	if (["D3", "D4"].includes(val)) {
		return "bg-sky-50 dark:bg-sky-950/40 text-sky-650 dark:text-sky-400 border-sky-100/50 dark:border-sky-900/30";
	}
	if (["SMA", "SMK", "MA"].includes(val)) {
		return "bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border-emerald-100/50 dark:border-emerald-900/30";
	}
	return "bg-slate-50 dark:bg-zinc-800 text-slate-650 dark:text-zinc-300 border-slate-100 dark:border-zinc-700/50";
};

// Date Formatter (Translates YYYY-MM into Month YYYY)
const formatMonthYear = (dateStr: string) => {
	if (!dateStr) return "";
	if (dateStr.toLowerCase() === "present") return "Present";
	if (!dateStr.includes("-")) return dateStr;
	const [year, month] = dateStr.split("-");
	const monthNames = [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun",
		"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
	];
	const mIdx = parseInt(month, 10) - 1;
	return `${monthNames[mIdx] || month} ${year}`;
};

// State for end date checkbox ("I am currently studying here")
const isCurrentStudy = ref(false);

// Watchers for checkbox interaction to update end_date
watch(
	() => isCurrentStudy.value,
	(newVal) => {
		if (newVal) {
			educationForm.value.end_date = "Present";
		} else if (educationForm.value.end_date === "Present") {
			educationForm.value.end_date = "";
		}
	},
);

// Watch modal state to reset current checkbox
watch(
	() => showAddModal.value,
	(newVal) => {
		if (newVal) {
			isCurrentStudy.value = false;
		}
	},
);

watch(
	() => showEditModal.value,
	(newVal) => {
		if (newVal) {
			isCurrentStudy.value = educationForm.value.end_date === "Present";
		}
	},
);
</script>

<template>
	<div class="space-y-6 select-none">
		<!-- Section Header -->
		<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
			<div class="min-w-0">
				<h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
					<GraduationCap class="w-5 h-5 text-indigo-500 shrink-0" />
					<span>Riwayat Pendidikan</span>
				</h2>
				<p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 leading-normal">
					Informasi latar belakang akademis dan jenjang pendidikan Anda.
				</p>
			</div>

			<!-- Add Button (Only for owner) -->
			<button
				v-if="isOwnProfile && !isLoading"
				@click="openAddModal"
				type="button"
				class="inline-flex items-center justify-center gap-1.5 px-4 py-2 sm:px-4.5 sm:py-2.5 rounded-full bg-slate-900 hover:bg-slate-800 dark:bg-slate-50 dark:hover:bg-slate-200 text-white dark:text-slate-950 text-xs font-bold transition-all shadow-xs cursor-pointer select-none shrink-0 w-full sm:w-auto"
			>
				<Plus class="w-4 h-4" />
				<span>Tambah Pendidikan</span>
			</button>
		</div>

		<!-- Skeleton Loading state -->
		<div v-if="isLoading" class="space-y-4">
			<div v-for="i in 2" :key="i" class="p-5 rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 flex gap-4">
				<Skeleton class="h-10 w-10 rounded-full shrink-0" />
				<div class="space-y-2 flex-1">
					<Skeleton class="h-4 w-1/4" />
					<Skeleton class="h-3 w-1/2" />
					<Skeleton class="h-3 w-3/4" />
				</div>
			</div>
		</div>

		<template v-else>
			<!-- Empty State -->
			<div
				v-if="localEducations.length === 0"
				class="py-16 text-center border border-dashed border-slate-200 dark:border-zinc-800/80 rounded-2xl flex flex-col items-center justify-center gap-3 bg-white dark:bg-zinc-900/10"
			>
				<div class="h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800/50 flex items-center justify-center text-slate-400">
					<GraduationCap class="w-6 h-6" />
				</div>
				<div class="space-y-1">
					<p class="text-sm font-bold text-slate-800 dark:text-zinc-200">Belum ada riwayat pendidikan</p>
					<p class="text-xs text-slate-400 max-w-xs mx-auto">
						Tunjukkan perjalanan akademis Anda kepada kolega dan perekrut kerja.
					</p>
				</div>
				<button
					v-if="isOwnProfile"
					@click="openAddModal"
					type="button"
					class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs font-bold shadow-3xs transition-colors cursor-pointer w-full sm:w-auto justify-center"
				>
					<Plus class="w-3.5 h-3.5" />
					<span>Tambah Pertama</span>
				</button>
			</div>

			<!-- Timeline Layout -->
			<div v-else class="relative pl-6 sm:pl-8 space-y-6 before:absolute before:left-[11px] sm:before:left-[15px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100 dark:before:bg-zinc-800">
				
				<div
					v-for="edu in localEducations"
					:key="edu.id"
					class="relative group"
				>
					<!-- Timeline bullet dot -->
					<div class="absolute left-[-23px] sm:left-[-29px] top-1.5 w-4 h-4 rounded-full border-2 border-white dark:border-zinc-950 bg-indigo-500 shadow-sm z-10 transition-transform group-hover:scale-125 duration-300"></div>

					<!-- Card container -->
					<div class="p-5 rounded-2xl border border-slate-200/60 dark:border-zinc-800/60 hover:border-slate-300 dark:hover:border-zinc-700 bg-white dark:bg-zinc-900 shadow-3xs hover:shadow-xs transition-all duration-300 relative flex flex-col sm:flex-row justify-between items-start gap-4">
						
						<!-- Main Info -->
						<div class="space-y-2 flex-1 min-w-0 w-full">
							<!-- Institution / Level -->
							<div class="flex items-start gap-2 flex-wrap sm:flex-nowrap">
								<span :class="['px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border shrink-0 mt-0.5', getLevelBadgeStyles(edu.level)]">
									{{ edu.level }}
								</span>
								<h3 class="text-sm font-black text-slate-800 dark:text-zinc-100 tracking-tight leading-snug">
									{{ edu.institution }}
								</h3>
							</div>

							<!-- Major/Field of Study -->
							<div v-if="edu.major" class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-zinc-400">
								<BookOpen class="w-3.5 h-3.5 text-slate-400" />
								<span>{{ edu.major }}</span>
							</div>

							<!-- Duration -->
							<div class="flex items-center gap-1.5 text-[10.5px] font-bold text-slate-400 dark:text-zinc-500">
								<Calendar class="w-3.5 h-3.5 text-slate-350" />
								<span>{{ formatMonthYear(edu.start_date) }} — {{ formatMonthYear(edu.end_date) }}</span>
							</div>

							<!-- Description -->
							<p v-if="edu.description" class="text-xs font-medium leading-relaxed text-slate-500 dark:text-zinc-400 pt-1.5 border-t border-slate-100/50 dark:border-zinc-800/50 mt-1 max-w-3xl">
								{{ edu.description }}
							</p>
						</div>

						<!-- Action buttons (For profile owner) -->
						<div
							v-if="isOwnProfile"
							class="flex sm:flex-col items-center gap-1.5 shrink-0 self-end sm:self-start opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200"
						>
							<button
								@click="openEditModal(edu)"
								type="button"
								class="p-2 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-500 hover:text-slate-800 dark:text-zinc-450 dark:hover:text-white shadow-2xs hover:bg-slate-50 transition-colors cursor-pointer"
								title="Edit Pendidikan"
							>
								<Pencil class="w-3.5 h-3.5" />
							</button>
							<button
								@click="deleteEducation(edu.id)"
								type="button"
								class="p-2 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-400 hover:text-red-600 dark:text-zinc-450 dark:hover:text-red-400 shadow-2xs hover:bg-red-50/50 dark:hover:bg-red-950/20 transition-colors cursor-pointer"
								title="Hapus Pendidikan"
							>
								<Trash2 class="w-3.5 h-3.5" />
							</button>
						</div>

					</div>
				</div>
			</div>
		</template>

		<!-- ──────────────────────────────────────────────────────────────────────── -->
		<!-- MODALS SECTION -->
		<!-- ──────────────────────────────────────────────────────────────────────── -->

		<!-- 1. Add Education Modal -->
		<Modal
			:show="showAddModal"
			title="Tambah Riwayat Pendidikan"
			max-width="md"
			@close="showAddModal = false"
		>
			<form @submit.prevent="storeEducation" class="space-y-4">
				
				<!-- Institution Input -->
				<div class="space-y-1.5">
					<label for="edu-add-inst" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
						Nama Instansi / Sekolah <span class="text-red-500">*</span>
					</label>
					<input
						id="edu-add-inst"
						v-model="educationForm.institution"
						type="text"
						required
						placeholder="e.g. Universitas Amikom Purwokerto"
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>

				<!-- Education Level & Major -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					
					<div class="space-y-1.5">
						<label for="edu-add-level" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
							Jenjang Pendidikan <span class="text-red-500">*</span>
						</label>
						<select
							id="edu-add-level"
							v-model="educationForm.level"
							required
							class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs cursor-pointer"
						>
							<option value="" disabled>Pilih Jenjang</option>
							<option value="SD">SD / MI</option>
							<option value="SMP">SMP / MTs</option>
							<option value="SMA">SMA / SMK / MA</option>
							<option value="D3">D3 (Ahli Madya)</option>
							<option value="D4">D4 (Sarjana Terapan)</option>
							<option value="S1">S1 (Sarjana)</option>
							<option value="S2">S2 (Magister)</option>
							<option value="S3">S3 (Doktor)</option>
						</select>
					</div>

					<div class="space-y-1.5">
						<label for="edu-add-major" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
							Jurusan / Program Studi
						</label>
						<input
							id="edu-add-major"
							v-model="educationForm.major"
							type="text"
							placeholder="e.g. Teknik Informatika"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

				</div>

				<!-- Start Date & End Date Selector -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					
					<MonthYearPickerPopover
						v-model="educationForm.start_date"
						label="Tahun Masuk"
						placeholder="Pilih Tahun Masuk"
						required
					/>

					<div class="relative">
						<!-- Toggle checkbox: currently studying -->
						<div class="absolute right-0 top-0 flex items-center gap-1.5">
							<input
								id="edu-add-current"
								type="checkbox"
								v-model="isCurrentStudy"
								class="h-3 w-3 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer"
							/>
							<label for="edu-add-current" class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer uppercase tracking-wider">
								Masih Kuliah
							</label>
						</div>

						<MonthYearPickerPopover
							v-if="!isCurrentStudy"
							v-model="educationForm.end_date"
							label="Tahun Lulus"
							placeholder="Pilih Tahun Lulus"
							required
						/>
						
						<!-- Read-only Present Input -->
						<div v-else class="space-y-1.5">
							<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
								Tahun Lulus <span class="text-red-500">*</span>
							</span>
							<div class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center shadow-2xs select-none">
								Present (Masih Kuliah)
							</div>
						</div>
					</div>

				</div>

				<!-- Description Input -->
				<div class="space-y-1.5">
					<label for="edu-add-desc" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
						Deskripsi & Prestasi (Opsional)
					</label>
					<textarea
						id="edu-add-desc"
						v-model="educationForm.description"
						rows="3"
						placeholder="Jelaskan prestasi, organisasi, riset, atau pencapaian akademis Anda..."
						class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs resize-none"
					></textarea>
				</div>

				<!-- Info Box Sync -->
				<div class="p-3.5 rounded-2xl border border-blue-100/50 dark:border-blue-900/30 bg-blue-50/40 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 text-[11px] leading-relaxed font-semibold flex items-start gap-2">
					<AlertCircle class="w-4 h-4 shrink-0 text-blue-500" />
					<span>Data pendidikan ini secara otomatis disinkronkan ke dalam CV Builder Anda di database.</span>
				</div>

				<!-- Action buttons -->
				<div class="flex justify-end gap-3 pt-2">
					<button
						@click="showAddModal = false"
						type="button"
						class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-600 dark:text-zinc-450 hover:bg-slate-50 dark:hover:bg-zinc-800/80 transition-colors cursor-pointer"
					>
						Batal
					</button>
					<button
						type="submit"
						:disabled="isSubmitting"
						class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-50 dark:hover:bg-slate-200 text-white dark:text-slate-950 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
					>
						<Loader2 v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
						<span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}</span>
					</button>
				</div>
			</form>
		</Modal>

		<!-- 2. Edit Education Modal -->
		<Modal
			:show="showEditModal"
			title="Edit Riwayat Pendidikan"
			max-width="md"
			@close="showEditModal = false"
		>
			<form @submit.prevent="updateEducation" class="space-y-4">
				
				<!-- Institution Input -->
				<div class="space-y-1.5">
					<label for="edu-edit-inst" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
						Nama Instansi / Sekolah <span class="text-red-500">*</span>
					</label>
					<input
						id="edu-edit-inst"
						v-model="educationForm.institution"
						type="text"
						required
						placeholder="e.g. Universitas Amikom Purwokerto"
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>

				<!-- Education Level & Major -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					
					<div class="space-y-1.5">
						<label for="edu-edit-level" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
							Jenjang Pendidikan <span class="text-red-500">*</span>
						</label>
						<select
							id="edu-edit-level"
							v-model="educationForm.level"
							required
							class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs cursor-pointer"
						>
							<option value="" disabled>Pilih Jenjang</option>
							<option value="SD">SD / MI</option>
							<option value="SMP">SMP / MTs</option>
							<option value="SMA">SMA / SMK / MA</option>
							<option value="D3">D3 (Ahli Madya)</option>
							<option value="D4">D4 (Sarjana Terapan)</option>
							<option value="S1">S1 (Sarjana)</option>
							<option value="S2">S2 (Magister)</option>
							<option value="S3">S3 (Doktor)</option>
						</select>
					</div>

					<div class="space-y-1.5">
						<label for="edu-edit-major" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
							Jurusan / Program Studi
						</label>
						<input
							id="edu-edit-major"
							v-model="educationForm.major"
							type="text"
							placeholder="e.g. Teknik Informatika"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

				</div>

				<!-- Start Date & End Date Selector -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					
					<MonthYearPickerPopover
						v-model="educationForm.start_date"
						label="Tahun Masuk"
						placeholder="Pilih Tahun Masuk"
						required
					/>

					<div class="relative">
						<!-- Toggle checkbox: currently studying -->
						<div class="absolute right-0 top-0 flex items-center gap-1.5">
							<input
								id="edu-edit-current"
								type="checkbox"
								v-model="isCurrentStudy"
								class="h-3 w-3 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer"
							/>
							<label for="edu-edit-current" class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer uppercase tracking-wider">
								Masih Kuliah
							</label>
						</div>

						<MonthYearPickerPopover
							v-if="!isCurrentStudy"
							v-model="educationForm.end_date"
							label="Tahun Lulus"
							placeholder="Pilih Tahun Lulus"
							required
						/>
						
						<!-- Read-only Present Input -->
						<div v-else class="space-y-1.5">
							<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
								Tahun Lulus <span class="text-red-500">*</span>
							</span>
							<div class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center shadow-2xs select-none">
								Present (Masih Kuliah)
							</div>
						</div>
					</div>

				</div>

				<!-- Description Input -->
				<div class="space-y-1.5">
					<label for="edu-edit-desc" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
						Deskripsi & Prestasi (Opsional)
					</label>
					<textarea
						id="edu-edit-desc"
						v-model="educationForm.description"
						rows="3"
						placeholder="Jelaskan prestasi, organisasi, riset, atau pencapaian akademis Anda..."
						class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs resize-none"
					></textarea>
				</div>

				<!-- Info Box Sync -->
				<div class="p-3.5 rounded-2xl border border-blue-100/50 dark:border-blue-900/30 bg-blue-50/40 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 text-[11px] leading-relaxed font-semibold flex items-start gap-2">
					<AlertCircle class="w-4 h-4 shrink-0 text-blue-500" />
					<span>Data pendidikan ini secara otomatis disinkronkan ke dalam CV Builder Anda di database.</span>
				</div>

				<!-- Action buttons -->
				<div class="flex justify-end gap-3 pt-2">
					<button
						@click="showEditModal = false"
						type="button"
						class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-600 dark:text-zinc-450 hover:bg-slate-50 dark:hover:bg-zinc-800/80 transition-colors cursor-pointer"
					>
						Batal
					</button>
					<button
						type="submit"
						:disabled="isSubmitting"
						class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-50 dark:hover:bg-slate-200 text-white dark:text-slate-950 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
					>
						<Loader2 v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
						<span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}</span>
					</button>
				</div>
			</form>
		</Modal>
	</div>
</template>
