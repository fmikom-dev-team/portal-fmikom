<script setup lang="ts">
import html2canvas from "html2canvas-pro";
import QRCode from "qrcode";
import { computed, onMounted, ref, watch } from "vue";

const props = defineProps<{
	profileUser: {
		id: number;
		name: string;
		email: string;
		tanggal_lahir?: string;
		pagi_username?: string;
		bio?: string;
		avatar?: string;
		works_count?: number;
		followers_count?: number;
		nim_nip?: string | null;
		program_studi?: string | null;
		fakultas?: string | null;
	};
}>();

const isVerticalFlipped = ref(false);
const isHorizontalFlipped = ref(false);
const isDarkTheme = ref(true);
const qrCodeDataUrl = ref("");
const isDownloading = ref(false);

// Refs for the actual card faces (not the flip wrapper)
const frontCardRef = ref<HTMLElement | null>(null);
const backCardRef = ref<HTMLElement | null>(null);
const horizontalFrontCardRef = ref<HTMLElement | null>(null);
const horizontalBackCardRef = ref<HTMLElement | null>(null);

const cardCoverImage = computed(() => {
	if (
		props.profileUser.avatar &&
		!props.profileUser.avatar.includes("api.dicebear.com")
	) {
		return props.profileUser.avatar;
	}
	return "https://images.unsplash.com/photo-1506366197612-8acc64095e53?q=80&w=480&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";
});

const profileUrl = computed(() => {
	const base = window.location.origin;
	if (props.profileUser.pagi_username) {
		return `${base}/pagi/${props.profileUser.pagi_username}`;
	}
	return `${base}/pagi/profile/${props.profileUser.id}`;
});

const generateQrCode = async () => {
	try {
		// Use white dots in dark theme to match card style and avoid clashing colors
		const darkColor = isDarkTheme.value ? "#ffffff" : "#0f0f12";
		const lightColor = isDarkTheme.value ? "#0f0f12" : "#ffffff";

		qrCodeDataUrl.value = await QRCode.toDataURL(profileUrl.value, {
			width: 600,
			margin: 2,
			errorCorrectionLevel: "H",
			color: { dark: darkColor, light: lightColor },
		});
	} catch (err) {
		console.error("Failed to generate QR Code", err);
	}
};

// Download card as high-quality PNG (300 DPI equivalent at 4x scale)
const downloadCard = async (type: "vertical" | "horizontal") => {
	const isFlippedVal =
		type === "vertical" ? isVerticalFlipped.value : isHorizontalFlipped.value;
	const target =
		type === "vertical"
			? isFlippedVal
				? backCardRef.value
				: frontCardRef.value
			: isFlippedVal
				? horizontalBackCardRef.value
				: horizontalFrontCardRef.value;
	if (!target) return;

	isDownloading.value = true;
	try {
		const canvas = await html2canvas(target, {
			scale: 4, // 4x = ~300 DPI for printing
			useCORS: true,
			allowTaint: false,
			backgroundColor: null,
			logging: false,
			imageTimeout: 30000,
			onclone: (doc) => {
				// 1. Remove all Y-rotation / mirroring transforms from inner container and both faces in the clone
				if (type === "vertical") {
					const inner = doc.querySelector(".flip-card-inner") as HTMLElement;
					if (inner) {
						inner.style.transform = "none";
						inner.style.transition = "none";
					}
					const front = doc.querySelector(".flip-card-front") as HTMLElement;
					if (front) {
						front.style.transform = "none";
						front.style.position = "relative";
					}
					const back = doc.querySelector(".flip-card-back") as HTMLElement;
					if (back) {
						back.style.transform = "none";
						back.style.position = "relative";
					}
				} else {
					const inner = doc.querySelector(
						".horizontal-flip-inner",
					) as HTMLElement;
					if (inner) {
						inner.style.transform = "none";
						inner.style.transition = "none";
					}
					const front = doc.querySelector(
						".horizontal-card-front",
					) as HTMLElement;
					if (front) {
						front.style.transform = "none";
						front.style.position = "relative";
					}
					const back = doc.querySelector(
						".horizontal-card-back",
					) as HTMLElement;
					if (back) {
						back.style.transform = "none";
						back.style.position = "relative";
					}
				}

				// 2. Inject a high-quality blurred background image to simulate backdrop-blur
				// since html2canvas doesn't support backdrop-filter (for vertical card front only)
				if (type === "vertical" && !isFlippedVal) {
					const cardInner = doc.querySelector(
						".flip-card-front .card__inner",
					) as HTMLElement;
					const coverImg = doc.querySelector(
						".flip-card-front .card__cover",
					) as HTMLImageElement;
					if (cardInner && coverImg) {
						const blurContainer = doc.createElement("div");
						blurContainer.style.position = "absolute";
						blurContainer.style.bottom = "0";
						blurContainer.style.left = "0";
						blurContainer.style.right = "0";
						blurContainer.style.height = "48%";
						blurContainer.style.overflow = "hidden";
						blurContainer.style.zIndex = "1";

						const blurImg = doc.createElement("img");
						blurImg.src = coverImg.src;
						blurImg.style.position = "absolute";
						blurImg.style.bottom = "0";
						blurImg.style.left = "0";
						blurImg.style.width = "100%";
						blurImg.style.height = "208%"; // Matches Card ratio perfectly from bottom
						blurImg.style.objectFit = "cover";
						blurImg.style.filter = "blur(20px)";
						blurImg.style.transform = "scale(1.15)"; // Prevents white edges

						blurContainer.appendChild(blurImg);
						coverImg.parentNode?.insertBefore(
							blurContainer,
							coverImg.nextSibling,
						);
					}
				}
			},
		});

		const link = document.createElement("a");
		const side = isFlippedVal ? "back" : "front";
		const theme = isDarkTheme.value ? "dark" : "light";
		link.download = `fmikom-id-${type}-${props.profileUser.pagi_username || props.profileUser.id}-${side}-${theme}.png`;
		link.href = canvas.toDataURL("image/png", 1.0);
		link.click();
	} catch (err) {
		console.error("Download failed", err);
	} finally {
		isDownloading.value = false;
	}
};

watch([isDarkTheme, profileUrl], () => {
	generateQrCode();
});

onMounted(generateQrCode);
</script>

<template>
	<div class="space-y-6 fmikom-id-workspace">
		<!-- Header with Theme Selector -->
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
			<div class="space-y-1 text-left">
				<h2 class="text-sm font-black text-[#14171f] tracking-tight">FMIKOM Digital Student ID</h2>
				<p class="text-[11px] text-[#677084] font-medium leading-relaxed">
					Klik pada kartu untuk membalikkan dan melihat QR Code profil Anda.
				</p>
			</div>

			<!-- Switch Theme Option -->
			<div class="flex items-center gap-1.5 self-start sm:self-auto bg-slate-100/80 p-1 rounded-full border border-slate-200/50">
				<button 
					@click="isDarkTheme = true" 
					class="px-4 py-1.5 text-[10px] font-bold rounded-full transition-all cursor-pointer border-none"
					:class="isDarkTheme ? 'bg-[#14171f] text-white shadow-xs' : 'bg-transparent text-[#677084] hover:text-[#14171f]'"
				>
					Gelap (Dark)
				</button>
				<button 
					@click="isDarkTheme = false" 
					class="px-4 py-1.5 text-[10px] font-bold rounded-full transition-all cursor-pointer border-none"
					:class="!isDarkTheme ? 'bg-white text-[#14171f] shadow-xs' : 'bg-transparent text-[#677084] hover:text-[#14171f]'"
				>
					Cerah (Light)
				</button>
			</div>
		</div>

		<!-- Cards Display Canvas -->
		<div class="my-8 flex flex-col lg:flex-row items-center lg:items-start justify-center gap-12 lg:gap-16 select-none w-full">
			<!-- ==================== VERTICAL CARD COLUMN ==================== -->
			<div class="flex flex-col items-center gap-6 w-full max-w-[19.5rem]">
				<span class="text-xs font-black tracking-wider uppercase opacity-40">Tipe Vertikal</span>
				<div class="perspective-container flex justify-center w-full">
					<div 
						class="flip-card-inner cursor-pointer" 
						:class="{ 'is-flipped': isVerticalFlipped }"
						@click="isVerticalFlipped = !isVerticalFlipped"
					>
						<!-- ========== FRONT FACE ========== -->
						<div ref="frontCardRef" data-card-face class="flip-card-front card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
							<div class="card__inner">
								<!-- Cover photo -->
								<img class="card__cover" :src="cardCoverImage" alt="ID Card Cover">

								<div class="card__body">
									<!-- Name -->
									<h2 class="card__header text-left">
										{{ props.profileUser.name }}
									</h2>

									<!-- Program Studi -->
									<p class="text-[10px] font-bold text-left mt-1 leading-normal prodi-text">
										{{ props.profileUser.program_studi || 'Teknik Informatika' }}
									</p>

									<!-- NIM -->
									<p class="text-[10px] font-semibold text-left leading-normal nim-text">
										NIM: {{ props.profileUser.nim_nip || '-' }}
									</p>

									<div class="flex items-end justify-between mt-3 w-full gap-2">
										<div class="flex-1">
											<p class="text-[9px] font-extrabold uppercase tracking-wide leading-none text-left">
												{{ props.profileUser.fakultas || 'Fakultas Matematika dan Ilmu Komputer' }}
											</p>
										</div>

										<img
											:src="isDarkTheme ? '/cek1.svg?v=2' : '/cek2.svg?v=2'"
											alt="FMIKOM Logo"
											class="h-4 shrink-0 select-none"
											style="width: 82px; object-fit: contain;"
											draggable="false"
										>
									</div>
								</div>
							</div>
						</div>

						<!-- ========== BACK FACE ========== -->
						<div ref="backCardRef" data-card-face class="flip-card-back card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
							<div class="back-panel p-6 flex flex-col justify-between h-full">
								<!-- Top Branding -->
								<div class="flex justify-between items-start w-full">
									<div class="text-left">
										<h3 class="text-sm font-black tracking-wider branding-title">FMIKOM PORTAL</h3>
										<span class="text-[9px] font-bold uppercase tracking-widest block branding-subtitle">Kartu Mahasiswa Digital</span>
									</div>
									<div class="w-8 h-8 rounded-full flex items-center justify-center border branding-avatar">
										<span class="text-xs font-black">FM</span>
									</div>
								</div>

								<!-- QR Code — centered, large, NO blur overlay -->
								<div class="flex-1 flex flex-col items-center justify-center gap-3 my-4">
									<div 
										class="qr-wrapper relative flex items-center justify-center overflow-hidden"
										:class="isDarkTheme ? 'bg-[#0f0f12] border-zinc-800' : 'bg-white border-zinc-200'"
									>
										<!-- QR Code image — crisp, no blur -->
										<img 
											v-if="qrCodeDataUrl"
											:src="qrCodeDataUrl"
											alt="Profile QR Code"
											class="qr-image rounded-[0.75rem]"
										>
										<div v-else class="qr-placeholder animate-pulse" :class="isDarkTheme ? 'bg-zinc-900' : 'bg-zinc-100'"></div>

										<!-- Center FM badge -->
										<div 
											v-if="qrCodeDataUrl"
											class="absolute w-9 h-9 rounded-xl shadow-md flex items-center justify-center border fm-badge pointer-events-none"
											:class="isDarkTheme ? 'bg-[#111215] border-blue-500/40 text-blue-400' : 'bg-white border-blue-200 text-[#2563eb]'"
										>
											<span class="text-[10px] font-black tracking-tight">FM</span>
										</div>
									</div>

									<!-- Scan label -->
									<div class="text-center space-y-0.5">
										<p class="text-[10px] font-black tracking-wide" :class="isDarkTheme ? 'text-white' : 'text-zinc-800'">
											@{{ props.profileUser.pagi_username || 'user' }}
										</p>
										<p class="text-[8px] font-semibold uppercase tracking-widest" :class="isDarkTheme ? 'text-zinc-500' : 'text-zinc-400'">
											Pindai untuk mengunjungi profil
										</p>
									</div>
								</div>

								<!-- Bottom footer with cek logo -->
								<div class="w-full flex justify-between items-center pt-3 border-t border-line">
									<span class="text-[7px] font-bold uppercase tracking-wider" :class="isDarkTheme ? 'text-zinc-600' : 'text-zinc-400'">
										ID: {{ props.profileUser.id }}
									</span>
									<!-- cek logo on back too -->
									<img 
										:src="isDarkTheme ? '/cek1.svg?v=2' : '/cek2.svg?v=2'"
										alt="FMIKOM"
										class="h-4 shrink-0 select-none opacity-70"
										style="width: 82px; object-fit: contain;"
										draggable="false"
									>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Download Button for Vertical Card -->
				<button
					@click="downloadCard('vertical')"
					:disabled="isDownloading"
					class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-[11px] font-bold transition-all border-none cursor-pointer shadow-sm hover:shadow active:scale-98 disabled:opacity-60 disabled:cursor-not-allowed w-full mt-2"
					:class="isDarkTheme 
						? 'bg-[#14171f] text-white hover:bg-[#222834]' 
						: 'bg-[#0f172a] text-white hover:bg-[#1e293b]'"
				>
					<!-- Download icon -->
					<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
						<polyline points="7 10 12 15 17 10"/>
						<line x1="12" y1="15" x2="12" y2="3"/>
					</svg>
					<span>Unduh Sisi {{ isVerticalFlipped ? 'Belakang' : 'Depan' }}</span>
				</button>
			</div>

			<!-- ==================== HORIZONTAL CARD COLUMN ==================== -->
			<div class="flex flex-col items-center gap-6 w-full max-w-[28.5rem]">
				<span class="text-xs font-black tracking-wider uppercase opacity-40">Tipe Horizontal (ATM Card Style)</span>
				<div class="perspective-container-horizontal flex justify-center w-full">
					<div 
						class="horizontal-flip-inner cursor-pointer" 
						:class="{ 'is-flipped': isHorizontalFlipped }"
						@click="isHorizontalFlipped = !isHorizontalFlipped"
					>
						<!-- ========== FRONT FACE ========== -->
						<div ref="horizontalFrontCardRef" data-card-face class="horizontal-card-front card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
							<div class="horizontal-card-inner">
								<!-- Premium background pattern/overlay -->
								<div class="card__glass-overlay"></div>

								<div class="flex flex-col justify-between h-full p-5 relative z-10">
									<!-- Top branding & logo -->
									<div class="flex justify-between items-start w-full">
										<!-- Logo FMIKOM ID / cek -->
										<img
											:src="isDarkTheme ? '/cek1.svg?v=2' : '/cek2.svg?v=2'"
											alt="FMIKOM Logo"
											class="h-3.5 sm:h-4.5 select-none"
											style="width: 86px; object-fit: contain;"
											draggable="false"
										>
										<span class="text-[8px] font-extrabold uppercase tracking-widest text-[#2563eb]" :class="isDarkTheme ? 'text-blue-400' : 'text-blue-600'">
											STUDENT CARD
										</span>
									</div>

									<!-- Middle part: EMV Chip & Details & Photo -->
									<div class="flex items-center justify-between gap-4 my-2">
										<!-- Left section: chip, name, info -->
										<div class="flex-1 flex flex-col gap-2.5 text-left">
											<!-- Gold EMV Chip -->
											<svg class="h-5 w-7 sm:h-7 sm:w-9 rounded-xs shrink-0 shadow-xs border border-yellow-600/30" viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect width="100" height="80" rx="12" fill="url(#chip-grad-horizontal)" />
												<path d="M 0,25 H 40 V 55 H 0" stroke="#443000" stroke-width="2.5" />
												<path d="M 100,25 H 60 V 55 H 100" stroke="#443000" stroke-width="2.5" />
												<path d="M 30,0 V 80" stroke="#443000" stroke-width="2.5" />
												<path d="M 70,0 V 80" stroke="#443000" stroke-width="2.5" />
												<path d="M 40,25 H 60 V 55 H 40 Z" fill="#cfab2f" stroke="#443000" stroke-width="2.5" />
												<defs>
													<linearGradient id="chip-grad-horizontal" x1="0" y1="0" x2="100" y2="80" gradientUnits="userSpaceOnUse">
														<stop offset="0%" stop-color="#ffe891"/>
														<stop offset="50%" stop-color="#cfab2f"/>
														<stop offset="100%" stop-color="#a07d10"/>
													</linearGradient>
												</defs>
											</svg>

											<!-- Details -->
											<div class="space-y-0.5">
												<h2 class="text-[11px] sm:text-sm font-black leading-tight uppercase tracking-tight text-limit-1" :class="isDarkTheme ? 'text-white' : 'text-[#14171f]'">
													{{ props.profileUser.name }}
												</h2>
												<p class="text-[8px] sm:text-[9px] font-bold leading-normal prodi-text">
													{{ props.profileUser.program_studi || 'Teknik Informatika' }}
												</p>
												<p class="text-[8px] sm:text-[9px] font-semibold leading-normal nim-text">
													NIM: {{ props.profileUser.nim_nip || '-' }}
												</p>
											</div>
										</div>

										<!-- Right section: Student Photo -->
										<div class="shrink-0">
											<div class="horizontal-avatar-frame border" :class="isDarkTheme ? 'border-zinc-800 bg-zinc-900/50' : 'border-zinc-200 bg-zinc-50'">
												<img class="w-full h-full object-cover" :src="cardCoverImage" alt="Student Photo">
											</div>
										</div>
									</div>

									<!-- Bottom section: Fakultas -->
									<div class="w-full border-t pt-2 border-line text-left flex justify-between items-center">
										<p class="text-[6.5px] sm:text-[7.5px] font-extrabold uppercase tracking-wider leading-none truncate max-w-[70%]">
											{{ props.profileUser.fakultas || 'Fakultas Matematika dan Ilmu Komputer' }}
										</p>
										<span class="text-[6.5px] sm:text-[7px] font-black uppercase tracking-widest" :class="isDarkTheme ? 'text-zinc-600' : 'text-zinc-400'">
											FMIKOM ID
										</span>
									</div>
								</div>
							</div>
						</div>

						<!-- ========== BACK FACE ========== -->
						<div ref="horizontalBackCardRef" data-card-face class="horizontal-card-back card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
							<div class="horizontal-card-inner flex flex-col justify-between h-full">
								<!-- Magnetic Stripe (ATM Feel) -->
								<div class="w-full h-7 sm:h-9 bg-zinc-900 mt-3 select-none flex items-center justify-between px-5">
									<span class="text-[6px] text-zinc-600 tracking-widest font-black uppercase">MAGNETIC STRIPE MOCKUP</span>
									<span class="text-[6px] text-zinc-600 tracking-widest font-black uppercase">FMIKOM SECURE</span>
								</div>

								<!-- Middle Content: Signature Panel and QR Code side-by-side -->
								<div class="flex-1 flex items-center justify-between gap-6 px-5 py-2">
									<!-- Left: Signature Panel & Info -->
									<div class="flex-1 flex flex-col gap-2">
										<!-- Signature panel -->
										<div class="w-full h-5 sm:h-6 bg-slate-100 border border-slate-200 rounded-xs flex items-center justify-end px-2 select-none relative overflow-hidden">
											<!-- Security wave lines background -->
											<div class="absolute inset-0 opacity-10 signature-lines"></div>
											<span class="text-[7px] text-[#677084] font-bold italic z-10">Signature Panel</span>
										</div>
										<!-- Terms/Info -->
										<div class="text-left space-y-0.5 select-none">
											<p class="text-[5.5px] sm:text-[6.5px] font-semibold leading-relaxed" :class="isDarkTheme ? 'text-zinc-500' : 'text-zinc-400'">
												Kartu ini adalah kartu identitas resmi mahasiswa Fakultas Matematika dan Ilmu Komputer. Dilarang menyalahgunakan kartu ini untuk keperluan ilegal.
											</p>
										</div>
									</div>

									<!-- Right: Small QR Code -->
									<div class="shrink-0 flex flex-col items-center gap-1">
										<div 
											class="h-[3.5rem] w-[3.5rem] sm:h-[4.8rem] sm:w-[4.8rem] rounded-xl p-1 flex items-center justify-center border overflow-hidden"
											:class="isDarkTheme ? 'border-zinc-800 bg-[#0f0f12]' : 'border-zinc-200 bg-white'"
										>
											<img 
												v-if="qrCodeDataUrl"
												:src="qrCodeDataUrl"
												alt="Profile QR Code"
												class="w-full h-full object-contain image-crisp rounded-lg"
											>
											<div v-else class="w-full h-full bg-zinc-100 animate-pulse rounded-md"></div>
										</div>
										<span class="text-[6px] sm:text-[7px] font-extrabold" :class="isDarkTheme ? 'text-zinc-400' : 'text-zinc-600'">
											@{{ props.profileUser.pagi_username || 'user' }}
										</span>
									</div>
								</div>

								<!-- Bottom Info -->
								<div class="w-full flex justify-between items-center px-5 pb-3 pt-1 text-[6px] sm:text-[7px] font-extrabold uppercase tracking-wider" :class="isDarkTheme ? 'text-zinc-600' : 'text-zinc-400'">
									<span>ID: {{ props.profileUser.id }}</span>
									<span>FMIKOM PORTAL &copy; 2026</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Download Button for Horizontal Card -->
				<button
					@click="downloadCard('horizontal')"
					:disabled="isDownloading"
					class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-[11px] font-bold transition-all border-none cursor-pointer shadow-sm hover:shadow active:scale-98 disabled:opacity-60 disabled:cursor-not-allowed w-full mt-2"
					:class="isDarkTheme 
						? 'bg-[#14171f] text-white hover:bg-[#222834]' 
						: 'bg-[#0f172a] text-white hover:bg-[#1e293b]'"
				>
					<!-- Download icon -->
					<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
						<polyline points="7 10 12 15 17 10"/>
						<line x1="12" y1="15" x2="12" y2="3"/>
					</svg>
					<span>Unduh Sisi {{ isHorizontalFlipped ? 'Belakang' : 'Depan' }}</span>
				</button>
			</div>
		</div>
	</div>
</template>

<style scoped>
/* Non-critical fonts loaded locally only when the FMIKOM-ID card component is shown */
@font-face {
	font-family: 'Plus Jakarta Sans';
	font-style: normal;
	font-weight: 400 700;
	font-display: swap;
	src: url('/fonts/plus-jakarta-sans-latin-400-normal.woff2') format('woff2');
}
@font-face {
	font-family: 'Outfit';
	font-style: normal;
	font-weight: 400 700;
	font-display: swap;
	src: url('/fonts/outfit-latin-400-normal.woff2') format('woff2');
}

.fmikom-id-workspace {
	font-family: 'Plus Jakarta Sans', 'Outfit', system-ui, -apple-system, sans-serif !important;
}

/* =========================================
   3D Flip Container
   ========================================= */
.perspective-container {
	perspective: 1200px;
}

.flip-card-inner {
	position: relative;
	width: 19.5rem;
	height: 30.5rem;
	max-width: 100%;
	text-align: center;
	transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
	transform-style: preserve-3d;
}

@media (max-width: 440px) {
	.flip-card-inner {
		width: 17rem;
		height: 26.5rem;
	}
}

.flip-card-inner.is-flipped {
	transform: rotateY(180deg);
}

.flip-card-front,
.flip-card-back {
	position: absolute !important;
	width: 100%;
	height: 100%;
	-webkit-backface-visibility: hidden;
	backface-visibility: hidden;
	border-radius: 0.75rem;
	overflow: clip;
}

.flip-card-back {
	transform: rotateY(180deg);
}

/* =========================================
   Card base
   ========================================= */
.card {
	width: 100%;
	height: 100%;
	border-radius: 0.75rem;
	overflow: clip;
	padding: 0;
	border: 8px solid;
	position: relative;
	box-sizing: border-box;
	transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

/* =========================================
   Dark Theme
   ========================================= */
.card.theme-dark {
	color: #e5e5e5;
	background: #111215;
	border-color: #1c1c1e;
	box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.85), inset 0 1px 2px rgba(255,255,255,0.1);
}

.card.theme-dark .card__body {
	background: linear-gradient(
		to top,
		rgba(20, 32, 38, 0.97) 0%,
		rgba(26, 42, 50, 0.88) 40%,
		rgba(32, 53, 63, 0.55) 70%,
		rgba(32, 53, 63, 0) 100%
	);
}

.card.theme-dark .card__header .chips { color: #ffffff; }
.card.theme-dark .verified-icon        { color: #93c5fd; }
.card.theme-dark .prodi-text           { color: #93c5fd; }
.card.theme-dark .nim-text             { color: #94a3b8; }
.card.theme-dark .fakultas-text        { color: #cbd5e1; }
.card.theme-dark .back-panel           { background: #0f0f12; }
.card.theme-dark .branding-title       { color: #ffffff; }
.card.theme-dark .branding-subtitle    { color: #71717a; }
.card.theme-dark .branding-avatar      { background: rgba(37,99,235,0.1); border-color: rgba(37,99,235,0.25); color: #60a5fa; }
.card.theme-dark .border-line          { border-color: rgba(63,63,70,0.7); }

/* =========================================
   Light Theme
   ========================================= */
.card.theme-light {
	color: #1f2937;
	background: #ffffff;
	border-color: #e8ecf0;
	box-shadow: 0 30px 60px -15px rgba(15,23,42,0.10), inset 0 1px 2px rgba(255,255,255,0.9), 0 4px 16px rgba(15,23,42,0.04);
}

.card.theme-light .card__body {
	background: linear-gradient(
		to top,
		rgba(255, 255, 255, 0.99) 0%,
		rgba(255, 255, 255, 0.93) 40%,
		rgba(255, 255, 255, 0.60) 70%,
		rgba(255, 255, 255, 0) 100%
	);
}

.card.theme-light .card__header .chips { color: #0f172a; }
.card.theme-light .verified-icon        { color: #3b82f6; }
.card.theme-light .prodi-text           { color: #2563eb; }
.card.theme-light .nim-text             { color: #64748b; }
.card.theme-light .fakultas-text        { color: #334155; }
.card.theme-light .back-panel           { background: #ffffff; }
.card.theme-light .branding-title       { color: #0f172a; }
.card.theme-light .branding-subtitle    { color: #64748b; }
.card.theme-light .branding-avatar      { background: rgba(37,99,235,0.05); border-color: rgba(37,99,235,0.15); color: #2563eb; }
.card.theme-light .border-line          { border-color: #e2e8f0; }

/* =========================================
   Card inner — FRONT only gets blur overlay
   ========================================= */
.card__inner {
	grid-template-areas: "stack";
	position: relative;
	border-radius: 0.25rem;
	overflow: clip;
	display: grid;
	height: 100%;
}

/* Blur overlay ONLY for front face (covers bottom gradient area) */
.flip-card-front .card__inner::after {
	content: "";
	position: absolute;
	inset: 0;
	border-radius: 0.25rem;
	backdrop-filter: blur(24px);
	-webkit-backdrop-filter: blur(24px);
	mask: linear-gradient(to top, black 35%, transparent 75%);
	-webkit-mask: linear-gradient(to top, black 35%, transparent 75%);
	pointer-events: none;
}

/* Back face: NO blur overlay at all */
.flip-card-back .card__inner::after {
	display: none;
}

.card__cover {
	grid-area: stack;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.card__body {
	position: relative;
	z-index: 2;
	grid-area: stack;
	margin-top: auto;
	padding: 1.5rem 1.6rem 1.6rem;
	box-sizing: border-box;
	display: flex;
	flex-direction: column;
	justify-content: flex-end;
}

.card__header {
	font-size: 1.2rem;
	font-weight: 700;
	margin: 0;
	letter-spacing: -0.02em;
}

/* =========================================
   Back panel
   ========================================= */
.back-panel {
	border-radius: 0.25rem;
	border: none;
	height: 100%;
	box-sizing: border-box;
	transition: background-color 0.3s;
}

/* =========================================
   QR Code — crisp, no blur
   ========================================= */
.qr-wrapper {
	width: 11rem;
	height: 11rem;
	border-radius: 1.25rem;
	border-width: 1px;
	border-style: solid;
	padding: 0.5rem;
	/* Ensure QR is never blurred */
	isolation: isolate;
	image-rendering: pixelated;
	overflow: hidden;
}

.qr-image {
	width: 100%;
	height: 100%;
	object-fit: contain;
	display: block;
	/* Critical: no filter/blur allowed here */
	image-rendering: -webkit-optimize-contrast;
	image-rendering: crisp-edges;
}

.qr-placeholder {
	width: 100%;
	height: 100%;
	border-radius: 0.75rem;
}

.chips {
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
}

/* =========================================
   Horizontal 3D Flip Container
   ========================================= */
.perspective-container-horizontal {
	perspective: 1200px;
}

.horizontal-flip-inner {
	position: relative;
	width: 28.5rem;
	height: 18rem;
	max-width: 100%;
	text-align: center;
	transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
	transform-style: preserve-3d;
}

@media (max-width: 480px) {
	.horizontal-flip-inner {
		width: 20rem;
		height: 12.6rem;
	}
}

.horizontal-flip-inner.is-flipped {
	transform: rotateY(180deg);
}

.horizontal-card-front,
.horizontal-card-back {
	position: absolute !important;
	width: 100%;
	height: 100%;
	-webkit-backface-visibility: hidden;
	backface-visibility: hidden;
	border-radius: 0.75rem;
	overflow: clip;
}

.horizontal-card-back {
	transform: rotateY(180deg);
}

.horizontal-card-inner {
	position: relative;
	border-radius: 0.25rem;
	overflow: clip;
	height: 100%;
}

/* =========================================
   Horizontal Card Custom Styling
   ========================================= */
.card__glass-overlay {
	position: absolute;
	inset: 0;
	background: radial-gradient(circle at 10% 20%, rgba(91, 142, 235, 0.05) 0%, transparent 60%);
	pointer-events: none;
	z-index: 1;
}

.horizontal-avatar-frame {
	width: 6.5rem;
	height: 8rem;
	border-radius: 0.5rem;
	overflow: hidden;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
	transition: border-color 0.3s, background-color 0.3s;
}

@media (max-width: 480px) {
	.horizontal-avatar-frame {
		width: 4.5rem;
		height: 5.5rem;
		border-radius: 0.35rem;
	}
}

.signature-lines {
	background: repeating-linear-gradient(
		45deg,
		rgba(59, 130, 246, 0.1),
		rgba(59, 130, 246, 0.1) 4px,
		transparent 4px,
		transparent 8px
	);
}

.text-limit-1 {
	display: -webkit-box;
	-webkit-line-clamp: 1;
	-webkit-box-orient: vertical;
	overflow: hidden;
}

.image-crisp {
	image-rendering: -webkit-optimize-contrast;
	image-rendering: crisp-edges;
}
</style>
