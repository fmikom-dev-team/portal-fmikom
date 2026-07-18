<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { Fingerprint, Loader2 } from "lucide-vue-next";
import { ref } from "vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthBase from "@/layouts/AuthLayout.vue";

defineOptions({ layout: AuthBase });

const {
	status,
	error,
	canResetPassword,
	canRegister,
	oauthProviders,
	passkeysEnabled,
} = defineProps<{
	status?: string;
	error?: string;
	canResetPassword?: boolean;
	canRegister?: boolean;
	oauthProviders?: Array<{ name: string; slug: string }>;
	passkeysEnabled?: boolean;
}>();

// Menggunakan useForm standar Inertia untuk menghandle state & submit
const form = useForm({
	email: "",
	password: "",
	remember: false,
});

const isLoggingInPasskey = ref(false);
const passkeyError = ref("");

const submit = () => {
	form.post("/login", {
		onFinish: () => form.reset("password"),
	});
};

const base64ToArrayBuffer = (base64: string) => {
	const binary_string = globalThis.atob(
		base64.replaceAll("-", "+").replaceAll("_", "/"),
	);
	const len = binary_string.length;
	const bytes = new Uint8Array(len);
	for (let i = 0; i < len; i++) {
		bytes[i] = binary_string.codePointAt(i) || 0;
	}
	return bytes.buffer;
};

const arrayBufferToBase64 = (buffer: ArrayBuffer) => {
	let binary = "";
	const bytes = new Uint8Array(buffer);
	for (let i = 0; i < bytes.byteLength; i++) {
		binary += String.fromCodePoint(bytes[i]);
	}
	return globalThis
		.btoa(binary)
		.replaceAll("+", "-")
		.replaceAll("/", "_")
		.replaceAll("=", "");
};

const loginWithPasskey = async () => {
	isLoggingInPasskey.value = true;
	passkeyError.value = "";
	try {
		// 1. Get options from server
		const { data: options } = await axios.post("/auth/passkeys/auth/options");

		// Convert base64 challenge to ArrayBuffer
		options.challenge = base64ToArrayBuffer(options.challenge);
		if (options.allowCredentials) {
			options.allowCredentials.forEach((c: any) => {
				c.id = base64ToArrayBuffer(c.id);
			});
		}

		// 2. Call WebAuthn API
		const assertion: any = await navigator.credentials.get({
			publicKey: options,
		});

		// 3. Send response back to server to authenticate
		await axios.post("/auth/passkeys/auth/verify", {
			id: assertion.id,
			rawId: arrayBufferToBase64(assertion.rawId),
			type: assertion.type,
			response: {
				authenticatorData: arrayBufferToBase64(
					assertion.response.authenticatorData,
				),
				clientDataJSON: arrayBufferToBase64(assertion.response.clientDataJSON),
				signature: arrayBufferToBase64(assertion.response.signature),
				userHandle: assertion.response.userHandle
					? arrayBufferToBase64(assertion.response.userHandle)
					: null,
			},
		});

		// Reload/redirect to dashboard upon successful login
		globalThis.location.href = "/dashboard";
	} catch (e: any) {
		passkeyError.value =
			e.response?.data?.error || e.message || "Passkey login failed.";
	} finally {
		isLoggingInPasskey.value = false;
	}
};
</script>

<template>
    <div class="w-full">
        <Head>
            <title>Log in – Portal FMIKOM</title>
        </Head>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div v-if="error" class="mb-4 text-center text-sm font-medium text-red-600 p-3 bg-red-50 rounded-lg border border-red-100">
            {{ error }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-4" autocomplete="on">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email" class="font-semibold text-slate-800 dark:text-slate-200">Email / NIM / NIP / NIB</Label>
                    <Input
                        id="email"
                        type="text"
                        v-model="form.email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com / 192801..."
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="font-semibold text-slate-800 dark:text-slate-200">Password</Label>
                        <TextLink
                            v-if="canResetPassword !== false"
                            href="/forgot-password"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <!-- PasswordInput mungkin sebelumnya ga pakai modelValue, kita asumsikan support v-model -->
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        required
                        :tabindex="2"
                        :autocomplete="'current-password'"
                        placeholder="Password"
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                        <Checkbox
                            id="remember"
                            :checked="form.remember"
                            @update:checked="(val: boolean) => form.remember = val"
                            :tabindex="3"
                        />
                        <span class="text-slate-600 dark:text-slate-400 text-sm">Remember me</span>
                    </Label>
                </div>

                <div class="flex justify-start">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :tabindex="4"
                        :disabled="form.processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Log in
                    </Button>
                </div>

                <!-- Passkey Option 1 -->
                <div v-if="passkeysEnabled" class="flex flex-col gap-2">
                    <Button
                        type="button"
                        @click="loginWithPasskey"
                        class="w-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 shadow-sm transition-all h-11 rounded-xl text-md font-medium flex items-center justify-center gap-2"
                        :disabled="isLoggingInPasskey"
                    >
                        <Loader2 v-if="isLoggingInPasskey" class="w-5 h-5 animate-spin text-slate-500" />
                        <Fingerprint v-else class="w-5 h-5 text-indigo-600" />
                        Sign in with Passkey
                    </Button>
                    <p v-if="passkeyError" class="text-xs text-red-600 text-center mt-1">
                        {{ passkeyError }}
                    </p>
                </div>
            </div>

            <div v-if="oauthProviders && oauthProviders.length > 0" class="mt-2 flex flex-col gap-3">
                <div class="relative flex items-center py-2">
                    <div class="grow border-t border-gray-200 dark:border-slate-800"></div>
                    <span class="shrink-0 mx-4 text-gray-400 dark:text-slate-500 text-sm font-medium">Or continue with</span>
                    <div class="grow border-t border-gray-200 dark:border-slate-800"></div>
                </div>
                
                <a
                    v-for="provider in oauthProviders"
                    :key="provider.slug"
                    :href="`/auth/oauth/${provider.slug}/redirect`"
                    class="flex items-center justify-center w-full px-4 py-2.5 border border-gray-300 dark:border-slate-800 rounded-xl shadow-sm bg-white dark:bg-slate-900 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                >
                    <!-- Google -->
                    <svg v-if="provider.slug === 'google'" class="w-5 h-5 mr-2 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <!-- GitHub -->
                    <svg v-else-if="provider.slug === 'github'" class="w-5 h-5 mr-2 shrink-0" viewBox="0 0 24 24" fill="#24292F" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    <!-- Microsoft -->
                    <svg v-else-if="provider.slug === 'microsoft'" class="w-5 h-5 mr-2 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.4 24H0V12.6h11.4V24z" fill="#F25022"/>
                        <path d="M24 24H12.6V12.6H24V24z" fill="#00A4EF"/>
                        <path d="M11.4 11.4H0V0h11.4v11.4z" fill="#7FBA00"/>
                        <path d="M24 11.4H12.6V0H24v11.4z" fill="#FFB900"/>
                    </svg>
                    <!-- Apple -->
                    <svg v-else-if="provider.slug === 'apple'" class="w-5 h-5 mr-2 shrink-0 fill-current text-black dark:text-white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/>
                    </svg>
                    <!-- Fallback -->
                    <svg v-else class="w-5 h-5 mr-2 shrink-0 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 5.523 4.477 10 10 10s10-4.477 10-10c0-5.523-4.477-10-10-10zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/></svg>
                    Continue with {{ provider.name }}
                </a>
            </div>

            <div
                class="text-center text-sm text-muted-foreground mt-4"
                v-if="canRegister !== false"
            >
                Don't have an account?
                <TextLink
                    href="/register"
                    class="underline underline-offset-4"
                    :tabindex="5"
                >Sign up</TextLink>
            </div>

            <div class="text-center text-sm text-slate-500 dark:text-slate-400 mt-3">
                Mahasiswa / Dosen / Staff?
                <TextLink
                    href="/activate"
                    class="underline underline-offset-4 font-semibold text-blue-600 dark:text-blue-400 ml-1"
                    :tabindex="6"
                >Aktivasi Akun</TextLink>
            </div>
        </form>
    </div>
</template>
