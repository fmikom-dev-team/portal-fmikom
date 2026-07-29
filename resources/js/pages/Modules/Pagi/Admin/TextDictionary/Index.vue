<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { BookOpen, FileText, Plus, Shield, Trash2 } from "lucide-vue-next";
import { ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	commentCensorMode?: string;
	customBannedWords?: string[];
}>();

const form = useForm({
	commentCensorMode: props.commentCensorMode ?? "reject",
	customBannedWords: props.customBannedWords ?? [],
	autoModeration: true,
	enableLocalEngine: true,
});

const newWord = ref("");

const addWord = () => {
	const word = newWord.value.trim().toLowerCase();
	if (word && !form.customBannedWords.includes(word)) {
		form.customBannedWords.push(word);
		newWord.value = "";
	}
};

const removeWord = (index: number) => {
	form.customBannedWords.splice(index, 1);
};

const saveDictionary = () => {
	form.post("/pagi/admin/moderation/settings", {
		preserveScroll: true,
	});
};
</script>

<template>
	<PagiAdminLayout title="Kamus Kata Teks Terlarang">
		<div class="space-y-6 max-w-5xl mx-auto">
			<!-- Header -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-sm">
				<div class="flex items-center gap-3.5">
					<div class="h-11 w-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
						<FileText class="w-6 h-6" />
					</div>
					<div>
						<h1 class="text-xl font-bold text-slate-800 dark:text-zinc-100">Kamus Kata Teks Terlarang</h1>
						<p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-0.5">Kelola daftar kata terlarang, kata kasar (profanity), dan kata kunci judi online/slot untuk pemindaian teks instan.</p>
					</div>
				</div>
				<button
					@click="saveDictionary"
					:disabled="form.processing"
					class="rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 px-5 py-2.5 text-[13px] font-bold text-white transition-all shadow-sm shrink-0"
				>
					{{ form.processing ? 'Menyimpan...' : '💾 Simpan Kamus Teks' }}
				</button>
			</div>

			<!-- Form Penambahan Kata -->
			<div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 p-6 space-y-5">
				<div>
					<label class="block text-[13px] font-bold text-slate-800 dark:text-zinc-200 mb-1">
						➕ Tambah Kata Terlarang Baru
					</label>
					<p class="text-[12px] text-slate-500 dark:text-zinc-400 mb-3">
						Ketik kata kasar, judi online, atau plesetan leetspeak yang ingin dilarang (misal: <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">anjing</code>, <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">5l0t</code>, <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[11px]">gacor</code>).
					</p>
					<div class="flex items-center gap-2">
						<input
							v-model="newWord"
							@keydown.enter.prevent="addWord"
							type="text"
							placeholder="Ketik kata terlarang lalu tekan Enter..."
							class="flex-1 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/60 px-4 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
						/>
						<button
							type="button"
							@click="addWord"
							class="rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 px-4 py-2.5 text-[13px] font-bold text-white transition-all shadow-sm flex items-center gap-1.5 shrink-0"
						>
							<Plus class="w-4 h-4" /> Tambah Kata
						</button>
					</div>
				</div>

				<!-- Mode Tindakan -->
				<div class="pt-4 border-t border-slate-100 dark:border-zinc-800">
					<label class="block text-[13px] font-bold text-slate-800 dark:text-zinc-200 mb-1.5">
						🛡️ Mode Penanganan Pesan & Komentar Melanggar:
					</label>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
						<label :class="['flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition-all', form.commentCensorMode === 'reject' ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-slate-50 dark:bg-zinc-800/40 border-slate-200 dark:border-zinc-700']">
							<input v-model="form.commentCensorMode" type="radio" value="reject" class="text-indigo-600 focus:ring-indigo-500" />
							<div>
								<p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Blokir & Ganti Bubble Komunitas (Rekomendasi)</p>
								<p class="text-[11px] text-slate-500 dark:text-zinc-400">Pesan ditolak dan diganti gelembung 🛡️ "Pesan ini melanggar kebijakan komunitas".</p>
							</div>
						</label>
						<label :class="['flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition-all', form.commentCensorMode === 'censor' ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-slate-50 dark:bg-zinc-800/40 border-slate-200 dark:border-zinc-700']">
							<input v-model="form.commentCensorMode" type="radio" value="censor" class="text-indigo-600 focus:ring-indigo-500" />
							<div>
								<p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Sensor Karakter (***)</p>
								<p class="text-[11px] text-slate-500 dark:text-zinc-400">Mengganti huruf kata terlarang menjadi tanda bintang (misal: a***ng).</p>
							</div>
						</label>
					</div>
				</div>

				<!-- Daftar Tag Kata Terlarang Kustom -->
				<div class="pt-4 border-t border-slate-100 dark:border-zinc-800">
					<div class="flex items-center justify-between mb-3">
						<h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-200 flex items-center gap-2">
							<BookOpen class="w-4 h-4 text-indigo-500" />
							Daftar Kata Terlarang Kustom Admin ({{ form.customBannedWords.length }})
						</h3>
					</div>

					<div class="flex flex-wrap gap-2">
						<span
							v-for="(word, idx) in form.customBannedWords"
							:key="idx"
							class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-300 text-[12px] font-bold shadow-xs"
						>
							🚫 {{ word }}
							<button type="button" @click="removeWord(idx)" class="text-indigo-400 hover:text-rose-500 transition-colors ml-1 font-bold">
								&times;
							</button>
						</span>
						<span v-if="form.customBannedWords.length === 0" class="text-[12px] italic text-slate-400 dark:text-zinc-500 py-2">
							Belum ada kata terlarang kustom tambahan.
						</span>
					</div>
				</div>

				<!-- Kamus Bawaan Sistem (Auto-Active) -->
				<div class="pt-5 border-t border-slate-100 dark:border-zinc-800 space-y-4">
					<div class="flex items-center gap-2">
						<div class="h-6 w-6 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
							⚡
						</div>
						<div>
							<h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Kamus Kata Bawaan Sistem (Aktif Otomatis)</h3>
							<p class="text-[11px] text-slate-400 dark:text-zinc-500">Kata-kata di bawah ini secara otomatis discan & dinormalisasi dari leetspeak (misal: <code class="px-1 py-0.2 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[10px]">5l0t</code> &rarr; <code class="px-1 py-0.2 bg-slate-100 dark:bg-zinc-800 rounded font-mono text-[10px]">slot</code>).</p>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
						<!-- Judi Online -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
								🎰 Judi Online &amp; Slot (14 Kata)
							</p>
							<div class="flex flex-wrap gap-1.5">
								<span v-for="w in ['slot', 'slot88', 'gacor', 'maxwin', 'pragmatic', 'zeus', 'rtp', 'depo', 'wd', 'scatter', 'judol', 'judi', 'judionline', 'pragmaticplay']" :key="w" class="px-2 py-0.5 rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-[11px] font-mono text-slate-700 dark:text-zinc-300">
									{{ w }}
								</span>
							</div>
						</div>

						<!-- Profanity -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
								🔞 Profanity / Kata Kasar (21 Kata)
							</p>
							<div class="flex flex-wrap gap-1.5">
								<span v-for="w in ['anjing', 'babi', 'bangsat', 'kontol', 'memek', 'ngentot', 'jancok', 'asu', 'dancok', 'taik', 'tai', 'tolol', 'goblok', 'bajingan', 'kampang', 'itil', 'pantek', 'peler', 'biadab', 'pepek', 'kimak']" :key="w" class="px-2 py-0.5 rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-[11px] font-mono text-slate-700 dark:text-zinc-300">
									{{ w }}
								</span>
							</div>
						</div>

						<!-- Seksual -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
								🔞 Konten Seksual &amp; Vulgar (10 Kata)
							</p>
							<div class="flex flex-wrap gap-1.5">
								<span v-for="w in ['bokep', 'sange', 'vcs', 'porno', 'nude', 'telanjang', 'mesum', 'openbo', 'bo_real', 'vid_viral_mesum']" :key="w" class="px-2 py-0.5 rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-[11px] font-mono text-slate-700 dark:text-zinc-300">
									{{ w }}
								</span>
							</div>
						</div>

						<!-- Ancaman & Harassment -->
						<div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 space-y-2">
							<p class="text-[11px] font-black uppercase text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
								⚠️ Ancaman &amp; Perundungan (11 Frasa)
							</p>
							<div class="flex flex-wrap gap-1.5">
								<span v-for="w in ['tolol banget', 'goblok banget', 'jelek banget', 'cacat', 'autis', 'banci', 'bencong', 'tak pateni', 'gua bunuh', 'mati aja', 'pengen tak bunuh']" :key="w" class="px-2 py-0.5 rounded-md bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-[11px] font-mono text-slate-700 dark:text-zinc-300">
									{{ w }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</PagiAdminLayout>
</template>
