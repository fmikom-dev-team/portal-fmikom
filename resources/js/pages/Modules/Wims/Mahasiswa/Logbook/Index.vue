<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronDown,
    Clock3,
    ImagePlus,
    List,
    ListOrdered,
    LoaderCircle,
    NotebookPen,
    CheckCircle2,
    XCircle,
    Clock,
    ChevronLeft,
    ChevronRight,
    Eye,
    X,
    Flame,
    Calendar,
    Award,
    BarChart3,
    Info,
} from 'lucide-vue-next';
import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatIndonesianDateLabel } from '@/lib/date';
import logbookRoutes from '@/routes/wims/logbook';

defineOptions({ layout: StudentLayout });

/* --- Types ------------------------------------------- */
type FlashProps   = { success?: string; error?: string | null };
type LogbookPhoto = { id: number; file_path?: string | null; url?: string | null };
type LogbookItem  = {
    id: number;
    tanggal?: string | null;
    tanggal_label?: string | null;
    jam_mulai?: string | null;
    jam_selesai?: string | null;
    aktivitas_harian?: string | null;
    kompetensi_dicapai?: string | null;
    catatan_mitra?: string | null;
    status?: string | null;
    is_revisable?: boolean;
    photos?: LogbookPhoto[];
};
type PageProps = { flash?: FlashProps; errors?: Record<string, string | undefined> };

const props = defineProps<{
    todayLabel?: string | null;
    hasPendaftaran?: boolean;
    canSubmitToday?: boolean;
    submitBlockedMessage?: string | null;
    todayLogbook?: LogbookItem | null;
    logbooks?: LogbookItem[];
}>();

const page               = usePage<PageProps>();
const photoInput         = ref<HTMLInputElement | null>(null);
const aktivitasTextarea  = ref<HTMLTextAreaElement | null>(null);
const kompetensiTextarea = ref<HTMLTextAreaElement | null>(null);
const previewUrls        = ref<string[]>([]);
const selectedRevisionId = ref<number | null>(props.todayLogbook?.status === 'revisi' ? props.todayLogbook.id : null);

/* --- Feature 1: Lightbox ------------------------------ */
const lightboxOpen   = ref(false);
const lightboxUrl    = ref('');
const lightboxIndex  = ref(0);
const lightboxImages = ref<string[]>([]);

const openLightbox = (images: string[], index: number) => {
    lightboxImages.value = images;
    lightboxIndex.value  = index;
    lightboxUrl.value    = images[index];
    lightboxOpen.value   = true;
};
const closeLightbox = () => { lightboxOpen.value = false; };
const prevLightbox  = () => {
    lightboxIndex.value = (lightboxIndex.value - 1 + lightboxImages.value.length) % lightboxImages.value.length;
    lightboxUrl.value   = lightboxImages.value[lightboxIndex.value];
};
const nextLightbox  = () => {
    lightboxIndex.value = (lightboxIndex.value + 1) % lightboxImages.value.length;
    lightboxUrl.value   = lightboxImages.value[lightboxIndex.value];
};

/* --- Feature 3: Streak Counter ----------------------- */
const streakCount = computed(() => {
    const logs = [...(props.logbooks ?? [])].sort(
        (a, b) => new Date(b.tanggal ?? 0).getTime() - new Date(a.tanggal ?? 0).getTime()
    );
    if (!logs.length) return 0;
    let streak = 1;
    for (let i = 1; i < logs.length; i++) {
        const prev = new Date(logs[i - 1].tanggal ?? 0);
        const curr = new Date(logs[i].tanggal ?? 0);
        const diff = (prev.getTime() - curr.getTime()) / (1000 * 60 * 60 * 24);
        if (diff === 1) streak++;
        else break;
    }
    return streak;
});
const streakMilestone = computed(() => {
    const s = streakCount.value;
    const milestones = [7, 14, 30, 60];
    const next = milestones.find(m => s < m) ?? 60;
    const prev = milestones[milestones.indexOf(next) - 1] ?? 0;
    const diff = next - prev;
    return { next, progress: diff > 0 ? Math.min(Math.round(((s - prev) / diff) * 100), 100) : 100, allDone: s >= 60 };
});

/* --- Feature 4: Stats Summary ------------------------ */
const stats = computed(() => {
    const all      = props.logbooks ?? [];
    const total    = all.length;
    const approved = all.filter(l => l.status === 'approved' || l.status === 'disetujui').length;
    const pending  = all.filter(l => !l.status || l.status === 'pending').length;
    const rejected = all.filter(l => l.status === 'rejected' || l.status === 'revisi').length;
    return { total, approved, pending, rejected };
});

/* --- Form --------------------------------------------- */
const form = useForm({
    jam_mulai: '',
    jam_selesai: '',
    aktivitas_harian: '',
    kompetensi_dicapai: '',
    photos: [] as File[],
});

const flash            = computed(() => page.props.flash ?? {});
const pageErrors       = computed(() => page.props.errors ?? {});
const hasTodayLogbook  = computed(() => Boolean(props.todayLogbook));
const hasPendaftaran   = computed(() => Boolean(props.hasPendaftaran));
const canSubmitToday   = computed(() => Boolean(props.canSubmitToday));
const revisionSourceLogbook = computed(() =>
    (props.logbooks ?? []).find((item) => item.id === selectedRevisionId.value) ?? null
);
const isRevisionMode = computed(() => revisionSourceLogbook.value?.status === 'revisi');
const displayLogbook = computed(() => isRevisionMode.value ? revisionSourceLogbook.value : (props.todayLogbook ?? null));
const hasLockedTodayLogbook = computed(() =>
    hasTodayLogbook.value && props.todayLogbook?.status !== 'revisi'
);
const aktivitasLength  = computed(() => form.aktivitas_harian.trim().length);
const kompetensiLength = computed(() => form.kompetensi_dicapai.trim().length);

const isSubmitDisabled = computed(() =>
    !hasPendaftaran.value ||
    (!isRevisionMode.value && !canSubmitToday.value) ||
    (!isRevisionMode.value && hasLockedTodayLogbook.value) ||
    !form.jam_mulai ||
    !form.jam_selesai ||
    form.jam_selesai <= form.jam_mulai ||
    aktivitasLength.value < 20 ||
    kompetensiLength.value < 10 ||
    form.processing
);
const formDisabled = computed(() =>
    form.processing || !hasPendaftaran.value || (!isRevisionMode.value && (!canSubmitToday.value || hasLockedTodayLogbook.value))
);
const globalError = computed(() =>
    (!hasPendaftaran.value ? 'Data pendaftaran magang belum tersedia.' : undefined) ||
    (!isRevisionMode.value && !canSubmitToday.value ? (props.submitBlockedMessage || 'Logbook hanya bisa diisi saat periode magang sedang aktif.') : undefined) ||
    pageErrors.value.logbook ||
    pageErrors.value.photos ||
    pageErrors.value['photos.0'] ||
    flash.value.error
);

const activePhotoCount   = computed(() =>
    form.photos.length > 0 ? form.photos.length : (displayLogbook.value?.photos?.length ?? 0)
);
const todayStatusText    = computed(() => statusLabel(displayLogbook.value?.status));
const todayStatusVariant = computed(() => statusVariant(displayLogbook.value?.status));

/* --- Helpers ------------------------------------------ */
const statusLabel = (s?: string | null) => {
    if (s === 'approved' || s === 'disetujui') return 'Disetujui';
    if (s === 'rejected') return 'Ditolak';
    if (s === 'revisi')   return 'Revisi';
    return 'Menunggu Review';
};
const statusVariant = (s?: string | null): 'approved' | 'rejected' | 'pending' => {
    if (s === 'approved' || s === 'disetujui') return 'approved';
    if (s === 'rejected' || s === 'revisi')    return 'rejected';
    return 'pending';
};
const statusBadgeClass = (s?: string | null) => {
    const v = statusVariant(s);
    if (v === 'approved') return 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30';
    if (v === 'rejected') return 'bg-red-50 dark:bg-red-500/15 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/30';
    return 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/30';
};
const statusDotClass = (s?: string | null) => {
    const v = statusVariant(s);
    if (v === 'approved') return 'bg-emerald-500';
    if (v === 'rejected') return 'bg-red-500';
    return 'bg-amber-400';
};
const logbookDateLabel = (logbook?: Pick<LogbookItem, 'tanggal_label' | 'tanggal'> | null, fallback?: string | null) => {
    if (logbook?.tanggal_label) {
        return formatIndonesianDateLabel(logbook.tanggal_label);
    }

    if (logbook?.tanggal) {
        return formatIndonesianDateLabel(logbook.tanggal);
    }

    return formatIndonesianDateLabel(fallback);
};

/* --- Photo helpers ------------------------------------ */
const revokePreviews = () => {
    previewUrls.value.forEach(u => URL.revokeObjectURL(u));
    previewUrls.value = [];
};
watch(() => form.photos, (files) => {
    revokePreviews();
    previewUrls.value = files.map(f => URL.createObjectURL(f));
}, { deep: true });
onBeforeUnmount(revokePreviews);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files  = Array.from(target.files ?? []);
    if (!files.length || formDisabled.value) { target.value = ''; return; }
    const slots  = 3 - form.photos.length;
    if (slots <= 0) { target.value = ''; return; }
    form.clearErrors('photos');
    form.clearErrors('photos.0');
    form.photos.push(...files.slice(0, slots));
    target.value = '';
};
const removePhoto    = (i: number) => form.photos.splice(i, 1);
const openFilePicker = () => {
    if (formDisabled.value || form.photos.length >= 3) return;
    photoInput.value?.click();
};
const resetDraft = () => {
    revokePreviews();
    form.reset();
    if (photoInput.value) photoInput.value.value = '';
};

/* --- List template helpers ---------------------------- */
const insertListTemplate = (
    template: string,
    textarea: HTMLTextAreaElement | null,
    currentValue: string,
    applyValue: (v: string) => void,
) => {
    if (formDisabled.value) return;
    const prefix      = currentValue.length === 0 ? '' : currentValue.endsWith('\n') ? '' : '\n';
    const basePosition = textarea?.selectionStart ?? currentValue.length;
    const nextValue   = currentValue.length === 0
        ? template
        : currentValue.slice(0, basePosition) + `${prefix}${template}` + currentValue.slice(textarea?.selectionEnd ?? basePosition);
    applyValue(nextValue);
    requestAnimationFrame(() => {
        if (!textarea) return;
        textarea.focus();
        const fml = template.startsWith('- ') ? 2 : 3;
        const cp  = currentValue.length === 0 ? fml : basePosition + prefix.length + fml;
        textarea.setSelectionRange(cp, cp);
    });
};
const insertActivityTemplate   = (t: string) => insertListTemplate(t, aktivitasTextarea.value, form.aktivitas_harian, v => { form.aktivitas_harian = v; });
const insertCompetencyTemplate = (t: string) => insertListTemplate(t, kompetensiTextarea.value, form.kompetensi_dicapai, v => { form.kompetensi_dicapai = v; });

const handleListEnter = (
    event: KeyboardEvent,
    currentValue: string,
    applyValue: (v: string) => void,
) => {
    if (event.key !== 'Enter' || event.shiftKey || event.altKey || event.ctrlKey || event.metaKey || event.isComposing) return;
    const textarea = event.target as HTMLTextAreaElement | null;
    if (!textarea || formDisabled.value) return;
    const { selectionStart: ss, selectionEnd: se } = textarea;
    if (ss !== se) return;
    const value         = currentValue;
    const lineStart     = value.lastIndexOf('\n', ss - 1) + 1;
    const lineEndI      = value.indexOf('\n', ss);
    const lineEnd       = lineEndI === -1 ? value.length : lineEndI;
    const currentLine   = value.slice(lineStart, lineEnd);
    const beforeCursor  = value.slice(lineStart, ss);
    const afterCursor   = value.slice(ss, lineEnd);
    const numberedMatch = beforeCursor.match(/^(\s*)(\d+)\.\s(.*)$/);
    const bulletMatch   = beforeCursor.match(/^(\s*)-\s(.*)$/);
    if (numberedMatch) {
        event.preventDefault();
        const [, indent, number, content] = numberedMatch;
        if (`${content}${afterCursor}`.trim().length === 0 && currentLine.trim() === `${number}.`) {
            applyValue(value.slice(0, lineStart) + value.slice(lineEnd));
            requestAnimationFrame(() => textarea.setSelectionRange(lineStart, lineStart));
            return;
        }
        const nm = `\n${indent}${Number(number) + 1}. `;
        applyValue(value.slice(0, ss) + nm + value.slice(se));
        requestAnimationFrame(() => textarea.setSelectionRange(ss + nm.length, ss + nm.length));
        return;
    }
    if (bulletMatch) {
        event.preventDefault();
        const [, indent, content] = bulletMatch;
        if (`${content}${afterCursor}`.trim().length === 0 && currentLine.trim() === '-') {
            applyValue(value.slice(0, lineStart) + value.slice(lineEnd));
            requestAnimationFrame(() => textarea.setSelectionRange(lineStart, lineStart));
            return;
        }
        const nm = `\n${indent}- `;
        applyValue(value.slice(0, ss) + nm + value.slice(se));
        requestAnimationFrame(() => textarea.setSelectionRange(ss + nm.length, ss + nm.length));
    }
};
const handleActivityEnter   = (e: KeyboardEvent) => handleListEnter(e, form.aktivitas_harian, v => { form.aktivitas_harian = v; });
const handleCompetencyEnter = (e: KeyboardEvent) => handleListEnter(e, form.kompetensi_dicapai, v => { form.kompetensi_dicapai = v; });

const submit = () => {
    if (isSubmitDisabled.value) return;

    const requestUrl = isRevisionMode.value && revisionSourceLogbook.value
        ? logbookRoutes.update.url({ logbook: revisionSourceLogbook.value.id })
        : logbookRoutes.store.url();

    form
        .transform((data) => ({
            ...data,
            ...(isRevisionMode.value ? { _method: 'PUT' } : {}),
        }))
        .post(requestUrl, {
        forceFormData: true,
        onSuccess: () => {
            selectedRevisionId.value = null;
            resetDraft();
        },
    });
};

const startRevision = (logbook: LogbookItem) => {
    if (!logbook.is_revisable) return;
    selectedRevisionId.value = logbook.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const resetRevisionMode = () => {
    selectedRevisionId.value = props.todayLogbook?.status === 'revisi' ? props.todayLogbook.id : null;
    resetDraft();
};

watch(
    displayLogbook,
    (logbook) => {
        revokePreviews();
        form.reset();

        if (photoInput.value) {
            photoInput.value.value = '';
        }

        form.jam_mulai = logbook?.jam_mulai ?? '';
        form.jam_selesai = logbook?.jam_selesai ?? '';
        form.aktivitas_harian = logbook?.aktivitas_harian ?? '';
        form.kompetensi_dicapai = logbook?.kompetensi_dicapai ?? '';
        form.photos = [];
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Logbook Harian" />

    <!-- -- Lightbox --------------------------------------- -->
    <Teleport to="body">
        <Transition name="lb">
            <div
                v-if="lightboxOpen"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm"
                @click.self="closeLightbox"
            >
                <button
                    class="absolute top-4 right-4 flex size-9 items-center justify-center rounded-full bg-wims-card/10 text-white transition hover:bg-wims-card/20"
                    @click="closeLightbox"
                >
                    <X class="size-4" />
                </button>
                <button
                    v-if="lightboxImages.length > 1"
                    class="absolute left-4 flex size-9 items-center justify-center rounded-full bg-wims-card/10 text-white transition hover:bg-wims-card/20"
                    @click="prevLightbox"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <img
                    :src="lightboxUrl"
                    alt="Preview foto"
                    class="max-h-[88vh] max-w-[88vw] rounded-xl object-contain shadow-2xl"
                />
                <button
                    v-if="lightboxImages.length > 1"
                    class="absolute right-4 flex size-9 items-center justify-center rounded-full bg-wims-card/10 text-white transition hover:bg-wims-card/20"
                    @click="nextLightbox"
                >
                    <ChevronRight class="size-4" />
                </button>
                <div v-if="lightboxImages.length > 1" class="absolute bottom-4 flex gap-1.5">
                    <span
                        v-for="(_, i) in lightboxImages" :key="i"
                        class="size-1.5 rounded-full transition-all"
                        :class="i === lightboxIndex ? 'bg-wims-card w-4' : 'bg-wims-card/40'"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>

    <div class="min-h-full">
        <!-- Animated background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-blue-400/[0.06] to-cyan-400/[0.04] blur-3xl animate-pulse dark:from-blue-500/[0.04] dark:to-cyan-400/[0.025]" style="animation-duration: 7s;" />
            <div class="absolute top-1/3 -left-16 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-indigo-400/[0.05] to-violet-400/[0.03] blur-3xl animate-pulse dark:from-blue-600/[0.03] dark:to-indigo-500/[0.02]" style="animation-duration: 10s; animation-delay: 3s;" />
            <div class="absolute -bottom-16 right-1/3 h-[350px] w-[350px] rounded-full bg-gradient-to-tl from-cyan-400/[0.04] to-blue-400/[0.03] blur-3xl animate-pulse dark:from-cyan-400/[0.025] dark:to-blue-500/[0.02]" style="animation-duration: 12s; animation-delay: 5s;" />
            <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.04]" style="background-image: radial-gradient(circle, currentColor 0.5px, transparent 0.5px); background-size: 24px 24px;" />
        </div>

        <div class="relative space-y-4 lg:space-y-5">

            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-2xl lg:rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 dark:from-[#1D3E8A] dark:via-[#1F5FCC] dark:to-[#0E7ACF]" />
                <div class="absolute inset-0 bg-gradient-to-t from-blue-700/30 via-transparent to-transparent dark:from-[#07152f]/55 dark:via-[#0f172a]/10 dark:to-transparent" />
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.18),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.18),_transparent_32%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.10),_transparent_28%)]" />
                <div class="absolute top-0 right-0 h-72 w-72 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/[0.08] blur-3xl dark:bg-white/[0.04]" />
                <div class="absolute bottom-0 left-0 h-56 w-56 -translate-x-1/4 translate-y-1/3 rounded-full bg-blue-900/20 blur-3xl dark:bg-slate-950/35" />
                <div class="absolute top-6 right-8 hidden h-16 w-16 rounded-full border border-white/[0.08] dark:border-white/[0.05] lg:block" />
                <div class="absolute top-12 right-16 hidden h-28 w-28 rounded-full border border-white/[0.05] dark:border-white/[0.03] lg:block" />
                <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.03]" style="background-image: radial-gradient(circle, white 0.5px, transparent 0.5px); background-size: 20px 20px;" />

                <div class="relative px-5 py-6 sm:px-7 sm:py-7 lg:px-8 lg:py-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <h1 class="text-[20px] font-bold tracking-tight text-white sm:text-[24px] lg:text-[30px] leading-[1.15]">
                                Logbook Harian
                            </h1>
                            <p class="mt-2 text-[13px] leading-relaxed text-white/78 dark:text-white/70 sm:text-sm">
                                Catat progres magang, kompetensi yang dicapai, dan dokumentasi kegiatan harianmu.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <div class="flex items-center gap-2.5">
                                    <Flame class="size-5 text-orange-300" :class="streakCount > 0 ? 'animate-pulse' : ''" />
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Streak</p>
                                        <p class="text-lg font-bold tabular-nums text-white leading-none">{{ streakCount }} hari</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Total Entri</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-white leading-none">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Stats Bar -->
            <div class="grid grid-cols-3 gap-3 lg:gap-4">
                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(16,185,129,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-500/15">
                            <Award class="size-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <p class="mt-2.5 text-[18px] font-bold tabular-nums text-wims-text leading-none">{{ stats.approved }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Disetujui</p>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(245,158,11,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-500/15">
                            <Clock class="size-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <p class="mt-2.5 text-[18px] font-bold tabular-nums text-wims-text leading-none">{{ stats.pending }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Menunggu</p>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(239,68,68,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-rose-500 to-red-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-500/15">
                            <XCircle class="size-4 text-rose-600 dark:text-rose-400" />
                        </div>
                        <p class="mt-2.5 text-[18px] font-bold tabular-nums text-wims-text leading-none">{{ stats.rejected }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Revisi</p>
                    </div>
                </div>
            </div>

            <!-- NEW ELEMENT: Streak Achievement Card -->
            <div class="relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-orange-500 via-amber-400 to-yellow-400" />
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-50 dark:from-orange-500/20 dark:to-amber-500/10">
                                <Flame class="size-5 text-orange-500 dark:text-orange-400" :class="streakCount > 0 ? 'animate-bounce' : ''" style="animation-duration: 2s;" />
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Streak Harian</p>
                                <p class="text-lg font-bold text-wims-text">{{ streakCount }} Hari Beruntun</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 sm:gap-4">
                            <template v-for="milestone in [7, 14, 30]" :key="milestone">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="flex size-8 items-center justify-center rounded-full border-2 transition-all duration-500"
                                        :class="streakCount >= milestone ? 'border-amber-400 bg-amber-50 dark:bg-amber-500/20' : 'border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40'">
                                        <Award class="size-3.5" :class="streakCount >= milestone ? 'text-amber-500' : 'text-slate-300 dark:text-slate-600'" />
                                    </div>
                                    <span class="text-[9px] font-bold" :class="streakCount >= milestone ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500'">{{ milestone }}d</span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-[10px] text-slate-400 dark:text-slate-500">
                            <span>Menuju {{ streakMilestone.allDone ? '60' : streakMilestone.next }} hari</span>
                            <span>{{ streakMilestone.allDone ? 100 : streakMilestone.progress }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                            <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-400 transition-all duration-700"
                                :style="{ width: (streakMilestone.allDone ? 100 : streakMilestone.progress) + '%' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div v-if="flash.success" class="flex items-start gap-3 rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3">
                <CheckCircle2 class="mt-0.5 size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                <div>
                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Berhasil</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400">{{ flash.success }}</p>
                </div>
            </div>
            <div v-if="globalError" class="flex items-start gap-3 rounded-xl border border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 px-4 py-3">
                <XCircle class="mt-0.5 size-4 shrink-0 text-rose-500 dark:text-rose-400" />
                <div>
                    <p class="text-sm font-bold text-rose-800 dark:text-rose-300">Perlu diperbaiki</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-rose-700 dark:text-rose-400">{{ globalError }}</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between border-b border-wims-border/50 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400">
                            <NotebookPen class="size-4" />
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Form Harian</p>
                            <p class="text-sm font-bold text-wims-text">{{ isRevisionMode ? 'Revisi Logbook' : 'Logbook Hari Ini' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="hidden items-center gap-1.5 rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 sm:flex">
                            <CalendarDays class="size-3.5 text-blue-500 dark:text-blue-400" />
                            {{ logbookDateLabel(displayLogbook, todayLabel) }}
                        </div>
                        <span
                            v-if="displayLogbook"
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold"
                            :class="statusBadgeClass(displayLogbook?.status)"
                        >
                            <span class="size-1.5 rounded-full" :class="statusDotClass(displayLogbook?.status)" />
                            {{ todayStatusText }}
                        </span>
                    </div>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">

                    <!-- Read-only notice -->
                    <div
                        v-if="hasLockedTodayLogbook && !isRevisionMode"
                        class="flex items-start gap-3 rounded-xl border border-blue-200/60 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/10 px-4 py-3.5"
                    >
                        <Info class="mt-0.5 size-4 shrink-0 text-blue-500 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-bold text-blue-800 dark:text-blue-300">Mode Baca</p>
                            <p class="mt-0.5 text-xs leading-5 text-blue-700 dark:text-blue-300">
                                Entri hari ini sudah tersimpan dan tidak bisa diubah dari halaman ini.
                            </p>
                        </div>
                    </div>

                    <!-- Time inputs -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="space-y-1.5">
                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Jam Mulai</span>
                            <div class="relative">
                                <Clock3 class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-blue-500/80 dark:text-blue-300" />
                                <div
                                    v-if="hasLockedTodayLogbook"
                                    class="flex h-10 w-full items-center rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 pl-9 pr-3 text-sm font-bold text-wims-text"
                                >
                                    {{ form.jam_mulai || '-' }}
                                </div>
                                <input
                                    v-else
                                    v-model="form.jam_mulai"
                                    type="time"
                                    :disabled="formDisabled"
                                    class="h-10 w-full appearance-none rounded-xl border border-wims-border/60 bg-wims-card pl-9 pr-3 text-sm text-wims-text outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                            </div>
                            <p v-if="form.errors.jam_mulai" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.jam_mulai }}</p>
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Jam Selesai</span>
                            <div class="relative">
                                <Clock3 class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-blue-500/80 dark:text-blue-300" />
                                <div
                                    v-if="hasLockedTodayLogbook"
                                    class="flex h-10 w-full items-center rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 pl-9 pr-3 text-sm font-bold text-wims-text"
                                >
                                    {{ form.jam_selesai || '-' }}
                                </div>
                                <input
                                    v-else
                                    v-model="form.jam_selesai"
                                    type="time"
                                    :disabled="formDisabled"
                                    class="h-10 w-full appearance-none rounded-xl border border-wims-border/60 bg-wims-card pl-9 pr-3 text-sm text-wims-text outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                            </div>
                            <p v-if="form.errors.jam_selesai" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.jam_selesai }}</p>
                        </label>
                    </div>

                    <div class="border-t border-wims-border/40" />

                    <!-- Aktivitas Harian -->
                    <label class="block space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Deskripsi Aktivitas</span>
                                <DropdownMenu v-if="!hasLockedTodayLogbook">
                                    <DropdownMenuTrigger as-child>
                                        <button
                                            type="button"
                                            class="flex items-center gap-1 rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-2 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-400 transition-colors hover:border-blue-300/60 hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                        >
                                            <List class="size-3" />
                                            Format
                                            <ChevronDown class="size-3" />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" :side-offset="5" class="min-w-40 rounded-xl border-wims-border">
                                        <DropdownMenuItem class="gap-2 text-sm" @select="insertActivityTemplate('1. ')">
                                            <ListOrdered class="size-4" /> Daftar Bernomor
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="gap-2 text-sm" @select="insertActivityTemplate('- ')">
                                            <List class="size-4" /> Daftar Berpoin
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-1 w-14 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="aktivitasLength >= 20 ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-500'"
                                        :style="`width: ${Math.min((aktivitasLength / 20) * 100, 100)}%`"
                                    />
                                </div>
                                <span class="text-xs tabular-nums text-slate-400 dark:text-slate-500">{{ aktivitasLength }}/20</span>
                            </div>
                        </div>
                        <textarea
                            ref="aktivitasTextarea"
                            v-model="form.aktivitas_harian"
                            :disabled="formDisabled"
                            rows="5"
                            placeholder="Apa yang kamu kerjakan hari ini?"
                            @keydown="handleActivityEnter"
                            class="w-full resize-none rounded-xl border px-4 py-3 text-sm leading-6 outline-none transition-colors placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="hasLockedTodayLogbook
                                ? 'border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 font-medium text-wims-text'
                                : 'border-wims-border/60 bg-wims-card text-wims-text focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:opacity-50'"
                        />
                        <p v-if="!hasLockedTodayLogbook" class="text-xs text-slate-400 dark:text-slate-500">
                            Gunakan format poin jika ada lebih dari satu aktivitas.
                        </p>
                        <p v-if="form.errors.aktivitas_harian" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.aktivitas_harian }}</p>
                    </label>

                    <!-- Kompetensi -->
                    <label class="block space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Kompetensi Yang Dicapai</span>
                                <DropdownMenu v-if="!hasLockedTodayLogbook">
                                    <DropdownMenuTrigger as-child>
                                        <button
                                            type="button"
                                            class="flex items-center gap-1 rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-2 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-400 transition-colors hover:border-blue-300/60 hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                        >
                                            <List class="size-3" />
                                            Format
                                            <ChevronDown class="size-3" />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" :side-offset="5" class="min-w-40 rounded-xl border-wims-border">
                                        <DropdownMenuItem class="gap-2 text-sm" @select="insertCompetencyTemplate('1. ')">
                                            <ListOrdered class="size-4" /> Daftar Bernomor
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="gap-2 text-sm" @select="insertCompetencyTemplate('- ')">
                                            <List class="size-4" /> Daftar Berpoin
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-1 w-14 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="kompetensiLength >= 10 ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-500'"
                                        :style="`width: ${Math.min((kompetensiLength / 10) * 100, 100)}%`"
                                    />
                                </div>
                                <span class="text-xs tabular-nums text-slate-400 dark:text-slate-500">{{ kompetensiLength }}/10</span>
                            </div>
                        </div>
                        <textarea
                            ref="kompetensiTextarea"
                            v-model="form.kompetensi_dicapai"
                            :disabled="formDisabled"
                            rows="4"
                            placeholder="Contoh: Memahami API, slicing UI, debugging, dll."
                            @keydown="handleCompetencyEnter"
                            class="w-full resize-none rounded-xl border px-4 py-3 text-sm leading-6 outline-none transition-colors placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="hasLockedTodayLogbook
                                ? 'border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 font-medium text-wims-text'
                                : 'border-wims-border/60 bg-wims-card text-wims-text focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:opacity-50'"
                        />
                        <p v-if="!hasLockedTodayLogbook" class="text-xs text-slate-400 dark:text-slate-500">
                            Gunakan format poin jika ada lebih dari satu kompetensi.
                        </p>
                        <p v-if="form.errors.kompetensi_dicapai" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.kompetensi_dicapai }}</p>
                    </label>

                    <div class="border-t border-wims-border/40" />

                    <!-- Photo upload -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Lampiran Foto</span>
                            <span class="rounded-full border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-2.5 py-0.5 text-xs font-bold text-slate-500 dark:text-slate-400">
                                {{ activePhotoCount }} / 3
                            </span>
                        </div>

                        <input
                            ref="photoInput"
                            type="file"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            multiple
                            class="hidden"
                            @change="handleFileChange"
                        />

                        <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/30 p-4">
                            <template v-if="hasLockedTodayLogbook && !isRevisionMode && !form.photos.length">
                                <div v-if="(displayLogbook?.photos ?? []).length" class="grid grid-cols-3 gap-3">
                                    <div
                                        v-for="(photo, i) in displayLogbook?.photos ?? []"
                                        :key="photo.id"
                                        class="group relative aspect-square cursor-zoom-in overflow-hidden rounded-xl border border-wims-border/60 bg-wims-card"
                                        @click="openLightbox((displayLogbook?.photos ?? []).map(p => p.url ?? '').filter(Boolean), i)"
                                    >
                                        <img :src="photo.url ?? undefined" alt="Foto logbook" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-hover:bg-black/25">
                                            <Eye class="size-5 scale-0 text-white transition group-hover:scale-100" />
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="py-4 text-center text-sm text-slate-400 dark:text-slate-500">Tidak ada lampiran foto.</p>
                            </template>

                            <template v-else-if="isRevisionMode && !previewUrls.length && (displayLogbook?.photos ?? []).length">
                                <div class="mb-3 grid grid-cols-3 gap-3">
                                    <div
                                        v-for="(photo, i) in displayLogbook?.photos ?? []"
                                        :key="photo.id"
                                        class="group relative aspect-square cursor-zoom-in overflow-hidden rounded-xl border border-wims-border/60 bg-wims-card"
                                        @click="openLightbox((displayLogbook?.photos ?? []).map(p => p.url ?? '').filter(Boolean), i)"
                                    >
                                        <img :src="photo.url ?? undefined" alt="Foto logbook revisi" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-hover:bg-black/25">
                                            <Eye class="size-5 scale-0 text-white transition group-hover:scale-100" />
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
                                    Jika Anda unggah foto baru saat revisi, lampiran lama akan diganti.
                                </p>
                            </template>

                            <template v-else>
                                <div v-if="previewUrls.length" class="mb-3 grid grid-cols-3 gap-3">
                                    <div
                                        v-for="(url, i) in previewUrls"
                                        :key="`${url}-${i}`"
                                        class="relative aspect-square overflow-hidden rounded-xl border border-wims-border/60 bg-wims-card"
                                    >
                                        <img :src="url" alt="Preview" class="h-full w-full cursor-zoom-in object-cover" @click="openLightbox(previewUrls, i)" />
                                        <button
                                            type="button"
                                            class="absolute top-1.5 right-1.5 flex size-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md transition hover:bg-red-600"
                                            @click="removePhoto(i)"
                                        >
                                            <X class="size-3" />
                                        </button>
                                    </div>
                                </div>

                                <button
                                    v-if="form.photos.length < 3"
                                    type="button"
                                    :disabled="formDisabled"
                                    class="flex w-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-wims-border/60 bg-wims-card py-6 text-center transition-colors hover:border-blue-300/60 hover:bg-blue-50 dark:hover:bg-blue-500/10 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="openFilePicker"
                                >
                                    <div class="flex size-9 items-center justify-center rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40">
                                        <ImagePlus class="size-4 text-slate-400 dark:text-slate-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-wims-text">Tambah Foto</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500">JPG, PNG · Maks 3 foto</p>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <p v-if="form.errors.photos" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.photos }}</p>
                        <p v-if="form.errors['photos.0']" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors['photos.0'] }}</p>
                    </div>

                    <!-- Catatan Mitra -->
                    <div
                        class="flex items-start gap-3 rounded-xl border p-4"
                        :class="displayLogbook?.catatan_mitra ? 'border-blue-200/60 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/10' : 'border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30'"
                    >
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg text-white"
                            :class="displayLogbook?.catatan_mitra ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
                        >
                            <NotebookPen class="size-4" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Catatan Pembimbing Mitra</p>
                            <p class="mt-1 text-sm leading-6" :class="displayLogbook?.catatan_mitra ? 'text-wims-text' : 'text-slate-400 dark:text-slate-500'">
                                {{ displayLogbook?.catatan_mitra || 'Belum ada catatan dari pembimbing mitra.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Lock notices -->
                    <div
                        v-if="!isRevisionMode && !canSubmitToday && !hasTodayLogbook"
                        class="flex items-center gap-3 rounded-xl border border-amber-200/60 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300"
                    >
                        <Clock class="size-4 shrink-0 text-amber-500 dark:text-amber-400" />
                        Pengisian logbook dikunci karena hari ini di luar rentang magang aktif.
                    </div>
                    <div
                        v-if="hasLockedTodayLogbook && !isRevisionMode"
                        class="rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3 text-center"
                    >
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                            Logbook tersimpan untuk {{ logbookDateLabel(displayLogbook, todayLabel) }}
                        </p>
                        <p class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">
                            Tunggu hari berikutnya untuk membuat entri baru.
                        </p>
                    </div>

                    <div
                        v-if="isRevisionMode"
                        class="flex items-center justify-between gap-3 rounded-xl border border-amber-200/60 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                                Perbaiki logbook {{ logbookDateLabel(displayLogbook) }}
                            </p>
                            <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">
                                Setelah dikirim ulang, status logbook akan kembali menjadi menunggu review mitra.
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            class="shrink-0 border-amber-200 bg-white text-amber-700 hover:bg-amber-100"
                            @click="resetRevisionMode"
                        >
                            Batal
                        </Button>
                    </div>

                    <!-- Submit button -->
                    <button
                        v-if="!hasLockedTodayLogbook || isRevisionMode"
                        type="button"
                        class="h-11 w-full rounded-xl text-sm font-bold transition-all active:scale-[0.98]"
                        :class="isSubmitDisabled
                            ? 'cursor-not-allowed bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500'
                            : 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'"
                        :disabled="isSubmitDisabled"
                        @click="submit"
                    >
                        <LoaderCircle v-if="form.processing" class="mx-auto size-5 animate-spin" />
                        <span v-else>{{ isRevisionMode ? 'Kirim Ulang Perbaikan' : 'Simpan Logbook' }}</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
input[type="time"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-clear-button,
input[type="time"]::-webkit-inner-spin-button {
    display: none;
}

input[type="time"] {
    color-scheme: light dark;
}

.lb-enter-active,
.lb-leave-active { transition: opacity 0.18s ease; }
.lb-enter-from,
.lb-leave-to { opacity: 0; }
</style>




