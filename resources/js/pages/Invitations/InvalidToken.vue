<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

const props = defineProps<{
	status: string;
	message: string;
	organization?: string;
}>();
</script>

<template>
    <Head :title="status === 'expired' ? 'Invitation Expired' : 'Invalid Invitation'" />
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f0f9ff 100%); display: flex; align-items: center; justify-content: center; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="background: white; border-radius: 16px; padding: 48px 40px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <!-- Icon -->
            <div style="width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"
                 :style="status === 'expired' ? 'background: #fef3c7;' : 'background: #fee2e2;'">
                <svg v-if="status === 'expired'" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                </svg>
                <svg v-else width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6"/>
                </svg>
            </div>

            <!-- Brand -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 20px;">
                <div style="width: 28px; height: 28px; background: linear-gradient(135deg, #2563eb, #7c3aed); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: white; font-size: 14px; font-weight: 800;">W</span>
                </div>
                <span style="font-size: 15px; font-weight: 700; color: #111827;">Portal FMIKOM</span>
            </div>

            <!-- Title -->
            <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: #111827; letter-spacing: -0.4px;">
                {{ status === 'expired' ? 'Invitation Expired' : status === 'already_accepted' ? 'Already Accepted' : 'Invalid Invitation' }}
            </h1>

            <!-- Message -->
            <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; line-height: 1.6;">
                {{ message }}
            </p>
            <p v-if="organization" style="margin: 0 0 32px; font-size: 14px; color: #9ca3af;">
                Organization: <strong style="color: #374151;">{{ organization }}</strong>
            </p>

            <!-- Action -->
            <a href="/"
               style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: white; font-size: 14px; font-weight: 600; padding: 12px 28px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 14px rgba(82,68,228,0.35);">
                Go to Portal FMIKOM
            </a>

            <p style="margin: 24px 0 0; font-size: 12px; color: #d1d5db;">
                Please contact your organization admin if you believe this is an error.
            </p>
        </div>
    </div>
</template>
