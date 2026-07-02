<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    ChevronLeft,
    Info,
    CheckCircle2,
    ClipboardList,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import type { BreadcrumbItem } from "@/types";
import type { Kuesioner, KuesionerSection, Pertanyaan } from "@/types/trace";
import { kuesioner } from "@/routes/module/trace/index";
import { store as alumniTracerStore } from "@/routes/module/trace/kuesioner/index";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import { TPageHeader } from "@/components/Trace";
import QuestionRenderer from "./components/QuestionRenderer.vue";
import QuizProgressBar from "./components/QuizProgressBar.vue";
import QuizNavigation from "./components/QuizNavigation.vue";

const props = defineProps<{
    kuesioner: Kuesioner;
    hasResponded: boolean;
    existingAnswers: Record<string, unknown>;
    readOnly?: boolean;
}>();

const form = useForm({
    answers: {} as Record<string, unknown>,
});

const isReadonly = computed(() => props.hasResponded || props.readOnly);

// ═══════════════════════════════════════════════════════════════
// NAVIGATION — wizard multi-section
// ═══════════════════════════════════════════════════════════════

const visibleSections = computed(() => {
    if (!props.kuesioner?.sections) {
        return [];
    }

    return props.kuesioner.sections.map((s: KuesionerSection, idx: number) => ({
        section: s,
        originalIndex: idx,
    }));
});

const currentVisibleIndex = ref(0);

const isLastVisibleSection = computed(() => {
    return currentVisibleIndex.value >= visibleSections.value.length - 1;
});

// FIX — Progress dihitung dari (index + 1) / total sehingga section pertama
// sudah menunjukkan progres (misal 1/3 = 33%), bukan 0%.
// Sebelumnya: (currentIndex / (total - 1)) * 100 → 0% di section pertama.
const progressPercent = computed(() => {
    const total = visibleSections.value.length;

    if (total <= 1) {
        return 100;
    }

    return Math.round(((currentVisibleIndex.value + 1) / total) * 100);
});

const nextSection = () => {
    if (!isLastVisibleSection.value) {
        currentVisibleIndex.value++;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

const prevSection = () => {
    if (currentVisibleIndex.value > 0) {
        currentVisibleIndex.value--;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

// ═══════════════════════════════════════════════════════════════
// INIT ANSWERS
// ═══════════════════════════════════════════════════════════════

if (props.kuesioner?.sections) {
    props.kuesioner.sections.forEach((section: KuesionerSection) => {
        section.pertanyaans.forEach((q: Pertanyaan) => {
            const matrixRows: string[] = q.meta?.rows || [];

            if (
                props.existingAnswers &&
                props.existingAnswers[q.id] !== undefined
            ) {
                const val = props.existingAnswers[q.id];

                if (q.tipe === "matrix") {
                    const matrixVal: Record<string, unknown> =
                        typeof val === "object" && val !== null
                            ? { ...val }
                            : {};
                    matrixRows.forEach((row: string) => {
                        matrixVal[row] =
                            matrixVal[row] !== undefined
                                ? Number(matrixVal[row])
                                : "";
                    });
                    form.answers[q.id] = matrixVal;
                } else if (q.tipe === "scale" || q.tipe === "radio") {
                    form.answers[q.id] = Number(val);
                } else {
                    form.answers[q.id] = val;
                }
            } else {
                if (q.tipe === "checkbox") {
                    form.answers[q.id] = [];
                } else if (q.tipe === "matrix") {
                    const matrixVal: Record<string, string> = {};
                    matrixRows.forEach((row: string) => {
                        matrixVal[row] = "";
                    });
                    form.answers[q.id] = matrixVal;
                } else {
                    form.answers[q.id] = "";
                }
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════════
// SUBMIT
// ═══════════════════════════════════════════════════════════════

const submit = () => {
    if (isReadonly.value) {
        return;
    }

    form.post(alumniTracerStore(props.kuesioner.id).url, {
        preserveScroll: true,
        onError: (errors) => {
            let firstErrorQid: number | null = null;

            for (const key in errors) {
                if (key.startsWith("answers.")) {
                    firstErrorQid = parseInt(key.split(".")[1]);
                    break;
                }
            }

            if (firstErrorQid !== null && props.kuesioner?.sections) {
                for (let si = 0; si < visibleSections.value.length; si++) {
                    const found = visibleSections.value[
                        si
                    ].section.pertanyaans?.find(
                        (p: Pertanyaan) => p.id === firstErrorQid,
                    );

                    if (found) {
                        currentVisibleIndex.value = si;
                        break;
                    }
                }
            }
        },
    });
};

const errorMessage = computed(
    () => (form.errors as Record<string, string | undefined>).error,
);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Kuesioner", href: "/trace/kuesioner" },
    { title: "Isi Kuesioner", href: "#" },
];
</script>

<template>
    <Head :title="'Isi Kuesioner - ' + kuesioner.judul" />
    <TraceAlumniLayout
        title="Kuesioner"
        :breadcrumbs="breadcrumbItems"
        role-name="Alumni"
    >
        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    :href="kuesioner().url"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0C447C] dark:text-slate-400 dark:hover:text-[#85B7EB]"
                >
                    <ChevronLeft class="h-4 w-4" /> Kembali ke Daftar
                </Link>
                <TPageHeader
                    :title="kuesioner?.judul ?? kuesioner?.title ?? 'Kuesioner'"
                    :description="
                        kuesioner?.deskripsi
                            ? kuesioner.deskripsi.substring(0, 100) + '...'
                            : ''
                    "
                    :icon="ClipboardList"
                >
                    <template #actions>
                        <div
                            class="flex items-center gap-2 rounded-full bg-[#0C447C]/10 px-4 py-2 text-sm font-bold text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]"
                        >
                            <Info class="h-4 w-4" /> Tracer Study
                        </div>
                    </template>
                </TPageHeader>
            </div>

            <!-- Description Card -->
            <div
                v-if="kuesioner.deskripsi && currentVisibleIndex === 0"
                class="mb-8 overflow-hidden rounded-2xl border border-[#0C447C]/10 bg-[#0C447C]/5 p-6 dark:border-[#85B7EB]/15 dark:bg-[#0C447C]/10"
            >
                <p
                    class="text-sm leading-relaxed text-[#0C447C] dark:text-[#85B7EB]"
                >
                    {{ kuesioner.deskripsi }}
                </p>
            </div>

            <!-- Already Responded Warning -->
            <div
                v-if="hasResponded || readOnly"
                class="mb-8 rounded-2xl border border-amber-100 bg-amber-50 p-6 dark:border-amber-900/30 dark:bg-amber-900/20"
            >
                <div class="flex gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400"
                    >
                        <CheckCircle2 class="h-6 w-6" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-bold text-amber-800 dark:text-amber-300"
                        >
                            {{ readOnly ? "Mode Baca Saja" : "Anda Sudah Mengisi" }}
                        </h3>
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            {{
                                readOnly
                                    ? "Admin dapat melihat kuesioner dalam mode alumni, tetapi tidak dapat mengirim jawaban."
                                    : "Anda sudah pernah mengisi kuesioner ini sebelumnya. Data tidak dapat diubah lagi dari halaman ini."
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Inline Error -->
            <div
                v-if="errorMessage"
                class="mb-8 rounded-2xl border border-red-100 bg-red-50 p-6 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-900/10 dark:text-red-200"
            >
                {{ errorMessage }}
            </div>

            <!-- Progress Bar -->
            <QuizProgressBar
                :current-index="currentVisibleIndex"
                :total-sections="visibleSections.length"
                :progress-percent="progressPercent"
            />

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="relative">
                    <template
                        v-for="({ section }, vIdx) in visibleSections"
                        :key="section.id ?? vIdx"
                    >
                        <div
                            v-show="vIdx === currentVisibleIndex"
                            class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div
                                class="border-b border-slate-100 bg-slate-50/50 px-8 py-6 dark:border-zinc-800 dark:bg-zinc-800/50"
                            >
                                <h2
                                    class="text-xl font-black text-slate-900 dark:text-white"
                                >
                                    {{ section.title ?? section.judul }}
                                </h2>
                                <p
                                    v-if="
                                        section.description ?? section.deskripsi
                                    "
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        section.description ?? section.deskripsi
                                    }}
                                </p>
                            </div>

                            <div class="space-y-10 p-8">
                                <QuestionRenderer
                                    v-for="(
                                        pertanyaan, pIdx
                                    ) in section.pertanyaans"
                                    :key="pertanyaan.id"
                                    :pertanyaan="pertanyaan"
                                    v-model="form.answers[pertanyaan.id]"
                                    :question-index="Number(pIdx)"
                                    :errors="
                                        form.errors as Record<
                                            string,
                                            string | undefined
                                        >
                                    "
                                    :readonly="isReadonly"
                                />
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer / Actions -->
                <QuizNavigation
                    :is-first="currentVisibleIndex === 0"
                    :is-last="isLastVisibleSection"
                    :processing="form.processing"
                    :has-responded="isReadonly"
                    @prev="prevSection"
                    @next="nextSection"
                />
            </form>
        </div>
    </TraceAlumniLayout>
</template>
