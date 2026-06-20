<script setup lang="ts">
// resources/js/pages/Modules/Fast/Mahasiswa/Ajukan.vue
import FastLayout from '@/layouts/Modules/Fast/FastLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, nextTick, ref } from 'vue';
import {
    FileText,
    UploadCloud,
    X,
    Send,
    ChevronDown,
    Paperclip,
    CheckCircle2,
    Search,
    ArrowRight,
    AlertCircle,
    Filter,
    Clock3,
    BarChart3,
} from 'lucide-vue-next';
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    placeholder: string;
    options: FieldOption[];
    help?: string;
    repeatable?: boolean;
    add_label?: string;
    item_label?: string;
    sumber_data?: 'data_pemohon' | 'data_kampus' | 'data_sistem';
    editable_role?: 'mahasiswa' | 'admin' | 'sistem';
    mode_form_pemohon?: 'editable' | 'readonly' | 'hidden';
};
type JenisSuratOption = {
    id: number;
    categoryId?: number | null;
    nama: string;
    slug?: string | null;
    deskripsi?: string | null;
    fieldConfig?: FieldConfig[];
};
type CategoryOption = {
    id: number;
    nama: string;
    slug: string;
    deskripsi?: string | null;
};
type Summary = {
    total: number;
    diproses: number;
    selesai: number;
    ditolak: number;
    dibatalkan?: number;
};
const props = defineProps<{
    summary: Summary;
    categories: CategoryOption[];
    jenisSurats: JenisSuratOption[];
    selectedJenisId?: number | null;
    userType?: {
        value?: string | null;
        label?: string | null;
    };
    endpoints?: { basePath: string };
}>();
const basePath = computed(() => props.endpoints?.basePath ?? '/mahasiswa');
type FieldValue = string | boolean | string[] | null;
const form = useForm<{
    jenis_surat_id: string;
    keperluan: string;
    tanggal_kebutuhan: string;
    field_data: Record<string, FieldValue>;
    lampiran: File[];
}>({
    jenis_surat_id: '',
    keperluan: '',
    tanggal_kebutuhan: '',
    field_data: {},
    lampiran: [],
});
const searchQuery = ref('');
const activeCategory = ref<number | null>(null);
const applicantFieldRefs = ref<Record<string, HTMLElement | null>>({});
const filteredJenis = computed(() => {
    let list = props.jenisSurats;
    if (activeCategory.value !== null) {
        list = list.filter((j) => j.categoryId === activeCategory.value);
    }
    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        list = list.filter(
            (j) =>
                j.nama.toLowerCase().includes(q) ||
                (j.deskripsi ?? '').toLowerCase().includes(q),
        );
    }
    return list;
});
const selectedJenis = computed<JenisSuratOption | null>(
    () =>
        props.jenisSurats.find((j) => String(j.id) === form.jenis_surat_id) ??
        null,
);
const showFormModal = ref(false);
const summaryCards = computed(() => [
    {
        key: 'total',
        label: 'Total Jenis Surat',
        value: props.jenisSurats.length,
        icon: BarChart3,
        tone: 'blue',
        summary: 'Seluruh template surat yang bisa diajukan',
    },
    {
        key: 'diproses',
        label: 'Pengajuan Aktif',
        value: props.summary?.diproses ?? 0,
        icon: Clock3,
        tone: 'amber',
        summary: 'Pengajuan yang sedang berjalan',
    },
]);

function categoryName(id?: number | null): string {
    if (!id) return 'Lainnya';
    return props.categories.find((c) => c.id === id)?.nama ?? 'Lainnya';
}

function categorySlug(id?: number | null): string {
    if (!id) return 'umum';
    return (
        props.categories.find((c) => c.id === id)?.slug ??
        categoryName(id).toLowerCase()
    );
}

function categoryTone(id?: number | null): {
    badge: string;
    card: string;
    icon: string;
    dot: string;
    accent: string;
    glow: string;
    surface: string;
} {
    const slug = categorySlug(id);
    if (slug.includes('akademik')) {
        return {
            badge: 'border-blue-100 bg-blue-50 text-blue-700',
            card: 'border-blue-200/80 hover:border-blue-300',
            icon: 'bg-blue-50 text-blue-600 ring-blue-100',
            dot: 'bg-blue-500',
            accent: 'from-blue-500 via-blue-400 to-cyan-400',
            glow: 'shadow-blue-500/15',
            surface: 'bg-gradient-to-br from-white/95 via-slate-50/95 to-blue-50/55',
        };
    }
    if (slug.includes('penelitian')) {
        return {
            badge: 'border-emerald-100 bg-emerald-50 text-emerald-700',
            card: 'border-emerald-200/80 hover:border-emerald-300',
            icon: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            dot: 'bg-emerald-500',
            accent: 'from-emerald-500 via-green-400 to-teal-400',
            glow: 'shadow-emerald-500/15',
            surface: 'bg-gradient-to-br from-white/95 via-slate-50/95 to-blue-50/55',
        };
    }
    if (slug.includes('magang') || slug.includes('pkl')) {
        return {
            badge: 'border-amber-100 bg-amber-50 text-amber-700',
            card: 'border-amber-200/80 hover:border-amber-300',
            icon: 'bg-amber-50 text-amber-600 ring-amber-100',
            dot: 'bg-amber-500',
            accent: 'from-amber-500 via-orange-400 to-yellow-400',
            glow: 'shadow-amber-500/15',
            surface: 'bg-gradient-to-br from-white/95 via-slate-50/95 to-blue-50/55',
        };
    }
    return {
        badge: 'border-slate-200 bg-white text-slate-600',
        card: 'border-slate-200/80 hover:border-blue-300',
        icon: 'bg-white text-blue-600 ring-blue-100',
        dot: 'bg-blue-500',
        accent: 'from-blue-500 via-blue-400 to-cyan-400',
        glow: 'shadow-blue-500/15',
        surface: 'bg-gradient-to-br from-white/95 via-slate-50/95 to-blue-50/55',
    };
}

function cardState(jenis: JenisSuratOption) {
    return String(jenis.id) === form.jenis_surat_id
        ? 'border-blue-300 shadow-[0_14px_24px_rgba(59,130,246,0.12)] -translate-y-1'
        : '';
}

function openForm(jenis: JenisSuratOption) {
    form.jenis_surat_id = String(jenis.id);
    form.clearErrors();
    initFieldData(jenis);
    showFormModal.value = true;
}

function closeForm() {
    showFormModal.value = false;
}
function initFieldData(jenis: JenisSuratOption | null) {
    const values: Record<string, FieldValue> = {};
    for (const f of jenis?.fieldConfig ?? []) {
        if (f.type === 'checkbox') values[f.name] = false;
        else if (['checkbox-group', 'multiselect'].includes(f.type))
            values[f.name] = [];
        else values[f.name] = '';
    }
    form.field_data = values;
}
function isApplicantFieldVisible(field: FieldConfig) {
    return (field.mode_form_pemohon ?? 'editable') !== 'hidden';
}
function isApplicantFieldReadonly(field: FieldConfig) {
    return (field.mode_form_pemohon ?? 'editable') === 'readonly';
}
function isApplicantFieldDisabled(field: FieldConfig) {
    return isApplicantFieldReadonly(field);
}
function applicantFieldHelp(field: FieldConfig) {
    return field.help ?? '';
}

function setApplicantFieldRef(name: string, el: any) {
    applicantFieldRefs.value[name] = el instanceof HTMLElement ? el : null;
}

function isFieldMissing(field: FieldConfig, value: FieldValue): boolean {
    if (field.type === 'checkbox') {
        return value !== true;
    }

    if (['checkbox-group', 'multiselect'].includes(field.type)) {
        return !Array.isArray(value) || value.length === 0;
    }

    if (Array.isArray(value)) {
        return value.length === 0;
    }

    return value === null || value === undefined || String(value).trim() === '';
}

function scrollToApplicantField(fieldKey: string) {
    nextTick(() => {
        const selector = `[data-field-key="${fieldKey}"]`;
        const wrapper =
            applicantFieldRefs.value[fieldKey] ??
            (document.querySelector(selector) as HTMLElement | null);

        if (!wrapper) return;

        wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });

        const focusable = wrapper.querySelector(
            'input:not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled])',
        ) as HTMLElement | null;

        focusable?.focus();
    });
}

function scrollToFirstFieldError(errors: Record<string, string>) {
    const keys = Object.keys(errors);
    if (keys.length === 0) return;

    const preferredOrder = ['keperluan', 'tanggal_kebutuhan', 'lampiran'];
    const firstMatch =
        preferredOrder.find((key) => keys.includes(key)) ??
        keys.find((key) => key.startsWith('field_data.')) ??
        keys[0];

    if (firstMatch) {
        scrollToApplicantField(firstMatch);
    }
}

function validateRequiredFields(): string | null {
    form.clearErrors();

    if (!String(form.keperluan).trim()) {
        form.setError('keperluan', 'Wajib diisi.');
        return 'keperluan';
    }

    if (!String(form.tanggal_kebutuhan).trim()) {
        form.setError('tanggal_kebutuhan', 'Wajib diisi.');
        return 'tanggal_kebutuhan';
    }

    for (const field of selectedJenis.value?.fieldConfig ?? []) {
        if (
            !isApplicantFieldVisible(field) ||
            isApplicantFieldReadonly(field) ||
            !field.required
        ) {
            continue;
        }

        const value = form.field_data[field.name] as FieldValue;
        if (isFieldMissing(field, value)) {
            form.setError(`field_data.${field.name}`, 'Wajib diisi.');
            return `field_data.${field.name}`;
        }
    }

    return null;
}
onMounted(() => {
    if (props.selectedJenisId) {
        form.jenis_surat_id = String(props.selectedJenisId);
        initFieldData(selectedJenis.value);
        showFormModal.value = true;
    }
});
const fileInput = ref<HTMLInputElement | null>(null);
function triggerFilePick() {
    fileInput.value?.click();
}
function onFilesPicked(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;
    form.lampiran = [...form.lampiran, ...Array.from(input.files)];
    input.value = '';
}
function removeFile(index: number) {
    form.lampiran = form.lampiran.filter((_, i) => i !== index);
}
function fileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
function todayString() {
    return new Date().toISOString().slice(0, 10);
}
function submit() {
    const firstInvalid = validateRequiredFields();
    if (firstInvalid) {
        scrollToApplicantField(firstInvalid);
        return;
    }

    form.post(`${basePath.value}/submissions`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeForm();
        },
        onError: (errors) => {
            scrollToFirstFieldError(errors as Record<string, string>);
        },
    });
}
function fieldError(name: string): string | undefined {
    return (form.errors as Record<string, string>)[`field_data.${name}`];
}
</script>
<template>
    <FastLayout
        title="Ajukan Surat"
        subtitle="Pilih jenis surat, saring kategori, lalu lanjutkan ke formulir pengajuan."
        active-menu="submit"
        :breadcrumbs="[
            { label: 'Dashboard', href: `${basePath}/dashboard` },
            { label: 'Ajukan Surat' },
        ]"
    >
        <Head title="Ajukan Surat - FAST" />
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Summary -->
            <section class="grid gap-3 sm:grid-cols-2">
                <article
                    v-for="stat in summaryCards"
                    :key="stat.key"
                    class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/95 p-4 text-slate-900 shadow-[0_10px_30px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:scale-[1.005] hover:shadow-[0_14px_28px_rgba(59,130,246,0.10)]"
                    :class="stat.tone === 'blue' ? 'border-blue-200/80' : stat.tone === 'green' ? 'border-emerald-200/80' : stat.tone === 'amber' ? 'border-amber-200/80' : 'border-slate-200/80'"
                >
                    <div
                        class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r transition-all duration-300 group-hover:h-1.5"
                        :class="stat.tone === 'blue' ? 'from-blue-500 via-blue-400 to-cyan-400' : stat.tone === 'green' ? 'from-emerald-500 via-green-400 to-teal-400' : stat.tone === 'amber' ? 'from-amber-500 via-orange-400 to-yellow-400' : 'from-slate-400 via-slate-300 to-slate-200'"
                    />
                    <div
                        class="absolute -right-8 -top-8 size-24 rounded-full opacity-0 blur-2xl transition-all duration-300 group-hover:opacity-100"
                        :class="stat.tone === 'blue' ? 'shadow-blue-500/15' : stat.tone === 'green' ? 'shadow-emerald-500/15' : stat.tone === 'amber' ? 'shadow-amber-500/15' : 'shadow-slate-500/15'"
                    />

                    <div class="relative flex h-full min-h-[148px] flex-col justify-between gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-semibold tracking-[0.22em] text-slate-400 uppercase">
                                    {{ stat.label }}
                                </p>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-[2.15rem]">
                                    {{ stat.value }}
                                </p>
                            </div>
                            <div
                                class="grid size-11 shrink-0 place-items-center rounded-2xl ring-1 transition-all duration-300 group-hover:scale-[1.03] group-hover:-rotate-1"
                                :class="
                                    stat.tone === 'blue'
                                        ? 'bg-blue-50 text-blue-600 ring-blue-100'
                                        : stat.tone === 'green'
                                          ? 'bg-emerald-50 text-emerald-600 ring-emerald-100'
                                          : stat.tone === 'amber'
                                            ? 'bg-amber-50 text-amber-600 ring-amber-100'
                                            : 'bg-slate-100 text-slate-600 ring-slate-200'
                                "
                            >
                                <component :is="stat.icon" class="size-5" />
                            </div>
                        </div>

                        <p class="text-xs leading-relaxed text-slate-500">
                            {{ stat.summary }}
                        </p>
                    </div>
                </article>
            </section>

            <!-- Search & Filter -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">
                            Temukan jenis surat dengan cepat
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Gunakan pencarian untuk nama surat, lalu pilih kategori yang paling relevan.
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari jenis surat, misalnya observasi atau cuti..."
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 py-0 pr-10 pl-10 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            @click="searchQuery = ''"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                </div>
            </section>

            <!-- Letter Cards -->
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold tracking-[0.2em] text-slate-400 uppercase">
                            Jenis surat
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">
                            Pilih surat yang ingin diajukan
                        </h3>
                    </div>
                    <p class="text-sm text-slate-500">
                        {{ filteredJenis.length }} jenis surat tersedia
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="jenis in filteredJenis"
                        :key="jenis.id"
                        class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/95 p-5 text-slate-900 shadow-[0_10px_24px_rgba(15,23,42,0.08)] transform-gpu transition-all duration-300 hover:-translate-y-1 hover:scale-[1.005] hover:shadow-[0_16px_30px_rgba(59,130,246,0.14)]"
                        :class="[categoryTone(jenis.categoryId).card, cardState(jenis)]"
                    >
                        <div
                            class="absolute inset-0 transition-opacity duration-300 group-hover:opacity-100"
                            :class="categoryTone(jenis.categoryId).surface"
                        />
                        <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-white/12 to-blue-50/20 backdrop-blur-[1px]" />
                        <div class="absolute inset-x-4 top-3 h-8 rounded-full bg-white/55 blur-2xl opacity-70" />
                        <div class="absolute inset-x-6 bottom-[-12px] h-6 rounded-full bg-blue-500/18 blur-2xl opacity-0 transition-all duration-300 group-hover:opacity-75" />
                        <div class="absolute inset-x-2 bottom-0 h-px bg-slate-200/80 opacity-80" />
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r transition-all duration-300 group-hover:h-1.5"
                            :class="categoryTone(jenis.categoryId).accent"
                        />
                        <div
                            class="absolute -right-8 -top-8 size-24 rounded-full opacity-0 blur-2xl transition-all duration-300 group-hover:opacity-100"
                            :class="categoryTone(jenis.categoryId).glow"
                        />

                        <div class="relative flex h-full min-h-[176px] flex-col justify-between gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div
                                    class="grid size-11 shrink-0 place-items-center rounded-2xl ring-1 transition-all duration-300 group-hover:scale-[1.03] group-hover:-rotate-1"
                                    :class="categoryTone(jenis.categoryId).icon"
                                >
                                    <FileText class="size-5" />
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold"
                                    :class="categoryTone(jenis.categoryId).badge"
                                >
                                    <span
                                        class="mr-1.5 size-1.5 rounded-full"
                                        :class="categoryTone(jenis.categoryId).dot"
                                    />
                                    {{ categoryName(jenis.categoryId) }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col">
                                <h4 class="text-base font-semibold text-slate-900">
                                    {{ jenis.nama }}
                                </h4>
                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Klik untuk membuka form pengajuan surat.
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                <p class="text-xs text-slate-500">
                                    Sesuaikan data pada popup
                                </p>
                                <button
                                    type="button"
                                    class="fast-btn fast-btn-primary px-4 py-2 text-sm"
                                    @click="openForm(jenis)"
                                >
                                    Ajukan Surat
                                    <ArrowRight class="size-3.5" />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <Transition name="modal">
            <div
                v-if="showFormModal && selectedJenis"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-3 py-4 sm:items-center sm:px-4"
                @click.self="closeForm"
            >
                <form
                    class="flex w-full max-w-3xl max-h-[92vh] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
                    @submit.prevent="submit"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6"
                    >
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold tracking-[0.2em] text-blue-600 uppercase">
                                {{ categoryName(selectedJenis.categoryId) }}
                            </p>
                            <h3 class="mt-1 truncate text-lg font-semibold text-slate-900">
                                {{ selectedJenis.nama }}
                            </h3>
                            <p
                                v-if="selectedJenis.deskripsi"
                                class="mt-1 max-w-2xl text-sm text-slate-500"
                            >
                                {{ selectedJenis.deskripsi }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="fast-btn fast-btn-ghost fast-btn-icon shrink-0 rounded-full border border-slate-200 text-slate-500"
                            @click="closeForm"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                        <div class="space-y-4">
                            <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid size-8 place-items-center rounded-xl bg-blue-600 text-white shadow-sm">
                                        <CheckCircle2 class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Data Pengajuan</p>
                                        <p class="text-[10px] text-slate-400">Isi informasi sesuai kebutuhan surat</p>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div data-field-key="keperluan">
                                        <label class="mb-1 block text-xs font-medium text-slate-700">
                                            Keperluan <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="form.keperluan"
                                            rows="3"
                                            placeholder="Jelaskan keperluan pengajuan surat (min. 10 karakter)..."
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                        />
                                        <p v-if="form.errors.keperluan" class="mt-1 text-xs text-red-500">
                                            {{ form.errors.keperluan }}
                                        </p>
                                    </div>

                                    <div data-field-key="tanggal_kebutuhan">
                                        <label class="mb-1 block text-xs font-medium text-slate-700">
                                            Tanggal Dibutuhkan <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.tanggal_kebutuhan"
                                            type="date"
                                            :min="todayString()"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                        />
                                        <p v-if="form.errors.tanggal_kebutuhan" class="mt-1 text-xs text-red-500">
                                            {{ form.errors.tanggal_kebutuhan }}
                                        </p>
                                    </div>

                                    <template v-for="field in selectedJenis.fieldConfig ?? []" :key="field.name">
                                        <div
                                            v-if="isApplicantFieldVisible(field)"
                                            :data-field-key="`field_data.${field.name}`"
                                            :ref="(el) => setApplicantFieldRef(`field_data.${field.name}`, el)"
                                        >
                                        <label class="mb-1 block text-xs font-medium text-slate-700">
                                            {{ field.label }}
                                            <span v-if="field.required" class="text-red-500">*</span>
                                        </label>
                                            <span
                                                v-if="isApplicantFieldReadonly(field)"
                                                class="mb-2 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                            >
                                                Data oleh kampus
                                            </span>
                                            <p
                                                v-if="applicantFieldHelp(field)"
                                                class="mb-2 text-[10px] text-amber-600"
                                            >
                                                {{ applicantFieldHelp(field) }}
                                            </p>

                                            <textarea
                                                v-if="field.type === 'textarea'"
                                                v-model="form.field_data[field.name] as string"
                                                :placeholder="field.placeholder"
                                                rows="3"
                                                :readonly="isApplicantFieldReadonly(field)"
                                                :class="[
                                                    'w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100',
                                                    isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-50 text-slate-500' : '',
                                                ]"
                                            />
                                            <div v-else-if="field.type === 'select'" class="relative">
                                                <select
                                                    v-model="form.field_data[field.name]"
                                                    :disabled="isApplicantFieldReadonly(field)"
                                                    :class="[
                                                        'h-10 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 pr-9 text-sm text-slate-900 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100',
                                                        isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-50 text-slate-500' : '',
                                                    ]"
                                                >
                                                    <option value="">-- Pilih --</option>
                                                    <option
                                                        v-for="opt in field.options"
                                                        :key="opt.value"
                                                        :value="opt.value"
                                                    >
                                                        {{ opt.label }}
                                                    </option>
                                                </select>
                                                <ChevronDown class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400" />
                                            </div>
                                            <div v-else-if="field.type === 'radio'" class="flex flex-wrap gap-3 pt-1">
                                                <label
                                                    v-for="opt in field.options"
                                                    :key="opt.value"
                                                    class="flex cursor-pointer items-center gap-1.5 text-sm"
                                                >
                                                    <input
                                                        type="radio"
                                                        :value="opt.value"
                                                        v-model="form.field_data[field.name]"
                                                        :disabled="isApplicantFieldReadonly(field)"
                                                        class="text-blue-600"
                                                    />
                                                    {{ opt.label }}
                                                </label>
                                            </div>
                                            <label v-else-if="field.type === 'checkbox'" class="flex cursor-pointer items-center gap-2 pt-1">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.field_data[field.name]"
                                                    :disabled="isApplicantFieldReadonly(field)"
                                                    class="rounded text-blue-600"
                                                />
                                                <span class="text-sm text-slate-700">{{ field.placeholder || field.label }}</span>
                                            </label>
                                            <div
                                                v-else-if="['checkbox-group', 'multiselect'].includes(field.type)"
                                                class="flex flex-wrap gap-3 pt-1"
                                            >
                                                <label
                                                    v-for="opt in field.options"
                                                    :key="opt.value"
                                                    class="flex cursor-pointer items-center gap-1.5 text-sm"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :value="opt.value"
                                                        v-model="form.field_data[field.name] as string[]"
                                                        :disabled="isApplicantFieldReadonly(field)"
                                                        class="rounded text-blue-600"
                                                    />
                                                    {{ opt.label }}
                                                </label>
                                            </div>
                                            <input
                                                v-else
                                                v-model="form.field_data[field.name] as string"
                                                :type="
                                                    field.type === 'number'
                                                        ? 'number'
                                                        : field.type === 'date'
                                                          ? 'date'
                                                          : field.type === 'email'
                                                          ? 'email'
                                                          : 'text'
                                                "
                                                :placeholder="field.placeholder"
                                                :readonly="isApplicantFieldReadonly(field)"
                                                :disabled="isApplicantFieldReadonly(field)"
                                                :class="[
                                                    'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100',
                                                    isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-50 text-slate-500' : '',
                                                ]"
                                            />
                                            <p v-if="fieldError(field.name)" class="mt-1 text-xs text-red-500">
                                                {{ fieldError(field.name) }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </section>

                            <section
                                data-field-key="lampiran"
                                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="grid size-8 place-items-center rounded-xl bg-blue-600 text-white shadow-sm">
                                        <Paperclip class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            Lampiran <span class="text-[10px] font-normal text-slate-400">(opsional)</span>
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            PDF, JPG, PNG, DOC - maks. 4 MB / berkas
                                        </p>
                                    </div>
                                </div>

                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    class="hidden"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    @change="onFilesPicked"
                                />
                                <button
                                    type="button"
                                    class="fast-btn fast-btn-soft flex w-full flex-col border-2 border-dashed border-slate-200 py-6 text-slate-500"
                                    @click="triggerFilePick"
                                >
                                    <UploadCloud class="size-6 text-slate-400" />
                                    <span class="text-xs font-medium">Klik untuk pilih berkas</span>
                                </button>
                                <p v-if="form.errors.lampiran" class="mt-1.5 text-xs text-red-500">
                                    {{ form.errors.lampiran }}
                                </p>
                                <ul v-if="form.lampiran.length" class="mt-3 space-y-2">
                                    <li
                                        v-for="(file, i) in form.lampiran"
                                        :key="i"
                                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2.5"
                                    >
                                        <FileText class="size-4 shrink-0 text-blue-600" />
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-medium text-slate-700">
                                                {{ file.name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400">
                                                {{ fileSize(file.size) }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="fast-btn fast-btn-ghost fast-btn-icon text-slate-400 hover:text-red-500"
                                            @click="removeFile(i)"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </li>
                                </ul>
                            </section>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    Kirim pengajuan setelah memastikan data benar
                                </p>
                                <p class="text-xs text-slate-400">
                                    Pengajuan akan diproses sesuai alur FAST.
                                </p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button
                                    type="button"
                                    class="fast-btn fast-btn-outline px-4 py-2 text-sm font-medium"
                                    @click="closeForm"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="fast-btn fast-btn-primary px-5 py-2 text-sm"
                                >
                                    <Send class="size-3.5" />
                                    {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </Transition>
    </FastLayout>
</template>
