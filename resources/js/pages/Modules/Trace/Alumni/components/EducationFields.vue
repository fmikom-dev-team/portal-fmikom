<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

interface CareerFormData {
    nama_universitas: string;
    program_studi_lanjutan: string;
    jenjang_pendidikan: string;
    sumber_biaya: string;
    [key: string]: unknown;
}

defineProps<{
    form: InertiaForm<CareerFormData>;
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Nama Universitas -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Nama Perguruan Tinggi / Universitas
                <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.nama_universitas"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                placeholder="Contoh: Universitas Indonesia"
                required
            />
            <p
                v-if="form.errors.nama_universitas"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.nama_universitas }}
            </p>
        </div>

        <!-- Program Studi Lanjutan -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Program Studi Lanjutan
                <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.program_studi_lanjutan"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                placeholder="Contoh: Magister Teknik Informatika"
                required
            />
            <p
                v-if="form.errors.program_studi_lanjutan"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.program_studi_lanjutan }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Jenjang Pendidikan -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Jenjang Pendidikan
                <span class="text-red-500">*</span>
            </label>
            <select
                v-model="form.jenjang_pendidikan"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
            >
                <option value="">Pilih Jenjang</option>
                <option value="S2">S2 (Magister)</option>
                <option value="S3">S3 (Doktor)</option>
                <option value="Profesi">Profesi</option>
                <option value="Spesialis">Spesialis</option>
            </select>
            <p
                v-if="form.errors.jenjang_pendidikan"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.jenjang_pendidikan }}
            </p>
        </div>

        <!-- Sumber Biaya -->
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Sumber Biaya Studi
            </label>
            <select
                v-model="form.sumber_biaya"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
                <option value="">Pilih Sumber Biaya</option>
                <option value="Biaya Sendiri">
                    Biaya Sendiri
                </option>
                <option value="Beasiswa Pemerintah">
                    Beasiswa Pemerintah
                </option>
                <option value="Beasiswa Swasta">
                    Beasiswa Swasta
                </option>
                <option value="Beasiswa Kampus">
                    Beasiswa Kampus
                </option>
                <option value="Lainnya">Lainnya</option>
            </select>
            <p
                v-if="form.errors.sumber_biaya"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.sumber_biaya }}
            </p>
        </div>
    </div>

</template>
