<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import EditorJsEditor from '@/components/editor/EditorJsEditor.vue';
import { TFormSection } from '@/components/trace';

interface Category {
    id: number;
    nama: string;
}

interface Mitra {
    id: number;
    nama_perusahaan: string;
}

const props = defineProps<{
    form: Record<string, unknown>;
    categories: Category[];
    mitras: Mitra[];
    statusOptions: { value: string; label: string }[];
}>();

const experienceLevels = [
    { value: 'fresh_graduate', label: 'Fresh Graduate' },
    { value: 'junior', label: 'Junior' },
    { value: 'mid_level', label: 'Mid Level' },
    { value: 'senior', label: 'Senior' },
    { value: 'internship', label: 'Internship' },
];

const locationTypes = [
    { value: 'onsite', label: 'Onsite' },
    { value: 'remote', label: 'Remote' },
    { value: 'hybrid', label: 'Hybrid' },
];

const tipeKerjaOptions = [
    { value: 'full_time', label: 'Full Time' },
    { value: 'part_time', label: 'Part Time' },
    { value: 'magang', label: 'Magang' },
    { value: 'freelance', label: 'Freelance' },
];

const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background/30 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-all appearance-none';
</script>

<template>
    <!-- Basic Information -->
    <TFormSection title="Informasi Lowongan">
        <div class="space-y-5">
            <!-- Title -->
            <div class="space-y-2">
                <Label for="title">Judul Lowongan <span class="text-red-500">*</span></Label>
                <Input
                    id="title"
                    v-model="form.title"
                    placeholder="Contoh: Frontend Developer"
                />
                <p v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</p>
            </div>

            <!-- Description (Rich Editor) -->
            <div class="space-y-2">
                <Label>Deskripsi <span class="text-red-500">*</span></Label>
                <div class="rounded-xl border border-slate-200 dark:border-zinc-700 overflow-hidden">
                    <EditorJsEditor
                        v-model="form.description"
                        placeholder="Deskripsikan pekerjaan, tanggung jawab, dan kualifikasi yang dibutuhkan..."
                        :min-height="250"
                        mode="simple"
                    />
                </div>
                <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
            </div>

            <!-- Category -->
            <div class="space-y-2">
                <Label for="job_category_id">Kategori <span class="text-red-500">*</span></Label>
                <select
                    id="job_category_id"
                    v-model="form.job_category_id"
                    :class="selectClass"
                >
                    <option value="" disabled>Pilih kategori</option>
                    <option
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.id"
                    >
                        {{ cat.nama }}
                    </option>
                </select>
                <p v-if="form.errors.job_category_id" class="text-sm text-red-500">{{ form.errors.job_category_id }}</p>
            </div>

            <!-- Posting atas nama (Mitra) -->
            <div class="space-y-2">
                <Label for="mitra_id">Posting atas nama</Label>
                <select
                    id="mitra_id"
                    v-model="form.mitra_id"
                    :class="selectClass"
                >
                    <option value="">Admin FMIKOM (tanpa mitra)</option>
                    <option
                        v-for="mitra in mitras"
                        :key="mitra.id"
                        :value="mitra.id"
                    >
                        {{ mitra.nama_perusahaan }}
                    </option>
                </select>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pilih mitra jika lowongan ini diposting atas nama perusahaan, atau biarkan kosong untuk posting sebagai Admin FMIKOM.
                </p>
                <p v-if="form.errors.mitra_id" class="text-sm text-red-500">{{ form.errors.mitra_id }}</p>
            </div>
        </div>
    </TFormSection>

    <!-- Work Details -->
    <TFormSection title="Detail Pekerjaan">
        <div class="space-y-5">
            <!-- Experience Level -->
            <div class="space-y-2">
                <Label for="experience_level">Tingkat Pengalaman</Label>
                <select
                    id="experience_level"
                    v-model="form.experience_level"
                    :class="selectClass"
                >
                    <option
                        v-for="level in experienceLevels"
                        :key="level.value"
                        :value="level.value"
                    >
                        {{ level.label }}
                    </option>
                </select>
                <p v-if="form.errors.experience_level" class="text-sm text-red-500">{{ form.errors.experience_level }}</p>
            </div>

            <!-- Location Type -->
            <div class="space-y-2">
                <Label for="location_type">Tipe Lokasi</Label>
                <select
                    id="location_type"
                    v-model="form.location_type"
                    :class="selectClass"
                >
                    <option
                        v-for="loc in locationTypes"
                        :key="loc.value"
                        :value="loc.value"
                    >
                        {{ loc.label }}
                    </option>
                </select>
                <p v-if="form.errors.location_type" class="text-sm text-red-500">{{ form.errors.location_type }}</p>
            </div>

            <!-- Location City (conditional) -->
            <div v-if="form.location_type === 'onsite' || form.location_type === 'hybrid'" class="space-y-2">
                <Label for="location_city">Kota</Label>
                <Input
                    id="location_city"
                    v-model="form.location_city"
                    placeholder="Contoh: Jakarta, Yogyakarta"
                />
                <p v-if="form.errors.location_city" class="text-sm text-red-500">{{ form.errors.location_city }}</p>
            </div>

            <!-- Tipe Kerja -->
            <div class="space-y-2">
                <Label for="tipe_kerja">Tipe Kerja</Label>
                <select
                    id="tipe_kerja"
                    v-model="form.tipe_kerja"
                    :class="selectClass"
                >
                    <option
                        v-for="tipe in tipeKerjaOptions"
                        :key="tipe.value"
                        :value="tipe.value"
                    >
                        {{ tipe.label }}
                    </option>
                </select>
                <p v-if="form.errors.tipe_kerja" class="text-sm text-red-500">{{ form.errors.tipe_kerja }}</p>
            </div>
        </div>
    </TFormSection>

    <!-- Salary & Deadline -->
    <TFormSection title="Gaji & Deadline">
        <div class="space-y-5">
            <!-- Salary Range -->
            <div class="space-y-2">
                <Label>Rentang Gaji (Rp)</Label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Input
                            v-model="form.salary_min"
                            type="number"
                            placeholder="Minimum"
                            min="0"
                        />
                        <p v-if="form.errors.salary_min" class="mt-1 text-sm text-red-500">{{ form.errors.salary_min }}</p>
                    </div>
                    <div>
                        <Input
                            v-model="form.salary_max"
                            type="number"
                            placeholder="Maksimum"
                            min="0"
                        />
                        <p v-if="form.errors.salary_max" class="mt-1 text-sm text-red-500">{{ form.errors.salary_max }}</p>
                    </div>
                </div>
            </div>

            <!-- Show Salary -->
            <div class="flex items-center gap-2.5">
                <Checkbox
                    id="is_salary_visible"
                    :checked="form.is_salary_visible"
                    @update:checked="(val: boolean) => form.is_salary_visible = val"
                />
                <Label for="is_salary_visible" class="cursor-pointer text-sm font-normal text-slate-600 dark:text-slate-400">
                    Tampilkan gaji pada lowongan
                </Label>
            </div>

            <!-- Deadline -->
            <div class="space-y-2">
                <Label for="deadline">Batas Akhir Lamaran <span class="text-red-500">*</span></Label>
                <Input
                    id="deadline"
                    v-model="form.deadline"
                    type="date"
                />
                <p v-if="form.errors.deadline" class="text-sm text-red-500">{{ form.errors.deadline }}</p>
            </div>
        </div>
    </TFormSection>

    <!-- Status -->
    <TFormSection title="Publikasi">
        <div class="space-y-5">
            <div class="space-y-2">
                <Label for="status">Status</Label>
                <select
                    id="status"
                    v-model="form.status"
                    :class="selectClass"
                >
                    <option
                        v-for="s in statusOptions"
                        :key="s.value"
                        :value="s.value"
                    >
                        {{ s.label }}
                    </option>
                </select>
                <slot name="status-hint" />
                <p v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <slot name="actions" />
            </div>
        </div>
    </TFormSection>
</template>
