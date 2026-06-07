<script setup lang="ts">
import { Link2, Linkedin, X } from "lucide-vue-next";
import QRCode from "qrcode";
import { nextTick, ref, watch } from "vue";
import Modal from "../../ui/Modal.vue";
import OptimizedImage from "../../ui/OptimizedImage.vue";

const props = defineProps<{
	show: boolean;
	user: any;
	displayRoleName: string;
	activeShareUrl: string;
}>();

const emit = defineEmits(["close", "toast"]);

const qrCanvas = ref<HTMLCanvasElement | null>(null);

const generateQrCode = async () => {
	await nextTick();
	if (qrCanvas.value) {
		try {
			await QRCode.toCanvas(qrCanvas.value, props.activeShareUrl, {
				width: 160,
				margin: 1.5,
				color: {
					dark: "#0f172a", // slate-900
					light: "#ffffff",
				},
			});
		} catch (err) {
			console.error("Failed to generate QR Code:", err);
		}
	}
};

watch(
	() => props.show,
	(newVal) => {
		if (newVal) {
			generateQrCode();
		}
	},
);

const copyProfileLink = () => {
	navigator.clipboard.writeText(props.activeShareUrl);
	emit("toast", "Tautan profil berhasil disalin ke papan klip!", "success");
};

const downloadQrCode = () => {
	if (!qrCanvas.value) return;
	const link = document.createElement("a");
	link.download = `${props.user.pagi_username || "pagi-profile"}-qr.png`;
	link.href = qrCanvas.value.toDataURL("image/png");
	link.click();
	emit("toast", "QR Code berhasil diunduh!", "success");
};
</script>

<template>
	<Modal :show="show" maxWidth="sm" @close="emit('close')">
		<div class="relative p-3 text-center flex flex-col items-center overflow-hidden">
			<!-- Colorful Gradient Glow Backdrop Aura -->
			<div class="absolute -top-14 left-1/2 -translate-x-1/2 w-64 h-32 bg-gradient-to-r from-indigo-200/40 via-purple-300/30 to-pink-300/40 dark:from-indigo-500/10 dark:via-purple-600/10 dark:to-pink-600/10 rounded-full blur-2xl pointer-events-none"></div>

			<!-- Elegant Close Button -->
			<button 
				type="button"
				@click="emit('close')" 
				class="absolute top-2 right-2 w-8 h-8 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center cursor-pointer bg-transparent border-none transition-colors z-10"
				aria-label="Close modal"
			>
				<X class="w-5 h-5" />
			</button>

			<!-- Glassmorphic Virtual Business Card Preview -->
			<div class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/40 backdrop-blur-md flex flex-col items-center p-5 text-center mb-6 shadow-3xs z-5 relative overflow-hidden">
				<!-- Avatar -->
				<div class="w-18 h-18 rounded-full border-4 border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 mt-3 relative z-10 shadow-xs">
					<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" alt="Avatar" className="w-full h-full object-cover" />
					<span v-else class="text-indigo-600 dark:text-indigo-400 font-extrabold text-lg">{{ user.name.charAt(0).toUpperCase() }}</span>
				</div>

				<!-- User Info -->
				<div class="mt-3 relative z-10">
					<h4 class="text-sm font-black text-slate-800 dark:text-white flex items-center justify-center gap-1">
						{{ user.name }}
						<img src="/premium.svg" class="w-4 h-4 select-none shrink-0" alt="Verified" />
					</h4>
					<p class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-0.5">{{ displayRoleName }}</p>
					<p v-if="user.pagi_username" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">@{{ user.pagi_username }}</p>
				</div>

				<!-- QR Code rendered on canvas (no external API) -->
				<div class="mt-5 p-3 bg-white rounded-2xl border border-slate-150 shadow-sm shrink-0 flex items-center justify-center">
					<canvas ref="qrCanvas" class="w-[160px] h-[160px] select-none rounded-lg" />
				</div>
				<p class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2">Pindai untuk melihat profil</p>
			</div>

			<h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1 z-5">Bagikan Profil Kreatif</h3>
			<p class="text-xs font-semibold text-slate-400 dark:text-slate-505 max-w-[260px] leading-relaxed mb-6 z-5">
				Ajak komunitas Anda menjelajahi karya, keahlian, dan studi kasus Anda.
			</p>

			<!-- Quick Share Options Grid -->
			<div class="grid grid-cols-5 gap-3 w-full z-5 px-2">
				<!-- Salin Tautan -->
				<button 
					type="button"
					@click="copyProfileLink"
					class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
				>
					<div class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-355 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
						<Link2 class="w-4 h-4" />
					</div>
					<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Salin</span>
				</button>

				<!-- WhatsApp -->
				<a 
					:href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Lihat portofolio kreatif saya di FMIKOM Portal: ' + activeShareUrl)"
					target="_blank"
					@click="emit('close')"
					class="flex flex-col items-center gap-1.5 no-underline group"
				>
					<div class="w-10 h-10 rounded-full border border-emerald-200/50 bg-[#e8f5e9]/50 hover:bg-[#c8e6c9]/50 dark:border-emerald-900/30 dark:bg-[#1b5e20]/20 dark:hover:bg-[#1b5e20]/40 text-emerald-600 dark:text-emerald-450 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
						<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.173 1.24 8.424 3.492 2.25 2.253 3.487 5.244 3.487 8.423 0 6.578-5.323 11.902-11.89 11.902-2.003 0-3.974-.505-5.724-1.468L0 24zm6.54-5.3c1.666.989 3.32 1.488 5.304 1.488 5.485 0 9.948-4.468 9.95-9.95.002-2.656-1.03-5.153-2.906-7.03C17.068 1.332 14.576.3 11.92.3c-5.485 0-9.95 4.467-9.953 9.95 0 1.996.505 3.655 1.5 5.313L2.3 21.7l6.297-1.652z" />
						</svg>
					</div>
					<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">WhatsApp</span>
				</a>

				<!-- Telegram -->
				<a 
					:href="'https://t.me/share/url?url=' + encodeURIComponent(activeShareUrl) + '&text=' + encodeURIComponent('Lihat portofolio kreatif saya di FMIKOM Portal')"
					target="_blank"
					@click="emit('close')"
					class="flex flex-col items-center gap-1.5 no-underline group"
				>
					<div class="w-10 h-10 rounded-full border border-sky-200/50 bg-[#e1f5fe]/50 hover:bg-[#b3e5fc]/50 dark:border-sky-900/30 dark:bg-[#01579b]/20 dark:hover:bg-[#01579b]/40 text-sky-600 dark:text-sky-450 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
						<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
							<path d="M9.417 15.181l-.397 5.584c.568 0 .814-.244 1.109-.537l2.663-2.545 5.518 4.041c1.012.564 1.725.267 1.998-.931l3.622-16.972.001-.001c.321-1.496-.541-2.081-1.527-1.714l-21.29 8.151c-1.453.564-1.431 1.374-.247 1.741l5.475 1.71 12.74-7.977c.599-.396 1.147-.183.699.213z" />
						</svg>
					</div>
					<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Telegram</span>
				</a>

				<!-- LinkedIn -->
				<a 
					:href="'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(activeShareUrl)"
					target="_blank"
					@click="emit('close')"
					class="flex flex-col items-center gap-1.5 no-underline group"
				>
					<div class="w-10 h-10 rounded-full border border-blue-200/50 bg-[#e8f4fd]/50 hover:bg-[#d0e8fc]/50 dark:border-blue-900/30 dark:bg-[#0b3c5d]/20 dark:hover:bg-[#0b3c5d]/40 text-[#0077b5] dark:text-[#32b0f5] flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
						<Linkedin class="w-4 h-4" />
					</div>
					<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">LinkedIn</span>
				</a>

				<!-- Unduh QR -->
				<button 
					type="button"
					@click="downloadQrCode"
					class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
				>
					<div class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-355 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
						</svg>
					</div>
					<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">QR Code</span>
				</button>
			</div>
		</div>
	</Modal>
</template>
