<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <AuthLayout
        title="Masuk ke FAST"
        description="Gunakan akun Anda untuk mengakses layanan administrasi FMIKOM."
    >
        <Head title="Login" />

        <div v-if="status" class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
            {{ status }}
        </div>

        <form class="mt-6 flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="password">Password</Label>
                    <TextLink
                        v-if="canResetPassword"
                        href="/forgot-password"
                        class="text-sm"
                    >
                        Lupa password?
                    </TextLink>
                </div>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                <input v-model="form.remember" type="checkbox" class="rounded border-slate-300">
                <span>Ingat saya</span>
            </label>

            <Button type="submit" class="w-full" :disabled="form.processing">
                <Spinner v-if="form.processing" class="mr-2" />
                <span>Masuk</span>
            </Button>
        </form>

        <div v-if="canRegister" class="mt-6 text-center text-sm text-muted-foreground">
            Belum punya akun?
            <TextLink href="/register" class="underline underline-offset-4">
                Daftar
            </TextLink>
        </div>
    </AuthLayout>
</template>
