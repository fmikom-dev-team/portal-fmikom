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

const props = defineProps<{
	oauthData: {
		name: string;
		email: string;
		provider: string;
	};
}>();

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
	role: "mahasiswa", // default
	nomor_induk: "",
	program_studi_id: "", // untuk mahasiswa & alumni
	tahun_lulus: "", // khusus alumni
	no_telepon: "", // khusus mitra
	nama_perusahaan: "", // khusus mitra
});

const step = ref(1);
const totalSteps = 2;

// Reset role-specific fields saat role berubah
watch(
	() => form.role,
	() => {
		form.program_studi_id = "";
		form.tahun_lulus = "";
		form.no_telepon = "";
		form.nomor_induk = "";
		form.nama_perusahaan = "";
		step.value = 1;
		realtimeErrors.value = { nomor_induk: "" };
	},
);

// Real-time validation state
const realtimeErrors = ref({ nomor_induk: "" });
const isChecking = ref(false);

const checkUnique = async () => {
	if (!form.nomor_induk) {
		return;
	}

	isChecking.value = true;

	try {
		const response = await axios.post("/api/check-user-exists", {
			email: props.oauthData.email,
			nomor_induk: form.nomor_induk,
		});

		realtimeErrors.value.nomor_induk = response.data.nomor_induk_exists
			? "Nomor identitas ini sudah terdaftar."
			: "";
	} catch (error) {
		console.error("Gagal memvalidasi API", error);
	} finally {
		isChecking.value = false;
	}
};

watch(
	() => form.nomor_induk,
	() => {
		realtimeErrors.value.nomor_induk = "";
	},
);

// Validasi step 1 (Role selection) selalu valid karena ada default 'mahasiswa'
const isStep1Valid = computed(() => {
	return !!form.role;
});

// Validasi step 2 (Identitas)
const isStep2Valid = computed(() => {
	if (form.role === "mahasiswa") {
		if (!form.nomor_induk || !form.program_studi_id) return false;
	} else if (form.role === "alumni") {
		if (!form.nomor_induk || !form.program_studi_id || !form.tahun_lulus)
			return false;
	} else if (form.role === "mitra") {
		if (!form.nama_perusahaan || !form.nomor_induk || !form.no_telepon)
			return false;
	}
	if (realtimeErrors.value.nomor_induk) return false;
	return true;
});

const nextStep = () => {
	if (step.value === 1) {
		step.value = 2;
	}
};

const backStep = () => {
	if (step.value > 1) {
		step.value--;
	}
};

const submit = async () => {
	await checkUnique();
	if (isStep2Valid.value) {
		form.post("/auth/oauth/register");
	}
};

// Password validation removed as OAuth registration no longer requires password setup

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
        <title>Pendaftaran Google</title>
    </Head>

        <!-- Stepper Indicator Responsive -->
        <div class="flex flex-col mb-8 px-1">
            <div class="flex items-center gap-1 sm:gap-2">
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 1 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">1</div>
                <div :class="['flex-1 h-1 rounded-full transition-colors', step >= 2 ? 'bg-[#2563eb]' : 'bg-slate-100']"></div>
                
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 2 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">2</div>
            </div>
            <div class="text-xs sm:text-sm font-medium text-slate-500 mt-2 text-right">Langkah {{ step }} dari {{ totalSteps }}</div>
        </div>

        <!-- Google Status Banner -->
        <div class="mb-6 p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center border border-slate-100 shadow-sm shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-[#2563eb] uppercase tracking-wider">Menghubungkan Akun Google</p>
                <h3 class="text-sm font-bold text-slate-800 truncate">{{ props.oauthData.name }}</h3>
                <p class="text-xs text-slate-500 truncate">{{ props.oauthData.email }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-5">

            <!-- STEP 1: Role -->
            <div v-show="step === 1" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800">Mendaftar Sebagai</h2>
                    <p class="text-sm text-slate-500">Pilih jenis keanggotaan Anda di portal ini.</p>
                </div>

                <div class="grid gap-2">
                    <div class="grid grid-cols-3 gap-2.5">
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'mahasiswa' ? 'border-[#2563eb] bg-indigo-50/50 text-[#2563eb] ring-1 ring-[#2563eb]' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            <input type="radio" v-model="form.role" value="mahasiswa" class="sr-only" />
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Mahasiswa</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'alumni' ? 'border-[#2563eb] bg-indigo-50/50 text-[#2563eb] ring-1 ring-[#2563eb]' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            <input type="radio" v-model="form.role" value="alumni" class="sr-only" />
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Alumni</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.role === 'mitra' ? 'border-[#2563eb] bg-indigo-50/50 text-[#2563eb] ring-1 ring-[#2563eb]' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            <input type="radio" v-model="form.role" value="mitra" class="sr-only" />
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="font-medium text-[11px] sm:text-xs text-center">Mitra</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <Button type="button" @click="nextStep" :disabled="!isStep1Valid" class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white rounded-xl h-11 text-md font-medium shadow-md">
                        Lanjut ke Identitas
                    </Button>
                </div>
            </div>

            <!-- STEP 2: Identitas -->
            <div v-show="step === 2" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 font-sans">
                        Lengkapi Data 
                        <span v-if="form.role === 'mahasiswa'">Mahasiswa</span>
                        <span v-else-if="form.role === 'alumni'">Alumni</span>
                        <span v-else>Mitra</span>
                    </h2>
                    <p class="text-sm text-slate-500">Lengkapi data identitas resmi Anda.</p>
                </div>

                <!-- Perusahaan (Mitra Only) -->
                <div v-if="form.role === 'mitra'" class="grid gap-2">
                    <Label for="nama_perusahaan" class="font-semibold text-slate-800">Nama Perusahaan</Label>
                    <Input id="nama_perusahaan" type="text" v-model="form.nama_perusahaan" required placeholder="Contoh: PT. Teknologi Bangsa" class="rounded-xl h-11 border-slate-200 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors" />
                    <InputError :message="form.errors.nama_perusahaan" />
                </div>

                <!-- NIM / NIB -->
                <div class="grid gap-2">
                    <Label for="nomor_induk" class="font-semibold text-slate-800">{{ nomorIndukLabel }}</Label>
                    <Input id="nomor_induk" type="text" v-model="form.nomor_induk" required :placeholder="nomorIndukPlaceholder" class="rounded-xl h-11 border-slate-200 focus-visible:ring-0 transition-colors" :class="realtimeErrors.nomor_induk ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                    <div v-if="realtimeErrors.nomor_induk" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.nomor_induk }}</span></div>
                    <InputError v-else :message="form.errors.nomor_induk" />
                </div>

                <!-- Program Studi (Mahasiswa & Alumni) -->
                <div v-if="form.role === 'mahasiswa' || form.role === 'alumni'" class="grid gap-2">
                    <Label for="program_studi_id" class="font-semibold text-slate-800">Program Studi</Label>
                    <div class="relative">
                        <select id="program_studi_id" v-model="form.program_studi_id" required class="w-full h-11 rounded-xl border border-slate-200 px-3 pr-10 text-sm bg-white text-slate-800 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer" :class="form.errors.program_studi_id ? 'border-red-500' : ''">
                            <option value="" disabled>Pilih program studi...</option>
                            <option v-for="prodi in programStudiOptions" :key="prodi.value" :value="prodi.value">{{ prodi.label }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                    </div>
                    <InputError :message="form.errors.program_studi_id" />
                </div>

                <!-- Tahun Lulus (Alumni Only) -->
                <div v-if="form.role === 'alumni'" class="grid gap-2">
                    <Label for="tahun_lulus" class="font-semibold text-slate-800">Tahun Lulus</Label>
                    <div class="relative">
                        <select id="tahun_lulus" v-model="form.tahun_lulus" required class="w-full h-11 rounded-xl border border-slate-200 px-3 pr-10 text-sm bg-white text-slate-800 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer">
                            <option value="" disabled>Pilih tahun lulus...</option>
                            <option v-for="year in tahunLulusOptions" :key="year" :value="year">{{ year }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                    </div>
                    <InputError :message="form.errors.tahun_lulus" />
                </div>

                <!-- Nomor Telepon (Mitra Only) -->
                <div v-if="form.role === 'mitra'" class="grid gap-2">
                    <Label for="no_telepon" class="font-semibold text-slate-800">Nomor Telepon</Label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                        <Input id="no_telepon" type="tel" v-model="form.no_telepon" required placeholder="Contoh: 08123456789" class="rounded-xl h-11 border-slate-200 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors pl-9" :class="form.errors.no_telepon ? 'border-red-500' : ''" />
                    </div>
                    <InputError :message="form.errors.no_telepon" />
                </div>

                <div class="mt-4 flex gap-3">
                    <Button type="button" variant="outline" @click="backStep" class="w-1/3 rounded-xl h-11 border-slate-200 hover:bg-slate-50 text-slate-600">Kembali</Button>
                    <Button type="submit" :disabled="!isStep2Valid || isChecking || form.processing" class="w-2/3 bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium">
                        <Spinner v-if="isChecking || form.processing" class="mr-2 h-4 w-4" /> Selesaikan Pendaftaran
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-muted-foreground mt-4">
                Bukan akun Anda?
                <TextLink href="/login" class="underline underline-offset-4 text-[#2563eb] font-medium">Ganti Akun</TextLink>
            </div>
        </form>
    </div>
</template>
