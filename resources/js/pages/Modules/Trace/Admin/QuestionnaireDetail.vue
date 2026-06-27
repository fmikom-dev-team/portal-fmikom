<script setup lang="ts">
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import {
    ChevronLeft,
    Plus,
    Eye,
    Save,
    Layout,
    AlertCircle,
    X,
    BarChart2,
    Settings,
    Circle,
    Loader2,
    CheckCircle2,
    ExternalLink,
} from "lucide-vue-next";
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import { Separator } from "@/components/ui/separator";
import { TStatusBadge } from "@/components/Trace";
import TraceAdminLayout from "@/layouts/TraceAdminLayout.vue";
import type { Kuesioner, KuesionerSection, Pertanyaan, OpsiJawaban } from '@/types/trace';

import { KUESIONER_CATEGORIES } from "@/utils/constants";
import { formatDateForInput } from "@/utils/date";
import QuestionnaireSectionCard from "./components/QuestionnaireSectionCard.vue";
import QuestionnaireSettingsCard from "./components/QuestionnaireSettingsCard.vue";
import QuestionnairePreview from "./components/QuestionnairePreview.vue";

const props = defineProps<{
    kuesioner: Kuesioner;
}>();

const breadcrumbs = computed(() => [
    { title: "Manajemen Kuesioner", href: "/trace/admin/questionnaires" },
    { title: form.judul || "Kuesioner Baru", href: "#" },
]);

const activeTab = ref("builder");
const showPreview = ref(false);

const openAnalytics = () => {
    window.open(`/trace/admin/questionnaires/${props.kuesioner.id}/analytics-page`, '_blank');
};

// ═══════════════════════════════════════════════════════════════
// FORM
// ═══════════════════════════════════════════════════════════════

const form = useForm<any>({
    judul: props.kuesioner.judul || "",
    subtitle: props.kuesioner.subtitle || "",
    kategori: props.kuesioner.kategori || KUESIONER_CATEGORIES[0],
    tahun:
        props.kuesioner.tahun?.toString() ||
        new Date().getFullYear().toString(),
    date_mulai: formatDateForInput(props.kuesioner.date_mulai),
    date_selesai: formatDateForInput(props.kuesioner.date_selesai),
    deskripsi: props.kuesioner.deskripsi || "",
    status: props.kuesioner.status || "active",
    sections: (props.kuesioner.sections || []).map((s: KuesionerSection) => ({
        id: s.id,
        judul: s.title ?? s.judul ?? "",
        deskripsi: s.description ?? s.deskripsi ?? "",
        conditions: s.conditions
            ? {
                  ...s.conditions,
                  pertanyaan_id:
                      s.conditions.pertanyaan_id == null
                          ? null
                          : isNaN(Number(s.conditions.pertanyaan_id))
                            ? s.conditions.pertanyaan_id
                            : Number(s.conditions.pertanyaan_id),
              }
            : null,
        pertanyaans: (s.pertanyaans || []).map((q: Pertanyaan) => {
            const rawMeta = q.meta || {};
            const meta = {
                target_table: "detail_jawabans",
                ...rawMeta,
                kategori: rawMeta.kategori || q.kategori || "Umum",
                acuan: rawMeta.acuan || (Array.isArray(q.acuan) ? q.acuan : []),
            };

            return {
                ...q,
                id: q.id,
                is_required: !!q.is_required,
                meta,
                tipe_data: q.tipe_data || "categorical",
                matrix_rows: rawMeta.rows || q.matrix_rows || [],
                logic_condition: null,
                opsi_jawabans: (q.opsi_jawabans || []).map((o: OpsiJawaban) => ({
                    ...o,
                    id: o.id,
                    skor: o.nilai ?? o.skor ?? 0,
                    nilai: o.nilai ?? o.skor ?? 0,
                })),
            };
        }),
    })),
});

// ═══════════════════════════════════════════════════════════════
// STATUS — computed from dates
// ═══════════════════════════════════════════════════════════════

const computedStatus = computed(() => {
    const status = form.status;
    const now = new Date();

    if (status !== "active" && status !== "published") {
        return {
            label: "Draft",
            badgeClass:
                "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400",
            dotClass: "bg-slate-400",
        };
    }

    if (form.date_mulai) {
        const start = new Date(form.date_mulai);
        if (start > now) {
            return {
                label: "Terjadwal",
                badgeClass:
                    "bg-[#0C447C]/10 text-[#0C447C] dark:bg-[#0C447C]/20 dark:text-[#85B7EB]",
                dotClass: "bg-[#0C447C]",
            };
        }
    }

    if (form.date_selesai) {
        const end = new Date(form.date_selesai);
        end.setHours(23, 59, 59, 999);
        if (end < now) {
            return {
                label: "Selesai",
                badgeClass:
                    "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400",
                dotClass: "bg-amber-500",
            };
        }
    }

    return {
        label: "Aktif",
        badgeClass:
            "bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400",
        dotClass: "bg-green-500",
    };
});

// ═══════════════════════════════════════════════════════════════
// AUTO-SAVE & DIRTY STATE
// ═══════════════════════════════════════════════════════════════

const saveState = ref<"idle" | "saving" | "saved">("idle");
const hasUnsavedChanges = ref(false);
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

// Deep watch form changes
watch(
    () =>
        JSON.stringify({
            judul: form.judul,
            subtitle: form.subtitle,
            kategori: form.kategori,
            tahun: form.tahun,
            date_mulai: form.date_mulai,
            date_selesai: form.date_selesai,
            deskripsi: form.deskripsi,
            status: form.status,
            sections: form.sections,
        }),
    () => {
        hasUnsavedChanges.value = true;
        saveState.value = "idle";

        // Auto-save after 30s of inactivity (only for existing questionnaires)
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        if (props.kuesioner.id) {
            autoSaveTimer = setTimeout(() => {
                doSave(true);
            }, 30000);
        }
    },
    { deep: true },
);

// Browser beforeunload
const handleBeforeUnload = (e: BeforeUnloadEvent) => {
    if (hasUnsavedChanges.value) {
        e.preventDefault();
        e.returnValue = "";
    }
};

onMounted(() => {
    window.addEventListener("beforeunload", handleBeforeUnload);
});

onBeforeUnmount(() => {
    window.removeEventListener("beforeunload", handleBeforeUnload);
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
});

// ═══════════════════════════════════════════════════════════════
// LEAVE CONFIRMATION (Inertia navigation)
// ═══════════════════════════════════════════════════════════════

const showLeaveDialog = ref(false);
const pendingLeaveUrl = ref("");

// Intercept back button click
const handleBack = () => {
    if (hasUnsavedChanges.value) {
        pendingLeaveUrl.value = "/trace/admin/questionnaires";
        showLeaveDialog.value = true;
    } else {
        router.get("/trace/admin/questionnaires");
    }
};

const confirmLeave = () => {
    hasUnsavedChanges.value = false;
    showLeaveDialog.value = false;
    router.get(pendingLeaveUrl.value || "/trace/admin/questionnaires");
};

const cancelLeave = () => {
    showLeaveDialog.value = false;
    pendingLeaveUrl.value = "";
};

// ═══════════════════════════════════════════════════════════════
// SECTIONS
// ═══════════════════════════════════════════════════════════════

const addSection = () => {
    form.sections.push({
        judul: "Bagian Baru",
        deskripsi: "",
        conditions: null,
        pertanyaans: [],
    });
};

const removeSection = (index: number) => {
    if (
        confirm(
            "Apakah Anda yakin ingin menghapus bagian ini? Semua pertanyaan di dalamnya akan ikut terhapus.",
        )
    ) {
        form.sections.splice(index, 1);
    }
};

// ═══════════════════════════════════════════════════════════════
// SAVE
// ═══════════════════════════════════════════════════════════════

const doSave = (isAutoSave = false) => {
    // Normalisasi
    form.sections.forEach((section: { pertanyaans: Array<{ tipe: string; matrix_rows?: string[]; opsi_jawabans?: unknown[]; is_required: boolean; logic_condition: null }> }) => {
        section.pertanyaans.forEach((question: { tipe: string; matrix_rows?: string[]; opsi_jawabans?: unknown[]; is_required: boolean; logic_condition: null }) => {
            if (question.tipe === "matrix" && !question.matrix_rows) {
                question.matrix_rows = [];
            }
            if (
                ["radio", "checkbox", "dropdown"].includes(question.tipe) &&
                !question.opsi_jawabans
            ) {
                question.opsi_jawabans = [];
            }
            question.is_required = !!question.is_required;
            question.logic_condition = null;
        });
    });

    saveState.value = "saving";

    const onSuccess = () => {
        saveState.value = "saved";
        hasUnsavedChanges.value = false;
        setTimeout(() => {
            if (saveState.value === "saved") saveState.value = "idle";
        }, 3000);
    };

    const onError = () => {
        saveState.value = "idle";
    };

    if (props.kuesioner.id) {
        form.put(`/trace/admin/questionnaires/${props.kuesioner.id}`, {
            preserveScroll: true,
            onSuccess,
            onError,
        });
    } else {
        form.post("/trace/admin/questionnaires", {
            onSuccess,
            onError,
        });
    }
};

const saveChanges = () => doSave(false);
</script>

<template>
    <Head :title="`Editor - ${form.judul || 'Kuesioner Baru'}`" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50/50 dark:bg-slate-950/20">
            <!-- ═══ STICKY TOOLBAR ═══ -->
            <div
                class="sticky top-0 z-30 border-b bg-background/80 backdrop-blur-md"
            >
                <!-- Top row: back + title + actions -->
                <div class="flex h-14 items-center justify-between gap-2 px-3 sm:h-16 sm:px-6">
                    <div class="flex min-w-0 items-center gap-2 sm:gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 shrink-0 rounded-full sm:h-9 sm:w-9"
                            @click="handleBack"
                        >
                            <ChevronLeft class="h-5 w-5" />
                        </Button>
                        <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
                            <span class="hidden text-sm font-medium text-muted-foreground sm:inline"
                                >Editor</span
                            >
                            <Separator orientation="vertical" class="hidden h-4 sm:block" />
                            <span
                                class="max-w-[120px] truncate text-sm font-bold sm:max-w-[200px]"
                                >{{ form.judul || "Kuesioner Tanpa Judul" }}</span
                            >
                            <!-- Status Badge — computed from dates -->
                            <TStatusBadge
                                :status="
                                    computedStatus.label === 'Aktif'
                                        ? 'active'
                                        : computedStatus.label === 'Draft'
                                          ? 'draft'
                                          : computedStatus.label === 'Terjadwal'
                                            ? 'pending'
                                            : 'closed'
                                "
                                :label="computedStatus.label"
                                size="sm"
                                class="ml-1 sm:ml-2"
                            />

                            <!-- Save State Indicator -->
                            <div
                                class="ml-1 flex items-center gap-1 text-[10px] font-bold sm:ml-2 sm:gap-1.5"
                            >
                                <template v-if="saveState === 'saving'">
                                    <Loader2
                                        class="h-3 w-3 animate-spin text-[#0C447C]"
                                    />
                                    <span class="hidden text-[#0C447C] sm:inline">Menyimpan...</span>
                                </template>
                                <template v-else-if="saveState === 'saved'">
                                    <CheckCircle2
                                        class="h-3 w-3 text-emerald-500"
                                    />
                                    <span class="hidden text-emerald-500 sm:inline">Tersimpan</span>
                                </template>
                                <template v-else-if="hasUnsavedChanges">
                                    <Circle
                                        class="h-2.5 w-2.5 fill-amber-400 text-amber-400"
                                    />
                                    <span class="hidden text-amber-500 sm:inline"
                                        >Belum disimpan</span
                                    >
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <div
                            class="flex items-center gap-0.5 rounded-xl bg-slate-100 p-0.5 sm:gap-1 sm:p-1 dark:bg-slate-800"
                        >
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-7 gap-1.5 rounded-lg px-2 transition-all sm:h-8 sm:gap-2 sm:px-3"
                                :class="
                                    activeTab === 'builder'
                                        ? 'bg-white text-[#0C447C] shadow-sm dark:bg-slate-700'
                                        : 'text-muted-foreground'
                                "
                                @click="activeTab = 'builder'"
                            >
                                <Settings class="h-4 w-4" />
                                <span class="hidden text-xs font-bold sm:inline">Builder</span>
                            </Button>
                            <Button
                                v-if="props.kuesioner.id"
                                variant="ghost"
                                size="sm"
                                class="h-7 gap-1.5 rounded-lg px-2 transition-all text-muted-foreground hover:text-[#0C447C] sm:h-8 sm:gap-2 sm:px-3 dark:hover:text-[#85B7EB]"
                                @click="openAnalytics"
                            >
                                <BarChart2 class="h-4 w-4" />
                                <span class="hidden text-xs font-bold sm:inline">Analisis</span>
                                <ExternalLink class="hidden h-3 w-3 sm:block" />
                            </Button>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-7 gap-1.5 rounded-xl px-2 sm:h-9 sm:gap-2 sm:px-3"
                            @click="showPreview = true"
                        >
                            <Eye class="h-4 w-4" />
                            <span class="hidden sm:inline">Pratinjau</span>
                        </Button>
                        <Button
                            size="sm"
                            class="h-7 gap-1.5 rounded-xl bg-[#0C447C] px-2.5 hover:bg-[#0C447C]/90 sm:h-9 sm:gap-2 sm:px-3"
                            :disabled="saveState === 'saving'"
                            @click="saveChanges"
                        >
                            <Loader2
                                v-if="saveState === 'saving'"
                                class="h-4 w-4 animate-spin"
                            />
                            <Save v-else class="h-4 w-4" />
                            <span class="hidden sm:inline">{{ saveState === "saving" ? "Menyimpan..." : "Simpan" }}</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ═══ BUILDER TAB ═══ -->
            <div
                v-if="activeTab === 'builder'"
                class="mx-auto max-w-4xl animate-in space-y-6 p-3 pb-32 duration-300 zoom-in-95 fade-in sm:space-y-8 sm:p-6 lg:p-10"
            >
                <div
                    v-if="form.errors.error"
                    class="flex animate-in items-start gap-3 rounded-2xl border border-destructive/20 bg-destructive/10 p-4 text-destructive fade-in slide-in-from-top-2"
                >
                    <AlertCircle class="mt-0.5 h-5 w-5 flex-shrink-0" />
                    <div class="flex-1">
                        <p
                            class="mb-1 text-sm font-black tracking-wider uppercase"
                        >
                            Gagal Menyimpan
                        </p>
                        <p
                            class="text-xs leading-relaxed font-medium opacity-90"
                        >
                            {{ form.errors.error }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="-mt-1 -mr-1 h-6 w-6 hover:bg-destructive/20"
                        @click="form.clearErrors('error')"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <QuestionnaireSettingsCard v-model:form="form" />

                <div
                    v-for="(section, sIndex) in form.sections"
                    :key="sIndex"
                    class="space-y-8"
                >
                    <QuestionnaireSectionCard
                        v-model:section="form.sections[sIndex]"
                        :sIndex="Number(sIndex)"
                        :totalSections="form.sections.length"
                        :allSections="form.sections"
                        @remove="removeSection(Number(sIndex))"
                    />
                </div>

                <Button
                    class="h-16 w-full gap-3 rounded-[2rem] border-2 border-dashed border-[#85B7EB]/40 bg-[#0C447C]/5 text-base font-black tracking-tight text-[#0C447C] shadow-sm transition-all hover:border-[#85B7EB] hover:bg-[#0C447C]/10 hover:shadow-md sm:h-20 sm:gap-4 sm:text-lg dark:border-[#85B7EB]/30 dark:bg-[#0C447C]/5"
                    @click="addSection"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#0C447C] text-white shadow-lg shadow-[#0C447C]/30 sm:h-10 sm:w-10 sm:rounded-2xl"
                    >
                        <Layout class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                    TAMBAH HALAMAN
                </Button>
            </div>

            <!-- ═══ PREVIEW DIALOG ═══ -->
            <Dialog v-model:open="showPreview">
                <DialogContent
                    class="max-h-[90vh] w-[95vw] max-w-4xl overflow-hidden rounded-2xl border-none p-0 shadow-2xl sm:rounded-3xl"
                >
                    <DialogHeader class="sr-only">
                        <DialogTitle>Pratinjau Kuesioner</DialogTitle>
                    </DialogHeader>
                    <QuestionnairePreview
                        :judul="form.judul"
                        :subtitle="form.subtitle"
                        :sections="form.sections"
                    />
                </DialogContent>
            </Dialog>

            <!-- ═══ LEAVE CONFIRMATION DIALOG ═══ -->
            <Dialog
                :open="showLeaveDialog"
                @update:open="(v: boolean) => (showLeaveDialog = v)"
            >
                <DialogContent class="max-w-md rounded-2xl">
                    <DialogHeader>
                        <DialogTitle>Perubahan Belum Disimpan</DialogTitle>
                        <DialogDescription>
                            Anda memiliki perubahan yang belum disimpan. Jika
                            Anda keluar sekarang, semua perubahan akan hilang.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <Button variant="outline" @click="cancelLeave"
                            >Batal</Button
                        >
                        <Button
                            class="bg-red-600 hover:bg-red-700 text-white"
                            @click="confirmLeave"
                        >
                            Keluar Tanpa Simpan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </TraceAdminLayout>
</template>
