<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { TPageHeader, TFormSection } from '@/components/trace';
import {
    Building2,
    Globe,
    Mail,
    Phone,
    MapPin,
    Save,
    Upload,
    X,
    ImageIcon,
} from 'lucide-vue-next';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';

const props = defineProps({
    mitra: { type: Object, required: true },
});

const form = useForm({
    _method: 'PUT',
    nama_perusahaan: props.mitra.nama_perusahaan ?? '',
    deskripsi: props.mitra.deskripsi ?? '',
    website: props.mitra.website ?? '',
    email_perusahaan: props.mitra.email_perusahaan ?? '',
    no_telp: props.mitra.no_telp ?? '',
    alamat_lengkap: props.mitra.alamat_lengkap ?? '',
    logo: null,
});

const getLogoUrl = (path) => {
    if (!path) return null;
    // Already a full URL or starts with /storage/ or blob:
    if (path.startsWith('http') || path.startsWith('/storage/') || path.startsWith('blob:')) return path;
    // Relative path from storage — prepend /storage/
    return `/storage/${path}`;
};

const logoPreview = ref(props.mitra.logo_url);
const fileInput = ref(null);

function onLogoChange(event) {
    const file = event.target.files[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
}

function removeLogo() {
    form.logo = null;
    logoPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function triggerFileInput() {
    fileInput.value?.click();
}

function submit() {
    form.post('/trace/open-job/profile', {
        onError: () => toast.error('Gagal memperbarui profil mitra. Periksa kembali form Anda.'),
    });
}
</script>

<template>
    <TraceMitraLayout title="Profil Perusahaan">
        <div class="mx-auto max-w-3xl">
            <TPageHeader title="Edit Profil" description="Kelola informasi perusahaan Anda" :icon="Building2" class="mb-6" />

            <TFormSection title="Informasi Perusahaan" description="Data ini akan ditampilkan di halaman lowongan kerja">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Logo Upload -->
                        <div class="space-y-2">
                            <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <ImageIcon class="h-4 w-4 text-violet-500" />
                                Logo Perusahaan
                            </Label>
                            <div class="flex items-center gap-4">
                                <!-- Preview -->
                                <div
                                    class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden transition-colors dark:border-zinc-700 dark:bg-zinc-800"
                                    :class="{ 'border-violet-300 dark:border-violet-700': logoPreview }"
                                >
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Logo preview"
                                        class="h-full w-full object-contain p-1"
                                    />
                                    <Building2 v-else class="h-8 w-8 text-slate-300 dark:text-zinc-600" />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        @change="onLogoChange"
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="rounded-lg text-sm gap-1.5"
                                        @click="triggerFileInput"
                                    >
                                        <Upload class="h-3.5 w-3.5" />
                                        {{ logoPreview ? 'Ganti Logo' : 'Upload Logo' }}
                                    </Button>
                                    <Button
                                        v-if="logoPreview"
                                        type="button"
                                        variant="ghost"
                                        class="rounded-lg text-sm text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 gap-1"
                                        @click="removeLogo"
                                    >
                                        <X class="h-3.5 w-3.5" /> Hapus
                                    </Button>
                                    <p class="text-[11px] text-slate-400">JPG, PNG, WebP · Maks. 2MB</p>
                                </div>
                            </div>
                            <p v-if="form.errors.logo" class="text-xs text-red-500">{{ form.errors.logo }}</p>
                        </div>

                        <!-- Nama Perusahaan -->
                        <div class="space-y-2">
                            <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <Building2 class="h-4 w-4 text-violet-500" />
                                Nama Perusahaan <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                v-model="form.nama_perusahaan"
                                placeholder="PT Teknologi Nusantara"
                                class="rounded-xl"
                                :class="{ 'border-red-300': form.errors.nama_perusahaan }"
                            />
                            <p v-if="form.errors.nama_perusahaan" class="text-xs text-red-500">{{ form.errors.nama_perusahaan }}</p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Deskripsi Perusahaan
                            </Label>
                            <Textarea
                                v-model="form.deskripsi"
                                placeholder="Ceritakan tentang perusahaan Anda..."
                                rows="4"
                                class="rounded-xl"
                            />
                            <p v-if="form.errors.deskripsi" class="text-xs text-red-500">{{ form.errors.deskripsi }}</p>
                        </div>

                        <!-- Website -->
                        <div class="space-y-2">
                            <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <Globe class="h-4 w-4 text-blue-500" />
                                Website
                            </Label>
                            <Input
                                v-model="form.website"
                                type="url"
                                placeholder="https://perusahaan.com"
                                class="rounded-xl"
                                :class="{ 'border-red-300': form.errors.website }"
                            />
                            <p v-if="form.errors.website" class="text-xs text-red-500">{{ form.errors.website }}</p>
                        </div>

                        <!-- Email & Telepon -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <Mail class="h-4 w-4 text-emerald-500" />
                                    Email Perusahaan
                                </Label>
                                <Input
                                    v-model="form.email_perusahaan"
                                    type="email"
                                    placeholder="hr@perusahaan.com"
                                    class="rounded-xl"
                                    :class="{ 'border-red-300': form.errors.email_perusahaan }"
                                />
                                <p v-if="form.errors.email_perusahaan" class="text-xs text-red-500">{{ form.errors.email_perusahaan }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <Phone class="h-4 w-4 text-amber-500" />
                                    No. Telepon
                                </Label>
                                <Input
                                    v-model="form.no_telp"
                                    type="tel"
                                    placeholder="08xx-xxxx-xxxx"
                                    class="rounded-xl"
                                />
                                <p v-if="form.errors.no_telp" class="text-xs text-red-500">{{ form.errors.no_telp }}</p>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="space-y-2">
                            <Label class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <MapPin class="h-4 w-4 text-red-500" />
                                Alamat Lengkap
                            </Label>
                            <Textarea
                                v-model="form.alamat_lengkap"
                                placeholder="Jl. Contoh No. 123, Kota, Provinsi"
                                rows="3"
                                class="rounded-xl"
                            />
                            <p v-if="form.errors.alamat_lengkap" class="text-xs text-red-500">{{ form.errors.alamat_lengkap }}</p>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end pt-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-violet-600 px-6 text-white hover:bg-violet-700"
                            >
                                <Save class="mr-1.5 h-4 w-4" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                        </div>
                    </form>
            </TFormSection>
        </div>
    </TraceMitraLayout>
</template>
