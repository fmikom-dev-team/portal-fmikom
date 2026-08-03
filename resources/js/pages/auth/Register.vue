<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { computed, ref, watch } from "vue";
import InputError from "@/components/InputError.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthBase from "@/layouts/AuthLayout.vue";

defineOptions({ layout: AuthBase });

// Pilihan program studi
const programStudiOptions = [
	{ value: 1, label: "Informatika" },
	{ value: 2, label: "Sistem Informasi" },
	{ value: 3, label: "Matematika" },
];

// Tahun lulus options untuk alumni
const currentYear = new Date().getFullYear();
const tahunLulusOptions = Array.from(
	{ length: currentYear - 1989 },
	(_, i) => currentYear - i,
);

const form = useForm({
	name: "",
	role: "alumni", // default (alumni / mitra)
	nomor_induk: "",
	email: "",
	program_studi_id: "",
	tahun_lulus: "",
	no_telepon: "",
	nama_perusahaan: "",
});

const step = ref(1);
const totalSteps = computed(() => 2);

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
		realtimeErrors.value = { email: "", nomor_induk: "", local_email: "", no_telepon: "" };
	},
);

// Real-time validation state
const realtimeErrors = ref({ email: "", nomor_induk: "", local_email: "", no_telepon: "" });
const isChecking = ref(false);

const isValidEmailFormat = (email: string) => {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const isValidPhoneFormat = (phone: string) => {
	return /^(\+?62|0)8[1-9][0-9]{7,11}$/.test(phone.trim());
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
	() => form.no_telepon,
	(newVal) => {
		if (newVal && !isValidPhoneFormat(newVal)) {
			realtimeErrors.value.no_telepon =
				"Format nomor HP/WhatsApp tidak valid (contoh: 08123456789).";
		} else {
			realtimeErrors.value.no_telepon = "";
		}
	},
);

watch(
	() => form.nomor_induk,
	() => {
		realtimeErrors.value.nomor_induk = "";
	},
);

const isStep2Valid = computed(() => {
	if (form.role === "alumni") {
		if (!form.nomor_induk || !form.email || !form.program_studi_id || !form.tahun_lulus)
			return false;
	} else if (form.role === "mitra") {
		if (!form.nama_perusahaan || !form.email || !form.no_telepon || !isValidPhoneFormat(form.no_telepon))
			return false;
	}
	if (
		realtimeErrors.value.nomor_induk ||
		realtimeErrors.value.local_email ||
		realtimeErrors.value.no_telepon ||
		(form.email && realtimeErrors.value.email)
	)
		return false;
	return true;
});

const nextStep = () => {
	if (step.value === 1 && form.name !== "" && form.role !== "") {
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
		form.post("/register");
	}
};

const nomorIndukLabel = computed(() => {
	if (form.role === "alumni") return "NIM Alumni";
	return "NIB / No. Perusahaan (Opsional)";
});

const nomorIndukPlaceholder = computed(() => {
	if (form.role === "alumni") return "Masukkan NIM Alumni Anda";
	return "Misal: 1982823000...";
});
</script>

<template>
    <div class="w-full">
        <Head>
            <title>Pendaftaran Akun Baru</title>
        </Head>

        <!-- Stepper Indicator Responsive -->
        <div class="flex flex-col mb-6 px-1">
            <div class="flex items-center gap-1 sm:gap-2">
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 1 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">1</div>
                <div :class="['flex-1 h-1 rounded-full transition-colors', step >= 2 ? 'bg-[#2563eb]' : 'bg-slate-100']"></div>
                <div :class="['w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-colors', step >= 2 ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400']">2</div>
            </div>
            <div class="text-xs sm:text-sm font-medium text-slate-500 mt-2 text-right">Langkah {{ step }} dari {{ totalSteps }}</div>
        </div>

        <!-- Banner Informasi Mahasiswa Aktif -->
        <div class="mb-6 p-3.5 bg-blue-50/70 dark:bg-slate-900 border border-blue-100 dark:border-slate-800 rounded-xl text-xs text-slate-600 dark:text-slate-400 space-y-1">
            <p class="font-semibold text-blue-900 dark:text-blue-300">ℹ️ Khusus Mahasiswa Aktif / Baru:</p>
            <p class="leading-relaxed">
                Akun Anda telah terdaftar otomatis dari SIAKAD. Tidak perlu daftar baru. Silakan <a href="/activate" class="underline font-semibold text-blue-600 dark:text-blue-400">Lakukan Aktivasi Akun Mahasiswa di sini →</a>
            </p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-5">

            <!-- STEP 1: Informasi Dasar -->
            <div v-show="step === 1" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Informasi Pendaftar</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pilih kategori pendaftaran dan isi nama Anda.</p>
                </div>

                <div class="grid gap-2">
                    <Label for="name" class="font-semibold text-slate-800 dark:text-slate-200">Nama Lengkap</Label>
                    <Input id="name" type="text" v-model="form.name" required autofocus autocomplete="name" placeholder="Contoh: Budi Santoso" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label class="font-semibold text-slate-800 dark:text-slate-200">Mendaftar Sebagai</Label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="['flex flex-col items-center justify-center gap-2 p-4 border rounded-xl cursor-pointer transition-all', form.role === 'alumni' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.role" value="alumni" class="sr-only" />
                            <!-- Custom Icon Topi Alumni -->
                            <svg class="w-6 h-6 shrink-0" viewBox="0 0 1024 768" fill="currentColor">
                                <path d="M1024 736q0 13-9.5 22.5T992 768t-22.5-9.5T960 736V315L607 492q-40 20-95 20t-95-20L39 303Q0 283 0 255.5T39 209L417 20q40-20 95-20t95 20l378 189q34 17 38 42q1 1 1 4v481zM639 556l193-97v141q0 43-93.5 73.5T512 704t-226.5-30.5T192 600V459l193 97q40 20 127 20t127-20z"/>
                            </svg>
                            <span class="font-semibold text-xs text-center">Alumni FMIKOM</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-2 p-4 border rounded-xl cursor-pointer transition-all', form.role === 'mitra' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.role" value="mitra" class="sr-only" />
                            <!-- Custom Icon Gedung Perusahaan Mitra -->
                            <svg class="w-6 h-6 shrink-0" viewBox="0 0 512 512" fill="currentColor">
                                <rect x="45" y="165" width="80" height="260" rx="6"/>
                                <rect x="145" y="40" width="220" height="385" rx="10"/>
                                <rect x="385" y="165" width="80" height="260" rx="6"/>
                                <rect x="30" y="440" width="452" height="35"/>
                                <g class="fill-white dark:fill-slate-900">
                                    <rect x="190" y="85" width="32" height="32"/>
                                    <rect x="255" y="85" width="32" height="32"/>
                                    <rect x="190" y="145" width="32" height="32"/>
                                    <rect x="255" y="145" width="32" height="32"/>
                                    <rect x="190" y="205" width="32" height="32"/>
                                    <rect x="255" y="205" width="32" height="32"/>
                                    <rect x="190" y="265" width="32" height="32"/>
                                    <rect x="255" y="265" width="32" height="32"/>
                                    <rect x="190" y="325" width="32" height="32"/>
                                    <rect x="255" y="325" width="32" height="32"/>
                                </g>
                                <g class="fill-white dark:fill-slate-900">
                                    <rect x="75" y="225" width="30" height="30"/>
                                    <rect x="75" y="285" width="30" height="30"/>
                                    <rect x="75" y="345" width="30" height="30"/>
                                </g>
                                <g class="fill-white dark:fill-slate-900">
                                    <rect x="405" y="225" width="30" height="30"/>
                                    <rect x="405" y="285" width="30" height="30"/>
                                    <rect x="405" y="345" width="30" height="30"/>
                                </g>
                            </svg>
                            <span class="font-semibold text-xs text-center">Mitra / Perusahaan</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <Button type="button" @click="nextStep" :disabled="!form.name || !form.role" class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white rounded-xl h-11 text-md font-medium shadow-md">
                        Lanjut ke Langkah 2
                    </Button>
                </div>
            </div>

            <!-- STEP 2: Detail Identitas & Kontak -->
            <div v-show="step === 2" class="grid gap-4 animate-in slide-in-from-right-4 fade-in duration-300">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">
                        Detail
                        <span v-if="form.role === 'alumni'">Alumni</span>
                        <span v-else>Mitra Perusahaan</span>
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Isi data lengkap untuk peninjauan akun oleh Admin.</p>
                </div>

                <!-- MITRA SPECIFIC: Nama Perusahaan -->
                <div v-if="form.role === 'mitra'" class="grid gap-2">
                    <Label for="nama_perusahaan" class="font-semibold text-slate-800 dark:text-slate-200">Nama Perusahaan / Instansi</Label>
                    <Input id="nama_perusahaan" type="text" v-model="form.nama_perusahaan" required placeholder="Contoh: PT. Teknologi Bangsa" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors" />
                    <InputError :message="form.errors.nama_perusahaan" />
                </div>

                <!-- NIM / NIB -->
                <div class="grid gap-2">
                    <Label for="nomor_induk" class="font-semibold text-slate-800 dark:text-slate-200">{{ nomorIndukLabel }}</Label>
                    <Input id="nomor_induk" type="text" v-model="form.nomor_induk" @input="form.nomor_induk = form.nomor_induk.replace(/[^a-zA-Z0-9.\-\/]/g, '')" :required="form.role === 'alumni'" :placeholder="nomorIndukPlaceholder" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 transition-colors" :class="realtimeErrors.nomor_induk ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                    <div v-if="realtimeErrors.nomor_induk" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.nomor_induk }}</span></div>
                    <InputError v-else :message="form.errors.nomor_induk" />
                </div>

                <!-- Email Utama -->
                <div class="grid gap-2">
                    <Label for="email" class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ form.role === 'alumni' ? 'Email Utama (Aktif)' : 'Email Resmi Perusahaan' }}
                    </Label>
                    <Input id="email" type="email" v-model="form.email" required autocomplete="email" placeholder="email@domain.com" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 transition-colors" :class="(realtimeErrors.local_email || realtimeErrors.email) ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : 'focus-visible:border-[#2563eb]'" />
                    <div v-if="realtimeErrors.local_email || realtimeErrors.email" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.local_email || realtimeErrors.email }}</span></div>
                    <InputError v-else :message="form.errors.email" />
                </div>

                <!-- ALUMNI SPECIFIC: Program Studi & Tahun Lulus -->
                <template v-if="form.role === 'alumni'">
                    <div class="grid gap-2">
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

                    <div class="grid gap-2">
                        <Label for="tahun_lulus" class="font-semibold text-slate-800 dark:text-slate-200">Tahun Lulus</Label>
                        <div class="relative">
                            <select id="tahun_lulus" v-model="form.tahun_lulus" required class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 px-3 pr-10 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors appearance-none cursor-pointer" :class="form.errors.tahun_lulus ? 'border-red-500' : ''">
                                <option value="" disabled>Pilih tahun lulus...</option>
                                <option v-for="year in tahunLulusOptions" :key="year" :value="year">{{ year }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></div>
                        </div>
                        <InputError :message="form.errors.tahun_lulus" />
                    </div>
                </template>

                <!-- MITRA SPECIFIC: Nomor Telepon -->
                <template v-if="form.role === 'mitra'">
                    <div class="grid gap-2">
                        <Label for="no_telepon" class="font-semibold text-slate-800 dark:text-slate-200">Nomor Telepon / WhatsApp Perusahaan</Label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                            <Input id="no_telepon" type="tel" v-model="form.no_telepon" @input="form.no_telepon = form.no_telepon.replace(/[^\d+]/g, '')" required placeholder="Contoh: 08123456789" class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors pl-9" :class="(realtimeErrors.no_telepon || form.errors.no_telepon) ? 'border-red-500 focus-visible:border-red-500 ring-1 ring-red-500' : ''" />
                        </div>
                        <div v-if="realtimeErrors.no_telepon" class="flex items-center gap-1 text-red-500 text-sm mt-1 animate-in fade-in slide-in-from-top-1"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span class="font-medium">{{ realtimeErrors.no_telepon }}</span></div>
                        <InputError v-else :message="form.errors.no_telepon" />
                    </div>
                </template>

                <div class="mt-4 flex gap-3">
                    <Button type="button" variant="outline" @click="backStep" class="w-1/3 rounded-xl h-11 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">Kembali</Button>
                    <Button type="submit" :disabled="!isStep2Valid || isChecking || form.processing" class="w-2/3 bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 text-md font-medium">
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
