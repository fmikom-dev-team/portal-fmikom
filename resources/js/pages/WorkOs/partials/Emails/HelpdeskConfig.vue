<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import axios from "axios";
import { toast } from "../../composables/useWorkOs";

const defaultTemplate = `Halo Admin FMIKOM, saya bermaksud mengajukan pembaruan email aktivasi akun:\n\n` +
	`• Nama Mahasiswa : {nama}\n` +
	`• NIM            : {nim}\n` +
	`• Email Lama     : {email_lama}\n` +
	`• Email Baru     : {email_baru}\n\n` +
	`Saya siap melampirkan foto KTM/KTP sebagai verifikasi fisik. Mohon bantuannya.`;

const form = reactive({
	helpdesk_wa_number: "",
	helpdesk_wa_template: defaultTemplate,
});

const isSaving = ref(false);
const isLoading = ref(true);

const fetchSetting = async () => {
	isLoading.value = true;
	try {
		const res = await axios.get("/workos/settings/helpdesk");
		if (res.data.wa_number) {
			form.helpdesk_wa_number = res.data.wa_number;
		}
		if (res.data.wa_template) {
			form.helpdesk_wa_template = res.data.wa_template;
		}
	} catch (e) {
		console.error("Gagal memuat setting helpdesk", e);
	} finally {
		isLoading.value = false;
	}
};

const saveHelpdeskConfig = async () => {
	isSaving.value = true;
	try {
		const cleanNumber = form.helpdesk_wa_number.replace(/\D/g, "");
		await axios.post("/workos/settings/update", {
			helpdesk_wa_number: cleanNumber,
			helpdesk_wa_template: form.helpdesk_wa_template,
		});
		form.helpdesk_wa_number = cleanNumber;
		toast("Konfigurasi WhatsApp Helpdesk CS & Template berhasil disimpan!", "success");
	} catch (e: any) {
		toast(e.response?.data?.message || "Gagal menyimpan konfigurasi.", "error");
	} finally {
		isSaving.value = false;
	}
};

const insertTag = (tag: string) => {
	form.helpdesk_wa_template += ` ${tag} `;
};

const previewText = computed(() => {
	const template = form.helpdesk_wa_template || defaultTemplate;
	return template
		.replace(/\{nama\}/g, "Maruf Muchlisin")
		.replace(/\{nim\}/g, "22EO10013")
		.replace(/\{email_lama\}/g, "da***6@gmail.com")
		.replace(/\{email_baru\}/g, "example.new@gmail.com");
});

const testWaLink = computed(() => {
	const wa = form.helpdesk_wa_number.replace(/\D/g, "") || "628123456789";
	return `https://wa.me/${wa}?text=${encodeURIComponent(previewText.value)}`;
});

const openTestWa = () => {
	if (!form.helpdesk_wa_number) {
		toast("Masukkan nomor WhatsApp terlebih dahulu untuk melakukan pengujian.", "error");
		return;
	}
	window.open(testWaLink.value, "_blank");
};

const resetTemplate = () => {
	form.helpdesk_wa_template = defaultTemplate;
};

onMounted(fetchSetting);
</script>

<template>
	<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
		<div class="lg:col-span-7 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
			<div>
				<h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
					💬 Konfigurasi WhatsApp Helpdesk CS FMIKOM
				</h3>
				<p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
					Atur nomor WhatsApp resmi CS/Admin dan kustomisasi template pesan otomatis yang akan dikirimkan oleh mahasiswa.
				</p>
			</div>

			<form @submit.prevent="saveHelpdeskConfig" class="space-y-5">
				<!-- Input WA Number -->
				<div class="space-y-2">
					<label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Nomor WhatsApp CS FMIKOM</label>
					<div class="relative">
						<input
							v-model="form.helpdesk_wa_number"
							type="text"
							placeholder="Contoh: 628123456789"
							class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-600 outline-none"
						/>
					</div>
					<p class="text-[11px] text-gray-400 dark:text-zinc-500">
						Gunakan kode negara (misal <strong>628...</strong>). Nomor ini akan menerima pesan WhatsApp dari mahasiswa.
					</p>
				</div>

				<!-- Custom Template Textarea -->
				<div class="space-y-2">
					<div class="flex items-center justify-between">
						<label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Template Pesan WhatsApp (Dapat Disesuaikan)</label>
						<button
							type="button"
							@click="resetTemplate"
							class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
						>
							Reset ke Default
						</button>
					</div>

					<!-- Placeholder Tags -->
					<div class="flex flex-wrap items-center gap-1.5 p-2 bg-gray-50 dark:bg-zinc-800/30 rounded-xl border border-gray-150 dark:border-zinc-800 text-[11px]">
						<span class="text-gray-400 dark:text-zinc-500 text-[10.5px] font-medium mr-1">Variabel Dinamis:</span>
						<button
							v-for="tag in ['{nama}', '{nim}', '{email_lama}', '{email_baru}']"
							:key="tag"
							type="button"
							@click="insertTag(tag)"
							class="px-2 py-0.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded text-blue-600 dark:text-blue-400 font-mono font-bold hover:bg-blue-50 dark:hover:bg-blue-950/50 transition-colors cursor-pointer"
						>
							+ {{ tag }}
						</button>
					</div>

					<textarea
						v-model="form.helpdesk_wa_template"
						rows="7"
						placeholder="Tulis format pesan WhatsApp..."
						class="w-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl p-3.5 text-xs font-mono font-medium text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-600 outline-none leading-relaxed resize-y"
					></textarea>
				</div>

				<div class="pt-2 flex items-center justify-between gap-3">
					<button
						type="button"
						@click="openTestWa"
						class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 cursor-pointer"
					>
						📲 Test Buka WA Admin
					</button>

					<button
						type="submit"
						:disabled="isSaving"
						class="px-4 py-2.5 bg-[#111827] hover:bg-black dark:bg-zinc-100 dark:text-zinc-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer disabled:opacity-50 border-0"
					>
						{{ isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi & Template' }}
					</button>
				</div>
			</form>
		</div>

		<!-- Live Preview Card -->
		<div class="lg:col-span-5 bg-gray-50 dark:bg-zinc-900/60 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5 space-y-3 sticky top-6">
			<div class="flex items-center justify-between">
				<h4 class="text-xs font-bold text-gray-800 dark:text-zinc-200 flex items-center gap-1.5">
					🔍 Live Format Preview
				</h4>
				<span class="text-[10px] px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded font-semibold">Real-time</span>
			</div>
			<p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
				Tampilan pesan WhatsApp yang akan diterima Admin CS saat mahasiswa mengajukan bantuan:
			</p>
			<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 rounded-xl p-3.5 text-[11.5px] text-emerald-900 dark:text-emerald-300 font-mono whitespace-pre-wrap leading-relaxed shadow-xs min-h-[180px]">
{{ previewText }}
			</div>
		</div>
	</div>
</template>
