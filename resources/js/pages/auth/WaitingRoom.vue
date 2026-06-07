<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import { Button } from "@/components/ui/button";

const props = defineProps<{
	status: "pending" | "approved" | "rejected";
	name: string;
	flash?: string | null;
}>();

const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null);

const startPolling = () => {
	pollingInterval.value = setInterval(() => {
		fetch("/api/check-approval-status")
			.then((res) => res.json())
			.then((data) => {
				if (data.status_approval === "approved") {
					// Berhasil disetujui, muat ulang halaman agar diarahkan ke Dashboard/OTP
					window.location.reload();
				} else if (
					data.status_approval === "rejected" &&
					props.status !== "rejected"
				) {
					// Update state inertia tanpa reload
					router.reload({ only: ["status"] });
				}
			})
			.catch((err) => console.error("Polling error", err));
	}, 5000);
};

onMounted(() => {
	if (props.status === "pending") {
		startPolling();
	}
});

onUnmounted(() => {
	if (pollingInterval.value) {
		clearInterval(pollingInterval.value);
	}
});

const ajukanUlang = () => {
	router.post(
		"/waiting-room/resign",
		{},
		{
			onSuccess: () => {
				startPolling();
			},
		},
	);
};

const logout = () => {
	router.post("/logout");
};
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 relative overflow-hidden">
        <Head title="Waiting Room" />

        <!-- Background Decor -->
        <div class="absolute inset-0 bg-grid-slate-100/[0.04] bg-[bottom_1px_center] opacity-20" style="mask-image: linear-gradient(to bottom, transparent, black);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-[#2563eb]/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-slate-100 p-8 sm:p-10 relative z-10 animate-in fade-in zoom-in-95 duration-500">
            <div class="text-center">
                <!-- Status: Pending -->
                <div v-if="status === 'pending'" class="flex flex-col items-center">
                    <div class="relative w-24 h-24 flex items-center justify-center mb-6">
                        <div class="absolute inset-0 border-4 border-[#2563eb]/20 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-[#2563eb] rounded-full border-t-transparent animate-spin"></div>
                        <svg class="w-10 h-10 text-[#2563eb] animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Akun Sedang Diverifikasi</h1>

                    <!-- Flash dari verifikasi OTP -->
                    <div v-if="props.flash" class="mb-4 text-sm font-medium text-green-700 bg-green-50 p-3 rounded-xl border border-green-200 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ props.flash }}
                    </div>

                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Halo <span class="font-semibold text-slate-700">{{ name }}</span>,<br/>
                        Pendaftaran Anda telah kami terima. Saat ini admin sedang meninjau data Anda. Silakan tunggu di halaman ini, sistem akan otomatis mengarahkan Anda ketika disetujui.
                    </p>

                    <div class="bg-indigo-50 text-indigo-700 text-xs font-medium px-4 py-2.5 rounded-full flex items-center gap-2 mb-6 shadow-sm border border-indigo-100 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Status Update: Real-time Polling
                    </div>
                </div>

                <!-- Status: Rejected -->
                <div v-if="status === 'rejected'" class="flex flex-col items-center">
                    <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mb-6 shadow-inner ring-4 ring-red-50/50">
                        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Pendaftaran Ditolak</h1>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Mohon maaf <strong>{{ name }}</strong>, data pendaftaran Anda ditolak oleh admin karena kemungkinan data tidak valid atau duplikat.
                    </p>

                    <Button @click="ajukanUlang" class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white rounded-xl h-11 text-md font-medium shadow-md mb-3">
                        Ajukan Ulang Permohonan
                    </Button>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-6">
                    <button @click="logout" class="text-sm font-medium text-slate-400 hover:text-slate-600 transition-colors flex items-center justify-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout & Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
