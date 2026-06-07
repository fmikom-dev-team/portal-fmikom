<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import Cropper from "cropperjs";
import {
	Camera,
	Github,
	Globe,
	Image,
	Linkedin,
	Loader2,
	MapPin,
	RotateCcw,
	RotateCw,
	Smile,
	Trash2,
	Twitter,
	X,
	ZoomIn,
	ZoomOut,
} from "lucide-vue-next";
import { computed, nextTick, ref, watch } from "vue";
import "cropperjs/dist/cropper.css";

const props = withDefaults(
	defineProps<{
		submitUrl: string;
		onCancel?: () => void;
		hideHeader?: boolean;
		useSimpleContainer?: boolean;
	}>(),
	{
		hideHeader: false,
		useSimpleContainer: false,
	},
);

const page = usePage();
const user = computed(() => page.props.auth?.user || ({} as any));

const form = useForm({
	name: (user.value.name || "").toUpperCase(),
	email: user.value.email || "",
	role_title: user.value.role_title || "",
	bio: user.value.bio || "",
	location: user.value.location || "",
	website: user.value.website || "",
	twitter: user.value.twitter || "",
	linkedin: user.value.linkedin || "",
	github: user.value.github || "",
	tanggal_lahir: user.value.tanggal_lahir || "",
	no_telepon: user.value.no_telepon || "",
	program_studi_id: user.value.program_studi_id || "",
	tahun_lulus: user.value.tahun_lulus || "",
	nomor_induk: user.value.nomor_induk || "",
	foto: null as File | null,
	remove_foto: false,
	avatar_url: "",
});

// File input element reference
const fileInput = ref<HTMLInputElement | null>(null);

// Image previews
const photoPreview = ref<string | null>(
	user.value.foto_path
		? user.value.foto_path.startsWith("http")
			? user.value.foto_path
			: `/storage/${user.value.foto_path}`
		: user.value.avatar || null,
);

const photoOption = ref<string>(user.value.foto_path ? "upload" : "avatar");

watch(
	() => user.value,
	(newUser) => {
		form.name = (newUser.name || "").toUpperCase();
		form.email = newUser.email || "";
		form.role_title = newUser.role_title || "";
		form.bio = newUser.bio || "";
		form.location = newUser.location || "";
		form.website = newUser.website || "";
		form.twitter = newUser.twitter || "";
		form.linkedin = newUser.linkedin || "";
		form.github = newUser.github || "";
		form.tanggal_lahir = newUser.tanggal_lahir || "";
		form.no_telepon = newUser.no_telepon || "";
		form.program_studi_id = newUser.program_studi_id || "";
		form.tahun_lulus = newUser.tahun_lulus || "";
		form.nomor_induk = newUser.nomor_induk || "";
		photoPreview.value = newUser.foto_path
			? newUser.foto_path.startsWith("http")
				? newUser.foto_path
				: `/storage/${newUser.foto_path}`
			: newUser.avatar || null;
		photoOption.value = newUser.foto_path ? "upload" : "avatar";
	},
	{ deep: true },
);

const programStudiOptions = [
	{ value: 1, label: "Informatika" },
	{ value: 2, label: "Sistem Informasi" },
	{ value: 3, label: "Matematika" },
];

const currentYear = new Date().getFullYear();
const tahunLulusOptions = Array.from(
	{ length: currentYear - 1989 },
	(_, i) => currentYear - i,
);

const idLabel = computed(() => {
	const type = user.value.user_type;
	if (type === "mahasiswa") return "NIM (Nomor Induk Mahasiswa)";
	if (type === "alumni") return "NIM Alumni";
	if (type === "dosen") return "NIDN / NIP Dosen";
	if (type === "staff") return "NIP Staff";
	if (type === "mitra") return "NIB / No. Perusahaan";
	return "Nomor Induk / ID";
});

// Trigger file input dialog
const triggerUpload = () => {
	fileInput.value?.click();
};

const isCropperOpen = ref(false);
const cropperImageSrc = ref("");
const cropperImageRef = ref<HTMLImageElement | null>(null);
let cropperInstance: Cropper | null = null;
let originalFileName = "profile.jpg";
let originalFileType = "image/jpeg";

const initCropper = () => {
	if (!cropperImageRef.value) return;
	if (cropperInstance) {
		cropperInstance.destroy();
	}
	cropperInstance = new Cropper(cropperImageRef.value, {
		aspectRatio: 1,
		viewMode: 1,
		dragMode: "move",
		autoCropArea: 0.8,
		restore: false,
		guides: true,
		center: true,
		highlight: false,
		cropBoxMovable: true,
		cropBoxResizable: true,
		toggleDragModeOnDblclick: false,
		background: true,
	});
};

const handleFileChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (file) {
		if (file.size > 2 * 1024 * 1024) {
			alert("File size exceeds 2MB limit.");
			return;
		}
		originalFileName = file.name;
		originalFileType = file.type;

		const reader = new FileReader();
		reader.onload = (event) => {
			if (event.target?.result) {
				cropperImageSrc.value = event.target.result as string;
				isCropperOpen.value = true;
				nextTick(() => {
					initCropper();
				});
			}
		};
		reader.readAsDataURL(file);

		// Reset file input value so same file can be selected again
		if (e.target) {
			(e.target as HTMLInputElement).value = "";
		}
	}
};

const handleCropSave = () => {
	if (!cropperInstance) return;

	// Get cropped canvas
	const canvas = cropperInstance.getCroppedCanvas({
		width: 400,
		height: 400,
		imageSmoothingEnabled: true,
		imageSmoothingQuality: "high",
	});

	if (canvas) {
		canvas.toBlob((blob) => {
			if (blob) {
				// Convert blob to File object
				const croppedFile = new File([blob], originalFileName, {
					type: originalFileType,
				});

				form.foto = croppedFile;
				form.remove_foto = false;
				form.avatar_url = "";

				// Set preview to cropped image
				photoPreview.value = URL.createObjectURL(croppedFile);
				photoOption.value = "upload";

				// Close cropper modal
				closeCropper();
			}
		}, originalFileType);
	}
};

const closeCropper = () => {
	isCropperOpen.value = false;
	cropperImageSrc.value = "";
	if (cropperInstance) {
		cropperInstance.destroy();
		cropperInstance = null;
	}
};

const zoomIn = () => {
	cropperInstance?.zoom(0.1);
};

const zoomOut = () => {
	cropperInstance?.zoom(-0.1);
};

const rotateLeft = () => {
	cropperInstance?.rotate(-90);
};

const rotateRight = () => {
	cropperInstance?.rotate(90);
};

const isOptionModalOpen = ref(false);
const isAvatarModalOpen = ref(false);

const notionistSeeds = [
	"Alexander",
	"Jocelyn",
	"Katherine",
	"Christian",
	"Caleb",
	"Aidan",
	"Sophia",
	"Maya",
	"Mason",
	"Jack",
	"Aria",
	"Lily",
	"Owen",
	"Wyatt",
	"Oliver",
	"Zoe",
	"Chloe",
	"Audrey",
	"Clara",
	"Leo",
	"Felix",
	"Milo",
	"Jasper",
	"Stella",
	"Bella",
	"Elena",
	"Iris",
	"Eva",
	"Nova",
	"Silas",
	"Finn",
	"Ezra",
];

const openOptions = () => {
	isOptionModalOpen.value = true;
};

const selectUploadOption = () => {
	isOptionModalOpen.value = false;
	triggerUpload();
};

const selectAvatarOption = () => {
	isOptionModalOpen.value = false;
	isAvatarModalOpen.value = true;
};

const selectAvatarSeed = (seed: string) => {
	const url = `https://api.dicebear.com/9.x/notionists/svg?seed=${seed}`;
	form.avatar_url = url;
	form.foto = null;
	form.remove_foto = false;
	photoPreview.value = url;
	photoOption.value = "avatar";
	isAvatarModalOpen.value = false;
};

const deletePhoto = () => {
	form.foto = null;
	form.avatar_url = "";
	form.remove_foto = true;
	photoPreview.value = user.value.avatar || null;
	photoOption.value = "avatar";
	isOptionModalOpen.value = false;
};

const handleCancel = () => {
	if (props.onCancel) {
		props.onCancel();
	} else {
		// Reset form
		form.reset();
		form.remove_foto = false;
		form.avatar_url = "";
		photoPreview.value = user.value.foto_path
			? user.value.foto_path.startsWith("http")
				? user.value.foto_path
				: `/storage/${user.value.foto_path}`
			: user.value.avatar || null;
		photoOption.value = user.value.foto_path ? "upload" : "avatar";
	}
};

// Submit form
const submitForm = () => {
	if (props.submitUrl === "/settings/profile") {
		form
			.transform((data) => ({
				...data,
				_method: "PATCH",
			}))
			.post(props.submitUrl, {
				preserveScroll: true,
				forceFormData: true,
			});
	} else {
		form.post(props.submitUrl, {
			preserveScroll: true,
			forceFormData: true,
		});
	}
};

// Computed display initials if no avatar is set
const initials = computed(() => {
	if (!form.name) return "?";
	return form.name
		.split(" ")
		.map((n) => n[0])
		.slice(0, 2)
		.join("")
		.toUpperCase();
});
</script>

<template>
    <div :class="useSimpleContainer ? 'w-full' : 'max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8'">
        <!-- Header -->
        <div v-if="!hideHeader" class="mb-8">
            <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Edit Profile</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 font-medium">
                Update your profile information. Changes will be reflected in the preview.
            </p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6 w-full">
            
            <!-- Form Cards -->
            <div class="space-y-6">
                
                <!-- Basic Information Card -->
                <div class="bg-white dark:bg-slate-900 rounded-[16px] border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Basic Information</h2>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">Your public profile information</p>
                    </div>

                    <!-- Profile Photo Uploader & Choice -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5 mb-6 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/60">
                        <div class="relative group select-none shrink-0 mx-auto sm:mx-0">
                            <div 
                                @click="openOptions"
                                class="w-16 h-16 sm:w-[72px] sm:h-[72px] rounded-full border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden cursor-pointer relative hover:ring-2 hover:ring-blue-500 hover:ring-offset-2 dark:hover:ring-offset-slate-900 transition-all shadow-sm animate-pulse-subtle"
                            >
                                <img 
                                    v-if="photoPreview" 
                                    :src="photoPreview" 
                                    class="w-full h-full object-cover" 
                                    alt="Profile avatar preview"
                                />
                                <div v-else class="text-lg font-black text-slate-400 dark:text-slate-500">
                                    {{ initials }}
                                </div>
                                
                                <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Camera class="w-4 h-4 text-white" />
                                </div>
                            </div>

                            <!-- Small camera icon badge -->
                            <div 
                                @click="openOptions"
                                class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                            >
                                <Camera class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                            </div>

                            <input 
                                type="file" 
                                ref="fileInput" 
                                class="hidden" 
                                accept="image/*" 
                                @change="handleFileChange"
                            />
                        </div>

                        <div class="flex-1 text-center sm:text-left space-y-1">
                            <div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Foto Profil</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">Klik lingkaran foto atau badge kamera untuk mengubah foto profil atau memilih avatar Notionists.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fields Grid -->
                    <div class="space-y-4">
                        <!-- Row 1: Name and Email Address -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Name</label>
                                <input 
                                    id="name" 
                                    v-model="form.name" 
                                    @input="form.name = form.name.toUpperCase()"
                                    type="text" 
                                    required
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all uppercase"
                                />
                                <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label for="email" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                <input 
                                    id="email" 
                                    v-model="form.email" 
                                    type="email" 
                                    disabled
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed focus:outline-none transition-all"
                                />
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium">To update your email, go to the Security settings tab.</p>
                            </div>
                        </div>

                        <!-- Row 2: Nomor Induk (Read Only) & No. Telepon (Editable) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="nomor_induk" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ idLabel }}</label>
                                <input 
                                    id="nomor_induk" 
                                    v-model="form.nomor_induk" 
                                    type="text" 
                                    disabled
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed focus:outline-none transition-all"
                                />
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium">Nomor identitas terdaftar secara otomatis.</p>
                            </div>
                            <div>
                                <label for="no_telepon" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">No. Telepon</label>
                                <input 
                                    id="no_telepon" 
                                    v-model="form.no_telepon" 
                                    type="tel" 
                                    placeholder="Contoh: 08123456789"
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.no_telepon" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.no_telepon }}</p>
                            </div>
                        </div>

                        <!-- Row 3: Program Studi & Tahun Lulus (Alumni only) -->
                        <div v-if="user.user_type === 'mahasiswa' || user.user_type === 'alumni' || form.program_studi_id" class="grid grid-cols-1 gap-4" :class="user.user_type === 'alumni' ? 'sm:grid-cols-2' : ''">
                            <div>
                                <label for="program_studi_id" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Program Studi</label>
                                <div class="relative">
                                    <select 
                                        id="program_studi_id" 
                                        v-model="form.program_studi_id" 
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                                    >
                                        <option value="" disabled class="text-slate-800 dark:bg-slate-900">Pilih program studi...</option>
                                        <option v-for="prodi in programStudiOptions" :key="prodi.value" :value="prodi.value" class="text-slate-800 dark:bg-slate-900">{{ prodi.label }}</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.program_studi_id" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.program_studi_id }}</p>
                            </div>
                            
                            <div v-if="user.user_type === 'alumni'">
                                <label for="tahun_lulus" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tahun Lulus</label>
                                <div class="relative">
                                    <select 
                                        id="tahun_lulus" 
                                        v-model="form.tahun_lulus" 
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                                    >
                                        <option value="" disabled class="text-slate-800 dark:bg-slate-900">Pilih tahun lulus...</option>
                                        <option v-for="year in tahunLulusOptions" :key="year" :value="year" class="text-slate-800 dark:bg-slate-900">{{ year }}</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.tahun_lulus" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.tahun_lulus }}</p>
                            </div>
                        </div>

                        <!-- Row 4: Role / Title & Tanggal Lahir -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="role_title" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Role / Title</label>
                                <input 
                                    id="role_title" 
                                    v-model="form.role_title" 
                                    type="text" 
                                    disabled
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed focus:outline-none transition-all"
                                />
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium">Your role is assigned automatically and cannot be changed.</p>
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Lahir</label>
                                <input 
                                    id="tanggal_lahir" 
                                    v-model="form.tanggal_lahir" 
                                    type="date" 
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.tanggal_lahir" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.tanggal_lahir }}</p>
                            </div>
                        </div>

                        <!-- Row 5: Location -->
                        <div>
                            <label for="location" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Location</label>
                            <input 
                                id="location" 
                                v-model="form.location" 
                                type="text" 
                                placeholder="e.g. Seattle, WA"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            />
                            <p v-if="form.errors.location" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.location }}</p>
                        </div>

                        <!-- Row 6: Bio -->
                        <div>
                            <label for="bio" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Bio</label>
                            <textarea 
                                id="bio" 
                                v-model="form.bio" 
                                rows="3"
                                placeholder="Describe yourself in a few words..."
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                            ></textarea>
                            <p v-if="form.errors.bio" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.bio }}</p>
                        </div>
                    </div>
                </div>

                <!-- Links Card -->
                <div class="bg-white dark:bg-slate-900 rounded-[16px] border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Links</h2>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">Your website and social links</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="website" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Website</label>
                            <input 
                                id="website" 
                                v-model="form.website" 
                                type="url" 
                                placeholder="https://example.com"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            />
                            <p v-if="form.errors.website" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.website }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="twitter" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Twitter</label>
                                <input 
                                    id="twitter" 
                                    v-model="form.twitter" 
                                    type="text" 
                                    placeholder="username"
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.twitter" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.twitter }}</p>
                            </div>
                            <div>
                                <label for="linkedin" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">LinkedIn</label>
                                <input 
                                    id="linkedin" 
                                    v-model="form.linkedin" 
                                    type="text" 
                                    placeholder="username"
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.linkedin" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.linkedin }}</p>
                            </div>
                            <div>
                                <label for="github" class="block text-[12px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">GitHub</label>
                                <input 
                                    id="github" 
                                    v-model="form.github" 
                                    type="text" 
                                    placeholder="username"
                                    class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.github" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.github }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button 
                        type="button"
                        @click="handleCancel"
                        class="h-11 px-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="h-11 px-6 rounded-xl bg-slate-900 dark:bg-white text-sm font-bold text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-50 transition-all shadow-sm flex items-center gap-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        Save changes
                    </button>
                </div>
            </div>
        </form>

        <!-- Cropper Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="isCropperOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md bg-slate-900/60 overflow-y-auto">
                <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[24px] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Sesuaikan Foto Profil</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mt-0.5">Geser dan sesuaikan foto untuk posisi terbaik</p>
                        </div>
                    </div>

                    <!-- Cropper Arena -->
                    <div class="p-6 bg-slate-50 dark:bg-slate-950 flex items-center justify-center min-h-[300px] max-h-[350px]">
                        <div class="w-full max-h-[300px] overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800 relative">
                            <img ref="cropperImageRef" :src="cropperImageSrc" class="max-w-full block" alt="Source image for cropping" />
                        </div>
                    </div>

                    <!-- Toolbars (Zoom, Rotate) -->
                    <div class="px-6 py-3 bg-slate-50/50 dark:bg-slate-900/50 border-t border-b border-slate-100 dark:border-slate-800 flex items-center justify-center gap-3">
                        <button 
                            type="button" 
                            @click="zoomIn"
                            title="Zoom In"
                            class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm cursor-pointer"
                        >
                            <ZoomIn class="w-4 h-4" />
                        </button>
                        <button 
                            type="button" 
                            @click="zoomOut"
                            title="Zoom Out"
                            class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm cursor-pointer"
                        >
                            <ZoomOut class="w-4 h-4" />
                        </button>
                        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                        <button 
                            type="button" 
                            @click="rotateLeft"
                            title="Rotate Left"
                            class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm cursor-pointer"
                        >
                            <RotateCcw class="w-4 h-4" />
                        </button>
                        <button 
                            type="button" 
                            @click="rotateRight"
                            title="Rotate Right"
                            class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm cursor-pointer"
                        >
                            <RotateCw class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 flex items-center justify-end gap-3 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button"
                            @click="closeCropper"
                            class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="button"
                            @click="handleCropSave"
                            class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm cursor-pointer"
                        >
                            Potong & Simpan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Options Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="isOptionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md bg-slate-900/60 overflow-y-auto">
                <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-[24px] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col p-6 relative">
                    <!-- Close button -->
                    <button 
                        type="button" 
                        @click="isOptionModalOpen = false" 
                        class="absolute top-4 right-4 p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Pilih Tindakan</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mb-6">Ubah tampilan foto profil Anda</p>

                    <div class="space-y-2.5">
                        <button 
                            type="button"
                            @click="selectUploadOption"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/65 text-left text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
                        >
                            <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                                <Image class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-white">Unggah Foto Baru</p>
                                <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mt-0.5">Pilih file foto dari perangkat Anda</p>
                            </div>
                        </button>

                        <button 
                            type="button"
                            @click="selectAvatarOption"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/65 text-left text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
                        >
                            <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                <Smile class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-white">Pilih Avatar Notionists</p>
                                <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mt-0.5">Gunakan ilustrasi avatar modern</p>
                            </div>
                        </button>

                        <button 
                            type="button"
                            @click="deletePhoto"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-rose-100 dark:border-rose-950/40 bg-rose-50/20 dark:bg-rose-950/10 hover:bg-rose-50/50 dark:hover:bg-rose-950/20 text-left text-sm font-bold text-rose-600 dark:text-rose-455 transition-colors cursor-pointer"
                        >
                            <div class="p-2 rounded-lg bg-rose-100/60 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">
                                <Trash2 class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-rose-650 dark:text-rose-450">Hapus Foto Profil</p>
                                <p class="text-[10px] font-medium text-rose-400 dark:text-rose-500/70 mt-0.5">Kembalikan ke inisial nama Anda</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Avatar Selection Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="isAvatarModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md bg-slate-900/60 overflow-y-auto">
                <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[24px] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between relative">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Pilih Avatar Notionists</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mt-0.5">Klik salah satu karakter di bawah untuk memilih</p>
                        </div>
                        <button 
                            type="button" 
                            @click="isAvatarModalOpen = false" 
                            class="p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Avatar Grid -->
                    <div class="p-6 bg-slate-50 dark:bg-slate-950/30 overflow-y-auto max-h-[360px]">
                        <div class="grid grid-cols-4 gap-4">
                            <button 
                                v-for="seed in notionistSeeds" 
                                :key="seed"
                                type="button"
                                @click="selectAvatarSeed(seed)"
                                class="aspect-square rounded-2xl border bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center p-2 relative transition-all duration-300 hover:scale-105 hover:shadow-md cursor-pointer group"
                                :class="[
                                    form.avatar_url.includes('seed=' + seed)
                                        ? 'border-blue-500 ring-2 ring-blue-500/30'
                                        : 'border-slate-150 dark:border-slate-800'
                                ]"
                            >
                                <img 
                                    :src="`https://api.dicebear.com/9.x/notionists/svg?seed=${seed}`"
                                    class="w-full h-full object-contain pointer-events-none rounded-xl"
                                    alt="Notionist avatar"
                                    loading="lazy"
                                />
                                
                                <div class="absolute inset-0 rounded-2xl border-2 border-transparent group-hover:border-blue-500/40 pointer-events-none transition-colors"></div>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 flex items-center justify-end gap-3 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            type="button"
                            @click="isAvatarModalOpen = false"
                            class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm cursor-pointer"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style>
/* Style cropper viewport as a circle to match avatar shape */
.cropper-view-box,
.cropper-face {
  border-radius: 50%;
  outline: 1px solid rgba(255, 255, 255, 0.5) !important;
}
.cropper-line,
.cropper-point {
  background-color: #3b82f6 !important;
}
.cropper-bg {
  background-image: repeating-linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%, #eee), repeating-linear-gradient(45deg, #eee 25%, #fff 25%, #fff 75%, #eee 75%, #eee) !important;
}
.dark .cropper-bg {
  background-image: repeating-linear-gradient(45deg, #1e293b 25%, transparent 25%, transparent 75%, #1e293b 75%, #1e293b), repeating-linear-gradient(45deg, #1e293b 25%, #0f172a 25%, #0f172a 75%, #1e293b 75%, #1e293b) !important;
}
</style>
