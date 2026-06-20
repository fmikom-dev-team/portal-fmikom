<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/categories/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    ToggleLeft,
    ToggleRight,
    X,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
type Category = {
    id: number;
    nama: string;
    slug: string;
    deskripsi?: string | null;
    icon?: string | null;
    warna?: string | null;
    urutan: number;
    is_active: boolean;
};
const props = defineProps<{ categories: Category[] }>();
const categorySearch = ref('');
const filteredCategories = computed(() =>
    categorySearch.value.trim()
        ? props.categories.filter(
              (c) =>
                  c.nama
                      .toLowerCase()
                      .includes(categorySearch.value.toLowerCase()) ||
                  (c.deskripsi ?? '')
                      .toLowerCase()
                      .includes(categorySearch.value.toLowerCase()),
          )
        : props.categories,
);
// â”€â”€ Warna options â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const warnaOptions = [
    { value: 'indigo', label: 'Indigo' },
    { value: 'emerald', label: 'Emerald' },
    { value: 'amber', label: 'Amber' },
    { value: 'blue', label: 'Biru' },
    { value: 'rose', label: 'Rose' },
    { value: 'violet', label: 'Violet' },
    { value: 'cyan', label: 'Cyan' },
    { value: 'slate', label: 'Slate' },
];
const warnaClass: Record<string, string> = {
    indigo: 'bg-indigo-100 text-indigo-700',
    emerald: 'bg-emerald-100 text-emerald-700',
    amber: 'bg-amber-100 text-amber-700',
    blue: 'bg-blue-100 text-blue-700',
    rose: 'bg-rose-100 text-rose-700',
    violet: 'bg-violet-100 text-violet-700',
    cyan: 'bg-cyan-100 text-cyan-700',
    slate: 'bg-slate-100 text-slate-700',
};
// â”€â”€ Modal state â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const showModal = ref(false);
const editTarget = ref<Category | null>(null);
const form = useForm({
    nama: '',
    deskripsi: '',
    icon: '',
    warna: 'indigo',
    urutan: 0,
    is_active: true,
});
function openAdd() {
    editTarget.value = null;
    form.reset();
    form.warna = 'blue';
    form.is_active = true;
    showModal.value = true;
}
function openEdit(cat: Category) {
    editTarget.value = cat;
    form.nama = cat.nama;
    form.deskripsi = cat.deskripsi ?? '';
    form.icon = cat.icon ?? '';
    form.warna = cat.warna ?? 'blue';
    form.urutan = cat.urutan;
    form.is_active = cat.is_active;
    showModal.value = true;
}
function closeModal() {
    showModal.value = false;
    editTarget.value = null;
    form.reset();
    form.clearErrors();
}
function submit() {
    if (editTarget.value) {
        form.put(`/admin/categories/${editTarget.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/categories', { onSuccess: () => closeModal() });
    }
}
function toggleActive(cat: Category) {
    router.put(
        `/admin/categories/${cat.id}`,
        { ...cat, is_active: !cat.is_active },
        { preserveScroll: true },
    );
}
function destroy(cat: Category) {
    if (confirm(`Hapus kategori "${cat.nama}"?`)) {
        router.delete(`/admin/categories/${cat.id}`, { preserveScroll: true });
    }
}
</script>
<template>
    <AdminLayout
        title="Kategori Surat"
        subtitle="Kelola kategori jenis surat"
        active-menu="categories"
        :breadcrumbs="[{ label: 'Kategori Surat' }]"
    >
        <template #actions>
            <button
                type="button"
                class="fast-btn fast-btn-primary flex items-center gap-1.5 px-4 py-2 text-xs font-semibold"
                @click="openAdd"
            >
                <Plus class="size-3.5" /> Tambah Kategori
            </button>
        </template>
        <Head title="Kategori Surat" />
        <!-- Search box -->
        <div class="relative mb-4">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
            />
            <input
                v-model="categorySearch"
                type="text"
                placeholder="Cari kategori..."
                class="h-10 w-full max-w-sm rounded-xl border border-slate-200 bg-white pr-3 pl-10 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
            />
        </div>
        <!-- Grid kategori -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div
                v-if="filteredCategories.length === 0"
                class="col-span-full flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <p class="text-sm text-slate-400">
                    {{
                        categorySearch
                            ? 'Tidak ada kategori yang cocok.'
                            : 'Belum ada kategori surat.'
                    }}
                </p>
                <button
                    v-if="!categorySearch"
                    type="button"
                    class="text-xs text-blue-600 hover:underline"
                    @click="openAdd"
                >
                    Tambah kategori pertama
                </button>
            </div>
            <div
                v-for="cat in filteredCategories"
                :key="cat.id"
                class="rounded-2xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <!-- Warna badge -->
                        <div
                            class="grid size-10 shrink-0 place-items-center rounded-xl text-sm font-bold"
                            :class="
                                warnaClass[cat.warna ?? 'slate'] ??
                                'bg-slate-100 text-slate-700'
                            "
                        >
                            {{ cat.nama.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ cat.nama }}
                            </p>
                            <p class="font-mono text-[10px] text-slate-400">
                                {{ cat.slug }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="
                            cat.is_active
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-slate-100 text-slate-400'
                        "
                    >
                        {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p
                    v-if="cat.deskripsi"
                    class="mt-3 line-clamp-2 text-xs leading-5 text-slate-500"
                >
                    {{ cat.deskripsi }}
                </p>
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400"
                            >Urutan: {{ cat.urutan }}</span
                        >
                        <span
                            v-if="cat.icon"
                            class="text-[10px] text-slate-400"
                        >
                            Icon: {{ cat.icon }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline grid size-7 place-items-center rounded-lg text-slate-500"
                            title="Edit"
                            @click="openEdit(cat)"
                        >
                            <Pencil class="size-3.5" />
                        </button>
                        <button
                            type="button"
                            class="fast-btn grid size-7 place-items-center rounded-lg"
                            :class="
                                cat.is_active
                                    ? 'fast-btn-soft text-amber-600'
                                    : 'fast-btn-soft text-blue-600'
                            "
                            :title="cat.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                            @click="toggleActive(cat)"
                        >
                            <component
                                :is="cat.is_active ? ToggleRight : ToggleLeft"
                                class="size-3.5"
                            />
                        </button>
                        <button
                            type="button"
                            class="fast-btn fast-btn-soft grid size-7 place-items-center rounded-lg border-red-200 bg-red-50 text-red-600 hover:border-red-300 hover:bg-red-100 hover:text-red-700"
                            title="Hapus"
                            @click="destroy(cat)"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Tambah/Edit -->
        <Transition name="fade">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="closeModal"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-6 py-4"
                    >
                        <h3 class="text-base font-bold text-slate-900">
                            {{
                                editTarget ? 'Edit Kategori' : 'Tambah Kategori'
                            }}
                        </h3>
                        <button
                            type="button"
                            class="text-slate-400 hover:text-slate-600"
                            @click="closeModal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>
                    <!-- Form -->
                    <form class="space-y-4 px-6 py-5" @submit.prevent="submit">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Nama Kategori
                                <span class="text-red-500">*</span></span
                            >
                            <input
                                v-model="form.nama"
                                type="text"
                                required
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                :class="
                                    form.errors.nama ? 'border-red-300' : ''
                                "
                                placeholder="Contoh: Surat Permohonan"
                            />
                            <p
                                v-if="form.errors.nama"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.nama }}
                            </p>
                        </label>
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Deskripsi</span
                            >
                            <textarea
                                v-model="form.deskripsi"
                                rows="2"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Deskripsi singkat kategori ini"
                            />
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700"
                                    >Warna</span
                                >
                                <select
                                    v-model="form.warna"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400"
                                >
                                    <option
                                        v-for="w in warnaOptions"
                                        :key="w.value"
                                        :value="w.value"
                                    >
                                        {{ w.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700"
                                    >Icon (Lucide)</span
                                >
                                <input
                                    v-model="form.icon"
                                    type="text"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Contoh: mail"
                                />
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700"
                                    >Urutan</span
                                >
                                <input
                                    v-model.number="form.urutan"
                                    type="number"
                                    min="0"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400"
                                />
                            </label>
                            <div class="flex items-end pb-0.5">
                                <label
                                    class="flex cursor-pointer items-center gap-2"
                                >
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-slate-300"
                                    />
                                    <span class="text-sm text-slate-700"
                                        >Aktif</span
                                    >
                                </label>
                            </div>
                        </div>
                        <!-- Preview warna -->
                        <div
                            class="rounded-xl border border-slate-100 bg-slate-50 p-3"
                        >
                            <p class="mb-2 text-xs text-slate-400">Preview:</p>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    warnaClass[form.warna] ??
                                    'bg-slate-100 text-slate-700'
                                "
                            >
                                {{ form.nama || 'Nama Kategori' }}
                            </span>
                        </div>
                        <div
                            class="flex justify-end gap-2 border-t border-slate-100 pt-4"
                        >
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium text-slate-600"
                            @click="closeModal"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="fast-btn fast-btn-primary rounded-xl px-4 py-2 text-sm"
                            :disabled="form.processing"
                        >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : editTarget
                                          ? 'Simpan Perubahan'
                                          : 'Tambah Kategori'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
