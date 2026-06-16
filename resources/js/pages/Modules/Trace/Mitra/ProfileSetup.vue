<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { TFormSection } from '@/components/trace';
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
    form.post('/trace/open-job/profile-setup', {
        onSuccess: () => toast.success('Profil berhasil dibuat!'),
        onError: () => toast.error('Gagal menyimpan. Periksa kembali form Anda.'),
    });
}
</script>

<template>
    <Head title="Profil Perusahaan — TRACE Mitra" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-[#85B7EB]/10 to-[#0C447C]/5 px-4 py-12">
        <!-- Background decorative blobs -->
        <div class="pointer-events-none absolute -left-40 -top-40 h-[500px] w-[500px] rounded-full bg-[#85B7EB]/20 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-32 -right-32 h-[420px] w-[420px] rounded-full bg-[#0C447C]/15 blur-3xl" />
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-[300px] w-[300px] -translate-x-1/2 rounded-full bg-[#85B7EB]/10 blur-2xl" />

        <div class="relative z-10 w-full max-w-2xl animate-fade-in-up">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0C447C] to-[#85B7EB] shadow-lg shadow-[#0C447C]/25 mb-6">
                <Briefcase class="h-8 w-8 text-white" :stroke-width="1.75" />
            </div>
            <TFormSection title="TRACE Mitra" description="Lengkapi profil perusahaan Anda untuk mulai memposting lowongan kerja">
                <form @submit.prevent="submit" class="space-y-5">
                        <!-- Nama Perusahaan -->
                        <div class="space-y-2">
                            <Label for="nama_perusahaan" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Building2 class="h-4 w-4 text-[#0C447C]" />
                                Nama Perusahaan
                                <span class="text-red-400">*</span>
                            </Label>
                            <Input
                                id="nama_perusahaan"
                                v-model="form.nama_perusahaan"
                                type="text"
                                placeholder="PT Contoh Perusahaan"
                                required
                                class="transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
                            />
                            <p v-if="form.errors.nama_perusahaan" class="text-xs text-red-500">
                                {{ form.errors.nama_perusahaan }}
                            </p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <Label for="deskripsi" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Building2 class="h-4 w-4 text-[#0C447C]" />
                                Deskripsi
                            </Label>
                            <Textarea
                                id="deskripsi"
                                v-model="form.deskripsi"
                                placeholder="Ceritakan tentang perusahaan Anda..."
                                rows="3"
                                class="resize-none transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
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
                                    <Globe class="h-4 w-4 text-[#0C447C]" />
                                    Website
                                </Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    type="url"
                                    placeholder="https://perusahaan.com"
                                    class="transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
                                />
                                <p v-if="form.errors.website" class="text-xs text-red-500">
                                    {{ form.errors.website }}
                                </p>
                            </div>

                            <!-- Email Perusahaan -->
                            <div class="space-y-2">
                                <Label for="email_perusahaan" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <Mail class="h-4 w-4 text-[#0C447C]" />
                                    Email Perusahaan
                                </Label>
                                <Input
                                    id="email_perusahaan"
                                    v-model="form.email_perusahaan"
                                    type="email"
                                    placeholder="info@perusahaan.com"
                                    class="transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
                                />
                                <p v-if="form.errors.email_perusahaan" class="text-xs text-red-500">
                                    {{ form.errors.email_perusahaan }}
                                </p>
                            </div>
                        </div>

                        <!-- No. Telp -->
                        <div class="space-y-2">
                            <Label for="no_telp" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <Phone class="h-4 w-4 text-[#0C447C]" />
                                No. Telp
                            </Label>
                            <Input
                                id="no_telp"
                                v-model="form.no_telp"
                                type="tel"
                                placeholder="(021) 1234-5678"
                                class="transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
                            />
                            <p v-if="form.errors.no_telp" class="text-xs text-red-500">
                                {{ form.errors.no_telp }}
                            </p>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="space-y-2">
                            <Label for="alamat_lengkap" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <MapPin class="h-4 w-4 text-[#0C447C]" />
                                Alamat Lengkap
                            </Label>
                            <Textarea
                                id="alamat_lengkap"
                                v-model="form.alamat_lengkap"
                                placeholder="Jl. Contoh No. 123, Kota, Provinsi"
                                rows="3"
                                class="resize-none transition-shadow focus:shadow-md focus:shadow-[#85B7EB]/30"
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
                                class="w-full bg-gradient-to-r from-[#0C447C] to-[#85B7EB] text-white shadow-lg shadow-[#0C447C]/25 transition-all hover:from-[#0C447C]/90 hover:to-[#85B7EB]/90 hover:shadow-xl hover:shadow-[#0C447C]/30 active:scale-[0.98]"
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
            </TFormSection>

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
