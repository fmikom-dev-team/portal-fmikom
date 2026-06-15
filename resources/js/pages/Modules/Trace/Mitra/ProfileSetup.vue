<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Building2,
    Globe,
    Mail,
    Phone,
    MapPin,
    Briefcase,
} from 'lucide-vue-next';

defineProps({
    provinces: { type: Array, default: () => [] },
});

const form = useForm({
    nama_perusahaan: '',
    deskripsi: '',
    website: '',
    email_perusahaan: '',
    no_telp: '',
    alamat_lengkap: '',
});

function submit() {
    form.post('/trace/open-job/profile-setup');
}
</script>

<template>
    <Head title="Profil Perusahaan — TRACE Mitra" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-violet-50/40 to-indigo-50 px-4 py-12">
        <!-- Background decorative blobs -->
        <div class="pointer-events-none absolute -left-40 -top-40 h-[500px] w-[500px] rounded-full bg-violet-200/30 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-32 -right-32 h-[420px] w-[420px] rounded-full bg-indigo-200/25 blur-3xl" />
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-[300px] w-[300px] -translate-x-1/2 rounded-full bg-purple-100/20 blur-2xl" />

        <div class="relative z-10 w-full max-w-2xl animate-fade-in-up">
            <Card class="border-white/60 bg-white/70 shadow-xl shadow-violet-100/40 backdrop-blur-xl">
                <CardHeader class="space-y-4 pb-2 text-center">
                    <!-- Branded icon -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-lg shadow-violet-500/25">
                        <Briefcase class="h-8 w-8 text-white" :stroke-width="1.75" />
                    </div>

                    <div class="space-y-1.5">
                        <CardTitle class="text-2xl font-bold tracking-tight text-slate-900">
                            TRACE Mitra
                        </CardTitle>
                        <CardDescription class="mx-auto max-w-md text-sm leading-relaxed text-slate-500">
                            Lengkapi profil perusahaan Anda untuk mulai memposting lowongan kerja
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="px-6 pb-8 pt-4 sm:px-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Nama Perusahaan -->
                        <div class="space-y-2">
                            <Label for="nama_perusahaan" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Building2 class="h-4 w-4 text-violet-500" />
                                Nama Perusahaan
                                <span class="text-red-400">*</span>
                            </Label>
                            <Input
                                id="nama_perusahaan"
                                v-model="form.nama_perusahaan"
                                type="text"
                                placeholder="PT Contoh Perusahaan"
                                required
                                class="transition-shadow focus:shadow-md focus:shadow-violet-100"
                            />
                            <p v-if="form.errors.nama_perusahaan" class="text-xs text-red-500">
                                {{ form.errors.nama_perusahaan }}
                            </p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <Label for="deskripsi" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Building2 class="h-4 w-4 text-violet-500" />
                                Deskripsi
                            </Label>
                            <Textarea
                                id="deskripsi"
                                v-model="form.deskripsi"
                                placeholder="Ceritakan tentang perusahaan Anda..."
                                rows="3"
                                class="resize-none transition-shadow focus:shadow-md focus:shadow-violet-100"
                            />
                            <p v-if="form.errors.deskripsi" class="text-xs text-red-500">
                                {{ form.errors.deskripsi }}
                            </p>
                        </div>

                        <!-- Website & Email — two-column on sm+ -->
                        <div class="grid gap-5 sm:grid-cols-2">
                            <!-- Website -->
                            <div class="space-y-2">
                                <Label for="website" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <Globe class="h-4 w-4 text-violet-500" />
                                    Website
                                </Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    type="url"
                                    placeholder="https://perusahaan.com"
                                    class="transition-shadow focus:shadow-md focus:shadow-violet-100"
                                />
                                <p v-if="form.errors.website" class="text-xs text-red-500">
                                    {{ form.errors.website }}
                                </p>
                            </div>

                            <!-- Email Perusahaan -->
                            <div class="space-y-2">
                                <Label for="email_perusahaan" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <Mail class="h-4 w-4 text-violet-500" />
                                    Email Perusahaan
                                </Label>
                                <Input
                                    id="email_perusahaan"
                                    v-model="form.email_perusahaan"
                                    type="email"
                                    placeholder="info@perusahaan.com"
                                    class="transition-shadow focus:shadow-md focus:shadow-violet-100"
                                />
                                <p v-if="form.errors.email_perusahaan" class="text-xs text-red-500">
                                    {{ form.errors.email_perusahaan }}
                                </p>
                            </div>
                        </div>

                        <!-- No. Telp -->
                        <div class="space-y-2">
                            <Label for="no_telp" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Phone class="h-4 w-4 text-violet-500" />
                                No. Telp
                            </Label>
                            <Input
                                id="no_telp"
                                v-model="form.no_telp"
                                type="tel"
                                placeholder="(021) 1234-5678"
                                class="transition-shadow focus:shadow-md focus:shadow-violet-100"
                            />
                            <p v-if="form.errors.no_telp" class="text-xs text-red-500">
                                {{ form.errors.no_telp }}
                            </p>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="space-y-2">
                            <Label for="alamat_lengkap" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <MapPin class="h-4 w-4 text-violet-500" />
                                Alamat Lengkap
                            </Label>
                            <Textarea
                                id="alamat_lengkap"
                                v-model="form.alamat_lengkap"
                                placeholder="Jl. Contoh No. 123, Kota, Provinsi"
                                rows="3"
                                class="resize-none transition-shadow focus:shadow-md focus:shadow-violet-100"
                            />
                            <p v-if="form.errors.alamat_lengkap" class="text-xs text-red-500">
                                {{ form.errors.alamat_lengkap }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <div class="pt-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-lg shadow-violet-500/25 transition-all hover:from-violet-700 hover:to-indigo-700 hover:shadow-xl hover:shadow-violet-500/30 active:scale-[0.98]"
                                size="lg"
                            >
                                <template v-if="form.processing">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Menyimpan...
                                </template>
                                <template v-else>
                                    Simpan & Lanjutkan
                                </template>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Footer note -->
            <p class="mt-6 text-center text-xs text-slate-400">
                Informasi ini dapat diubah nanti melalui pengaturan profil
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out both;
}
</style>
