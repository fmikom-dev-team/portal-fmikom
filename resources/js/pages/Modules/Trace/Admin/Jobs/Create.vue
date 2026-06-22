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

const props = defineProps<{
    categories: Category[];
    mitras: Mitra[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Lowongan', href: '/trace/admin/jobs' },
    { title: 'Buat Lowongan', href: '/trace/admin/jobs/create' },
];

const form = useForm({
    title: '',
    description: null,
    job_category_id: '',
    mitra_id: '',
    experience_level: 'fresh_graduate',
    location_type: 'onsite',
    location_city: '',
    tipe_kerja: 'full_time',
    salary_min: '',
    salary_max: '',
    is_salary_visible: true,
    deadline: '',
    status: 'published',
});

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

function submit() {
    form.transform((data) => ({
        ...data,
        description: data.description ? JSON.stringify(data.description) : '',
    })).post('/trace/admin/jobs', {
        onError: () => toast.error('Gagal menyimpan lowongan. Periksa kembali form Anda.'),
    });
}
</script>

<template>
    <TraceAdminLayout title="Buat Lowongan" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6">
            <TPageHeader
                title="Tambah Lowongan"
                description="Lengkapi informasi berikut untuk memublikasikan lowongan."
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
                            Sebagai admin, Anda dapat langsung mempublikasikan lowongan tanpa perlu review.
                        </p>
                    </template>
                    <template #actions>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 text-white shadow-md shadow-[#0C447C]/20 px-6"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Lowongan' }}
                        </Button>
                        <Link href="/trace/admin/jobs">
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
