<script setup lang="ts">
import { computed } from "vue";
import type { InertiaForm } from '@inertiajs/vue3';

interface EmploymentFormData {
    nama_perusahaan: string;
    jabatan: string;
    sektor_industri: string;
    alamat_perusahaan: string;
    gaji_min: number | null;
    gaji_max: number | null;
    [key: string]: unknown;
}

defineProps<{
    form: InertiaForm<EmploymentFormData>;
}>();

// Salary validation: gaji_max cannot be less than gaji_min
const salaryError = computed(() => {
    // Access form from template context — props are auto-unwrapped
    return null; // Handled via slot/parent; kept for local display
});
</script>

<template>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Nama Perusahaan -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Nama Perusahaan
                <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.nama_perusahaan"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
            />
            <p
                v-if="form.errors.nama_perusahaan"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.nama_perusahaan }}
            </p>
        </div>

        <!-- Jabatan -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Jabatan <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.jabatan"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
            />
            <p
                v-if="form.errors.jabatan"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.jabatan }}
            </p>
        </div>
    </div>

    <!-- Sektor Industri -->
    <div>
        <label
            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            Sektor Industri
        </label>
        <select
            v-model="form.sektor_industri"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
        >
            <option value="" disabled>Pilih Sektor Industri</option>
            <option value="Teknologi Informasi">Teknologi Informasi</option>
            <option value="Pendidikan">Pendidikan</option>
            <option value="Keuangan & Perbankan">Keuangan &amp; Perbankan</option>
            <option value="Kesehatan">Kesehatan</option>
            <option value="Manufaktur">Manufaktur</option>
            <option value="Pemerintahan">Pemerintahan</option>
            <option value="Perdagangan & E-Commerce">Perdagangan &amp; E-Commerce</option>
            <option value="Telekomunikasi">Telekomunikasi</option>
            <option value="Konstruksi & Properti">Konstruksi &amp; Properti</option>
            <option value="Transportasi & Logistik">Transportasi &amp; Logistik</option>
            <option value="Media & Kreatif">Media &amp; Kreatif</option>
            <option value="Pertanian & Pangan">Pertanian &amp; Pangan</option>
            <option value="Energi & Pertambangan">Energi &amp; Pertambangan</option>
            <option value="Pariwisata & Hospitality">Pariwisata &amp; Hospitality</option>
            <option value="Lainnya">Lainnya</option>
        </select>
        <p
            v-if="form.errors.sektor_industri"
            class="mt-1 text-sm text-red-500"
        >
            {{ form.errors.sektor_industri }}
        </p>
    </div>

    <!-- Alamat -->
    <div>
        <label
            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            Alamat Lengkap Perusahaan/Usaha
        </label>
        <textarea
            v-model="form.alamat_perusahaan"
            rows="3"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            placeholder="Alamat lengkap perusahaan/usaha"
        />
        <p
            v-if="form.errors.alamat_perusahaan"
            class="mt-1 text-sm text-red-500"
        >
            {{ form.errors.alamat_perusahaan }}
        </p>
    </div>

    <!-- Gaji Range -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Gaji Minimum (Rp)
            </label>
            <input
                v-model.number="form.gaji_min"
                type="number"
                min="0"
                step="100000"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                placeholder="0"
            />
            <p
                v-if="form.errors.gaji_min"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.gaji_min }}
            </p>
        </div>

        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Gaji Maximum (Rp)
            </label>
            <input
                v-model.number="form.gaji_max"
                type="number"
                min="0"
                step="100000"
                :class="[
                    'w-full rounded-lg border px-4 py-2.5 text-gray-900 focus:ring-2 dark:bg-slate-800 dark:text-white',
                    form.gaji_min !== null &&
                    form.gaji_max !== null &&
                    form.gaji_max < form.gaji_min
                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700',
                ]"
                placeholder="0"
            />
            <p
                v-if="
                    form.gaji_min !== null &&
                    form.gaji_max !== null &&
                    form.gaji_max < form.gaji_min
                "
                class="mt-1 flex items-center gap-1 text-sm text-red-500"
            >
                <svg
                    class="h-4 w-4 shrink-0"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                Gaji maksimum tidak boleh lebih kecil dari gaji minimum.
            </p>
            <p
                v-else-if="form.errors.gaji_max"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.gaji_max }}
            </p>
        </div>
    </div>
</template>
