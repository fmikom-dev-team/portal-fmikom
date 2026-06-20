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
}>();

const form = useForm({
	email: "",
});

const submit = () => {
	form.post("/forgot-password");
};
</script>

<template>
    <AuthLayout
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head>
        <title>Forgot password</title>
    </Head>

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email" class="font-semibold text-slate-800">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="rounded-xl h-11 border-slate-200 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :disabled="form.processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Email password reset link
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <TextLink href="/login">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
