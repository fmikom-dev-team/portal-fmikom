<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import {
	Check,
	ChevronDown,
	ExternalLink,
	Github,
	Globe,
	Instagram,
	Linkedin,
	Lock,
	LogOut,
	Pencil,
	Twitter,
	User as UserIcon,
	X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import UmumNavbar from "../ui/UmumNavbar.vue";

const activeSections = ref({
	akun: true, // open by default
	pribadi: false,
	sosial: false,
});

const toggleSection = (section: "akun" | "pribadi" | "sosial") => {
	activeSections.value[section] = !activeSections.value[section];
};

interface ProfileUser {
	id: number;
	name: string;
	email: string;
	tanggal_lahir?: string;
	billing_address?: string;
	legal_entity_name?: string;
	pagi_username?: string;
	progress: number;
	bio?: string;
	avatar?: string;
	works_count?: number;
	followers_count?: number;

	// Expanded Database fields
	no_telepon?: string | null;
	location?: string | null;
	website?: string | null;
	twitter?: string | null;
	linkedin?: string | null;
	github?: string | null;
	instagram?: string | null;
}

const props = defineProps<{
	profileUser: ProfileUser;
	roleName: string;
}>();

// Editing Modals State
const activeModal = ref<"email" | "dob" | "personal" | "social" | null>(null);

// Form Fields
const form = ref({
	email: props.profileUser.email || "",
	name: props.profileUser.name || "",
	tanggal_lahir: props.profileUser.tanggal_lahir || "",
	billing_address: props.profileUser.billing_address || "",
	legal_entity_name: props.profileUser.legal_entity_name || "",
	no_telepon: props.profileUser.no_telepon || "",
	location: props.profileUser.location || "",
	bio: props.profileUser.bio || "",
	website: props.profileUser.website || "",
	twitter: props.profileUser.twitter || "",
	linkedin: props.profileUser.linkedin || "",
	github: props.profileUser.github || "",
	instagram: props.profileUser.instagram || "",
	pagi_username: props.profileUser.pagi_username || "",
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);
const showSuccessToast = ref(false);

const openEditModal = (type: "email" | "dob" | "personal" | "social") => {
	errors.value = {};
	activeModal.value = type;
};

const closeModal = () => {
	activeModal.value = null;
};

const handleSave = () => {
	isSubmitting.value = true;
	errors.value = {};

	router.post("/pagi/settings/update", form.value, {
		onSuccess: () => {
			closeModal();
			showSuccessToast.value = true;
			setTimeout(() => {
				showSuccessToast.value = false;
			}, 3000);
		},
		onError: (err) => {
			errors.value = err;
		},
		onFinish: () => {
			isSubmitting.value = false;
		},
	});
};

const currentTab = ref("akun");

const locationOptions = [
	"Banyumas",
	"Purwokerto",
	"Cilacap",
	"Purbalingga",
	"Kebumen",
	"Banjarnegara",
	"Brebes",
	"Tegal",
	"Pekalongan",
	"Semarang",
	"Surakarta",
	"Yogyakarta",
	"Jakarta",
	"Bandung",
	"Surabaya",
	"Lainnya",
];

const handleLogout = () => {
	router.post("/logout");
};

// Keep form updated if props change dynamically
watch(
	() => props.profileUser,
	(newUser) => {
		form.value.email = newUser.email || "";
		form.value.name = newUser.name || "";
		form.value.tanggal_lahir = newUser.tanggal_lahir || "";
		form.value.billing_address = newUser.billing_address || "";
		form.value.legal_entity_name = newUser.legal_entity_name || "";
		form.value.no_telepon = newUser.no_telepon || "";
		form.value.location = newUser.location || "";
		form.value.bio = newUser.bio || "";
		form.value.website = newUser.website || "";
		form.value.twitter = newUser.twitter || "";
		form.value.linkedin = newUser.linkedin || "";
		form.value.github = newUser.github || "";
		form.value.instagram = newUser.instagram || "";
		form.value.pagi_username = newUser.pagi_username || "";
	},
	{ deep: true },
);
</script>

<template>
	<Head>
        <title>Pengaturan Akun</title>
    </Head>

	<div class="min-h-screen settings-container flex flex-col">
		<UmumNavbar :roleName="props.roleName" />

		<!-- Main Workspace Layout -->
		<main class="flex-1 w-full max-w-7xl mx-auto px-6 py-12 flex flex-col md:flex-row gap-12 text-left relative">
			
			<!-- Left Navigation Sidebar -->
			<aside class="w-full md:w-64 shrink-0 no-print space-y-2">
				<div class="space-y-1">
					<button 
						@click="currentTab = 'akun'"
						class="w-full flex items-center gap-3.5 px-4 py-3 text-xs font-semibold rounded-xl transition-all outline-none border-none text-left cursor-pointer bg-[#f5f6f9] text-[#14171f]"
					>
						<UserIcon class="w-4.5 h-4.5" />
						<span>Akun</span>
					</button>

					<!-- Log out button -->
					<button 
						@click="handleLogout"
						class="w-full flex items-center gap-3.5 px-4 py-3 text-xs font-semibold rounded-xl text-red-600 hover:bg-red-50/50 transition-all outline-none border-none text-left cursor-pointer"
					>
						<LogOut class="w-4.5 h-4.5" />
						<span>Keluar</span>
					</button>
				</div>
			</aside>

			<!-- Right Content Panel -->
			<section class="flex-1 max-w-2xl space-y-12">
				
				<!-- Akun Tab Content -->
				<div v-if="currentTab === 'akun'" class="space-y-6 animate-modal-in">
					<!-- Informasi Akun -->
					<div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xs space-y-5 transition-all">
						<button 
							@click="toggleSection('akun')"
							class="w-full flex items-center justify-between text-left cursor-pointer border-none bg-transparent outline-none focus:outline-none"
						>
							<div class="space-y-1">
								<h2 class="text-sm font-black text-[#14171f] tracking-tight">Informasi Akun</h2>
								<p class="text-[11px] text-[#677084] font-medium leading-relaxed">
									Informasi utama akun login dan username Anda.
								</p>
							</div>
							<ChevronDown 
								class="w-4 h-4 text-[#677084] transition-transform duration-200"
								:class="{ 'rotate-180': activeSections.akun }"
							/>
						</button>

						<div v-show="activeSections.akun" class="space-y-5 pt-3 border-t border-slate-50 animate-modal-in">
							<!-- E-mail item -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">E-mail</span>
									<span class="block text-xs font-semibold text-[#14171f]">{{ props.profileUser.email }}</span>
								</div>
								<div class="w-8 h-8 flex items-center justify-center text-[#94a3b8]" title="E-mail hanya dapat diubah melalui Pengaturan Portal Utama">
									<Lock class="w-4 h-4" />
								</div>
							</div>

							<!-- Username PAGI -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Username PAGI</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.pagi_username ? '@' + props.profileUser.pagi_username : 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Tanggal Lahir item -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Tanggal lahir</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.tanggal_lahir || 'MM/YYYY/DD' }}
									</span>
								</div>
								<button 
									@click="openEditModal('dob')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>
						</div>
					</div>

					<!-- Informasi Pribadi -->
					<div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xs space-y-5 transition-all">
						<button 
							@click="toggleSection('pribadi')"
							class="w-full flex items-center justify-between text-left cursor-pointer border-none bg-transparent outline-none focus:outline-none"
						>
							<div class="space-y-1">
								<h2 class="text-sm font-black text-[#14171f] tracking-tight">Informasi Pribadi</h2>
								<p class="text-[11px] text-[#677084] font-medium leading-relaxed">
									Informasi data diri lengkap Anda yang disimpan secara aman.
								</p>
							</div>
							<ChevronDown 
								class="w-4 h-4 text-[#677084] transition-transform duration-200"
								:class="{ 'rotate-180': activeSections.pribadi }"
							/>
						</button>

						<div v-show="activeSections.pribadi" class="space-y-5 pt-3 border-t border-slate-50 animate-modal-in">
							<!-- Nama Lengkap -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Nama Lengkap</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.name || 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Nomor Telepon -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Nomor Telepon / WA</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.no_telepon || 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Lokasi / Kota -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Lokasi / Kota</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.location || 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Bio -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Bio Singkat</span>
									<span class="block text-xs font-semibold text-[#14171f] line-clamp-1 max-w-sm">
										{{ props.profileUser.bio || 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Alamat item -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Alamat Lengkap</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.billing_address || 'Belum diisi' }}
									</span>
									<span class="block text-[10px] text-[#677084] font-medium">
										Untuk keperluan kepatuhan dan perpajakan
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Nama badan hukum item -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Nama badan hukum</span>
									<span class="block text-xs font-semibold text-[#14171f]">
										{{ props.profileUser.legal_entity_name || 'Belum diisi' }}
									</span>
								</div>
								<button 
									@click="openEditModal('personal')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>
						</div>
					</div>

					<!-- Media Sosial -->
					<div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xs space-y-5 transition-all">
						<button 
							@click="toggleSection('sosial')"
							class="w-full flex items-center justify-between text-left cursor-pointer border-none bg-transparent outline-none focus:outline-none"
						>
							<div class="space-y-1">
								<h2 class="text-sm font-black text-[#14171f] tracking-tight">Media Sosial & Tautan</h2>
								<p class="text-[11px] text-[#677084] font-medium leading-relaxed">
									Kaitkan akun media sosial Anda untuk dipublikasikan pada profil Anda.
								</p>
							</div>
							<ChevronDown 
								class="w-4 h-4 text-[#677084] transition-transform duration-200"
								:class="{ 'rotate-180': activeSections.sosial }"
							/>
						</button>

						<div v-show="activeSections.sosial" class="space-y-5 pt-3 border-t border-slate-50 animate-modal-in">
							<!-- Website -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1 flex-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Situs Web</span>
									<span class="block text-xs font-semibold text-[#14171f] flex items-center gap-1.5">
										<Globe class="w-3.5 h-3.5 text-[#677084]" />
										<a v-if="props.profileUser.website" :href="props.profileUser.website" target="_blank" class="hover:underline text-indigo-600 flex items-center gap-0.5">
											{{ props.profileUser.website }} <ExternalLink class="w-2.5 h-2.5" />
										</a>
										<span v-else>Belum diisi</span>
									</span>
								</div>
								<button 
									@click="openEditModal('social')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- GitHub -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1 flex-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">GitHub</span>
									<span class="block text-xs font-semibold text-[#14171f] flex items-center gap-1.5">
										<Github class="w-3.5 h-3.5 text-[#677084]" />
										<a v-if="props.profileUser.github" :href="'https://github.com/' + props.profileUser.github" target="_blank" class="hover:underline text-indigo-600 flex items-center gap-0.5">
											{{ props.profileUser.github }} <ExternalLink class="w-2.5 h-2.5" />
										</a>
										<span v-else>Belum diisi</span>
									</span>
								</div>
								<button 
									@click="openEditModal('social')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- LinkedIn -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1 flex-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">LinkedIn</span>
									<span class="block text-xs font-semibold text-[#14171f] flex items-center gap-1.5">
										<Linkedin class="w-3.5 h-3.5 text-[#677084]" />
										<a v-if="props.profileUser.linkedin" :href="'https://linkedin.com/in/' + props.profileUser.linkedin" target="_blank" class="hover:underline text-indigo-600 flex items-center gap-0.5">
											{{ props.profileUser.linkedin }} <ExternalLink class="w-2.5 h-2.5" />
										</a>
										<span v-else>Belum diisi</span>
									</span>
								</div>
								<button 
									@click="openEditModal('social')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Instagram -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1 flex-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Instagram</span>
									<span class="block text-xs font-semibold text-[#14171f] flex items-center gap-1.5">
										<Instagram class="w-3.5 h-3.5 text-[#677084]" />
										<a v-if="props.profileUser.instagram" :href="'https://instagram.com/' + props.profileUser.instagram" target="_blank" class="hover:underline text-indigo-600 flex items-center gap-0.5">
											{{ props.profileUser.instagram }} <ExternalLink class="w-2.5 h-2.5" />
										</a>
										<span v-else>Belum diisi</span>
									</span>
								</div>
								<button 
									@click="openEditModal('social')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>

							<!-- Twitter / X -->
							<div class="flex items-center justify-between py-1 border-b border-slate-50">
								<div class="space-y-1 flex-1">
									<span class="block text-[11px] font-black uppercase text-[#677084]">Twitter / X</span>
									<span class="block text-xs font-semibold text-[#14171f] flex items-center gap-1.5">
										<Twitter class="w-3.5 h-3.5 text-[#677084]" />
										<a v-if="props.profileUser.twitter" :href="'https://twitter.com/' + props.profileUser.twitter" target="_blank" class="hover:underline text-indigo-600 flex items-center gap-0.5">
											{{ props.profileUser.twitter }} <ExternalLink class="w-2.5 h-2.5" />
										</a>
										<span v-else>Belum diisi</span>
									</span>
								</div>
								<button 
									@click="openEditModal('social')"
									class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-[#4a5264] transition-all cursor-pointer border-none bg-transparent"
								>
									<Pencil class="w-4 h-4" />
								</button>
							</div>
						</div>
					</div>
				</div>
			</section>
		</main>

		<!-- Modals Overlay -->
		<div 
			v-if="activeModal !== null"
			class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 backdrop-blur-xs p-6"
		>
			<div class="bg-white border border-slate-100 rounded-3xl max-w-md w-full p-8 shadow-2xl space-y-6 text-left relative transform animate-modal-in">
				<!-- Close Button -->
				<button 
					@click="closeModal" 
					class="absolute top-6 right-6 w-8 h-8 rounded-full hover:bg-slate-50 flex items-center justify-center text-[#4a5264] cursor-pointer border-none bg-transparent"
				>
					<X class="w-4.5 h-4.5" />
				</button>

				<div class="space-y-2">
					<h3 class="text-sm font-black text-[#14171f]">
						{{ activeModal === 'email' ? 'Ubah E-mail' : '' }}
						{{ activeModal === 'dob' ? 'Ubah Tanggal Lahir' : '' }}
						{{ activeModal === 'personal' ? 'Ubah Informasi Pribadi' : '' }}
						{{ activeModal === 'social' ? 'Ubah Media Sosial & Tautan' : '' }}
					</h3>
					<p class="text-[11px] text-[#677084] font-semibold">
						Masukkan data terbaru dan simpan perubahan Anda.
					</p>
				</div>

				<!-- Form Inputs -->
				<form @submit.prevent="handleSave" class="space-y-4">
					<!-- Email Field -->
					<div v-if="activeModal === 'email'" class="space-y-1.5">
						<label class="text-[10px] font-black uppercase text-[#677084]">E-mail Baru</label>
						<input 
							type="email" 
							v-model="form.email"
							class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
						/>
						<span v-if="errors.email" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.email }}</span>
					</div>

					<!-- DOB Field -->
					<div v-if="activeModal === 'dob'" class="space-y-1.5">
						<label class="text-[10px] font-black uppercase text-[#677084]">Tanggal Lahir</label>
						<input 
							type="date" 
							v-model="form.tanggal_lahir"
							class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
						/>
						<span v-if="errors.tanggal_lahir" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.tanggal_lahir }}</span>
					</div>

					<!-- Personal Info Fields -->
					<div v-if="activeModal === 'personal'" class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
						<!-- Name -->
						<div class="space-y-1.5 opacity-70">
							<label class="text-[10px] font-black uppercase text-[#677084] flex items-center gap-1.5">
								Nama Lengkap
								<Lock class="w-3.5 h-3.5 text-[#94a3b8] shrink-0" />
							</label>
							<input 
								type="text" 
								v-model="form.name"
								disabled
								class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-500 outline-none cursor-not-allowed select-none"
								title="Nama lengkap hanya dapat diubah melalui Pengaturan Portal Utama"
							/>
							<span class="text-[9px] text-[#677084] font-medium block">
								Nama lengkap diatur dari Portal Utama.
							</span>
						</div>
						<!-- Username PAGI -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Username PAGI</label>
							<input 
								type="text" 
								v-model="form.pagi_username"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.pagi_username" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.pagi_username }}</span>
						</div>
						<!-- Phone -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Nomor Telepon / WA</label>
							<input 
								type="text" 
								v-model="form.no_telepon"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.no_telepon" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.no_telepon }}</span>
						</div>
						<!-- Location -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Lokasi / Kota</label>
							<select 
								v-model="form.location"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all cursor-pointer"
							>
								<option value="" disabled>Pilih Lokasi / Kota</option>
								<option v-if="form.location && !locationOptions.includes(form.location)" :value="form.location">{{ form.location }}</option>
								<option v-for="loc in locationOptions" :key="loc" :value="loc">{{ loc }}</option>
							</select>
							<span v-if="errors.location" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.location }}</span>
						</div>
						<!-- Bio -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Bio Singkat</label>
							<textarea 
								v-model="form.bio"
								rows="2"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all resize-none"
							></textarea>
							<span v-if="errors.bio" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.bio }}</span>
						</div>
						<!-- Address -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Alamat Lengkap</label>
							<textarea 
								v-model="form.billing_address"
								rows="2"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all resize-none"
							></textarea>
							<span v-if="errors.billing_address" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.billing_address }}</span>
						</div>
						<!-- Legal Entity Name -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Nama badan hukum</label>
							<input 
								type="text" 
								v-model="form.legal_entity_name"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.legal_entity_name" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.legal_entity_name }}</span>
						</div>
					</div>

					<!-- Social Info Fields -->
					<div v-if="activeModal === 'social'" class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
						<!-- Website -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Situs Web</label>
							<input 
								type="text" 
								v-model="form.website"
								placeholder="https://example.com"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.website" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.website }}</span>
						</div>
						<!-- GitHub -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">GitHub Username</label>
							<input 
								type="text" 
								v-model="form.github"
								placeholder="Username GitHub saja"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.github" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.github }}</span>
						</div>
						<!-- LinkedIn -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">LinkedIn Username</label>
							<input 
								type="text" 
								v-model="form.linkedin"
								placeholder="Username LinkedIn saja"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.linkedin" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.linkedin }}</span>
						</div>
						<!-- Instagram -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Instagram Username</label>
							<input 
								type="text" 
								v-model="form.instagram"
								placeholder="Username Instagram saja"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.instagram" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.instagram }}</span>
						</div>
						<!-- Twitter / X -->
						<div class="space-y-1.5">
							<label class="text-[10px] font-black uppercase text-[#677084]">Twitter / X Username</label>
							<input 
								type="text" 
								v-model="form.twitter"
								placeholder="Username Twitter saja"
								class="w-full bg-[#f5f6f9] border border-transparent focus:border-[#000000] focus:bg-white rounded-xl px-4 py-3 text-xs text-[#14171f] outline-none transition-all"
							/>
							<span v-if="errors.twitter" class="text-[10px] text-red-500 font-bold block pt-1">{{ errors.twitter }}</span>
						</div>
					</div>

					<!-- Form Actions -->
					<div class="flex gap-3 pt-4 justify-end">
						<button 
							type="button" 
							@click="closeModal"
							class="px-5 py-2.5 rounded-full border border-slate-200 text-xs font-bold text-[#4a5264] hover:bg-slate-50 active:scale-98 transition-all cursor-pointer bg-white"
						>
							Batal
						</button>
						<button 
							type="submit" 
							:disabled="isSubmitting"
							class="px-6 py-2.5 rounded-full bg-[#14171f] text-white text-xs font-bold hover:bg-[#222834] active:scale-98 transition-all cursor-pointer border-none"
						>
							{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
						</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Toast Success Notification -->
		<div 
			v-if="showSuccessToast"
			class="fixed bottom-8 left-8 z-50 bg-[#14171f] text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-toast-in text-xs font-bold select-none border border-white/5"
		>
			<Check class="w-4 h-4 text-emerald-400" />
			<span>Pengaturan akun berhasil disimpan!</span>
		</div>

	</div>
</template>

<style scoped>
/* Non-critical fonts loaded locally only when this settings page is visited */
@font-face {
	font-family: 'Plus Jakarta Sans';
	font-style: normal;
	font-weight: 400 700;
	font-display: swap;
	src: url('/fonts/plus-jakarta-sans-latin-400-normal.woff2') format('woff2');
}
@font-face {
	font-family: 'Outfit';
	font-style: normal;
	font-weight: 400 700;
	font-display: swap;
	src: url('/fonts/outfit-latin-400-normal.woff2') format('woff2');
}

.settings-container {
	font-family: 'Plus Jakarta Sans', 'Outfit', system-ui, -apple-system, sans-serif !important;
	background-color: #fbfcfd;
}

/* Modals animations */
@keyframes modalIn {
	from { opacity: 0; transform: scale(0.95); }
	to { opacity: 1; transform: scale(1); }
}

.animate-modal-in {
	animation: modalIn 170ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Toast animations */
@keyframes toastIn {
	from { opacity: 0; transform: translateY(12px); }
	to { opacity: 1; transform: translateY(0); }
}

.animate-toast-in {
	animation: toastIn 200ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

input:focus-visible, textarea:focus-visible, select:focus-visible, button:focus-visible {
	outline: 2px solid #000000 !important;
	outline-offset: 2px;
}
</style>
