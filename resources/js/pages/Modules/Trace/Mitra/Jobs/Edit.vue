<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ArrowLeft, Briefcase, ImageUp, X } from 'lucide-vue-next';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import { TPageHeader, TFormSection, TStatusBadge } from '@/components/Trace';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import EditorJsEditor from '@/components/editor/EditorJsEditor.vue';
import { Checkbox } from '@/components/ui/checkbox';

interface Category {
    id: number;
    nama: string;
    slug: string;
}

interface Job {
    id: number;
    title: string;
    description: string | object | null;
    job_category_id: number | null;
    experience_level: string;
    location_type: string;
    location_city: string | null;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    deadline: string | null;
    status: string;
    poster_url: string | null;
}

const props = defineProps<{
    job: Job;
    categories: Category[];
}>();

function parseDescription(desc: string | object | null): object | null {
    if (!desc) return null;
    if (typeof desc === 'object') return desc;
    try {
        return JSON.parse(desc as string);
    } catch {
        return null;
    }
}

const form = useForm({
    title: props.job.title ?? '',
    description: parseDescription(props.job.description),
    job_category_id: props.job.job_category_id ?? '',
    experience_level: props.job.experience_level ?? 'fresh_graduate',
    location_type: props.job.location_type ?? 'onsite',
    location_city: props.job.location_city ?? '',
    tipe_kerja: props.job.tipe_kerja ?? 'full_time',
    salary_min: props.job.salary_min ?? '',
    salary_max: props.job.salary_max ?? '',
    is_salary_visible: props.job.is_salary_visible ?? true,
    deadline: props.job.deadline ? props.job.deadline.split('T')[0] : '',
    status: props.job.status ?? 'draft',
    poster: null as File | null,
});

const posterPreview = ref<string | null>(props.job.poster_url);

function onPosterChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.poster = file;
        posterPreview.value = URL.createObjectURL(file);
    }
}

function removePoster() {
    form.poster = null;
    posterPreview.value = null;
}

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

const statusOptions = [
    { value: 'draft', label: 'Simpan sebagai Draft' },
    { value: 'pending_review', label: 'Ajukan Review ke Admin' },
    { value: 'closed', label: 'Tutup Lowongan' },
];

function submit() {
    form.transform((data) => {
        const payload = {
            ...data,
            description: data.description ? JSON.stringify(data.description) : '',
            _method: 'PUT',
        };
        // Do not update status if already published, pending_review, or closed
        if (['published', 'pending_review', 'closed'].includes(props.job.status)) {
            delete payload.status;
        }
        return payload;
    }).post(`/trace/open-job/jobs-listings/${props.job.id}`, {
        forceFormData: true,
        onError: () => toast.error('Gagal memperbarui lowongan. Periksa kembali form Anda.'),
    });
}

const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background/30 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-all appearance-none';
</script>

<template>
    <TraceMitraLayout title="Edit Lowongan">
        <TPageHeader title="Edit Lowongan" description="Perbarui lowongan kerja perusahaan Anda" :icon="Briefcase" class="mb-6">
            <template #actions>
                <Link
                    :href="`/trace/open-job/jobs-listings/${job.id}`"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-[#0C447C] dark:text-slate-400 dark:hover:text-[#85B7EB] transition-colors"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </template>
        </TPageHeader>

        <form @submit.prevent="submit" class="max-w-3xl space-y-6">
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

                    <!-- Poster Upload -->
                    <div class="space-y-2">
                        <Label>Poster / Gambar Lowongan</Label>
                        <div
                            v-if="!posterPreview"
                            class="relative flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 dark:border-zinc-600 bg-slate-50/50 dark:bg-zinc-800/30 p-8 text-center cursor-pointer hover:border-[#0C447C] dark:hover:border-[#85B7EB] hover:bg-slate-100/50 dark:hover:bg-zinc-700/30 transition-all"
                            @click="($refs.posterInput as HTMLInputElement)?.click()"
                        >
                            <ImageUp class="h-10 w-10 text-slate-400 dark:text-zinc-500" />
                            <div>
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Klik untuk upload poster</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1">JPG, PNG, atau WebP (maks. 5MB)</p>
                            </div>
                            <input
                                ref="posterInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onPosterChange"
                            />
                        </div>
                        <div v-else class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700">
                            <img :src="posterPreview" alt="Preview poster" class="w-full h-48 object-cover" />
                            <button
                                type="button"
                                class="absolute top-2 right-2 rounded-full bg-red-500 hover:bg-red-600 text-white p-1.5 shadow-lg transition-colors"
                                @click="removePoster"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.poster" class="text-sm text-red-500">{{ form.errors.poster }}</p>
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

            <!-- Status & Submit -->
            <TFormSection title="Publikasi">
                <div class="space-y-5">
                    <div v-if="['published', 'pending_review', 'closed'].includes(job.status)" class="space-y-2">
                        <Label>Status Lowongan</Label>
                        <div class="flex items-center gap-2">
                            <TStatusBadge :status="job.status" size="md" />
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Status lowongan ini sudah aktif/diproses dan tidak perlu diubah.
                            </span>
                        </div>
                    </div>
                    <div v-else class="space-y-2">
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
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Pilih "Ajukan Review ke Admin" jika Anda ingin mempublikasikan kembali setelah melakukan perubahan.
                        </p>
                        <p v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 text-white shadow-md shadow-[#0C447C]/20 px-6"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                        <Link :href="`/trace/open-job/jobs-listings/${job.id}`">
                            <Button type="button" variant="outline" class="rounded-xl">
                                Batal
                            </Button>
                        </Link>
                    </div>
                </div>
            </TFormSection>
        </form>
    </TraceMitraLayout>
</template>
