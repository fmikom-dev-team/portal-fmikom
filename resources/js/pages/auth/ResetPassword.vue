<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const props = defineProps<{
	token: string;
	email: string;
}>();

const form = useForm({
	token: props.token,
	email: props.email,
	password: "",
	password_confirmation: "",
});

const submit = () => {
	form.post("/reset-password", {
		onFinish: () => form.reset("password", "password_confirmation"),
	});
};

// Validasi Password Real-time
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

const passwordMismatch = computed(() => {
	return (
		form.password_confirmation !== "" &&
		form.password !== form.password_confirmation
	);
});

const isValid = computed(() => {
	return (
		passwordCriteria.value.length &&
		passwordCriteria.value.lowercase &&
		passwordCriteria.value.uppercase &&
		passwordCriteria.value.number &&
		passwordCriteria.value.symbol &&
		!passwordMismatch.value &&
		form.password !== ""
	);
});
</script>

<template>
    <AuthLayout
        title="Reset Password Baru"
        description="Silakan buat password baru Anda di bawah ini"
    >
        <Head>
        <title>Reset Password</title>
    </Head>

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="font-semibold text-slate-800">Email Utama</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        autocomplete="email"
                        class="rounded-xl h-11 border-slate-200 bg-slate-50 text-slate-500 focus-visible:ring-0 transition-colors"
                        readonly
                    />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

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
                        Konfirmasi Password Baru
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        :class="[
                            'rounded-xl h-11 border-slate-200 focus-visible:ring-0 transition-colors',
                            passwordMismatch ? 'border-red-500 ring-2 ring-red-100' : 'focus-visible:border-[#2563eb]'
                        ]"
                        placeholder="••••••••"
                    />
                    <!-- Alert Mismatch Real-time -->
                    <div v-if="passwordMismatch" class="flex items-center gap-1.5 text-red-500 text-sm mt-1 font-medium animate-in fade-in slide-in-from-top-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Konfirmasi tidak sinkron
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Indikator Password Real-time -->
                <div class="text-xs sm:text-sm text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100 grid gap-2">
                    <div class="font-medium text-slate-700 mb-1">Syarat Kombinasi Sandi:</div>
                    <div class="flex items-center gap-2" :class="passwordCriteria.length ? 'text-green-600 font-medium' : ''">
                        <svg v-if="passwordCriteria.length" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Minimal 8 karakter
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        <div class="flex items-center gap-2" :class="passwordCriteria.lowercase ? 'text-green-600 font-medium' : ''"><svg v-if="passwordCriteria.lowercase" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Huruf kecil</div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.uppercase ? 'text-green-600 font-medium' : ''"><svg v-if="passwordCriteria.uppercase" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Huruf besar</div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.number ? 'text-green-600 font-medium' : ''"><svg v-if="passwordCriteria.number" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Angka</div>
                        <div class="flex items-center gap-2" :class="passwordCriteria.symbol ? 'text-green-600 font-medium' : ''"><svg v-if="passwordCriteria.symbol" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Simbol</div>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="mt-1 w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                    :disabled="form.processing || !isValid"
                    data-test="reset-password-button"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Simpan dan Perbarui Sandi
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
