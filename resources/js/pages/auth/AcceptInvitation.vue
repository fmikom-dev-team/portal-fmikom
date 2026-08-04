<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { AlertCircle, CheckCircle2, KeyRound, Loader2, Lock, Mail, User as UserIcon } from "lucide-vue-next";
import { computed, ref } from "vue";

const props = defineProps<{
	invalid?: boolean;
	message?: string;
	token?: string;
	email?: string;
	first_name?: string;
	last_name?: string;
	user_type?: string;
}>();

const page = usePage();
const brandLogo = computed(() => {
	const settings = (page.props as any).siteSettings;
	return settings?.brand_logo || "/asset/brand-logo.webp";
});

const form = useForm({
	token: props.token || "",
	name: trimName(),
	password: "",
	password_confirmation: "",
});

function trimName(): string {
	const combined = `${props.first_name || ""} ${props.last_name || ""}`.trim();
	if (combined) return combined;
	if (props.email) return props.email.split("@")[0];
	return "";
}

const showPassword = ref(false);

const submit = () => {
	form.post("/invitations/accept", {
		onFinish: () => form.reset("password", "password_confirmation"),
	});
};
</script>

<template>
	<Head title="Terima Undangan Akun — Portal FMIKOM UNUGHA" />

	<div class="min-h-screen bg-slate-50 text-slate-900 flex items-center justify-center p-4 font-sans relative overflow-hidden">
		<!-- Subtle Clean Background Accents -->
		<div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

		<div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 space-y-6 relative z-10">
			<!-- Header / Brand Logo -->
			<div class="text-center space-y-3">
				<div class="w-16 h-16 bg-white border border-slate-200/80 shadow-md shadow-slate-200/40 rounded-2xl flex items-center justify-center mx-auto p-2.5 overflow-hidden">
					<img :src="brandLogo" alt="Logo Aplikasi" class="w-full h-full object-contain" />
				</div>
				<div>
					<h1 class="text-2xl font-bold tracking-tight text-slate-900">Terima Undangan Akun</h1>
					<p class="text-xs text-slate-500 mt-1">Portal FMIKOM Universitas Nahdlatul Ulama Al Ghazali</p>
				</div>
			</div>

			<!-- Invalid Token Alert -->
			<div v-if="invalid" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-700 text-xs leading-relaxed">
				<AlertCircle class="w-5 h-5 shrink-0 mt-0.5 text-red-600" />
				<div>
					<strong class="font-bold block mb-1 text-red-800">Tautan Tidak Valid</strong>
					{{ message || 'Tautan undangan ini sudah kadaluarsa atau tidak ditemukan.' }}
				</div>
			</div>

			<!-- Accept Form -->
			<form v-else @submit.prevent="submit" class="space-y-4">
				<!-- Email Readonly Badge -->
				<div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between">
					<div class="flex items-center gap-2.5">
						<Mail class="w-4 h-4 text-blue-600 shrink-0" />
						<div class="text-xs">
							<span class="text-slate-400 block text-[10px] font-medium">Alamat Email Undangan</span>
							<strong class="text-slate-800 font-semibold">{{ email }}</strong>
						</div>
					</div>
					<span class="px-2.5 py-0.5 bg-blue-100 border border-blue-200 text-blue-700 rounded-full text-[10px] font-bold uppercase tracking-wider">
						{{ user_type || 'User' }}
					</span>
				</div>

				<!-- Name Input -->
				<div class="space-y-1.5">
					<label class="block text-xs font-bold text-slate-700">Nama Lengkap</label>
					<div class="relative">
						<UserIcon class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
						<input
							v-model="form.name"
							type="text"
							required
							placeholder="Masukkan nama lengkap"
							class="w-full bg-slate-50/50 border border-slate-200 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-900 placeholder:text-slate-400 outline-none transition-all font-medium"
						/>
					</div>
					<p v-if="form.errors.name" class="text-[11px] text-red-600 mt-1 font-medium">{{ form.errors.name }}</p>
				</div>

				<!-- Password Input -->
				<div class="space-y-1.5">
					<label class="block text-xs font-bold text-slate-700">Kata Sandi Baru</label>
					<div class="relative">
						<Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
						<input
							v-model="form.password"
							:type="showPassword ? 'text' : 'password'"
							required
							placeholder="Minimal 8 karakter"
							class="w-full bg-slate-50/50 border border-slate-200 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-900 placeholder:text-slate-400 outline-none transition-all font-medium"
						/>
					</div>
					<p v-if="form.errors.password" class="text-[11px] text-red-600 mt-1 font-medium">{{ form.errors.password }}</p>
				</div>

				<!-- Password Confirmation Input -->
				<div class="space-y-1.5">
					<label class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi</label>
					<div class="relative">
						<KeyRound class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
						<input
							v-model="form.password_confirmation"
							:type="showPassword ? 'text' : 'password'"
							required
							placeholder="Ketik ulang kata sandi"
							class="w-full bg-slate-50/50 border border-slate-200 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-900 placeholder:text-slate-400 outline-none transition-all font-medium"
						/>
					</div>
				</div>

				<!-- Submit Button -->
				<button
					type="submit"
					:disabled="form.processing"
					class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs transition-all shadow-md shadow-blue-500/20 active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 border-0 mt-2"
				>
					<Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
					<CheckCircle2 v-else class="w-4 h-4" />
					<span>{{ form.processing ? 'Mengaktifkan Akun...' : 'Aktifkan Akun Sekarang' }}</span>
				</button>
			</form>

			<div class="text-center pt-2 border-t border-slate-100">
				<p class="text-[11px] text-slate-400">© {{ new Date().getFullYear() }} FMIKOM UNUGHA. All rights reserved.</p>
			</div>
		</div>
	</div>
</template>
