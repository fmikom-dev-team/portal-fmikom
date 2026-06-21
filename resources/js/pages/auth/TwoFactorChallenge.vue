<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
	InputOTP,
	InputOTPGroup,
	InputOTPSlot,
} from "@/components/ui/input-otp";
import AuthLayout from "@/layouts/AuthLayout.vue";
import type { TwoFactorConfigContent } from "@/types";

const authConfigContent = computed<TwoFactorConfigContent>(() => {
	if (showRecoveryInput.value) {
		return {
			title: "Recovery code",
			description:
				"Please confirm access to your account by entering one of your emergency recovery codes.",
			buttonText: "login using an authentication code",
		};
	}

	return {
		title: "Authentication code",
		description:
			"Enter the authentication code provided by your authenticator application.",
		buttonText: "login using a recovery code",
	};
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
	showRecoveryInput.value = !showRecoveryInput.value;
	clearErrors();
	form.reset();
};

const form = useForm({
	code: "",
	recovery_code: "",
});

const submit = () => {
	form.post("/two-factor-challenge", {
		onFinish: () => {
			form.reset();
		},
	});
};
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head>
            <title>Two-factor authentication</title>
        </Head>

        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <form @submit.prevent="submit" class="space-y-4">
                    <div
                        class="flex flex-col items-center justify-center space-y-3 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <InputOTP
                                id="otp"
                                v-model="form.code"
                                :maxlength="6"
                                :disabled="form.processing"
                                autofocus
                            >
                                <InputOTPGroup>
                                    <InputOTPSlot
                                        v-for="index in 6"
                                        :key="index"
                                        :index="index - 1"
                                    />
                                </InputOTPGroup>
                            </InputOTP>
                        </div>
                        <InputError :message="form.errors.code" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Continue
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(form.clearErrors)"
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </form>
            </template>

            <template v-else>
                <form @submit.prevent="submit" class="space-y-4">
                    <Input
                        v-model="form.recovery_code"
                        name="recovery_code"
                        type="text"
                        placeholder="Enter recovery code"
                        :autofocus="showRecoveryInput"
                        required
                    />
                    <InputError :message="form.errors.recovery_code" />
                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Continue
                    </Button>

                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(form.clearErrors)"
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </AuthLayout>
</template>
