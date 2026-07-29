<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Image as ImageIcon, Plus, ShieldCheck, Sparkles } from "lucide-vue-next";
import { ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	customImageRules?: string[];
	enableVisionAi?: boolean;
}>();

const form = useForm({
	customImageRules: props.customImageRules ?? [],
	enableVisionAi: props.enableVisionAi ?? true,
	autoModeration: true,
	commentCensorMode: "reject",
});

const newRule = ref("");

const addRule = () => {
	const rule = newRule.value.trim();
	if (rule && !form.customImageRules.includes(rule)) {
		form.customImageRules.push(rule);
		newRule.value = "";
	}
};

const removeRule = (index: number) => {
	form.customImageRules.splice(index, 1);
};

const saveDictionary = () => {
	form.post("/pagi/admin/moderation/settings", {
		preserveScroll: true,
	});
};
</script>

<template>
	<PagiAdminLayout title="Kamus Gambar Visual Terlarang">
		<div class="space-y-6 max-w-5xl mx-auto">
			<!-- Header -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-sm">
				<div class="flex items-center gap-3.5">
					<div class="h-11 w-11 rounded-xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
						<ImageIcon class="w-6 h-6" />
					</div>
					<div>
						<h1 class="text-xl font-bold text-slate-800 dark:text-zinc-100">Kamus Gambar Visual Terlarang</h1>
						<p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-0.5">Kelola aturan dan indikator visual khusus yang wajib dideteksi oleh AI pada foto postingan & karya.</p>
					</div>
				</div>
				<button
					@click="saveDictionary"
					:disabled="form.processing"
					class="rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-50 px-5 py-2.5 text-[13px] font-bold text-white transition-all shadow-sm shrink-0"
				>
					{{ form.processing ? 'Menyimpan...' : '💾 Simpan Kamus Gambar' }}
				</button>
			</div>

			<!-- Form Penambahan Indikator Gambar -->
			<div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 p-6 space-y-5">
				<div>
					<label class="block text-[13px] font-bold text-slate-800 dark:text-zinc-200 mb-1">
						🖼️ Tambah Indikator Gambar Terlarang Kustom
					</label>
					<p class="text-[12px] text-slate-500 dark:text-zinc-400 mb-3">
						Ketik deskripsi konteks visual atau gambar yang dilarang di lingkungan kampus (misal: <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">foto pakaian minim</code>, <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">tangkapan layar judi slot</code>, <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">foto sertifikat palsu</code>).
					</p>
					<div class="flex items-center gap-2">
						<input
							v-model="newRule"
							@keydown.enter.prevent="addRule"
							type="text"
							placeholder="Ketik indikator konteks gambar lalu tekan Enter..."
							class="flex-1 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/60 px-4 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-purple-500"
						/>
						<button
							type="button"
							@click="addRule"
							class="rounded-xl bg-purple-700 hover:bg-purple-800 px-4 py-2.5 text-[13px] font-bold text-white transition-all shadow-sm flex items-center gap-1.5 shrink-0"
						>
							<Plus class="w-4 h-4" /> Tambah Indikator
						</button>
					</div>
				</div>

				<!-- Info Integrasi Gemini Vision AI -->
				<div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 flex items-start gap-3">
					<Sparkles class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" />
					<div class="text-[12px] text-purple-900 dark:text-purple-200">
						<p class="font-bold">Otomatis Terhubung ke Engine Vision AI Cloud & Local Fallback</p>
						<p class="text-purple-700 dark:text-purple-300 mt-0.5">
							Seluruh aturan kustom di atas digabungkan secara dinamis ke dalam prompt analisis Google Gemini Vision AI dan local fallback. Gambar yang melanggar akan otomatis diburamkan dengan Content Warning Blur.
						</p>
					</div>
				</div>

				<!-- Daftar Tag Indikator Gambar Kustom -->
				<div class="pt-4 border-t border-slate-100 dark:border-zinc-800">
					<div class="flex items-center justify-between mb-3">
						<h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-200 flex items-center gap-2">
							<ShieldCheck class="w-4 h-4 text-purple-500" />
							Daftar Indikator Gambar Kustom Admin ({{ form.customImageRules.length }})
						</h3>
					</div>

					<div class="flex flex-wrap gap-2">
						<span
							v-for="(rule, idx) in form.customImageRules"
							:key="idx"
							class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-purple-700 dark:text-purple-300 text-[12px] font-bold shadow-xs"
						>
							🖼️ {{ rule }}
							<button type="button" @click="removeRule(idx)" class="text-purple-400 hover:text-rose-500 transition-colors ml-1 font-bold">
								&times;
							</button>
						</span>
						<span v-if="form.customImageRules.length === 0" class="text-[12px] italic text-slate-400 dark:text-zinc-500 py-2">
							Belum ada indikator kustom tambahan.
						</span>
					</div>
				</div>

				<!-- Indikator Gambar Bawaan Sistem (Auto-Active) -->
				<div class="pt-5 border-t border-slate-100 dark:border-zinc-800 space-y-4">
					<div class="flex items-center gap-2">
						<div class="h-6 w-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
							⚡
						</div>
						<div>
							<h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Deteksi Visual Bawaan Sistem (Aktif Otomatis)</h3>
							<p class="text-[11px] text-slate-400 dark:text-zinc-500">Sistem AI Vision mendeteksi kategori visual di bawah ini secara otomatis menggunakan Gemini Vision AI &amp; Local Pre-scan.</p>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
						<!-- NSFW & Seksual -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
								🔞 Konten Seksual &amp; Vulgar (NSFW)
							</p>
							<p class="text-[11.5px] text-slate-600 dark:text-zinc-300">
								Ketelanjangan (nude), foto pakaian minim transparan, gerakan mesum, dan gestur vulgar.
							</p>
						</div>

						<!-- OCR Judi Online -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
								🎰 Poster / Flyer Judi Online (OCR)
							</p>
							<p class="text-[11.5px] text-slate-600 dark:text-zinc-300">
								Banner slot online, logo Maxwin/Slot88, angka jackpot, dan promosi deposit judi online.
							</p>
						</div>

						<!-- Kekerasan -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
								🩸 Kekerasan Parah &amp; Darah (Gore)
							</p>
							<p class="text-[11.5px] text-slate-600 dark:text-zinc-300">
								Foto luka parah, genangan darah, tayangan penganiayaan, senjata tajam/senjata api berbahaya.
							</p>
						</div>

						<!-- Penipuan & Scam -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
								⚠️ Penipuan &amp; Scam (Phishing)
							</p>
							<p class="text-[11.5px] text-slate-600 dark:text-zinc-300">
								Tangkapan layar bukti transfer dana kaget palsu, ijazah/sertifikat rekayasa palsu, dan scam link phishing.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</PagiAdminLayout>
</template>
