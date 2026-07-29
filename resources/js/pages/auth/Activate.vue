<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/components/InputError.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

defineProps<{
	status?: string;
	error?: string;
}>();

const form = useForm({
	identifier: "",
});

const submit = () => {
	form.post("/activate");
};
</script>

<template>
    <AuthLayout
        title="Aktivasi Akun Mahasiswa"
        description="Masukkan NIM Anda untuk memverifikasi identitas dan mengaktifkan akun"
    >
        <Head>
            <title>Aktivasi Akun Mahasiswa</title>
        </Head>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 bg-green-50 dark:bg-green-950/30 p-3 rounded-xl border border-green-200 dark:border-green-900">
            {{ status }}
        </div>
        <div v-if="error" class="mb-4 text-center text-sm font-medium text-red-600 bg-red-50 dark:bg-red-950/30 p-3 rounded-xl border border-red-200 dark:border-red-900">
            {{ error }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <div class="grid gap-2">
                    <Label for="identifier" class="font-semibold text-slate-800 dark:text-slate-200">
                        NIM (Nomor Induk Mahasiswa)
                    </Label>
                    <Input
                        id="identifier"
                        type="text"
                        v-model="form.identifier"
                        required
                        placeholder="Contoh: 210101001"
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.identifier" />
                </div>

                <div class="mt-2 flex justify-start">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :disabled="form.processing || !form.identifier"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Cari & Verifikasi NIM
                    </Button>
                </div>
            </form>

            <!-- Informasi Bantuan Dosen / Staff -->
            <div class="p-3.5 bg-blue-50/70 dark:bg-slate-900 border border-blue-100 dark:border-slate-800 rounded-xl text-xs text-slate-600 dark:text-slate-400 space-y-1">
                <p class="font-semibold text-blue-900 dark:text-blue-300">ℹ️ Informasi Aktivasi Akun Dosen & Staff:</p>
                <p class="leading-relaxed">
                    Aktivasi akun Dosen dan Staff dikelola langsung oleh Administrator IT Kampus. Silakan hubungi Sekretariat Fakultas / Unit IT untuk mendapatkan link aktivasi resmi.
                </p>
            </div>

            <div class="text-center text-sm text-muted-foreground dark:text-slate-400 mt-4">
                Sudah punya akun aktif?
                <TextLink href="/login" class="underline underline-offset-4">Masuk</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
