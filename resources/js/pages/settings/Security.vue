<script setup lang="ts">
import { onUnmounted, ref } from "vue";
import { Form, Head, useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	CheckCircle2,
	Fingerprint,
	Loader2,
	Pencil,
	Plus,
	ShieldCheck,
	X,
} from "lucide-vue-next";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import TwoFactorRecoveryCodes from "@/components/TwoFactorRecoveryCodes.vue";
import TwoFactorSetupModal from "@/components/TwoFactorSetupModal.vue";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { useTwoFactorAuth } from "@/composables/useTwoFactorAuth";
import AppLayout from "@/layouts/AppLayout.vue";
import SettingsLayout from "@/layouts/settings/Layout.vue";
import { edit } from "@/routes/security";
import { disable, enable } from "@/routes/two-factor/index";
import type { BreadcrumbItem } from "@/types";

type Props = {
	canManageTwoFactor?: boolean;
	requiresConfirmation?: boolean;
	twoFactorEnabled?: boolean;
	passkeysEnabled?: boolean;
	passkeys?: Array<{
		id: number;
		name: string;
		last_used_at: string | null;
		created_at: string;
	}>;
};

const props = withDefaults(defineProps<Props>(), {
	canManageTwoFactor: false,
	requiresConfirmation: false,
	twoFactorEnabled: false,
	passkeysEnabled: false,
	passkeys: () => [],
});

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: "Security settings",
		href: edit(),
	},
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

const disableMfa = async () => {
	if (confirm("Are you sure you want to disable 2FA?")) {
		await axios.delete("/auth/mfa/disable");
		window.location.reload();
	}
};

onUnmounted(() => clearTwoFactorAuthData());

const page = usePage();
const user = page.props.auth?.user || ({} as any);

const emailForm = useForm({
	email: user.email || "",
	current_password: "",
});

const submitEmailForm = () => {
	emailForm.put("/settings/email", {
		preserveScroll: true,
		onSuccess: () => {
			emailForm.current_password = "";
		},
	});
};

const passwordForm = useForm({
	current_password: "",
	password: "",
	password_confirmation: "",
});

const submitPasswordForm = () => {
	passwordForm.put("/settings/password", {
		preserveScroll: true,
		onSuccess: () => {
			passwordForm.reset();
		},
		onError: () => {
			passwordForm.reset("password", "password_confirmation", "current_password");
		},
	});
};

const isRegisteringPasskey = ref(false);
const passkeyError = ref("");
const passkeySuccess = ref("");
const localPasskeys = ref([...props.passkeys]);

// State for renaming passkeys
const editingPasskeyId = ref<number | null>(null);
const editingPasskeyName = ref("");

const startEditing = (pk: any) => {
	editingPasskeyId.value = pk.id;
	editingPasskeyName.value = pk.name;
};

const cancelEditing = () => {
	editingPasskeyId.value = null;
};

const saveRename = async (pk: any) => {
	if (!editingPasskeyName.value.trim()) return;
	try {
		const { data } = await axios.patch(`/auth/passkeys/${pk.id}`, {
			name: editingPasskeyName.value,
		});
		// Find the local item and update its name
		const index = localPasskeys.value.findIndex((p) => p.id === pk.id);
		if (index !== -1) {
			localPasskeys.value[index].name = data.passkey.name;
		}
		editingPasskeyId.value = null;
		passkeySuccess.value = "Kunci sandi berhasil diubah namanya.";
	} catch (e: any) {
		passkeyError.value = "Gagal mengubah nama kunci sandi.";
	}
};

const formatCreatedDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
};

const formatLastUsedDate = (dateString: string | null) => {
	if (!dateString) return "Belum digunakan";

	const diffMs = Date.now() - new Date(dateString).getTime();
	const diffMins = Math.floor(diffMs / 60000);

	if (diffMins < 2) return "Baru saja";
	if (diffMins < 60) return `${diffMins} menit yang lalu`;

	const diffHours = Math.floor(diffMins / 60);
	if (diffHours < 24) return `${diffHours} jam yang lalu`;

	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};

const base64ToArrayBuffer = (base64: string) => {
	const binary_string = window.atob(
		base64.replace(/-/g, "+").replace(/_/g, "/"),
	);
	const len = binary_string.length;
	const bytes = new Uint8Array(len);
	for (let i = 0; i < len; i++) {
		bytes[i] = binary_string.charCodeAt(i);
	}
	return bytes.buffer;
};

const arrayBufferToBase64 = (buffer: ArrayBuffer) => {
	let binary = "";
	const bytes = new Uint8Array(buffer);
	for (let i = 0; i < bytes.byteLength; i++) {
		binary += String.fromCharCode(bytes[i]);
	}
	return window
		.btoa(binary)
		.replace(/\+/g, "-")
		.replace(/\//g, "_")
		.replace(/=/g, "");
};

const registerNewPasskey = async () => {
	isRegisteringPasskey.value = true;
	passkeyError.value = "";
	passkeySuccess.value = "";
	try {
		const { data: options } = await axios.post(
			"/auth/passkeys/register/options",
		);
		options.challenge = base64ToArrayBuffer(options.challenge);
		options.user.id = base64ToArrayBuffer(options.user.id);

		const credential: any = await navigator.credentials.create({
			publicKey: options,
		});

		const { data } = await axios.post("/auth/passkeys/register/verify", {
			id: credential.id,
			rawId: arrayBufferToBase64(credential.rawId),
			type: credential.type,
			response: {
				attestationObject: arrayBufferToBase64(
					credential.response.attestationObject,
				),
				clientDataJSON: arrayBufferToBase64(credential.response.clientDataJSON),
			},
		});

		passkeySuccess.value = "Kunci sandi berhasil didaftarkan!";
		localPasskeys.value.push(data.passkey);
	} catch (e: any) {
		passkeyError.value =
			e.response?.data?.error || e.message || "Gagal mendaftarkan kunci sandi.";
	} finally {
		isRegisteringPasskey.value = false;
	}
};

const deletePasskey = async (id: number) => {
	if (!confirm("Apakah Anda yakin ingin menghapus kunci sandi ini?")) return;
	try {
		await axios.delete(`/auth/passkeys/${id}`);
		localPasskeys.value = localPasskeys.value.filter((pk) => pk.id !== id);
		passkeySuccess.value = "Kunci sandi berhasil dihapus.";
	} catch (e: any) {
		passkeyError.value = "Gagal menghapus kunci sandi.";
	}
};

const getPasskeyIcon = (name: string) => {
	const lowercaseName = name.toLowerCase();
	if (
		lowercaseName.includes("mac") ||
		lowercaseName.includes("icloud keychain") ||
		lowercaseName.includes("apple") ||
		lowercaseName.includes("iphone") ||
		lowercaseName.includes("ipad")
	) {
		return "apple";
	}
	if (
		lowercaseName.includes("windows") ||
		lowercaseName.includes("microsoft") ||
		lowercaseName.includes("hello")
	) {
		return "windows";
	}
	if (
		lowercaseName.includes("android") ||
		lowercaseName.includes("poco") ||
		lowercaseName.includes("samsung") ||
		lowercaseName.includes("xiaomi") ||
		lowercaseName.includes("oppo") ||
		lowercaseName.includes("vivo")
	) {
		return "android";
	}
	if (
		lowercaseName.includes("pengelola sandi") ||
		lowercaseName.includes("google") ||
		lowercaseName.includes("google password manager")
	) {
		return "google";
	}
	return "key";
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head>
        <title>Security settings</title>
    </Head>

        <h1 class="sr-only">Security settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <form @submit.prevent="submitPasswordForm" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <PasswordInput
                            id="current_password"
                            v-model="passwordForm.current_password"
                            name="current_password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError :message="passwordForm.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <PasswordInput
                            id="password"
                            v-model="passwordForm.password"
                            name="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError :message="passwordForm.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm password</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            name="password_confirmation"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError :message="passwordForm.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="passwordForm.processing"
                            data-test="update-password-button"
                        >
                            Save password
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="passwordForm.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <!-- Separator -->
            <hr class="border-slate-100 dark:border-slate-800/80 my-8" />

            <!-- Update Email Address Form -->
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Update email address"
                    description="Ensure your email address is up to date. Updating your email requires confirming your current password for security reasons."
                />

                <form @submit.prevent="submitEmailForm" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="email">New email address</Label>
                        <input
                            id="email"
                            v-model="emailForm.email"
                            type="email"
                            required
                            class="flex h-10 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-1"
                            placeholder="New email address"
                        />
                        <InputError :message="emailForm.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email_current_password">Current password</Label>
                        <PasswordInput
                            id="email_current_password"
                            v-model="emailForm.current_password"
                            name="current_password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Confirm with your password"
                        />
                        <InputError :message="emailForm.errors.current_password" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="emailForm.processing"
                        >
                            <Loader2 v-if="emailForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Save email
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="emailForm.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div v-if="canManageTwoFactor" class="space-y-6">
                <Heading
                    variant="small"
                    title="Two-factor authentication"
                    description="Manage your two-factor authentication settings"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-muted-foreground">
                        When you enable two-factor authentication, you will be
                        prompted for a secure pin during login. This pin can be
                        retrieved from a TOTP-supported application on your
                        phone.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Continue setup
                        </Button>
                        <Button
                            v-else
                            @click="showSetupModal = true"
                        >
                            Enable 2FA
                        </Button>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-muted-foreground">
                        You will be prompted for a secure, random pin during
                        login, which you can retrieve from the TOTP-supported
                        application on your phone.
                    </p>

                    <div class="relative inline">
                        <Button
                            variant="destructive"
                            @click="disableMfa"
                        >
                            Disable 2FA
                        </Button>
                    </div>

                    <TwoFactorRecoveryCodes />
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>

            <!-- Passkeys Section -->
            <div v-if="passkeysEnabled">
                <!-- Separator -->
                <hr class="border-slate-100 dark:border-slate-800/80 my-8" />
                
                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Kunci sandi dan kunci keamanan"
                        description="Kelola sidik jari, pengenalan wajah, atau kunci keamanan fisik perangkat Anda. Kunci sandi memungkinkan Anda masuk dengan aman tanpa kata sandi."
                    />

                    <!-- Alerts -->
                    <div v-if="passkeyError" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg flex items-start gap-2 border border-red-100 max-w-2xl">
                        <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
                        <span>{{ passkeyError }}</span>
                    </div>

                    <div v-if="passkeySuccess" class="bg-emerald-50 text-emerald-600 text-sm px-4 py-3 rounded-lg flex items-start gap-2 border border-emerald-100 max-w-2xl">
                        <CheckCircle2 class="w-4 h-4 shrink-0 mt-0.5" />
                        <span>{{ passkeySuccess }}</span>
                    </div>

                    <!-- Register Button -->
                    <div>
                        <Button 
                            type="button" 
                            @click="registerNewPasskey" 
                            :disabled="isRegisteringPasskey"
                            class="flex items-center gap-2"
                        >
                            <Loader2 v-if="isRegisteringPasskey" class="w-4 h-4 animate-spin" />
                            <Plus v-else class="w-4 h-4" />
                            Buat kunci sandi baru
                        </Button>
                    </div>

                    <!-- Google-styled Card Container -->
                    <div class="max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <span class="block text-lg font-medium text-slate-900 dark:text-slate-100">Kunci sandi</span>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Buat kunci sandi di perangkat Anda, atau Anda dapat membuat kunci sandi di kunci keamanan Anda. 
                            <a href="#" class="text-blue-605 hover:underline inline-flex items-center gap-0.5 ml-1">
                                Pelajari lebih lanjut
                                <svg class="w-3.5 h-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                            </a>
                        </p>

                        <!-- Subtitle -->
                        <span class="block text-xs font-semibold tracking-wider text-slate-400 dark:text-slate-500 mt-6 mb-4 uppercase">Perangkat Anda</span>

                        <!-- List of Passkeys / Devices -->
                        <div v-if="localPasskeys.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <div v-for="pk in localPasskeys" :key="pk.id" class="py-4 flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4 grow">
                                    <!-- Device Icon -->
                                    <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-center shrink-0">
                                        <template v-if="getPasskeyIcon(pk.name) === 'apple'">
                                            <svg class="w-5 h-5 text-slate-800 dark:text-slate-200" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/>
                                            </svg>
                                        </template>
                                        <template v-else-if="getPasskeyIcon(pk.name) === 'windows'">
                                            <svg class="w-5 h-5 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M0 3.449L9.75 2.1v9.45H0V3.449zM0 12.45h9.75v9.45L0 20.551v-8.101zM10.8 1.95L24 0v11.55H10.8V1.95zM10.8 12.45H24v11.55l-13.2-1.95v-9.6z"/>
                                            </svg>
                                        </template>
                                        <template v-else-if="getPasskeyIcon(pk.name) === 'android'">
                                            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17.523 15.3l1.807 3.13a.5.5 0 1 1-.866.5l-1.833-3.178A10.457 10.457 0 0 1 12 17c-2.062 0-3.978-.6-5.594-1.625l-1.833 3.179a.5.5 0 0 1-.866-.5l1.807-3.13A10.5 10.5 0 0 1 1 7h22a10.5 10.5 0 0 1-4.477 8.3zM7 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                            </svg>
                                        </template>
                                        <template v-else-if="getPasskeyIcon(pk.name) === 'google'">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                            </svg>
                                        </template>
                                        <template v-else>
                                            <svg class="w-5 h-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="7.5" cy="15.5" r="5.5"/>
                                                <path d="m21 2-9.6 9.6"/>
                                                <path d="m15.5 7.5 3 3"/>
                                                <path d="M18.5 4.5 21.5 7.5"/>
                                            </svg>
                                        </template>
                                    </div>

                                    <!-- Content -->
                                    <div class="grow">
                                        <!-- Title / Rename mode -->
                                        <div v-if="editingPasskeyId === pk.id" class="flex items-center gap-2 max-w-md">
                                            <input 
                                                v-model="editingPasskeyName" 
                                                type="text" 
                                                class="rename-input flex h-8 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#2563eb]"
                                                @keyup.enter="saveRename(pk)"
                                                @keyup.esc="cancelEditing"
                                            />
                                            <button @click="saveRename(pk)" class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 transition-colors">Simpan</button>
                                            <button @click="cancelEditing" class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-lg hover:bg-slate-200 transition-colors">Batal</button>
                                        </div>
                                        <span v-else class="block font-semibold text-slate-800 dark:text-slate-100 text-sm md:text-md">
                                            {{ pk.name }}
                                        </span>

                                        <!-- Details -->
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 space-y-0.5">
                                            <span class="block">Dibuat: {{ formatCreatedDate(pk.created_at) }}</span>
                                            <span v-if="getPasskeyIcon(pk.name) === 'apple'" class="block text-slate-400 dark:text-slate-500">
                                                Kunci ini hanya dapat digunakan dengan sandi. 
                                                <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Pelajari lebih lanjut</a>
                                            </span>
                                            <span class="block">Terakhir digunakan: {{ formatLastUsedDate(pk.last_used_at) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button 
                                        v-if="editingPasskeyId !== pk.id"
                                        @click="startEditing(pk)" 
                                        class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors"
                                        title="Ubah nama"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button 
                                        @click="deletePasskey(pk.id)" 
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-full transition-colors"
                                        title="Hapus"
                                    >
                                        <X class="w-4.5 h-4.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-slate-500 dark:text-slate-400 italic py-4">
                            Belum ada kunci sandi yang terdaftar. Daftarkan biometrik perangkat Anda di atas untuk pengalaman masuk tanpa kata sandi yang lebih cepat dan aman.
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
