<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent } from "@/components/ui/dialog";

const page = usePage();

// Ambil session_lifetime dari backend via Inertia shared props (dalam milidetik)
const idleTimeoutDuration = computed(() => {
	// page.props.auth.session_lifetime dikirim dari HandleInertiaRequests.php
	// biome-ignore lint/suspicious/noExplicitAny: session lifetime from Inertia auth
	const lifetime = (page.props.auth as any)?.session_lifetime;
	if (typeof lifetime === "number" && lifetime > 0) {
		// Berikan buffer 10 detik agar modal muncul sesaat sebelum backend benar-benar kedaluwarsa.
		// Jika lifetime sangat singkat (misal untuk testing <= 60 detik), gunakan buffer 5 detik.
		const buffer = lifetime <= 60 * 1000 ? 5 * 1000 : 10 * 1000;
		return Math.max(1000, lifetime - buffer); // minimal 1 detik
	}
	return 30 * 60 * 1000; // default fallback 30 menit
});

// Format durasi idle untuk ditampilkan pada pesan deskripsi modal
const idleTimeFormatted = computed(() => {
	// biome-ignore lint/suspicious/noExplicitAny: session lifetime from Inertia auth
	const totalMs = (page.props.auth as any)?.session_lifetime;
	if (typeof totalMs !== "number" || totalMs <= 0) {
		return "30 menit";
	}
	const minutes = Math.round(totalMs / (60 * 1000));
	if (minutes >= 60) {
		const hours = Math.floor(minutes / 60);
		const remainingMinutes = minutes % 60;
		if (remainingMinutes > 0) {
			return `${hours} jam ${remainingMinutes} menit`;
		}
		return `${hours} jam`;
	}
	if (minutes === 0) {
		return `${Math.round(totalMs / 1000)} detik`;
	}
	return `${minutes} menit`;
});

const idleTimer = ref<number | null>(null);
const isSessionExpired = ref(false);

const events = [
	"mousedown",
	"mousemove",
	"keydown",
	"scroll",
	"touchstart",
] as Array<keyof DocumentEventMap>;

const resetTimer = () => {
	// Jika sesi sudah dianggap expired atau user belum login, kita tidak melacak aktivitas
	if (isSessionExpired.value || !page.props.auth?.user) {
		return;
	}

	if (idleTimer.value) {
		clearTimeout(idleTimer.value);
	}

	idleTimer.value = globalThis.setTimeout(
		expireSession,
		idleTimeoutDuration.value,
	);
};

const expireSession = () => {
	isSessionExpired.value = true;
	stopTracking();

	// Opsional: Lakukan logout diam-diam ke backend agar cookie ditarik
	fetch("/logout", {
		method: "POST",
		headers: {
			"X-CSRF-TOKEN":
				document
					.querySelector('meta[name="csrf-token"]')
					?.getAttribute("content") || "",
		},
	}).catch(() => {});
};

const logout = () => {
	// Pengguna mengklik tombol "Login Kembali", kita refresh halaman penuh
	globalThis.location.href = "/login";
};

const stopTracking = () => {
	for (const event of events) {
		document.removeEventListener(event, resetTimer);
	}

	if (idleTimer.value) {
		clearTimeout(idleTimer.value);
	}
};

const startTracking = () => {
	// Hanya lacak jika user sedang dalam keadaan login
	if (!page.props.auth?.user) {
		return;
	}

	for (const event of events) {
		document.addEventListener(event, resetTimer, { passive: true });
	}
	resetTimer();
};

onMounted(() => {
	startTracking();
});

onUnmounted(() => {
	stopTracking();
});
</script>

<template>
    <Dialog :open="isSessionExpired">
        <!-- Hapus tombol close bawaan jika memungkinkan lewat class custom, atau pertahankan UI bersih -->
        <DialogContent class="sm:max-w-[400px] p-8 text-center shadow-2xl border-0 rounded-4xl bg-white">
            <div class="flex flex-col items-center justify-center space-y-5">
                <!-- Icon Modern -->
                <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center animate-bounce shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Sesi Berakhir</h3>
                    <p class="text-slate-500 font-medium text-sm leading-relaxed px-2">
                        Sistem mengamankan akun Anda secara otomatis karena tidak ada aktivitas selama {{ idleTimeFormatted }} terakhir.
                    </p>
                </div>

                <div class="w-full pt-4">
                    <Button @click="logout" class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_8px_20px_rgba(82,68,228,0.3)] transition-all h-12 rounded-2xl text-[15px] font-semibold border-0">
                        Masuk Kembali
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
