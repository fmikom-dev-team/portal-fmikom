<script setup lang="ts">
import {
	Award,
	ExternalLink,
	FileText,
	Image as ImageIcon,
	Loader2,
	Pencil,
	Plus,
	Tags,
	Trash2,
	UploadCloud,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Skeleton } from "@/components/ui/skeleton";
import Modal from "../ui/Modal.vue";
import Progress from "../ui/Progress.vue";
import {
	getOrgHex,
	getOrgLogo,
	POPULAR_ORGANIZATIONS,
	useCertificates,
} from "./composables/useCertificates";
import MonthYearPickerPopover from "./MonthYearPickerPopover.vue";

const props = withDefaults(
	defineProps<{
		isOwnProfile: boolean;
		certificates: Array<{
			id: number;
			title: string;
			issuer: string;
			date: string;
			expirationDate?: string;
			credentialId?: string;
			credentialUrl?: string;
			skills?: string[];
			media?: Array<{
				name: string;
				path: string;
				type?: string;
				thumbnail_path?: string;
			}>;
		}>;
		isLoading?: boolean;
		isStudent?: boolean;
	}>(),
	{
		isStudent: true,
	},
);

const emit = defineEmits<{
	(e: "add-toast", message: string, type: string): void;
	(e: "update-certificates", list: any[]): void;
}>();

const {
	localCerts,
	pdfThumbnails,
	generatingPdfPaths,
	failedLogos,

	// Add Form State
	showAddModal,
	formTitle,
	formIssuer,
	formLogoUrl,
	showAddLogoUpload,
	formDate,
	formExpirationDate,
	formCredentialId,
	formCredentialUrl,
	formSkills,
	formNewMedia,
	formNewMediaPreviews,
	isSubmittingAdd,

	// Edit Form State
	showEditModal,
	editingCertId,
	editTitle,
	editIssuer,
	editLogoUrl,
	showEditLogoUpload,
	editDate,
	editExpirationDate,
	editCredentialId,
	editCredentialUrl,
	editSkills,
	editExistingMedia,
	editNewMedia,
	editNewMediaPreviews,
	isSavingEdit,

	// Upload Progress
	isUploading,
	isCheckingAddLogo,
	isCheckingEditLogo,
	isUploadingAddLogo,
	isUploadingEditLogo,
	isDeletingId,

	// Methods
	openAddModal,
	closeAddModal,
	handleAddCertificate,
	openEditModal,
	closeEditModal,
	handleSaveEdit,
	handleDeleteCertificate,
	handleLogoUpload,
	onAddIssuerChange,
	onEditIssuerChange,
	validateAndAddFile,
	removeNewFile,
	removeExistingFile,
} = useCertificates(
	() => props.certificates,
	(msg, type) => emit("add-toast", msg, type),
	(list) => emit("update-certificates", list),
);

// Autocomplete & tagging states local to component for clean UI interaction
const skillInput = ref("");
const showSkillSuggestions = ref(false);
const showAddAutocomplete = ref(false);
const showEditAutocomplete = ref(false);
const addFileInput = ref<HTMLInputElement | null>(null);
const editFileInput = ref<HTMLInputElement | null>(null);

const FMIKOM_SKILLS_SUGGESTIONS = [
	"Figma",
	"UI/UX Design",
	"Vue.js",
	"Laravel",
	"Tailwind CSS",
	"TypeScript",
	"Git",
	"Docker",
	"Database Systems",
	"Cloud Computing",
	"Algoritma & Struktur Data",
	"Analisis Data",
	"Artificial Intelligence",
];

const filteredSkills = computed(() => {
	const val = skillInput.value.toLowerCase().trim();
	if (!val) return FMIKOM_SKILLS_SUGGESTIONS;
	return FMIKOM_SKILLS_SUGGESTIONS.filter((s) => s.toLowerCase().includes(val));
});

const filteredAddIssuers = computed(() => {
	const val = formIssuer.value.toLowerCase().trim();
	if (!val) return POPULAR_ORGANIZATIONS;
	return POPULAR_ORGANIZATIONS.filter((org) =>
		org.name.toLowerCase().includes(val),
	);
});

const filteredEditIssuers = computed(() => {
	const val = editIssuer.value.toLowerCase().trim();
	if (!val) return POPULAR_ORGANIZATIONS;
	return POPULAR_ORGANIZATIONS.filter((org) =>
		org.name.toLowerCase().includes(val),
	);
});

const formatMonthYear = (dateStr: string) => {
	if (!dateStr) return "";
	if (!dateStr.includes("-")) return dateStr;
	const [year, month] = dateStr.split("-");
	const monthNames = [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December",
	];
	const mIdx = Number.parseInt(month, 10) - 1;
	return `${monthNames[mIdx] || month} ${year}`;
};

const selectAddIssuer = (name: string) => {
	formIssuer.value = name;
	onAddIssuerChange(name);
	showAddAutocomplete.value = false;
};

const selectEditIssuer = (name: string) => {
	editIssuer.value = name;
	onEditIssuerChange(name);
	showEditAutocomplete.value = false;
};

const addSkill = (skill: string) => {
	const s = skill.trim();
	if (!s) return;
	if (showAddModal.value) {
		if (!formSkills.value.includes(s)) formSkills.value.push(s);
	} else if (showEditModal.value) {
		if (!editSkills.value.includes(s)) editSkills.value.push(s);
	}
	skillInput.value = "";
	showSkillSuggestions.value = false;
};

const removeSkill = (index: number) => {
	if (showAddModal.value) {
		formSkills.value.splice(index, 1);
	} else if (showEditModal.value) {
		editSkills.value.splice(index, 1);
	}
};

const handleDrop = (e: DragEvent, isEdit: boolean) => {
	e.preventDefault();
	if (e.dataTransfer?.files) {
		for (const file of e.dataTransfer.files) {
			validateAndAddFile(file, isEdit);
		}
	}
};

const handleFileChange = (e: Event, isEdit: boolean) => {
	const target = e.target as HTMLInputElement;
	if (target.files) {
		for (const file of target.files) {
			validateAndAddFile(file, isEdit);
		}
	}
	target.value = "";
};

const closeAllPopovers = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (!target.closest(".autocomplete-container")) {
		showAddAutocomplete.value = false;
		showEditAutocomplete.value = false;
	}
	if (!target.closest(".skills-container")) {
		showSkillSuggestions.value = false;
	}
};

onMounted(() => {
	document.addEventListener("click", closeAllPopovers);
});

onUnmounted(() => {
	document.removeEventListener("click", closeAllPopovers);
});
</script>

<template>
	<div class="space-y-6 max-w-3xl">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
			<div>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Certificates & Licenses</h3>
				<p class="text-xs text-slate-500 mt-0.5">Professional certifications and academic achievements.</p>
			</div>
			<button 
				v-if="isOwnProfile && isStudent" 
				@click="openAddModal" 
				class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-xs font-semibold transition-all duration-300 shadow-sm hover:scale-[1.02] active:scale-[0.98] cursor-pointer border-none shrink-0 w-full sm:w-auto justify-center"
			>
				<Plus class="w-3.5 h-3.5" />
				<span>Upload Certificate</span>
			</button>
		</div>
		
		<div class="grid grid-cols-1 gap-4">
			<!-- Skeletons -->
			<template v-if="isLoading">
				<div 
					v-for="n in 2" 
					:key="n" 
					class="flex items-center gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl"
				>
					<Skeleton class="w-12 h-12 rounded-xl shrink-0" />
					<div class="flex-1 space-y-2">
						<Skeleton class="h-4 w-48" />
						<Skeleton class="h-3 w-32" />
						<Skeleton class="h-2.5 w-24" />
					</div>
				</div>
			</template>
			
			<template v-else-if="localCerts && localCerts.length > 0">
				<div 
					v-for="cert in localCerts" 
					:key="cert.id" 
					class="flex flex-col gap-3.5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-805 p-5 rounded-2xl shadow-2xs hover:shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all group/card"
				>
					<div class="flex items-start justify-between gap-4">
						<div class="flex items-start gap-4 min-w-0 flex-1">
							<!-- Org logo: show brand logo or fallback initials -->
							<div class="w-12 h-12 rounded-xl flex items-center justify-center border border-slate-100/80 dark:border-slate-800 shrink-0 mt-0.5 overflow-hidden bg-white dark:bg-slate-800 shadow-xs p-1.5">
								<template v-if="getOrgLogo(cert.issuer)">
									<img 
										:src="getOrgLogo(cert.issuer) || undefined" 
										:alt="cert.issuer"
										class="w-full h-full object-contain"
									/>
								</template>
								<template v-else-if="cert.logo_url && !failedLogos[cert.issuer]">
									<img 
										:src="cert.logo_url" 
										:alt="cert.issuer"
										@error="failedLogos[cert.issuer] = true"
										class="w-full h-full object-contain"
									/>
								</template>
								<template v-else>
									<span 
										class="w-full h-full rounded-lg flex items-center justify-center text-white font-black text-xs"
										:style="{ backgroundColor: getOrgHex(cert.issuer) }"
									>{{ cert.issuer?.slice(0,2).toUpperCase() || '??' }}</span>
								</template>
							</div>
							<div class="flex-1 min-w-0">
								<h4 class="text-sm font-black text-slate-800 dark:text-white truncate flex items-center gap-2">
									<span>{{ cert.title }}</span>
									<a 
										v-if="cert.credentialUrl" 
										:href="cert.credentialUrl" 
										target="_blank" 
										rel="noopener noreferrer"
										class="text-slate-400 hover:text-indigo-500 transition-colors"
										title="Verify Credential"
									>
										<ExternalLink class="w-3.5 h-3.5" />
									</a>
								</h4>
								<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
									{{ cert.issuer }} • Issued {{ formatMonthYear(cert.date) }} 
									<span v-if="cert.expirationDate"> • Expires {{ formatMonthYear(cert.expirationDate) }}</span>
									<span v-else> • No Expiration</span>
								</p>
								<p v-if="cert.credentialId" class="text-[10px] text-slate-400 mt-1 font-semibold uppercase tracking-wider">Credential ID: {{ cert.credentialId }}</p>
							</div>
						</div>

						<!-- Actions -->
						<div v-if="isOwnProfile && isStudent" class="flex items-center gap-1 shrink-0">
							<button 
								@click="openEditModal(cert)"
								class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center justify-center transition-colors border-none bg-transparent cursor-pointer"
								title="Edit Certificate"
							>
								<Pencil class="w-3.5 h-3.5" />
							</button>
							<button 
								@click="handleDeleteCertificate(cert.id, cert.title)"
								:disabled="isDeletingId === cert.id"
								class="w-8 h-8 rounded-full hover:bg-red-50 dark:hover:bg-red-950/30 text-slate-500 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center transition-colors border-none bg-transparent cursor-pointer disabled:opacity-50"
								title="Delete Certificate"
							>
								<Loader2 v-if="isDeletingId === cert.id" class="w-3.5 h-3.5 animate-spin" />
								<Trash2 v-else class="w-3.5 h-3.5" />
							</button>
						</div>
					</div>

					<!-- Skills Tagged -->
					<div v-if="cert.skills && cert.skills.length > 0" class="flex flex-wrap gap-1.5 pt-1">
						<span 
							v-for="skill in cert.skills" 
							:key="skill"
							class="px-2.5 py-0.5 rounded-md bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-800 text-[10px] font-bold text-slate-650 dark:text-slate-300"
						>
							{{ skill }}
						</span>
					</div>

					<!-- Attached Documents Preview -->
					<div v-if="cert.media && cert.media.length > 0" class="pt-3.5 border-t border-dashed border-slate-100 dark:border-slate-800 space-y-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Media & Lampiran</p>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
							<a 
								v-for="file in cert.media" 
								:key="file.path"
								:href="'/storage/' + file.path" 
								target="_blank"
								class="group/media flex items-center gap-3 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-805 bg-slate-50/40 dark:bg-slate-900/40 hover:bg-slate-50/90 dark:hover:bg-slate-900/90 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer shadow-3xs"
							>
								<!-- Left: Thumbnail (fixed square size) -->
								<div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-150 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-800 flex items-center justify-center relative">
									<!-- Case 1: PDF without thumbnail -->
									<template v-if="(file.type === 'pdf' || file.path.endsWith('.pdf')) && !file.thumbnail_path">
										<div v-if="generatingPdfPaths[file.path]" class="absolute inset-0 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
											<Skeleton class="w-full h-full" />
										</div>
										
										<img 
											v-else-if="pdfThumbnails[file.path]"
											:src="pdfThumbnails[file.path]" 
											:alt="file.name"
											class="w-full h-full object-cover transition-transform duration-300 group-hover/media:scale-105"
										/>
										
										<FileText v-else class="w-5 h-5 text-indigo-500" />
									</template>
									
									<!-- Case 2: Image or PDF with thumbnail -->
									<template v-else>
										<div class="absolute inset-0 bg-slate-100 dark:bg-slate-800 flex items-center justify-center media-skeleton group-hover/media:opacity-0 transition-opacity">
											<Skeleton class="w-full h-full" />
										</div>
										
										<img 
											:src="'/storage/' + (file.thumbnail_path || file.path)" 
											:alt="file.name"
											class="w-full h-full object-cover transition-transform duration-300 group-hover/media:scale-105"
											@load="(e) => (e.target as HTMLImageElement).previousElementSibling?.remove()"
											@error="(e) => { (e.target as HTMLImageElement).style.display = 'none'; }"
										/>
									</template>
								</div>
								
								<!-- Right: File Details -->
								<div class="flex-1 min-w-0 pr-1">
									<p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate group-hover/media:text-indigo-600 dark:group-hover/media:text-indigo-400 transition-colors">
										{{ file.name }}
									</p>
									<p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
										<span class="px-1 py-0.25 rounded bg-slate-100 dark:bg-slate-800 text-[8px] font-black">{{ file.type || (file.path.endsWith('.pdf') ? 'pdf' : 'image') }}</span>
										<span>Buka Dokumen</span>
									</p>
								</div>
								
								<ExternalLink class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600 shrink-0 mr-1 transition-colors group-hover/media:text-indigo-500" />
							</a>
						</div>
					</div>
				</div>
			</template>
			
			<template v-else>
				<!-- Empty state -->
				<div class="border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center bg-slate-50/50 dark:bg-slate-900/10">
					<Award class="w-8 h-8 text-slate-400 mx-auto mb-3" />
					<h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">No Certificates Added</h3>
					<p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">Showcase your verified achievements and credentials on your profile.</p>
				</div>
			</template>
		</div>

		<!-- Add Certificate Modal -->
		<Modal :show="showAddModal" title="Upload Certificate" max-width="lg" :prevent-close="isSubmittingAdd" @close="closeAddModal">
			<form @submit.prevent="handleAddCertificate" class="space-y-4">

				<div class="space-y-1.5">
					<label for="cert-add-title" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Certificate Title <span class="text-red-500">*</span></label>
					<input 
						id="cert-add-title"
						v-model="formTitle" 
						type="text" 
						required
						placeholder="e.g. AWS Certified Solutions Architect" 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>
				
				<!-- Autocomplete Organization Input -->
				<div class="space-y-1.5 relative autocomplete-container">
					<div class="flex justify-between items-center">
						<label for="cert-add-issuer" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issuing Organization <span class="text-red-500">*</span></label>
						<!-- Tiny loading spinner or logo preview -->
						<div v-if="isCheckingAddLogo" class="flex items-center gap-1.5 text-[9px] text-slate-400">
							<Loader2 class="w-3 h-3 animate-spin text-indigo-500" />
							<span>Checking logo...</span>
						</div>
						<div v-else-if="formLogoUrl" class="flex items-center gap-1.5">
							<img :src="formLogoUrl" class="w-4 h-4 object-contain rounded" alt="Organization logo" />
							<span class="text-[9px] text-emerald-500 font-bold">Logo detected</span>
						</div>
					</div>
					<input 
						id="cert-add-issuer"
						v-model="formIssuer" 
						type="text" 
						required
						@focus="showAddAutocomplete = true"
						@click.stop="showAddAutocomplete = true"
						@input="onAddIssuerChange(formIssuer)"
						placeholder="e.g. Amazon Web Services, Microsoft, Google" 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
					<!-- Inline Upload Logo Option if not found -->
					<div v-if="showAddLogoUpload && formIssuer.trim()" class="mt-1.5 p-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/10 flex items-center justify-between gap-3">
						<div class="text-[9px] text-slate-400 font-semibold leading-normal">
							Logo tidak ditemukan. Upload logo organisasi? (Opsional)
						</div>
						<label class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-55 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold cursor-pointer hover:bg-indigo-100/50 dark:hover:bg-indigo-900/60 transition-colors border border-indigo-100/10">
							<UploadCloud class="w-3 h-3" />
							<span>{{ isUploadingAddLogo ? 'Uploading...' : 'Choose Logo' }}</span>
							<input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event, false)" :disabled="isUploadingAddLogo" />
						</label>
					</div>
					<!-- Dropdown Autocomplete menu -->
					<div 
						v-if="showAddAutocomplete && filteredAddIssuers.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-48 overflow-y-auto"
					>
						<button
							v-for="org in filteredAddIssuers"
							:key="org.name"
							type="button"
							@click="selectAddIssuer(org.name)"
							class="w-full flex items-center gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/70 text-left border-none bg-transparent cursor-pointer transition-colors"
						>
							<span 
								class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden bg-white dark:bg-slate-100 border border-slate-100 dark:border-slate-200 shadow-sm p-1"
							>
								<img v-if="org.logo" :src="org.logo" :alt="org.name" class="w-full h-full object-contain" />
								<span v-else class="font-black text-[9px] leading-none" :style="{ color: org.hex }">{{ org.name.slice(0,2).toUpperCase() }}</span>
							</span>
							<span class="flex-1 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ org.name }}</span>
							<Award class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
						</button>
					</div>
				</div>

				<!-- Reusable Month-Year Custom Popover Selectors -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<MonthYearPickerPopover
						v-model="formDate"
						label="Issue Date"
						placeholder="Select Issue Date"
						required
					/>
					<MonthYearPickerPopover
						v-model="formExpirationDate"
						label="Expiration Date (Optional)"
						placeholder="Select Expiration Date"
						show-clear
					/>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5">
						<label for="cert-add-cred-id" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential ID (Optional)</label>
						<input 
							id="cert-add-cred-id"
							v-model="formCredentialId" 
							type="text" 
							placeholder="e.g. AWS-12345" 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

					<div class="space-y-1.5">
						<label for="cert-add-cred-url" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential URL (Optional)</label>
						<input 
							id="cert-add-cred-url"
							v-model="formCredentialUrl" 
							type="url" 
							placeholder="e.g. https://credly.com/..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>
				</div>

				<!-- Skills Tagging Panel -->
				<div class="space-y-1.5 relative skills-container">
					<label for="cert-add-skill-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Skills & Competency (Optional)</label>
					<div class="w-full border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 rounded-xl p-2 min-h-12 flex flex-wrap gap-1.5 items-center">
						<span 
							v-for="(skill, index) in formSkills" 
							:key="skill"
							class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/20 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-100/30"
						>
							<span>{{ skill }}</span>
							<X @click="removeSkill(index)" class="w-3 h-3 hover:text-red-500 cursor-pointer" />
						</span>
						<input 
							id="cert-add-skill-input"
							v-model="skillInput" 
							type="text"
							@focus="showSkillSuggestions = true"
							@click.stop="showSkillSuggestions = true"
							@keydown.enter.prevent="addSkill(skillInput)"
							placeholder="Add skill (press Enter)..." 
							class="flex-1 min-w-[120px] bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none border-none py-1 px-1.5"
						/>
					</div>

					<!-- Skills Suggestions Menu -->
					<div 
						v-if="showSkillSuggestions && filteredSkills.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-40 overflow-y-auto"
					>
						<button
							v-for="s in filteredSkills"
							:key="s"
							type="button"
							@click="addSkill(s)"
							class="w-full flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-left border-none bg-transparent cursor-pointer"
						>
							<span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ s }}</span>
							<Tags class="w-3.5 h-3.5 text-slate-400" />
						</button>
					</div>
				</div>

				<!-- Drag and drop upload panel -->
				<div class="space-y-1.5">
					<label for="cert-add-file-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Attachment (Images / PDF, Max 3 Files, Max 20MB)</label>
					<div 
						@dragover.prevent
						@drop.prevent="handleDrop($event, false)"
						@click="addFileInput?.click()"
						class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/30 dark:bg-slate-900/20 rounded-2xl p-5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center min-h-24"
					>
						<input 
							id="cert-add-file-input"
							type="file" 
							ref="addFileInput" 
							multiple
							accept="image/*,application/pdf"
							class="hidden" 
							@change="handleFileChange($event, false)"
						/>
						<UploadCloud class="w-6 h-6 text-slate-400 mb-1.5" />
						<p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
							Tarik & lepas file di sini, atau <span class="text-indigo-500 hover:underline">pilih file</span>
						</p>
						<p class="text-[9px] text-slate-400 mt-0.5">JPEG, PNG, WebP, GIF, PDF (Max. 20MB per file)</p>
					</div>

					<!-- Uploaded Files List -->
					<div v-if="formNewMediaPreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
						<div 
							v-for="(preview, index) in formNewMediaPreviews" 
							:key="index"
							class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
						>
							<div v-if="preview.isGenerating" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 p-2">
								<Skeleton class="w-full h-full" />
							</div>
							
							<template v-else>
								<img 
									v-if="preview.url" 
									:src="preview.url" 
									alt="Pratinjau Sertifikat"
									class="w-full h-full object-cover" 
								/>
								<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
									<FileText class="w-6 h-6 text-slate-400 mb-1" />
									<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ preview.name }}</span>
								</div>

								<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
									<FileText v-if="preview.type === 'pdf'" class="w-2 h-2 text-indigo-400" />
									<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
									<span>{{ preview.type }}</span>
								</div>

								<button 
									type="button" 
									@click.stop="removeNewFile(index, false)"
									class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
								>
									<X class="w-3.5 h-3.5" />
								</button>
							</template>
						</div>
					</div>
				</div>

				<div class="flex items-center justify-end gap-2 pt-2">
					<button 
						type="button"
						@click="closeAddModal" 
						class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer bg-white dark:bg-slate-900"
						:disabled="isSubmittingAdd"
					>
						Cancel
					</button>
					<button 
						type="submit"
						class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#18181b] hover:bg-zinc-700 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-sm transition-all cursor-pointer border-none"
						:disabled="isSubmittingAdd"
					>
						<span v-if="isSubmittingAdd" class="flex items-center gap-1.5">
							<Loader2 class="w-3.5 h-3.5 animate-spin" /> Uploading...
						</span>
						<span v-else>Save Certificate</span>
					</button>
				</div>
			</form>
		</Modal>

		<!-- Edit Certificate Modal -->
		<Modal :show="showEditModal" title="Edit Certificate" max-width="lg" :prevent-close="isSavingEdit" @close="closeEditModal">
			<form @submit.prevent="handleSaveEdit" class="space-y-4">


				<div class="space-y-1.5">
					<label for="cert-edit-title" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Certificate Title <span class="text-red-500">*</span></label>
					<input 
						id="cert-edit-title"
						v-model="editTitle" 
						type="text" 
						required
						placeholder="Certificate Title..." 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>
				
				<div class="space-y-1.5 relative autocomplete-container">
					<div class="flex justify-between items-center">
						<label for="cert-edit-issuer" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issuing Organization <span class="text-red-500">*</span></label>
						<div v-if="isCheckingEditLogo" class="flex items-center gap-1.5 text-[9px] text-slate-400">
							<Loader2 class="w-3 h-3 animate-spin text-indigo-500" />
							<span>Checking logo...</span>
						</div>
						<div v-else-if="editLogoUrl" class="flex items-center gap-1.5">
							<img :src="editLogoUrl" alt="Logo preview" class="w-4 h-4 object-contain rounded" />
							<span class="text-[9px] text-emerald-500 font-bold">Logo detected</span>
						</div>
					</div>
					<input 
						id="cert-edit-issuer"
						v-model="editIssuer" 
						type="text" 
						required
						@focus="showEditAutocomplete = true"
						@click.stop="showEditAutocomplete = true"
						@input="onEditIssuerChange(editIssuer)"
						placeholder="e.g. Coursera..." 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
					<div v-if="showEditLogoUpload && editIssuer.trim()" class="mt-1.5 p-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/10 flex items-center justify-between gap-3">
						<div class="text-[9px] text-slate-400 font-semibold leading-normal">
							Logo tidak ditemukan. Upload logo organisasi? (Opsional)
						</div>
						<label class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-55 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold cursor-pointer hover:bg-indigo-100/50 dark:hover:bg-indigo-900/60 transition-colors border border-indigo-100/10">
							<UploadCloud class="w-3 h-3" />
							<span>{{ isUploadingEditLogo ? 'Uploading...' : 'Choose Logo' }}</span>
							<input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event, true)" :disabled="isUploadingEditLogo" />
						</label>
					</div>
					<div 
						v-if="showEditAutocomplete && filteredEditIssuers.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-48 overflow-y-auto"
					>
						<button
							v-for="org in filteredEditIssuers"
							:key="org.name"
							type="button"
							@click="selectEditIssuer(org.name)"
							class="w-full flex items-center gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/70 text-left border-none bg-transparent cursor-pointer transition-colors"
						>
							<span 
								class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden bg-white dark:bg-slate-100 border border-slate-100 dark:border-slate-200 shadow-sm p-1"
							>
								<img v-if="org.logo" :src="org.logo" :alt="org.name" class="w-full h-full object-contain" />
								<span v-else class="font-black text-[9px] leading-none" :style="{ color: org.hex }">{{ org.name.slice(0,2).toUpperCase() }}</span>
							</span>
							<span class="flex-1 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ org.name }}</span>
							<Award class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
						</button>
					</div>
				</div>

				<!-- Reusable Month-Year Custom Popover Selectors -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<MonthYearPickerPopover
						v-model="editDate"
						label="Issue Date"
						placeholder="Select Issue Date"
						required
					/>
					<MonthYearPickerPopover
						v-model="editExpirationDate"
						label="Expiration Date (Optional)"
						placeholder="Select Expiration Date"
						show-clear
					/>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5">
						<label for="cert-edit-id-label" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential ID (Optional)</label>
						<input 
							id="cert-edit-id-label"
							v-model="editCredentialId" 
							type="text" 
							placeholder="Credential ID..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

					<div class="space-y-1.5">
						<label for="cert-edit-url-label" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential URL (Optional)</label>
						<input 
							id="cert-edit-url-label"
							v-model="editCredentialUrl" 
							type="url" 
							placeholder="Credential URL..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>
				</div>

				<div class="space-y-1.5 relative skills-container">
					<label for="cert-edit-skill-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Skills & Competency (Optional)</label>
					<div class="w-full border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 rounded-xl p-2 min-h-12 flex flex-wrap gap-1.5 items-center">
						<span 
							v-for="(skill, index) in editSkills" 
							:key="skill"
							class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/20 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-100/30"
						>
							<span>{{ skill }}</span>
							<X @click="removeSkill(index)" class="w-3 h-3 hover:text-red-500 cursor-pointer" />
						</span>
						<input 
							id="cert-edit-skill-input"
							v-model="skillInput" 
							type="text"
							@focus="showSkillSuggestions = true"
							@click.stop="showSkillSuggestions = true"
							@keydown.enter.prevent="addSkill(skillInput)"
							placeholder="Add skill (press Enter)..." 
							class="flex-1 min-w-[120px] bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none border-none py-1 px-1.5"
						/>
					</div>

					<div 
						v-if="showSkillSuggestions && filteredSkills.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-40 overflow-y-auto"
					>
						<button
							v-for="s in filteredSkills"
							:key="s"
							type="button"
							@click="addSkill(s)"
							class="w-full flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-left border-none bg-transparent cursor-pointer"
						>
							<span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ s }}</span>
							<Tags class="w-3.5 h-3.5 text-slate-400" />
						</button>
					</div>
				</div>

				<!-- Attached Files -->
				<div class="space-y-1.5">
					<label for="cert-edit-file-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Attachment (Images / PDF, Max 3 Files, Max 20MB)</label>
					
					<!-- Existing Files -->
					<div v-if="editExistingMedia.length > 0" class="space-y-2 pb-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">File Terunggah (Uploaded Files):</p>
						<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
							<div 
								v-for="(file, index) in editExistingMedia" 
								:key="file.path"
								class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
							>
								<template v-if="(file.type === 'pdf' || file.path.endsWith('.pdf')) && !file.thumbnail_path">
									<div v-if="generatingPdfPaths[file.path]" class="absolute inset-0 bg-slate-105 dark:bg-slate-800 animate-pulse flex flex-col items-center justify-center p-2">
										<Loader2 class="w-5 h-5 text-indigo-500 animate-spin mb-1" />
										<span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Generating...</span>
									</div>
									
									<img 
										v-else-if="pdfThumbnails[file.path]"
										:src="pdfThumbnails[file.path]" 
										alt="PDF thumbnail"
										class="w-full h-full object-cover"
									/>
									
									<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
										<FileText class="w-6 h-6 text-slate-400 mb-1" />
										<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ file.name }}</span>
									</div>
								</template>
								
								<template v-else>
									<img 
										:src="'/storage/' + (file.thumbnail_path || file.path)" 
										alt="Attachment preview"
										class="w-full h-full object-cover" 
									/>
								</template>

								<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
									<FileText v-if="file.type === 'pdf' || file.path.endsWith('.pdf')" class="w-2 h-2 text-indigo-400" />
									<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
									<span>{{ file.type || (file.path.endsWith('.pdf') ? 'pdf' : 'image') }}</span>
								</div>

								<button 
									type="button" 
									@click.stop="removeExistingFile(index)"
									class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
								>
									<X class="w-3.5 h-3.5" />
								</button>
							</div>
						</div>
					</div>

					<div 
						@dragover.prevent
						@drop.prevent="handleDrop($event, true)"
						@click="editFileInput?.click()"
						class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/30 dark:bg-slate-900/20 rounded-2xl p-5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center min-h-24"
					>
						<input 
							type="file" 
							id="cert-edit-file-input"
							ref="editFileInput" 
							multiple
							accept="image/*,application/pdf"
							class="hidden" 
							@change="handleFileChange($event, true)"
						/>
						<UploadCloud class="w-6 h-6 text-slate-400 mb-1.5" />
						<p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
							Tarik & lepas file baru di sini, atau <span class="text-indigo-500 hover:underline">pilih file</span>
						</p>
						<p class="text-[9px] text-slate-400 mt-0.5">JPEG, PNG, WebP, GIF, PDF (Max. 20MB per file)</p>
					</div>

					<!-- New Files List -->
					<div v-if="editNewMediaPreviews.length > 0" class="space-y-2 pt-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">File Baru Ditambahkan:</p>
						<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
							<div 
								v-for="(preview, index) in editNewMediaPreviews" 
								:key="index"
								class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
							>
								<div v-if="preview.isGenerating" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 animate-pulse p-2">
									<Loader2 class="w-5 h-5 text-indigo-500 animate-spin mb-1" />
									<span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Generating thumb...</span>
								</div>
								
								<template v-else>
									<img 
										v-if="preview.url" 
										:src="preview.url" 
										alt="New attachment preview"
										class="w-full h-full object-cover" 
									/>
									<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
										<FileText class="w-6 h-6 text-slate-400 mb-1" />
										<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ preview.name }}</span>
									</div>

									<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
										<FileText v-if="preview.type === 'pdf'" class="w-2 h-2 text-indigo-400" />
										<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
										<span>{{ preview.type }}</span>
									</div>

									<button 
										type="button" 
										@click.stop="removeNewFile(index, true)"
										class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
									>
										<X class="w-3.5 h-3.5" />
									</button>
								</template>
							</div>
						</div>
					</div>
				</div>

				<div class="flex items-center justify-end gap-2 pt-2">
					<button 
						type="button"
						@click="closeEditModal" 
						class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
						:disabled="isSavingEdit"
					>
						Cancel
					</button>
					<button 
						type="submit"
						class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#18181b] hover:bg-zinc-700 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-sm transition-all cursor-pointer border-none"
						:disabled="isSavingEdit"
					>
						<span v-if="isSavingEdit" class="flex items-center gap-1.5">
							<Loader2 class="w-3.5 h-3.5 animate-spin" /> Saving...
						</span>
						<span v-else>Save Changes</span>
					</button>
				</div>
			</form>
		</Modal>
	</div>
</template>
