<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    ChevronLeft,
    Send,
    AlertCircle,
    Info,
    ArrowRight,
    ArrowLeft,
    CheckCircle2,
    ClipboardList,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import type { BreadcrumbItem } from "@/types";
import { kuesioner } from "@/routes/module/trace";
import { store as alumniTracerStore } from "@/routes/module/trace/kuesioner";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import { TPageHeader } from '@/components/trace';

const props = defineProps<{
    kuesioner: any;
    hasResponded: boolean;
    existingAnswers: Record<string, any>;
}>();

const form = useForm({
    answers: {} as Record<string, any>,
});

const isReadonly = computed(() => props.hasResponded);

const isEvenIndex = (index: number) => index % 2 === 0;

// ═══════════════════════════════════════════════════════════════
// NAVIGATION — wizard multi-section
// ═══════════════════════════════════════════════════════════════

const visibleSections = computed(() => {
    if (!props.kuesioner?.sections) {
        return [];
    }

    return props.kuesioner.sections.map((s: any, idx: number) => ({
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
    props.kuesioner.sections.forEach((section: any) => {
        section.pertanyaans.forEach((q: any) => {
            const matrixRows: string[] = q.meta?.rows || [];

            if (
                props.existingAnswers &&
                props.existingAnswers[q.id] !== undefined
            ) {
                const val = props.existingAnswers[q.id];

                if (q.tipe === "matrix") {
                    const matrixVal: Record<string, any> =
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
    if (props.hasResponded) {
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
                        (p: any) => p.id === firstErrorQid,
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
    // FIX #5 — "Answer" (English) diganti "Isi Kuesioner" (Indonesia)
    // sesuai konvensi bahasa UI yang konsisten.
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
                    :description="kuesioner?.deskripsi ? kuesioner.deskripsi.substring(0, 100) + '...' : ''"
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
                v-if="hasResponded"
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
                            Anda Sudah Mengisi
                        </h3>
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            Anda sudah pernah mengisi kuesioner ini sebelumnya.
                            Data tidak dapat diubah lagi dari halaman ini.
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
            <div class="mb-8" v-if="visibleSections.length > 1">
                <div
                    class="mb-3 flex justify-between text-sm font-bold text-slate-500 dark:text-slate-400"
                >
                    <span class="text-[#0C447C] dark:text-[#85B7EB]">
                        Bagian {{ currentVisibleIndex + 1 }} dari
                        {{ visibleSections.length }}
                    </span>
                    <span>{{ progressPercent }}% Selesai</span>
                </div>
                <div
                    class="h-3 w-full overflow-hidden rounded-full border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"
                >
                    <div
                        class="relative h-full bg-[#0C447C] dark:bg-[#85B7EB] transition-all duration-500 ease-out"
                        :style="{ width: progressPercent + '%' }"
                    >
                        <div
                            class="absolute inset-0 bg-white/20"
                            style="
                                background-image: linear-gradient(
                                    45deg,
                                    rgba(255, 255, 255, 0.15) 25%,
                                    transparent 25%,
                                    transparent 50%,
                                    rgba(255, 255, 255, 0.15) 50%,
                                    rgba(255, 255, 255, 0.15) 75%,
                                    transparent 75%,
                                    transparent
                                );
                                background-size: 1rem 1rem;
                            "
                        ></div>
                    </div>
                </div>
            </div>

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
                                <template
                                    v-for="(
                                        pertanyaan, pIdx
                                    ) in section.pertanyaans"
                                    :key="pertanyaan.id"
                                >
                                    <div
                                        class="space-y-4"
                                        :id="'question-' + pertanyaan.id"
                                    >
                                        <label
                                            class="block text-base font-bold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ Number(pIdx) + 1 }}.
                                            {{ pertanyaan.teks }}
                                            <span
                                                v-if="pertanyaan.is_required"
                                                class="ml-1 text-red-500"
                                                title="Wajib Diisi"
                                                >*</span
                                            >
                                        </label>

                                        <!-- Text Input -->
                                        <div
                                            v-if="pertanyaan.tipe === 'text'"
                                            class="relative"
                                        >
                                            <input
                                                v-model="
                                                    form.answers[pertanyaan.id]
                                                "
                                                type="text"
                                                placeholder="Jawaban Anda"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                                :disabled="isReadonly"
                                            />
                                        </div>

                                        <!-- Textarea -->
                                        <div
                                            v-if="
                                                pertanyaan.tipe === 'textarea'
                                            "
                                            class="relative"
                                        >
                                            <textarea
                                                v-model="
                                                    form.answers[pertanyaan.id]
                                                "
                                                rows="4"
                                                placeholder="Jawaban Anda"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                                :disabled="isReadonly"
                                            ></textarea>
                                        </div>

                                        <!-- Number Input -->
                                        <div
                                            v-if="pertanyaan.tipe === 'number'"
                                            class="relative"
                                        >
                                            <input
                                                v-model="
                                                    form.answers[pertanyaan.id]
                                                "
                                                type="number"
                                                placeholder="0"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                                :disabled="isReadonly"
                                            />
                                        </div>

                                        <!-- Dropdown -->
                                        <div
                                            v-if="
                                                pertanyaan.tipe === 'dropdown'
                                            "
                                            class="relative"
                                        >
                                            <select
                                                v-model="
                                                    form.answers[pertanyaan.id]
                                                "
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                                :disabled="isReadonly"
                                            >
                                                <option value="" disabled>
                                                    Pilih Jawaban
                                                </option>
                                                <option
                                                    v-for="opsi in pertanyaan.opsi_jawabans"
                                                    :key="opsi.id"
                                                    :value="opsi.id"
                                                >
                                                    {{ opsi.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Radio -->
                                        <div
                                            v-if="pertanyaan.tipe === 'radio'"
                                            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1"
                                        >
                                            <label
                                                v-for="opsi in pertanyaan.opsi_jawabans"
                                                :key="opsi.id"
                                                class="relative flex cursor-pointer items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-green-200 hover:bg-green-50/50 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-green-900/50"
                                                :class="{
                                                    'border-green-500 bg-green-50/50 dark:border-green-500/50 dark:bg-green-900/20':
                                                        form.answers[
                                                            pertanyaan.id
                                                        ] === opsi.id,
                                                }"
                                            >
                                                <input
                                                    v-model="
                                                        form.answers[
                                                            pertanyaan.id
                                                        ]
                                                    "
                                                    type="radio"
                                                    :name="'q_' + pertanyaan.id"
                                                    :value="opsi.id"
                                                    class="h-5 w-5 border-slate-300 text-green-600 focus:ring-green-500"
                                                    :disabled="isReadonly"
                                                />
                                                <span
                                                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                                    >{{ opsi.label }}</span
                                                >
                                            </label>
                                        </div>

                                        <!-- Checkbox -->
                                        <div
                                            v-if="
                                                pertanyaan.tipe === 'checkbox'
                                            "
                                            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1"
                                        >
                                            <label
                                                v-for="opsi in pertanyaan.opsi_jawabans"
                                                :key="opsi.id"
                                                class="relative flex cursor-pointer items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-green-200 hover:bg-green-50/50 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-green-900/50"
                                                :class="{
                                                    'border-green-500 bg-green-50/50 dark:border-green-500/50 dark:bg-green-900/20':
                                                        form.answers[
                                                            pertanyaan.id
                                                        ]?.includes(opsi.id),
                                                }"
                                            >
                                                <input
                                                    v-model="
                                                        form.answers[
                                                            pertanyaan.id
                                                        ]
                                                    "
                                                    type="checkbox"
                                                    :value="opsi.id"
                                                    class="h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                                    :disabled="isReadonly"
                                                />
                                                <span
                                                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                                    >{{ opsi.label }}</span
                                                >
                                            </label>
                                        </div>

                                        <!-- Scale -->
                                        <div
                                            v-if="pertanyaan.tipe === 'scale'"
                                            class="space-y-6 pt-4"
                                        >
                                            <div
                                                class="flex justify-between px-4 text-xs font-black tracking-widest text-slate-400 uppercase"
                                            >
                                                <span>{{ pertanyaan.meta?.scale_label_min || 'Sangat Tidak Setuju' }}</span>
                                                <span>{{ pertanyaan.meta?.scale_label_max || 'Sangat Setuju' }}</span>
                                            </div>
                                            <div
                                                class="flex items-center justify-between gap-2 px-2"
                                            >
                                                <label
                                                    v-for="n in ((pertanyaan.meta?.scale_max || 5) - (pertanyaan.meta?.scale_min || 1) + 1)"
                                                    :key="n"
                                                    class="group relative flex flex-1 cursor-pointer flex-col items-center gap-3"
                                                >
                                                    <div
                                                        class="absolute inset-0 rounded-xl bg-green-50 opacity-0 transition-opacity group-hover:opacity-100 dark:bg-green-900/20"
                                                        :class="{
                                                            'opacity-100':
                                                                form.answers[
                                                                    pertanyaan
                                                                        .id
                                                                ] === (pertanyaan.meta?.scale_min || 1) + n - 1,
                                                        }"
                                                        style="
                                                            z-index: 0;
                                                            transform: scale(
                                                                1.2
                                                            );
                                                        "
                                                    ></div>
                                                    <input
                                                        v-model="
                                                            form.answers[
                                                                pertanyaan.id
                                                            ]
                                                        "
                                                        type="radio"
                                                        :name="
                                                            'q_' + pertanyaan.id
                                                        "
                                                        :value="(pertanyaan.meta?.scale_min || 1) + n - 1"
                                                        class="relative z-10 h-6 w-6 cursor-pointer border-slate-300 text-green-600 focus:ring-green-500"
                                                        :disabled="isReadonly"
                                                    />
                                                    <span
                                                        class="relative z-10 text-sm font-black text-slate-600 dark:text-slate-300"
                                                        :class="{
                                                            'text-green-600 dark:text-green-400':
                                                                form.answers[
                                                                    pertanyaan
                                                                        .id
                                                                ] === (pertanyaan.meta?.scale_min || 1) + n - 1,
                                                        }"
                                                        >{{ (pertanyaan.meta?.scale_min || 1) + n - 1 }}</span
                                                    >
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Matrix -->
                                        <div
                                            v-if="pertanyaan.tipe === 'matrix'"
                                            class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm dark:border-slate-800"
                                        >
                                            <table class="w-full text-sm">
                                                <thead
                                                    class="border-b border-slate-100 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-800/80"
                                                >
                                                    <tr>
                                                        <th
                                                            class="min-w-50 p-5 text-left font-black text-slate-900 dark:text-white"
                                                        >
                                                            Pernyataan
                                                        </th>
                                                        <th
                                                            v-for="(col, ci) in (pertanyaan.meta?.columns?.length ? pertanyaan.meta.columns : ['1','2','3','4','5'])"
                                                            :key="ci"
                                                            class="w-16 p-4 text-center font-black text-slate-900 dark:text-white text-xs"
                                                        >
                                                            {{ col }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="divide-y divide-gray-50 dark:divide-slate-800/50"
                                                >
                                                    <tr
                                                        v-for="(
                                                            row, rIdx
                                                        ) in pertanyaan.meta
                                                            ?.rows"
                                                        :key="row"
                                                        class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30"
                                                        :class="{
                                                            'bg-slate-50/30 dark:bg-slate-800/10':
                                                                isEvenIndex(
                                                                    rIdx,
                                                                ),
                                                        }"
                                                    >
                                                        <td
                                                            class="p-5 font-medium text-slate-700 dark:text-slate-300"
                                                        >
                                                            {{ row }}
                                                        </td>
                                                        <td
                                                            v-for="(col, ci) in (pertanyaan.meta?.columns?.length ? pertanyaan.meta.columns : ['1','2','3','4','5'])"
                                                            :key="ci"
                                                            class="p-4 text-center"
                                                        >
                                                            <label
                                                                class="flex w-full cursor-pointer justify-center p-2"
                                                            >
                                                                <input
                                                                    v-model="
                                                                        form
                                                                            .answers[
                                                                            pertanyaan
                                                                                .id
                                                                        ][row]
                                                                    "
                                                                    type="radio"
                                                                    :name="
                                                                        'q_' +
                                                                        pertanyaan.id +
                                                                        '_' +
                                                                        rIdx
                                                                    "
                                                                    :value="ci + 1"
                                                                    class="h-5 w-5 cursor-pointer border-slate-300 text-green-600 focus:ring-green-500"
                                                                    :disabled="
                                                                        isReadonly
                                                                    "
                                                                />
                                                            </label>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Field Error -->
                                        <div
                                            v-if="
                                                form.errors[
                                                    `answers.${pertanyaan.id}`
                                                ]
                                            "
                                            class="mt-2 flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm font-bold text-red-600 dark:bg-red-900/20 dark:text-red-400"
                                        >
                                            <AlertCircle
                                                class="h-4 w-4 shrink-0"
                                            />
                                            <span
                                                >Pertanyaan ini wajib diisi
                                                dengan benar.</span
                                            >
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer / Actions -->
                <div
                    class="mt-6 flex flex-col-reverse gap-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div>
                        <button
                            v-if="currentVisibleIndex > 0"
                            type="button"
                            @click="prevSection"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 hover:text-slate-900 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        >
                            <ArrowLeft class="h-5 w-5" />
                            KEMBALI
                        </button>
                        <span
                            v-else
                            class="hidden px-4 text-sm text-slate-500 sm:inline-block"
                            >Langkah awal kuesioner</span
                        >
                    </div>

                    <div>
                        <button
                            v-if="!isLastVisibleSection"
                            type="button"
                            @click="nextSection"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0C447C] px-8 py-4 text-sm font-black text-white shadow-lg shadow-[#0C447C]/30 transition-all hover:-translate-y-0.5 hover:bg-[#0C447C]/90 hover:shadow-xl hover:shadow-[#0C447C]/40 active:translate-y-0 sm:w-auto dark:bg-[#85B7EB] dark:text-slate-900 dark:shadow-[#85B7EB]/20 dark:hover:bg-[#85B7EB]/90"
                        >
                            SELANJUTNYA
                            <ArrowRight class="h-5 w-5" />
                        </button>

                        <button
                            v-else
                            type="submit"
                            :disabled="form.processing || props.hasResponded"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0C447C] px-8 py-4 text-sm font-black text-white shadow-lg shadow-[#0C447C]/30 transition-all hover:-translate-y-0.5 hover:bg-[#0C447C]/90 hover:shadow-xl hover:shadow-[#0C447C]/40 active:translate-y-0 disabled:transform-none disabled:opacity-50 disabled:shadow-none sm:w-auto dark:bg-[#85B7EB] dark:text-slate-900 dark:shadow-[#85B7EB]/20 dark:hover:bg-[#85B7EB]/90"
                        >
                            <Send v-if="!form.processing" class="h-5 w-5" />
                            <span
                                v-else
                                class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></span>
                            KIRIM JAWABAN KUESIONER
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </TraceAlumniLayout>
</template>
