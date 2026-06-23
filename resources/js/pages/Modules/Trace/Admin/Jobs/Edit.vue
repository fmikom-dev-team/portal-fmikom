<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Briefcase } from 'lucide-vue-next';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { TPageHeader } from '@/components/trace';
import JobFormFields from './components/JobFormFields.vue';

interface Category {
    id: number;
    nama: string;
}

interface Mitra {
    id: number;
    nama_perusahaan: string;
}

interface Job {
    id: number;
    title: string;
    description: string | object | null;
    job_category_id: number | null;
    mitra_id: number | null;
    experience_level: string;
    location_type: string;
    location_city: string | null;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    deadline: string | null;
    status: string;
}

const props = defineProps<{
    job: Job;
    categories: Category[];
    mitras: Mitra[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Lowongan', href: '/trace/admin/jobs' },
    { title: 'Edit Lowongan', href: `/trace/admin/jobs/${props.job.id}/edit` },
];

function parseDescription(desc: string | object | null): object | null {
    if (!desc) return null;
    if (typeof desc === 'object') return desc;
    try {
        return JSON.parse(desc);
    } catch {
        return null;
    }
}

const form = useForm({
    title: props.job.title ?? '',
    description: parseDescription(props.job.description),
    job_category_id: props.job.job_category_id ?? '',
    mitra_id: props.job.mitra_id ?? '',
    experience_level: props.job.experience_level ?? 'fresh_graduate',
    location_type: props.job.location_type ?? 'onsite',
    location_city: props.job.location_city ?? '',
    tipe_kerja: props.job.tipe_kerja ?? 'full_time',
    salary_min: props.job.salary_min ?? '',
    salary_max: props.job.salary_max ?? '',
    is_salary_visible: props.job.is_salary_visible ?? true,
    deadline: props.job.deadline ? props.job.deadline.split('T')[0] : '',
    status: props.job.status ?? 'published',
});

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'closed', label: 'Closed' },
];

function submit() {
    form.transform((data) => ({
        ...data,
        description: data.description ? JSON.stringify(data.description) : '',
    })).put(`/trace/admin/jobs/${props.job.id}`, {
        onError: () => toast.error('Gagal memperbarui lowongan. Periksa kembali form Anda.'),
    });
}
</script>

<template>
    <TraceAdminLayout title="Edit Lowongan" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6">
            <TPageHeader
                title="Edit Lowongan"
                description="Perbarui informasi lowongan kerja."
                :icon="Briefcase"
            />

            <form @submit.prevent="submit" class="space-y-6">
                <JobFormFields
                    :form="form"
                    :categories="categories"
                    :mitras="mitras"
                    :status-options="statusOptions"
                >
                    <template #status-hint>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Sebagai admin, Anda dapat langsung mengubah status lowongan.
                        </p>
                    </template>
                    <template #actions>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 text-white shadow-md shadow-[#0C447C]/20 px-6"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                        <Link :href="`/trace/admin/jobs/${job.id}`">
                            <Button type="button" variant="outline" class="rounded-xl">
                                Batal
                            </Button>
                        </Link>
                    </template>
                </JobFormFields>
            </form>
        </div>
    </TraceAdminLayout>
</template>
