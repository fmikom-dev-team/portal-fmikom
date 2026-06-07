<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const form = useForm({
	password: "",
	password_confirmation: "",
});

const submit = () => {
	form.post("/force-change-password", {
		onFinish: () => form.reset("password", "password_confirmation"),
	});
};

// Validasi Realtime
const passwordCriteria = computed(() => {
	const p = form.password;

	if (!p) {
		return {
			length: false,
			lowercase: false,
			uppercase: false,
			number: false,
			symbol: false,
		};
	}

	return {
		length: p.length >= 8,
		lowercase: /[a-z]/.test(p),
		uppercase: /[A-Z]/.test(p),
		number: /[0-9]/.test(p),
		symbol: /[:!@#$%^&*]/.test(p),
	};
});

// Validasi Cocoknya Konfirmasi
const passwordMismatch = computed(() => {
	return (
		form.password_confirmation !== "" &&
		form.password !== form.password_confirmation
	);
});
</script>

<template>
    <AuthLayout
        title="Buat Password Baru"
        description="Amankan akun Anda dengan password baru"
    >
        <Head title="Ganti Password" />

        <div class="mb-4 text-sm text-slate-600">
            Ini adalah pertama kalinya Anda masuk. Harap mengubah password default Anda dengan password yang baru dan kuat untuk melanjutkan ke dalam sistem.
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="password" class="font-semibold text-slate-800">Password Baru</Label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        autocomplete="new-password"
                        class="rounded-xl h-11 border-slate-200 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                        autofocus
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="font-semibold text-slate-800">
                        Konfirmasi Password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        :class="[
                            'rounded-xl h-11 border-slate-200 focus-visible:ring-0 transition-colors',
                            passwordMismatch ? 'border-red-500 focus-visible:border-red-500 ring-2 ring-red-100' : 'focus-visible:border-[#2563eb]'
                        ]"
                        placeholder="••••••••"
                    />

                    <!-- Alert Mismatch Real-time -->
                    <div v-if="passwordMismatch" class="flex items-center gap-1.5 text-red-500 text-sm mt-1 font-medium animate-in fade-in slide-in-from-top-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Konfirmasi password tidak cocok dengan password baru.
                    </div>

                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Indikator Password Realtime -->
                <div class="text-xs sm:text-sm text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100 grid gap-2">
                    <div class="font-medium text-slate-700 mb-1">Syarat Password:</div>
                    <div class="flex items-center gap-2" :class="passwordCriteria.length ? 'text-green-600 font-medium' : ''">
                        <svg v-if="passwordCriteria.length" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Minimal 8 karakter
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        <div class="flex items-center gap-2" :class="passwordCriteria.lowercase ? 'text-green-600 font-medium' : ''">
                            <svg v-if="passwordCriteria.lowercase" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            1 Huruf kecil
                        </div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.uppercase ? 'text-green-600 font-medium' : ''">
                            <svg v-if="passwordCriteria.uppercase" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            1 Huruf besar
                        </div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.number ? 'text-green-600 font-medium' : ''">
                            <svg v-if="passwordCriteria.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            1 Angka
                        </div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.symbol ? 'text-green-600 font-medium' : ''">
                            <svg v-if="passwordCriteria.symbol" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Simbol (:!@#$%^&*)
                        </div>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="mt-1 w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                    :disabled="form.processing || !passwordCriteria.length || !passwordCriteria.lowercase || !passwordCriteria.uppercase || !passwordCriteria.number || !passwordCriteria.symbol || passwordMismatch"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Simpan & Lanjutkan ke Dashboard
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
