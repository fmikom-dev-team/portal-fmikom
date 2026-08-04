<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import { AlertCircle } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps<{
	status: string;
	message: string;
	organization?: string;
}>();

const page = usePage();
const brandLogo = computed(() => {
	const settings = (page.props as any).siteSettings;
	return settings?.brand_logo || "/asset/brand-logo.webp";
});
</script>

<template>
	<Head title="Tautan Undangan Tidak Valid — Portal FMIKOM UNUGHA" />

	<div class="min-h-screen bg-slate-50 text-slate-900 flex items-center justify-center p-4 font-sans relative overflow-hidden">
		<!-- Subtle Clean Background Accents -->
		<div class="absolute -top-32 -left-32 w-96 h-96 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -bottom-32 -right-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

		<div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl p-8 shadow-xl shadow-slate-200/50 space-y-6 text-center relative z-10">
			<!-- Header / Brand Logo -->
			<div class="space-y-3">
				<div class="w-16 h-16 bg-white border border-slate-200/80 shadow-md shadow-slate-200/40 rounded-2xl flex items-center justify-center mx-auto p-2.5 overflow-hidden">
					<img :src="brandLogo" alt="Logo Aplikasi" class="w-full h-full object-contain" />
				</div>
				<div>
					<h1 class="text-2xl font-bold tracking-tight text-slate-900">Terima Undangan Akun</h1>
					<p class="text-xs text-slate-500 mt-1">Portal FMIKOM Universitas Nahdlatul Ulama Al Ghazali</p>
				</div>
			</div>

			<!-- Invalid Token Alert Box matching AcceptInvitation design -->
			<div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-700 text-xs leading-relaxed text-left">
				<AlertCircle class="w-5 h-5 shrink-0 mt-0.5 text-red-600" />
				<div>
					<strong class="font-bold block mb-1 text-red-800">Tautan Tidak Valid</strong>
					{{ message || 'Tautan undangan ini sudah tidak berlaku, kadaluarsa, atau telah digunakan.' }}
				</div>
			</div>

			<!-- Action Button -->
			<div class="pt-2">
				<a
					href="/"
					class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs transition-all shadow-md shadow-blue-500/20 active:scale-[0.99] flex items-center justify-center gap-2 text-decoration-none border-0"
				>
					Kembali ke Portal FMIKOM
				</a>
			</div>

			<div class="pt-2 border-t border-slate-100">
				<p class="text-[11px] text-slate-400">© {{ new Date().getFullYear() }} FMIKOM UNUGHA. All rights reserved.</p>
			</div>
		</div>
	</div>
</template>
