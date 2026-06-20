<script setup lang="ts">
import { Check, Copy, Download, Link2, Share2, X } from "lucide-vue-next";
import QRCode from "qrcode";
import linkedinIcon from "thesvg/linkedin";
import telegramIcon from "thesvg/telegram";
import whatsappIcon from "thesvg/whatsapp";
import xIcon from "thesvg/x";
import { computed, nextTick, onUnmounted, ref, watch } from "vue";
import OptimizedImage from "./OptimizedImage.vue";

export interface ShareProject {
	id: number;
	title: string;
	image?: string | null;
	cover_fit?: string;
}

export interface ShareUser {
	name: string;
	foto_path?: string | null;
	pagi_username?: string | null;
}

const props = defineProps<{
	show: boolean;
	user: ShareUser;
	project?: ShareProject | null;
	displayRoleName?: string;
	activeShareUrl?: string;
}>();

const emit = defineEmits<{
	(e: "close"): void;
	(
		e: "toast",
		message: string,
		type: "success" | "error" | "info" | "warning",
	): void;
}>();

const copied = ref(false);
const qrCanvas = ref<HTMLCanvasElement | null>(null);

function cleanSvg(icon: any, className = "w-4 h-4 fill-current"): string {
	let svgStr = (icon.variants?.mono ??
		icon.variants?.default ??
		icon.svg) as string;
	svgStr = svgStr
		.replace(/\bwidth="[^"]*"/g, "")
		.replace(/\bheight="[^"]*"/g, "");
	svgStr = svgStr.replace(/fill="(?!none)[^"]*"/g, 'fill="currentColor"');
	if (!svgStr.includes("class=")) {
		svgStr = svgStr.replace("<svg", `<svg class="${className}"`);
	} else {
		svgStr = svgStr.replace(/\bclass="[^"]*"/g, `class="${className}"`);
	}
	return svgStr;
}

const whatsappSvg = computed(() =>
	cleanSvg(whatsappIcon, "w-4.5 h-4.5 fill-current"),
);
const telegramSvg = computed(() =>
	cleanSvg(telegramIcon, "w-4.5 h-4.5 fill-current"),
);
const linkedinSvg = computed(() =>
	cleanSvg(linkedinIcon, "w-4 h-4 fill-current"),
);
const xSvg = computed(() => cleanSvg(xIcon, "w-4 h-4 fill-current"));

const isVideoUrl = (url: string | null | undefined): boolean => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov"].includes(ext || "");
};

const activeUrl = computed(() => {
	return props.activeShareUrl || window.location.href;
});

const generateQrCode = async () => {
	await nextTick();
	if (qrCanvas.value && !props.project) {
		try {
			await QRCode.toCanvas(qrCanvas.value, activeUrl.value, {
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
		document.body.style.overflow = newVal ? "hidden" : "";
		if (newVal && !props.project) {
			generateQrCode();
		}
	},
	{ immediate: true },
);

onUnmounted(() => {
	document.body.style.overflow = "";
});

const copyLink = async () => {
	try {
		await navigator.clipboard.writeText(activeUrl.value);
		copied.value = true;
		emit("toast", "Link successfully copied!", "success");
		setTimeout(() => {
			copied.value = false;
		}, 2000);
	} catch (err) {
		emit("toast", "Failed to copy link.", "error");
	}
};

const downloadQrCode = () => {
	if (!qrCanvas.value) return;
	const link = document.createElement("a");
	link.download = `${props.user.pagi_username || "pagi-profile"}-qr.png`;
	link.href = qrCanvas.value.toDataURL("image/png");
	link.click();
	emit("toast", "QR Code successfully downloaded!", "success");
};

const whatsAppUrl = computed(() => {
	const text = props.project
		? `Lihat karya "${props.project.title}" oleh ${props.user.name} di FMIKOM Portal: `
		: `Lihat portofolio kreatif ${props.user.name} di FMIKOM Portal: `;
	return `https://api.whatsapp.com/send?text=${encodeURIComponent(text + activeUrl.value)}`;
});

const telegramUrl = computed(() => {
	const text = props.project
		? `Lihat karya "${props.project.title}" oleh ${props.user.name} di FMIKOM Portal`
		: `Lihat portofolio kreatif ${props.user.name} di FMIKOM Portal`;
	return `https://t.me/share/url?url=${encodeURIComponent(activeUrl.value)}&text=${encodeURIComponent(text)}`;
});

const linkedInUrl = computed(
	() =>
		`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(activeUrl.value)}`,
);

const twitterUrl = computed(() => {
	const text = props.project
		? `Saya baru saja memposting karya baru di FMIKOM Portal: ${props.project.title}`
		: `Lihat portofolio kreatif saya di FMIKOM Portal`;
	return `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(activeUrl.value)}`;
});
</script>

<template>
	<Teleport to="body">
		<!-- Backdrop -->
		<Transition
			enter-active-class="transition ease-out duration-200"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition ease-in duration-150"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-[20000] bg-black/40 backdrop-blur-[4px]"
				@click.self="emit('close')"
			/>
		</Transition>

		<!-- Modal Panel -->
		<Transition
			enter-active-class="transition ease-out duration-300 cubic-bezier(0.34, 1.56, 0.64, 1)"
			enter-from-class="opacity-0 scale-95 translate-y-4"
			enter-to-class="opacity-100 scale-100 translate-y-0"
			leave-active-class="transition ease-in duration-200"
			leave-from-class="opacity-100 scale-100 translate-y-0"
			leave-to-class="opacity-0 scale-95 translate-y-4"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-[20001] flex items-center justify-center p-4 pointer-events-none"
			>
				<div class="w-full max-w-[420px] bg-white dark:bg-zinc-950 rounded-[32px] border border-slate-200/80 dark:border-zinc-800/80 shadow-[0_24px_60px_rgba(0,0,0,0.15)] dark:shadow-[0_24px_60px_rgba(0,0,0,0.5)] pointer-events-auto overflow-hidden relative">
					
					<!-- Colorful Glow Backdrop Aura (only for profile mode) -->
					<div v-if="!project" class="absolute -top-16 left-1/2 -translate-x-1/2 w-72 h-36 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 dark:from-indigo-500/5 dark:via-purple-600/5 dark:to-pink-600/5 rounded-full blur-3xl pointer-events-none"></div>

					<!-- Close Button -->
					<button
						@click="emit('close')"
						class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-colors z-50 cursor-pointer border-none bg-transparent"
					>
						<X class="w-4.5 h-4.5" />
					</button>

					<!-- Modal Contents -->
					<div class="p-6">
						<!-- Header Row -->
						<div class="flex items-center gap-3 mb-5 pr-6">
							<!-- User Avatar -->
							<div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-900 shrink-0 border border-slate-200/60 dark:border-zinc-800 flex items-center justify-center shadow-xs">
								<img
									v-if="user.foto_path"
									:src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path"
									class="w-full h-full object-cover"
									alt="Avatar"
								/>
								<div v-else class="w-full h-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-950 text-indigo-650 dark:text-indigo-300 font-extrabold text-sm">
									{{ user.name?.charAt(0)?.toUpperCase() }}
								</div>
							</div>

							<!-- Names -->
							<div class="flex-1 min-w-0 text-left">
								<p class="text-xs font-black text-slate-900 dark:text-white leading-tight flex items-center gap-1">
									{{ user.name }}
									<img src="/premium.svg" class="w-3.5 h-3.5 select-none shrink-0" alt="Verified" />
								</p>
								<p class="text-[10px] text-slate-404 dark:text-zinc-500 font-bold uppercase tracking-wider mt-0.5">
									{{ project ? 'now sharing a project' : displayRoleName || 'member' }}
								</p>
							</div>
						</div>

						<!-- Core Showcase Card -->
						<!-- Mode A: Project Card Preview -->
						<div
							v-if="project"
							class="mb-6 rounded-2xl overflow-hidden bg-slate-100 dark:bg-zinc-900 aspect-[16/9] relative border border-slate-100 dark:border-zinc-800 shadow-inner"
						>
							<video
								v-if="project.image && isVideoUrl(project.image)"
								:src="project.image"
								autoplay muted loop playsinline
								class="w-full h-full object-cover"
							/>
							<img
								v-else-if="project.image"
								:src="project.image"
								:class="['w-full h-full', project.cover_fit === 'contain' ? 'object-contain' : 'object-cover']"
								alt="Project thumbnail"
							/>
							<div v-else class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-zinc-900 text-slate-400">
								<Share2 class="w-8 h-8 stroke-1.5" />
							</div>
							<div class="absolute inset-x-0 bottom-0 h-12 bg-linear-to-t from-black/20 to-transparent pointer-events-none" />
						</div>

						<!-- Mode B: Glassmorphic Virtual Card with QR Code -->
						<div
							v-else
							class="mb-6 rounded-2xl border border-slate-200/60 dark:border-zinc-800/60 bg-slate-50/50 dark:bg-zinc-900/30 backdrop-blur-md flex flex-col items-center p-5 text-center relative overflow-hidden"
						>
							<!-- User Big Avatar -->
							<div class="w-16 h-16 rounded-full border-4 border-white dark:border-zinc-950 bg-slate-100 dark:bg-zinc-900 overflow-hidden flex items-center justify-center shrink-0 shadow-xs relative z-10">
								<img
									v-if="user.foto_path"
									:src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path"
									class="w-full h-full object-cover"
									alt="Avatar"
								/>
								<div v-else class="w-full h-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-950 text-indigo-650 dark:text-indigo-300 font-black text-lg">
									{{ user.name?.charAt(0)?.toUpperCase() }}
								</div>
							</div>

							<div class="mt-2.5 relative z-10">
								<h4 class="text-xs font-black text-slate-800 dark:text-white leading-tight">
									{{ user.name }}
								</h4>
								<p v-if="user.pagi_username" class="text-[9px] font-bold text-slate-404 dark:text-zinc-500 mt-0.5">@{{ user.pagi_username }}</p>
							</div>

							<!-- Client-side generated QR Code canvas -->
							<div class="mt-4 p-2 bg-white rounded-2xl border border-slate-150 shadow-sm shrink-0 flex items-center justify-center">
								<canvas ref="qrCanvas" class="w-[140px] h-[140px] select-none rounded-lg" />
							</div>
							<p class="text-[8px] font-black text-slate-404 dark:text-zinc-500 uppercase tracking-widest mt-2 leading-none">Pindai untuk melihat profil</p>
						</div>

						<!-- Explanatory text -->
						<div class="text-left mb-5">
							<div class="flex items-center gap-2 mb-1">
								<Share2 class="w-4.5 h-4.5 text-indigo-500 dark:text-indigo-400 shrink-0" />
								<h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
									{{ project ? 'Share this project' : 'Share this profile' }}
								</h3>
							</div>
							<p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
								{{ project ? "Bagikan karya kreatif ini dengan rekan kerja atau komunitas Anda." : "Bagikan portofolio Anda untuk mempromosikan keahlian dan karya Anda." }}
							</p>
						</div>

						<!-- Social sharing options -->
						<div class="grid grid-cols-5 gap-2.5">
							<!-- WhatsApp -->
							<a
								:href="whatsAppUrl"
								target="_blank"
								@click="emit('close')"
								title="WhatsApp"
								class="flex flex-col items-center gap-1.5 no-underline group cursor-pointer"
							>
								<div class="w-10 h-10 rounded-full border border-emerald-250 bg-emerald-50 dark:border-emerald-950 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white dark:group-hover:bg-emerald-500/30 transition-all duration-300" v-html="whatsappSvg"></div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-emerald-500 transition-colors">WhatsApp</span>
							</a>

							<!-- Telegram -->
							<a
								:href="telegramUrl"
								target="_blank"
								@click="emit('close')"
								title="Telegram"
								class="flex flex-col items-center gap-1.5 no-underline group cursor-pointer"
							>
								<div class="w-10 h-10 rounded-full border border-sky-250 bg-sky-50 dark:border-sky-950 dark:bg-sky-950/20 text-sky-600 dark:text-sky-400 flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-sky-500 group-hover:text-white dark:group-hover:bg-sky-500/30 transition-all duration-300" v-html="telegramSvg"></div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-sky-500 transition-colors">Telegram</span>
							</a>

							<!-- LinkedIn -->
							<a
								:href="linkedInUrl"
								target="_blank"
								@click="emit('close')"
								title="LinkedIn"
								class="flex flex-col items-center gap-1.5 no-underline group cursor-pointer"
							>
								<div class="w-10 h-10 rounded-full border border-blue-250 bg-blue-50 dark:border-blue-950 dark:bg-blue-950/20 text-[#0077b5] dark:text-[#32b0f5] flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-[#0077b5] group-hover:text-white dark:group-hover:bg-[#0077b5]/30 transition-all duration-300" v-html="linkedinSvg"></div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-blue-600 transition-colors">LinkedIn</span>
							</a>

							<!-- X / Twitter -->
							<a
								:href="twitterUrl"
								target="_blank"
								@click="emit('close')"
								title="Share on X"
								class="flex flex-col items-center gap-1.5 no-underline group cursor-pointer"
							>
								<div class="w-10 h-10 rounded-full border border-slate-250 bg-slate-50 dark:border-zinc-800 dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-black group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-black transition-all duration-300" v-html="xSvg"></div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-black dark:group-hover:text-white transition-colors">X / Twitter</span>
							</a>

							<!-- Action C: Copy Link (or Download QR Code for profile mode) -->
							<button
								v-if="!project"
								type="button"
								@click="downloadQrCode"
								class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
							>
								<div class="w-10 h-10 rounded-full border border-indigo-200 bg-indigo-50/50 dark:border-indigo-950 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-indigo-650 group-hover:text-white transition-all duration-300">
									<Download class="w-4 h-4" />
								</div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-indigo-650 transition-colors">Download QR</span>
							</button>

							<button
								v-else
								type="button"
								@click="copyLink"
								class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
							>
								<div class="w-10 h-10 rounded-full border border-slate-200 bg-white hover:bg-slate-50 dark:border-zinc-800 dark:bg-zinc-950 text-slate-650 dark:text-zinc-200 flex items-center justify-center shadow-3xs group-hover:scale-110 group-hover:bg-indigo-650 group-hover:text-white transition-all duration-300">
									<Check v-if="copied" class="w-4.5 h-4.5 text-emerald-500" />
									<Link2 v-else class="w-4.5 h-4.5" />
								</div>
								<span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 group-hover:text-indigo-650 transition-colors">{{ copied ? 'Copied!' : 'Copy Link' }}</span>
							</button>
						</div>

						<!-- Unified Copy Link row at the very bottom for profile mode -->
						<div v-if="!project" class="mt-5 pt-4 border-t border-slate-100 dark:border-zinc-900">
							<div class="flex items-center gap-2 bg-slate-50 dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800/80 rounded-xl p-1.5 pl-3.5">
								<span class="text-[10px] font-bold text-slate-450 dark:text-zinc-500 truncate flex-1 text-left">{{ activeUrl }}</span>
								<button
									@click="copyLink"
									class="px-4.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-wider transition-colors cursor-pointer border-none shadow-sm flex items-center gap-1.5 shrink-0"
								>
									<Check v-if="copied" class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
									<Copy v-else class="w-3.5 h-3.5 shrink-0" />
									<span>{{ copied ? 'Copied' : 'Copy' }}</span>
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>
		</Transition>
	</Teleport>
</template>
