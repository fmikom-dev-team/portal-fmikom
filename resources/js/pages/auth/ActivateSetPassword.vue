<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const props = defineProps<{
	session: string;
}>();

const form = useForm({
	session: props.session,
	password: "",
	password_confirmation: "",
});

const submit = () => {
	form.post("/activate/set-password", {
		onFinish: () => form.reset("password", "password_confirmation"),
	});
};

// Real-time password criteria verification
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

const isPasswordValid = computed(() => {
	return (
		form.password !== "" &&
		form.password_confirmation !== "" &&
		!passwordMismatch.value &&
		Object.values(passwordCriteria.value).every(Boolean) &&
		!form.processing
	);
});
</script>

<template>
    <AuthLayout
        title="Buat Password Baru"
        description="Amankan akun Anda dengan membuat password baru"
    >
        <Head>
            <title>Buat Password Aktivasi</title>
        </Head>

        <div class="space-y-6">
            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="grid gap-2">
                    <Label for="password" class="font-semibold text-slate-800 dark:text-slate-200">Password Baru</Label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        required
                        autofocus
                        placeholder="Password Baru"
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="font-semibold text-slate-800 dark:text-slate-200">Konfirmasi Password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        required
                        placeholder="Konfirmasi Password Baru"
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Password strength indicator layout -->
                <div class="bg-slate-50 dark:bg-slate-950/20 border border-slate-100 dark:border-slate-800 rounded-xl p-4 text-xs">
                    <p class="font-semibold text-slate-700 dark:text-slate-300 mb-2">Syarat Keamanan Password:</p>
                    <ul class="space-y-1.5">
                        <li :class="['flex items-center gap-2 transition-colors duration-200', passwordCriteria.length ? 'text-green-600' : 'text-slate-400 dark:text-slate-500']">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', passwordCriteria.length ? 'bg-green-600' : 'bg-slate-300 dark:bg-slate-800']"></span>
                            Minimal 8 karakter
                        </li>
                        <li :class="['flex items-center gap-2 transition-colors duration-200', passwordCriteria.lowercase ? 'text-green-600' : 'text-slate-400 dark:text-slate-500']">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', passwordCriteria.lowercase ? 'bg-green-600' : 'bg-slate-300 dark:bg-slate-800']"></span>
                            Mengandung huruf kecil (a-z)
                        </li>
                        <li :class="['flex items-center gap-2 transition-colors duration-200', passwordCriteria.uppercase ? 'text-green-600' : 'text-slate-400 dark:text-slate-500']">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', passwordCriteria.uppercase ? 'bg-green-600' : 'bg-slate-300 dark:bg-slate-800']"></span>
                            Mengandung huruf besar (A-Z)
                        </li>
                        <li :class="['flex items-center gap-2 transition-colors duration-200', passwordCriteria.number ? 'text-green-600' : 'text-slate-400 dark:text-slate-500']">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', passwordCriteria.number ? 'bg-green-600' : 'bg-slate-300 dark:bg-slate-800']"></span>
                            Mengandung angka (0-9)
                        </li>
                        <li :class="['flex items-center gap-2 transition-colors duration-200', passwordCriteria.symbol ? 'text-green-600' : 'text-slate-400 dark:text-slate-500']">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', passwordCriteria.symbol ? 'bg-green-600' : 'bg-slate-300 dark:bg-slate-800']"></span>
                            Mengandung simbol (: ! @ # $ % ^ & *)
                        </li>
                        <li v-if="passwordMismatch" class="text-red-500 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 bg-red-500"></span>
                            Konfirmasi password tidak cocok
                        </li>
                    </ul>
                </div>

                <div class="mt-2 flex justify-start">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :disabled="!isPasswordValid"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Aktifkan Akun Saya
                    </Button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
