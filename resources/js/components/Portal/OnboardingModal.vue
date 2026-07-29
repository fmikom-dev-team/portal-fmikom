<script setup lang="ts">
import { ref, watch, onUnmounted } from "vue";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-vue-next";
import { cn } from "@/lib/utils";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
}>();

const step = ref(1);
const isPaused = ref(false);
let autoPlayInterval: ReturnType<typeof setInterval> | null = null;

const stepContent = [
    {
        title: "Selamat Datang di Portal FMIKOM",
        description:
            "Platform sistem informasi terintegrasi Fakultas Matematika dan Ilmu Komputer untuk mempermudah seluruh layanan akademik, kemahasiswaan, dan administrasi kampus.",
        image: "https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1200&q=80",
    },
    {
        title: "Modul Terintegrasi & Modern",
        description:
            "Akses cepat ke manajemen magang (WIMS), portofolio karya digital mahasiswa (PAGI), tracer alumni, hingga sistem administrasi surat resmi fakultas.",
        image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80",
    },
    {
        title: "Keamanan & Otentikasi Terpusat",
        description:
            "Dilengkapi dengan verifikasi akun mandiri via OTP WhatsApp & Email, Single Sign-On (SSO), serta proteksi keamanan data tingkat tinggi.",
        image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80",
    },
    {
        title: "Siap Memulai Pengalaman Baru?",
        description:
            "Masuk menggunakan akun terdaftar atau aktifkan akun NIM/NIDN Anda secara langsung untuk menikmati seluruh fitur dan layanan FMIKOM.",
        image: "https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80",
    },
];

const totalSteps = stepContent.length;

const startAutoPlay = () => {
    stopAutoPlay();
    isPaused.value = false;
    autoPlayInterval = setInterval(() => {
        if (!isPaused.value) {
            step.value = step.value >= totalSteps ? 1 : step.value + 1;
        }
    }, 4500);
};

const stopAutoPlay = () => {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
    }
};

const nextStep = () => {
    if (step.value < totalSteps) {
        step.value++;
    } else {
        step.value = 1;
    }
};

const prevStep = () => {
    if (step.value > 1) {
        step.value--;
    } else {
        step.value = totalSteps;
    }
};

const setStepDirectly = (targetStep: number) => {
    step.value = targetStep;
};

const handleOpenChange = (val: boolean) => {
    if (val) {
        step.value = 1;
        startAutoPlay();
    } else {
        stopAutoPlay();
    }
    emit("update:open", val);
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            step.value = 1;
            startAutoPlay();
        } else {
            stopAutoPlay();
        }
    }
);

onUnmounted(() => {
    stopAutoPlay();
});
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent
            @mouseenter="isPaused = true"
            @mouseleave="isPaused = false"
            class="gap-0 p-0 sm:max-w-[620px] rounded-2xl overflow-hidden shadow-2xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-950 [&>button:last-child]:text-white [&>button:last-child]:bg-black/40 [&>button:last-child]:hover:bg-black/70 [&>button:last-child]:p-2 [&>button:last-child]:rounded-full [&>button:last-child]:top-4 [&>button:last-child]:right-4 [&>button:last-child]:z-30 transition-all duration-300"
        >
            <!-- Header Image + Navigation Controls -->
            <div class="p-3 relative bg-slate-100 dark:bg-zinc-900/60 group">
                <!-- Main Image Header with Generous Proportional Height -->
                <div class="relative overflow-hidden rounded-xl h-64 sm:h-76 shadow-xs">
                    <img
                        class="w-full h-full object-cover object-center transition-all duration-500 ease-out transform scale-100 group-hover:scale-105"
                        :src="stepContent[step - 1].image"
                        :alt="stepContent[step - 1].title"
                    />

                    <!-- Soft Gradient Overlay for Arrow Visibility -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/20 pointer-events-none" />

                    <!-- Left / Right Slide Arrow Buttons -->
                    <button
                        type="button"
                        @click="prevStep"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-xs transition-all duration-200 opacity-80 hover:opacity-100 hover:scale-110 cursor-pointer z-20"
                        title="Slide Sebelumnya"
                    >
                        <ChevronLeft class="w-5 h-5" />
                    </button>

                    <button
                        type="button"
                        @click="nextStep"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-xs transition-all duration-200 opacity-80 hover:opacity-100 hover:scale-110 cursor-pointer z-20"
                        title="Slide Berikutnya"
                    >
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Body Content -->
            <div class="space-y-6 px-7 pb-7 pt-4">
                <DialogHeader class="text-left space-y-2 min-h-[90px] justify-center">
                    <DialogTitle class="text-xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight leading-snug">
                        {{ stepContent[step - 1].title }}
                    </DialogTitle>
                    <DialogDescription class="text-[13.5px] font-normal text-slate-600 dark:text-zinc-400 leading-relaxed">
                        {{ stepContent[step - 1].description }}
                    </DialogDescription>
                </DialogHeader>

                <!-- Footer Navigation & Indicators -->
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center pt-4 border-t border-slate-100 dark:border-zinc-800/60">
                    <!-- Clickable Step Dots -->
                    <div class="flex items-center space-x-2 max-sm:order-1">
                        <button
                            v-for="(_, index) in totalSteps"
                            :key="index"
                            type="button"
                            @click="setStepDirectly(index + 1)"
                            :class="cn(
                                'h-2 rounded-full transition-all duration-300 cursor-pointer',
                                index + 1 === step ? 'bg-[#2563eb] w-7' : 'bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 w-2'
                            )"
                            :title="`Pergi ke slide ${index + 1}`"
                        />
                    </div>

                    <!-- Footer Action Buttons -->
                    <DialogFooter class="flex flex-row items-center justify-end gap-2.5">
                        <button
                            v-if="step > 1"
                            type="button"
                            @click="prevStep"
                            class="h-9.5 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-zinc-300 dark:hover:text-white border border-slate-200 dark:border-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
                        >
                            Kembali
                        </button>

                        <DialogClose as-child>
                            <button
                                type="button"
                                class="h-9.5 px-4 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-white transition-colors cursor-pointer"
                            >
                                Lewati
                            </button>
                        </DialogClose>

                        <button
                            v-if="step < totalSteps"
                            type="button"
                            @click="nextStep"
                            class="group inline-flex h-9.5 items-center justify-center rounded-xl bg-[#2563eb] px-5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95 cursor-pointer"
                        >
                            Lanjut
                            <ArrowRight
                                class="-me-0.5 ms-1.5 opacity-70 transition-transform group-hover:translate-x-0.5"
                                :size="14"
                                stroke-width="2"
                            />
                        </button>
                        <DialogClose v-else as-child>
                            <button
                                type="button"
                                class="inline-flex h-9.5 items-center justify-center rounded-xl bg-[#2563eb] px-5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95 cursor-pointer"
                            >
                                Paham & Mulai
                            </button>
                        </DialogClose>
                    </DialogFooter>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
