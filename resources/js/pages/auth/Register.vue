<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { computed, ref, watch } from "vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthBase from "@/layouts/AuthLayout.vue";

defineOptions({ layout: AuthBase });

// Pilihan program studi (hardcoded sesuai permintaan)
const programStudiOptions = [
	{ value: 1, label: "Informatika" },
	{ value: 2, label: "Sistem Informasi" },
	{ value: 3, label: "Matematika" },
];

// Tahun lulus options untuk alumni (10 tahun kebelakang + tahun ini)
const currentYear = new Date().getFullYear();
const tahunLulusOptions = Array.from(
	{ length: currentYear - 1989 },
	(_, i) => currentYear - i,
);

const form = useForm({
	name: "",
	role: "alumni", // default
	nomor_induk: "",
	email: "",
	program_studi_id: "", // untuk mahasiswa & alumni
	tahun_lulus: "", // khusus alumni
	no_telepon: "", // khusus mitra
	nama_perusahaan: "", // khusus mitra
});

const step = ref(1);

const totalSteps = computed(() => {
	return 3;
});

// Reset role-specific fields saat role berubah
watch(
	() => form.role,
	() => {
		form.program_studi_id = "";
		form.tahun_lulus = "";
		form.no_telepon = "";
		form.nomor_induk = "";
		form.email = "";
		form.nama_perusahaan = "";
		step.value = 1;
		realtimeErrors.value = { email: "", nomor_induk: "", local_email: "" };
	},
);

// Real-time validation state
const realtimeErrors = ref({ email: "", nomor_induk: "", local_email: "" });
const isChecking = ref(false);

const isValidEmailFormat = (email: string) => {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const checkUnique = async () => {
	if (!form.email && !form.nomor_induk) {
		return;
	}
	if (form.email && !isValidEmailFormat(form.email)) {
		return;
	}

	isChecking.value = true;

	try {
		const response = await axios.post("/api/check-user-exists", {
			email: form.email,
			nomor_induk: form.nomor_induk,
		});

		realtimeErrors.value.email = response.data.email_exists
			? "Email ini sudah terdaftar di sistem."
			: "";

		realtimeErrors.value.nomor_induk = response.data.nomor_induk_exists
			? "Nomor identitas ini sudah terdaftar."
			: "";
	} catch (error) {
		console.error("Gagal memvalidasi API", error);
	} finally {
		isChecking.value = false;
	}
};

// Check local format and clear API errors on change
watch(
	() => form.email,
	(newVal) => {
		realtimeErrors.value.email = "";
		if (newVal && !isValidEmailFormat(newVal)) {
			realtimeErrors.value.local_email =
				"Format email tidak valid (harus mengandung @ dan domain).";
		} else {
			realtimeErrors.value.local_email = "";
		}
	},
);

watch(
	() => form.nomor_induk,
	() => {
		realtimeErrors.value.nomor_induk = "";
	},
);

// Validasi step 2
const isStep2Valid = computed(() => {
	if (form.role === "mahasiswa") {
		if (!form.nomor_induk || !form.email || !form.program_studi_id)
			return false;
	} else if (form.role === "alumni") {
		if (!form.nomor_induk || !form.email) return false;
	} else if (form.role === "mitra") {
		if (!form.nama_perusahaan || !form.nomor_induk) return false;
	}
	if (
		realtimeErrors.value.nomor_induk ||
		realtimeErrors.value.local_email ||
		(form.email && realtimeErrors.value.email)
	)
		return false;
	return true;
});

// Validasi step 3 (Hanya untuk Alumni dan Mitra)
const isStep3Valid = computed(() => {
	if (form.role === "alumni") {
		if (!form.program_studi_id || !form.tahun_lulus) return false;
	} else if (form.role === "mitra") {
		if (!form.email || !form.no_telepon) return false;
		if (realtimeErrors.value.local_email || realtimeErrors.value.email)
			return false;
	}
	return true;
});

const nextStep = async () => {
	if (step.value === 1 && form.name !== "" && form.role !== "") {
		step.value = 2;
	} else if (step.value === 2) {
		await checkUnique();
		if (isStep2Valid.value) {
			step.value = 3;
		}
	}
};

const backStep = () => {
	if (step.value > 1) {
		step.value--;
	}
};

const submit = () => {
	form.post("/register");
};

// Label nomor induk berdasarkan role
const nomorIndukLabel = computed(() => {
	if (form.role === "mahasiswa") return "NIM";
	if (form.role === "alumni") return "NIM Alumni";
	return "NIB / No. Perusahaan";
});

const nomorIndukPlaceholder = computed(() => {
	if (form.role === "mahasiswa") return "Masukkan Nomor Induk Mahasiswa";
	if (form.role === "alumni") return "Masukkan NIM Alumni Anda";
	return "Misal: 1982823000...";
});
</script>

<template>
    <div class="w-full">
        <Head>
        <title>Register</title>
    </Head>

        <!-- Stepper Indicator Responsive -->
        <div class="flex flex-col mb-8 px-1">
            <div class="flex items-center gap-1 sm:gap-2">
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 1 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">1</div>
                <div :class="['flex-1 h-1 rounded-full transition-colors', step >= 2 ? 'bg-[#2563eb]' : 'bg-slate-100']"></div>
                
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 2 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">2</div>
                <div :class="['flex-1 h-1 rounded-full transition-colors', step >= 3 ? 'bg-[#2563eb]' : 'bg-slate-100']"></div>
                
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 3 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">3</div>
                
                <template v-if="totalSteps === 4">
                    <div :class="['flex-1 h-1 rounded-full transition-colors', step >= 4 ? 'bg-[#2563eb]' : 'bg-slate-100']"></div>
                    <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 4 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">4</div>
                </template>
            </div>
            <div class="text-xs sm:text-sm font-medium text-slate-500 mt-2 text-right">Langkah {{ step }} dari {{ totalSteps }}</div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-5">

            <!-- STEP 1: Profil & Role -->
            <div v-show="step === 1" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Informasi Dasar</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pilih peran dan isi nama lengkap Anda.</p>
                </div>

                <div class="grid gap-2">
                    <Label for="name" class="font-semibold text-slate-800 dark:text-slate-200">Nama Lengkap</Label>
                    <Input id="name" type="text" v-model="form.name" required autofocus autocomplete="name" placeholder="Contoh: John Doe" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label class="font-semibold text-slate-800 dark:text-slate-200">Mendaftar Sebagai</Label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'mahasiswa' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.role" value="mahasiswa" class="sr-only" />
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Mahasiswa</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'alumni' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.role" value="alumni" class="sr-only" />
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Alumni</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'mitra' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.role" value="mitra" class="sr-only" />
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Mitra</span>
                        </label>
                    </div>
                </div>

                <div v-if="form.role === 'mahasiswa'" class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-300 text-sm mt-1 animate-in fade-in duration-300">
                    <strong>Pemberitahuan:</strong> Mahasiswa, Dosen, dan Staff tidak perlu mendaftar akun baru. Akun Anda sudah terdaftar di sistem. Silakan lakukan aktivasi akun Anda di halaman <a href="/activate" class="underline font-semibold text-blue-600 dark:text-blue-400">Aktivasi Akun</a>.
                </div>

                <div class="mt-4">
                    <Button type="button" @click="nextStep" :disabled="!form.name || !form.role || form.role === 'mahasiswa'" class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white rounded-xl h-11 text-md font-medium shadow-md">
                        Lanjut ke Tahap 2
                    </Button>
                </div>
            </div>

            <!-- STEP 2: Identitas / Kontak Utama -->
            <div v-show="step === 2" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">
                        Identitas
                        <span v-if="form.role === 'mahasiswa'">Kampus</span>
                        <span v-else-if="form.role === 'alumni'">Alumni</span>
                        <span v-else>Perusahaan</span>
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Lengkapi data identitas Anda.</p>
                </div>

                <div v-if="form.role === 'mitra'" class="grid gap-2">
                    <Label for="nama_perusahaan" class="font-semibold text-slate-800 dark:text-slate-200">Nama Perusahaan</Label>
                    <Input id="nama_perusahaan" type="text" v-model="form.nama_perusahaan" required placeholder="Contoh: PT. Teknologi Bangsa" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors" />
                    <InputError :message="form.errors.nama_perusahaan" />
                </div>

                <!-- NIM / NIB -->
                <div class="grid gap-2">
                    <Label for="nomor_induk" class="font-semibold text-slate-800 dark:text-slate-200">{{ nomorIndukLabel }}</Label>
                    <Input id="nomor_induk" type="text" v-model="form.nomor_induk" required :placeholder="nomorIndukPlaceholder" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 transition-colors" :class="realtimeErrors.nomor_induk ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                    <div v-if="realtimeErrors.nomor_induk" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.nomor_induk }}</span></div>
                    <InputError v-else :message="form.errors.nomor_induk" />
                </div>

                <!-- Email (Hanya Mahasiswa & Alumni di Step 2) -->
                <div v-if="form.role === 'mahasiswa' || form.role === 'alumni'" class="grid gap-2">
                    <Label for="email" class="font-semibold text-slate-800 dark:text-slate-200">Email Utama</Label>
                    <Input id="email" type="email" v-model="form.email" required autocomplete="email" placeholder="email@domain.com" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 transition-colors" :class="(realtimeErrors.local_email || realtimeErrors.email) ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                    <div v-if="realtimeErrors.local_email || realtimeErrors.email" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.local_email || realtimeErrors.email }}</span></div>
                    <InputError v-else :message="form.errors.email" />
                </div>

                <!-- Program Studi (Hanya Mahasiswa di Step 2) -->
                <div v-if="form.role === 'mahasiswa'" class="grid gap-2">
                    <Label for="program_studi_id" class="font-semibold text-slate-800 dark:text-slate-200">Program Studi</Label>
                    <div class="relative">
                        <select id="program_studi_id" v-model="form.program_studi_id" required class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 px-3 pr-10 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer" :class="form.errors.program_studi_id ? 'border-red-500' : ''">
                            <option value="" disabled>Pilih program studi...</option>
                            <option v-for="prodi in programStudiOptions" :key="prodi.value" :value="prodi.value">{{ prodi.label }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                    </div>
                    <InputError :message="form.errors.program_studi_id" />
                </div>

                <div class="mt-4 flex gap-3">
                    <Button type="button" variant="outline" @click="backStep" class="w-1/3 rounded-xl h-11 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">Kembali</Button>
                    <Button type="button" @click="nextStep" :disabled="!isStep2Valid || isChecking" class="w-2/3 bg-[#2563eb] hover:bg-[#3B2DCB] text-white rounded-xl h-11 text-md font-medium shadow-md">
                        <Spinner v-if="isChecking" class="mr-2 h-4 w-4" /> Lanjut Tahap 3
                    </Button>
                </div>
            </div>

            <!-- STEP 3: Informasi Ekstra (Alumni & Mitra) -->
            <div v-show="step === 3" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">
                        <span v-if="form.role === 'alumni'">Informasi Akademik</span>
                        <span v-else-if="form.role === 'mitra'">Kontak Lanjutan</span>
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <span v-if="form.role === 'alumni'">Lengkapi data kelulusan Anda.</span>
                        <span v-else-if="form.role === 'mitra'">Gunakan email aktif agar dapat diverifikasi.</span>
                    </p>
                </div>

                <!-- ALUMNI FIELDS -->
                <template v-if="form.role === 'alumni'">
                    <div class="grid gap-2">
                        <Label for="alumni_program_studi_id" class="font-semibold text-slate-800 dark:text-slate-200">Program Studi</Label>
                        <div class="relative">
                            <select id="alumni_program_studi_id" v-model="form.program_studi_id" required class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 px-3 pr-10 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer" :class="form.errors.program_studi_id ? 'border-red-500' : ''">
                                <option value="" disabled>Pilih program studi...</option>
                                <option v-for="prodi in programStudiOptions" :key="prodi.value" :value="prodi.value">{{ prodi.label }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                        </div>
                        <InputError :message="form.errors.program_studi_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="tahun_lulus" class="font-semibold text-slate-800 dark:text-slate-200">Tahun Lulus</Label>
                        <div class="relative">
                            <select id="tahun_lulus" v-model="form.tahun_lulus" required class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 px-3 pr-10 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer">
                                <option value="" disabled>Pilih tahun lulus...</option>
                                <option v-for="year in tahunLulusOptions" :key="year" :value="year">{{ year }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                        </div>
                        <InputError :message="form.errors.tahun_lulus" />
                    </div>
                </template>

                <!-- MITRA FIELDS -->
                <template v-if="form.role === 'mitra'">
                    <div class="grid gap-2">
                        <Label for="mitra_email" class="font-semibold text-slate-800 dark:text-slate-200">Email Perusahaan</Label>
                        <Input id="mitra_email" type="email" v-model="form.email" required autocomplete="email" placeholder="email@domain.com" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 transition-colors" :class="(realtimeErrors.local_email || realtimeErrors.email) ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                        <div v-if="realtimeErrors.local_email || realtimeErrors.email" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.local_email || realtimeErrors.email }}</span></div>
                        <InputError v-else :message="form.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="no_telepon" class="font-semibold text-slate-800 dark:text-slate-200">Nomor Telepon</Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                            <Input id="no_telepon" type="tel" v-model="form.no_telepon" required placeholder="Contoh: 08123456789" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors pl-9" :class="form.errors.no_telepon ? 'border-red-500' : ''" />
                        </div>
                        <InputError :message="form.errors.no_telepon" />
                    </div>
                </template>

                <div class="mt-4 flex gap-3">
                    <Button type="button" variant="outline" @click="backStep" class="w-1/3 rounded-xl h-11 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">Kembali</Button>
                    <Button type="submit" :disabled="!isStep3Valid || isChecking || form.processing" class="w-2/3 bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium">
                        <Spinner v-if="form.processing || isChecking" class="mr-2 h-4 w-4" /> Selesaikan Pendaftaran
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-muted-foreground dark:text-slate-400 mt-4">
                Sudah punya akun?
                <TextLink href="/login" class="underline underline-offset-4 text-[#2563eb] dark:text-blue-400 font-medium">Masuk</TextLink>
            </div>
        </form>
    </div>
</template>
