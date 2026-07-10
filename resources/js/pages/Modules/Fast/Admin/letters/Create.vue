<script setup lang="ts">
// File: resources/js/pages/Modules/Fast/Admin/letters/Create.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LetterStepIndicator from '@/components/Modules/Fast/Admin/LetterStepIndicator.vue';
import {
    Search,
    X,
    FileText,
    AlertCircle,
    ArrowRight,
    ChevronDown,
} from 'lucide-vue-next';
type JenisSurat = {
    id: number;
    nama: string;
    slug?: string | null;
    kode_surat?: string | null;
    deskripsi?: string | null;
    perlu_approval?: boolean;
    letter_mode?: 'personal' | 'institution';
    letter_mode_label?: string;
    requires_subject_user?: boolean;
    category?: {
        id?: number | null;
        nama?: string | null;
        warna?: string | null;
        icon?: string | null;
    } | null;
};
type Category = {
    id: number;
    nama: string;
    warna?: string | null;
    icon?: string | null;
};
const props = withDefaults(
    defineProps<{
        jenisSurats?: JenisSurat[];
        categories?: Category[];
    }>(),
    {
        jenisSurats: () => [],
        categories: () => [],
    },
);
const form = useForm({ jenis_surat_id: '' });
const { can } = useFastPermissions();
const searchQuery = ref('');
const activeCategory = ref<number | null>(null);
const submittingJenisId = ref<number | null>(null);
const filtered = computed(() => {
    let list = props.jenisSurats ?? [];
    if (activeCategory.value !== null) {
        list = list.filter((j) => j.category?.id === activeCategory.value);
    }
    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        list = list.filter(
            (j) =>
                j.nama.toLowerCase().includes(q) ||
                (j.category?.nama ?? '').toLowerCase().includes(q) ||
                (j.deskripsi ?? '').toLowerCase().includes(q),
        );
    }
    return list;
});
const colorMap: Record<string, string> = {
    indigo: 'bg-indigo-50 text-indigo-600 border-indigo-200',
    emerald: 'bg-blue-50 text-blue-600 border-blue-200',
    amber: 'bg-amber-50 text-amber-600 border-amber-200',
    blue: 'bg-blue-50 text-blue-600 border-blue-200',
    rose: 'bg-rose-50 text-rose-600 border-rose-200',
    violet: 'bg-violet-50 text-violet-600 border-violet-200',
    cyan: 'bg-cyan-50 text-cyan-600 border-cyan-200',
    slate: 'bg-slate-50 text-slate-600 border-slate-200',
};
function catColor(warna?: string | null) {
    return colorMap[warna ?? ''] ?? colorMap['slate'];
}
function selectJenisSurat(jenis: JenisSurat) {
    form.jenis_surat_id = String(jenis.id);
}
function resetFilters() {
    searchQuery.value = '';
    activeCategory.value = null;
}
function handleJenisAction(jenis: JenisSurat) {
    selectJenisSurat(jenis);
    submittingJenisId.value = jenis.id;
    submit();
}
function submit() {
    form.post('/admin/surat/select-type', {
        onFinish: () => {
            submittingJenisId.value = null;
        },
    });
}
function isSubmittingJenis(jenis: JenisSurat): boolean {
    return submittingJenisId.value === jenis.id && form.processing;
}
</script>
<template>
    <AdminLayout
        title="Buat Surat Keluar"
        subtitle="Pilih jenis surat keluar yang akan dibuat"
        active-menu="letters.create"
        :breadcrumbs="[{ label: 'Buat Surat Keluar' }]"
    >
        <Head title="Buat Surat Keluar" />
        <!-- Greeting + Step combined hero -->
        <div
            class="mb-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Pilih Jenis Surat
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Temukan dan pilih jenis surat yang ingin dibuat. Setiap
                        jenis memiliki template dan alur approval yang berbeda.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <LetterStepIndicator :current-step="1" />
                </div>
            </div>
        </div>
        <div class="space-y-5">
            <!-- Search & Category tabs -->
            <div class="space-y-3">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_220px_auto] xl:items-end">
                    <label class="block">
                        <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Pencarian
                        </span>
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama surat, kode, atau kategori..."
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                class="fast-btn fast-btn-ghost fast-btn-icon absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                @click="searchQuery = ''"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                    </label>
                    <div class="relative w-full">
                        <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Kategori
                        </span>
                        <select
                            v-model="activeCategory"
                            class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-8 pl-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        >
                            <option :value="null">Semua kategori</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.nama }}
                            </option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-3.5 size-3.5 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                    <button
                        type="button"
                        class="h-11 w-full rounded-2xl border border-blue-200 bg-blue-50 px-5 text-sm font-medium text-blue-700 transition-colors hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800 sm:w-auto"
                        @click="resetFilters"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
            <!-- Grid cards -->
            <div
                v-if="filtered.length === 0"
                class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-200 py-10 text-center"
            >
                <AlertCircle class="size-8 text-slate-300" />
                <p class="text-sm text-slate-400">
                    Tidak ada jenis surat yang cocok.
                </p>
                <button
                    type="button"
                    class="text-xs text-blue-600 hover:underline"
                    @click="resetFilters"
                >
                    Hapus filter
                </button>
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="jenis in filtered"
                    :key="jenis.id"
                    class="group relative flex h-full cursor-pointer flex-col rounded-2xl border p-5 text-left transition-all hover:shadow-md"
                    :class="
                        String(jenis.id) === form.jenis_surat_id
                            ? 'border-blue-300 bg-blue-50/50 shadow-sm ring-1 ring-blue-200'
                            : 'border-slate-200 bg-white hover:border-blue-200'
                    "
                    @click="selectJenisSurat(jenis)"
                >
                    <!-- Selected indicator -->
                    <div
                        v-if="String(jenis.id) === form.jenis_surat_id"
                        class="absolute top-3 right-3 grid size-5 place-items-center rounded-full bg-blue-500 text-white"
                    >
                        <svg
                            class="size-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="grid size-10 shrink-0 place-items-center rounded-xl border"
                            :class="
                                String(jenis.id) === form.jenis_surat_id
                                    ? 'border-blue-500 bg-blue-500 text-white'
                                    : catColor(jenis.category?.warna)
                            "
                        >
                            <FileText class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ jenis.nama }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ jenis.category?.nama ?? 'Tanpa Kategori' }}
                            </p>
                            <div class="mt-2 flex items-center gap-2">
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                    :class="
                                        jenis.perlu_approval
                                            ? 'border border-amber-100 bg-amber-50 text-amber-700'
                                            : 'border border-blue-100 bg-blue-50 text-blue-700'
                                    "
                                >
                                    {{
                                        jenis.perlu_approval
                                            ? 'Perlu Approval'
                                            : 'Langsung Selesai'
                                    }}
                                </span>
                                <span
                                    class="rounded-full border px-2 py-0.5 text-[10px] font-medium"
                                    :class="
                                        jenis.letter_mode === 'institution'
                                            ? 'border-blue-100 bg-blue-50 text-blue-700'
                                            : 'border-amber-100 bg-amber-50 text-amber-700'
                                    "
                                >
                                    {{ jenis.letter_mode_label ?? (jenis.letter_mode === 'institution' ? 'Surat Institusi' : 'Surat Personal') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="can('fast.admin.surat.create')"
                        class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4"
                    >
                        <p class="text-xs text-slate-500">
                            Klik tombol untuk lanjut ke form
                        </p>
                        <button
                            type="button"
                            class="fast-btn fast-btn-primary inline-flex items-center gap-1.5 border border-blue-500/10 px-3 py-2 text-xs font-semibold shadow-[0_8px_18px_rgba(37,99,235,0.22)] transition-all hover:shadow-[0_10px_20px_rgba(37,99,235,0.28)] disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"
                            @click.stop="handleJenisAction(jenis)"
                        >
                            {{
                                isSubmittingJenis(jenis)
                                    ? 'Memproses...'
                                    : 'Isi Form Surat'
                            }}
                            <ArrowRight v-if="!isSubmittingJenis(jenis)" class="size-3.5" />
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </AdminLayout>
</template>
<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
