<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const form = useForm({
	password: "",
});

const submit = () => {
	form.post("/user/confirm-password", {
		onFinish: () => {
			form.reset("password");
		},
	});
};
</script>

<template>
    <AuthLayout
        title="Confirm your password"
        description="This is a secure area of the application. Please confirm your password before continuing."
    >
        <Head>
            <title>Confirm password</title>
        </Head>

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password" class="font-semibold text-slate-800">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        v-model="form.password"
                        class="mt-1 block w-full rounded-xl h-11 border-slate-200 focus-visible:ring-0 focus-visible:border-[#2563eb] transition-colors"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-11 rounded-xl text-md font-medium"
                        :disabled="form.processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Confirm password
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
