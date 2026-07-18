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
	tanggal_lahir: "",
	user_type: "mahasiswa", // default
});

const submit = () => {
	form.post("/activate");
};
</script>

<template>
    <AuthLayout
        title="Aktivasi Akun"
        description="Lakukan verifikasi identitas Anda untuk mengaktifkan akun"
    >
        <Head>
            <title>Aktivasi Akun</title>
        </Head>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <div v-if="error" class="mb-4 text-center text-sm font-medium text-red-600">
            {{ error }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <div class="grid gap-2">
                    <Label class="font-semibold text-slate-800 dark:text-slate-200">Tipe Pengguna</Label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.user_type === 'mahasiswa' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.user_type" value="mahasiswa" class="sr-only" />
                            <span class="font-medium text-xs text-center">Mahasiswa</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.user_type === 'dosen' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.user_type" value="dosen" class="sr-only" />
                            <span class="font-medium text-xs text-center">Dosen</span>
                        </label>
                        <label :class="['flex flex-col items-center justify-center gap-1.5 p-3 border rounded-xl cursor-pointer transition-all', form.user_type === 'staff' ? 'border-[#2563eb] bg-indigo-50/50 dark:bg-indigo-950/20 text-[#2563eb] dark:text-indigo-400 ring-1 ring-[#2563eb]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
                            <input type="radio" v-model="form.user_type" value="staff" class="sr-only" />
                            <span class="font-medium text-xs text-center">Staff</span>
                        </label>
                    </div>
                    <InputError :message="form.errors.user_type" />
                </div>

                <div class="grid gap-2">
                    <Label for="identifier" class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ form.user_type === 'mahasiswa' ? 'NIM' : 'NIP / NIDN' }}
                    </Label>
                    <Input
                        id="identifier"
                        type="text"
                        v-model="form.identifier"
                        required
                        placeholder="Masukkan nomor identitas Anda"
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.identifier" />
                </div>

                <div class="grid gap-2">
                    <Label for="tanggal_lahir" class="font-semibold text-slate-800 dark:text-slate-200">Tanggal Lahir</Label>
                    <Input
                        id="tanggal_lahir"
                        type="date"
                        v-model="form.tanggal_lahir"
                        required
                        class="rounded-xl h-11 border-slate-200 dark:border-slate-800 dark:bg-slate-950 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.tanggal_lahir" />
                </div>

                <div class="mt-2 flex justify-start">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :disabled="form.processing"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Verifikasi Identitas
                    </Button>
                </div>
            </form>

            <div class="text-center text-sm text-muted-foreground dark:text-slate-400 mt-4">
                Sudah punya akun aktif?
                <TextLink href="/login" class="underline underline-offset-4">Masuk</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
