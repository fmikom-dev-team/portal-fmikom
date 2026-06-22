<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { TPageHeader, TFormSection } from '@/components/trace';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    ArrowLeft,
    Pencil,
    Image,
    Loader2,
    Save,
    Upload,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface EventDetail {
    id: number;
    title: string;
    description: string;
    location: string;
    event_date: string;
    event_time_start: string | null;
    event_time_end: string | null;
    registration_deadline: string | null;
    max_participants: number | null;
    poster_path: string | null;
    status: 'draft' | 'published' | 'closed';
}

const props = defineProps<{
    event: EventDetail;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Events', href: '/trace/admin/events' },
    { title: props.event.title, href: `/trace/admin/events/${props.event.id}` },
    { title: 'Edit', href: `/trace/admin/events/${props.event.id}/edit` },
]);

// Format date from ISO to YYYY-MM-DD for input
function toDateInput(val: string | null): string {
    if (!val) return '';
    return val.substring(0, 10);
}

// Format time to HH:mm for <input type="time"> (strip seconds if present)
function toTimeInput(val: string | null): string {
    if (!val) return '';
    return val.substring(0, 5);
}

const form = useForm({
    _method: 'PUT',
    title: props.event.title,
    description: props.event.description,
    location: props.event.location ?? '',
    event_date: toDateInput(props.event.event_date),
    event_time_start: toTimeInput(props.event.event_time_start),
    event_time_end: toTimeInput(props.event.event_time_end),
    registration_deadline: toDateInput(props.event.registration_deadline),
    max_participants: props.event.max_participants ?? '',
    poster: null as File | null,
    status: props.event.status,
});

const posterPreview = ref<string | null>(
    props.event.poster_path ? `/storage/${props.event.poster_path}` : null,
);

const handlePosterChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.poster = file;
        posterPreview.value = URL.createObjectURL(file);
    }
};

const removePoster = () => {
    form.poster = null;
    posterPreview.value = null;
};

const submit = () => {
    form.post(`/trace/admin/events/${props.event.id}`, {
        forceFormData: true,
        onError: () => toast.error('Gagal memperbarui event. Periksa kembali form Anda.'),
    });
};
</script>

<template>
    <TraceAdminLayout title="Edit Event" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-[#0C447C] hover:bg-[#85B7EB]/10 dark:hover:bg-[#85B7EB]/10">
                    <Link :href="`/trace/admin/events/${event.id}`">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <TPageHeader
                    title="Edit Event"
                    :description="event.title"
                    :icon="Pencil"
                />
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <TFormSection title="Informasi Dasar" description="Detail utama event yang akan ditampilkan kepada peserta.">
                        <div class="space-y-6">
                            <!-- Title -->
                            <div class="space-y-2">
                                <Label for="title" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Judul Event <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="Masukkan judul event..."
                                    class="rounded-xl"
                                />
                                <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Deskripsi <span class="text-red-500">*</span>
                                </Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Jelaskan detail event..."
                                    class="min-h-[120px] rounded-xl"
                                />
                                <p v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</p>
                            </div>

                            <!-- Location -->
                            <div class="space-y-2">
                                <Label for="location" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Lokasi <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="location"
                                    v-model="form.location"
                                    placeholder="Gedung Rektorat Lt. 5, Aula Utama..."
                                    class="rounded-xl"
                                />
                                <p v-if="form.errors.location" class="text-sm text-red-500 mt-1">{{ form.errors.location }}</p>
                            </div>
                        </div>
                    </TFormSection>

                    <TFormSection title="Jadwal & Kapasitas" description="Atur tanggal, waktu, dan batas peserta event.">
                        <div class="space-y-6">
                            <!-- Date & Time Row -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <Label for="event_date" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Tanggal Event <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="event_date"
                                        v-model="form.event_date"
                                        type="date"
                                        class="rounded-xl"
                                    />
                                    <p v-if="form.errors.event_date" class="text-sm text-red-500 mt-1">{{ form.errors.event_date }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="event_time_start" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Waktu Mulai <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="event_time_start"
                                        v-model="form.event_time_start"
                                        type="time"
                                        class="rounded-xl"
                                    />
                                    <p v-if="form.errors.event_time_start" class="text-sm text-red-500 mt-1">{{ form.errors.event_time_start }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="event_time_end" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Waktu Selesai <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="event_time_end"
                                        v-model="form.event_time_end"
                                        type="time"
                                        class="rounded-xl"
                                    />
                                    <p v-if="form.errors.event_time_end" class="text-sm text-red-500 mt-1">{{ form.errors.event_time_end }}</p>
                                </div>
                            </div>

                            <!-- Deadline & Max Participants -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="registration_deadline" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Batas Pendaftaran <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="registration_deadline"
                                        v-model="form.registration_deadline"
                                        type="date"
                                        class="rounded-xl"
                                    />
                                    <p v-if="form.errors.registration_deadline" class="text-sm text-red-500 mt-1">{{ form.errors.registration_deadline }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="max_participants" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Maks. Peserta
                                    </Label>
                                    <Input
                                        id="max_participants"
                                        v-model="form.max_participants"
                                        type="number"
                                        min="1"
                                        placeholder="Kosongkan jika tanpa batas"
                                        class="rounded-xl"
                                    />
                                    <p v-if="form.errors.max_participants" class="text-sm text-red-500 mt-1">{{ form.errors.max_participants }}</p>
                                </div>
                            </div>
                        </div>
                    </TFormSection>

                    <TFormSection title="Media & Status" description="Upload poster dan atur status publikasi event.">
                        <div class="space-y-6">
                            <!-- Poster Upload -->
                            <div class="space-y-2">
                                <Label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Poster Event
                                </Label>
                                <!-- Hidden file input (always rendered) -->
                                <input
                                    ref="posterInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handlePosterChange"
                                />
                                <div
                                    v-if="!posterPreview"
                                    class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 transition-colors hover:border-sky-300 hover:bg-sky-50/30 dark:border-slate-700 dark:bg-slate-800/30 dark:hover:border-sky-700 cursor-pointer"
                                    @click="($refs.posterInput as HTMLInputElement)?.click()"
                                >
                                    <Upload class="h-8 w-8 text-slate-300 dark:text-slate-600 mb-2" />
                                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Klik untuk upload poster</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">JPG, PNG, max 2MB · Rasio 2:3 (contoh: 800×1200px)</p>
                                </div>
                                <div v-else class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                    <img :src="posterPreview" alt="Preview poster" class="w-full object-contain" />
                                    <div class="absolute top-3 right-3 flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="($refs.posterInput as HTMLInputElement)?.click()"
                                            class="flex items-center gap-1.5 rounded-xl bg-white/90 backdrop-blur px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-lg hover:bg-white transition-colors"
                                        >
                                            <Upload class="h-3.5 w-3.5" />
                                            Ganti
                                        </button>
                                        <button
                                            type="button"
                                            @click="removePoster"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-500 text-white shadow-lg hover:bg-red-600 transition-colors"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                                <p v-if="form.errors.poster" class="text-sm text-red-500 mt-1">{{ form.errors.poster }}</p>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <Label for="status" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Status
                                </Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <p v-if="form.errors.status" class="text-sm text-red-500 mt-1">{{ form.errors.status }}</p>
                            </div>
                        </div>
                    </TFormSection>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <Button as-child variant="outline" class="rounded-xl">
                        <Link :href="`/trace/admin/events/${event.id}`">Batal</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm"
                    >
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Save v-else class="h-4 w-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                </div>
            </form>
        </div>
    </TraceAdminLayout>
</template>
