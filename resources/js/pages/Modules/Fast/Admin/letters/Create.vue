<script setup lang="ts">
// File: resources/js/pages/Modules/Fast/Admin/letters/Create.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import LetterStepIndicator from '@/components/Modules/Fast/Admin/LetterStepIndicator.vue';
import {
    Search,
    X,
    FileText,
    AlertCircle,
    ArrowRight,
} from 'lucide-vue-next';
type JenisSurat = {
    id: number;
    nama: string;
    slug?: string | null;
    kode_surat?: string | null;
    deskripsi?: string | null;
    perlu_approval?: boolean;
    qr_mode?: string;
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
const searchQuery = ref('');
const activeCategory = ref<number | null>(null);
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
const selected = computed(
    () =>
        (props.jenisSurats ?? []).find((j) => String(j.id) === form.jenis_surat_id) ??
        null,
);
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
const isPaused = ref(false);
function submit() {
    form.post('/admin/surat/select-type');
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
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama surat, kode, atau kategori..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white py-0 pr-10 pl-10 text-sm text-slate-700 transition outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
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
                <!-- Category tabs infinite marquee -->
                <div
                    class="overflow-hidden rounded-xl"
                    @mouseenter="isPaused = true"
                    @mouseleave="isPaused = false"
                >
                    <div
                        class="marquee-track flex gap-2"
                        :class="{ 'marquee-paused': isPaused }"
                    >
                        <!-- Set 1 -->
                        <button
                            type="button"
                            class="fast-btn shrink-0 px-3 py-1.5 text-xs font-medium"
                            :class="
                                activeCategory === null
                                    ? 'fast-btn-primary'
                                    : 'fast-btn-outline text-slate-500'
                            "
                            @click="activeCategory = null"
                        >
                            Semua ({{ jenisSurats.length }})
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="'a-' + cat.id"
                            type="button"
                            class="fast-btn shrink-0 px-3 py-1.5 text-xs font-medium"
                            :class="
                                activeCategory === cat.id
                                    ? 'fast-btn-primary'
                                    : 'fast-btn-outline text-slate-500'
                            "
                            @click="
                                activeCategory =
                                    activeCategory === cat.id ? null : cat.id
                            "
                        >
                            {{ cat.nama }}
                        </button>
                        <!-- Set 2 (duplicate untuk seamless loop) -->
                        <button
                            type="button"
                            class="fast-btn shrink-0 px-3 py-1.5 text-xs font-medium"
                            :class="
                                activeCategory === null
                                    ? 'fast-btn-primary'
                                    : 'fast-btn-outline text-slate-500'
                            "
                            @click="activeCategory = null"
                        >
                            Semua ({{ jenisSurats.length }})
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="'b-' + cat.id"
                            type="button"
                            class="fast-btn shrink-0 px-3 py-1.5 text-xs font-medium"
                            :class="
                                activeCategory === cat.id
                                    ? 'fast-btn-primary'
                                    : 'fast-btn-outline text-slate-500'
                            "
                            @click="
                                activeCategory =
                                    activeCategory === cat.id ? null : cat.id
                            "
                        >
                            {{ cat.nama }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- Selected detail card (horizontal, full width) -->
            <div
                v-if="selected"
                class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-white p-5"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-[10px] font-semibold tracking-wider text-blue-500 uppercase"
                        >
                            {{ selected.category?.nama ?? 'Tanpa Kategori' }}
                        </p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">
                            {{ selected.nama }}
                        </p>
                        <p
                            v-if="selected.deskripsi"
                            class="mt-1 text-xs text-slate-500"
                        >
                            {{ selected.deskripsi }}
                        </p>
                        <div class="mt-2 flex items-center gap-3">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border bg-white px-2 py-0.5 text-[10px] font-medium"
                                :class="
                                    selected.perlu_approval
                                        ? 'border-amber-100 text-amber-700'
                                        : 'border-blue-100 text-blue-700'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        selected.perlu_approval
                                            ? 'bg-amber-400'
                                            : 'bg-blue-400'
                                    "
                                />
                                {{
                                    selected.perlu_approval
                                        ? 'Approval Diperlukan'
                                        : 'Langsung Selesai'
                                }}
                            </span>
                            <span class="text-[10px] text-slate-400"
                                >QR:
                                {{
                                    selected.qr_mode === 'immediate'
                                        ? 'Langsung'
                                        : 'Setelah Approve'
                                }}</span
                            >
                        </div>
                    </div>
                    <button
                        type="button"
                        class="fast-btn fast-btn-primary flex shrink-0 items-center justify-center gap-2 px-6 py-2.5 text-sm"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{
                            form.processing ? 'Memproses...' : 'Isi Form Surat'
                        }}
                        <ArrowRight v-if="!form.processing" class="size-4" />
                    </button>
                </div>
            </div>
            <!-- Empty hint -->
            <div
                v-else
                class="flex items-center gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-5 py-4"
            >
                <FileText class="size-5 shrink-0 text-slate-300" />
                <p class="text-xs text-slate-400">
                    Klik salah satu kartu di bawah untuk memilih jenis surat.
                </p>
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
                    @click="
                        searchQuery = '';
                        activeCategory = null;
                    "
                >
                    Hapus filter
                </button>
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="jenis in filtered"
                    :key="jenis.id"
                    type="button"
                    class="group relative rounded-2xl border p-5 text-left transition-all hover:shadow-md"
                    :class="
                        String(jenis.id) === form.jenis_surat_id
                            ? 'border-blue-300 bg-blue-50/50 shadow-sm ring-1 ring-blue-200'
                            : 'border-slate-200 bg-white hover:border-blue-200'
                    "
                    @click="form.jenis_surat_id = String(jenis.id)"
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
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.marquee-track {
    animation: marquee 25s linear infinite;
    width: max-content;
}
.marquee-paused {
    animation-play-state: paused;
}
@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>
