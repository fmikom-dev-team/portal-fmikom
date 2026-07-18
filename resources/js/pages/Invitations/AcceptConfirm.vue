<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";

const props = defineProps<{
	token: string;
	email: string;
	organization: string;
	role: string;
	invited_by: string;
}>();

const acceptInvitation = () => {
    router.post(`/invitations/accept/${props.token}`);
};
</script>

<template>
    <Head>
        <title>Accept Invitation</title>
    </Head>
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f0fdf4 100%); display: flex; align-items: center; justify-content: center; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="background: white; border-radius: 16px; padding: 48px 40px; max-width: 500px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            
            <!-- Invitation Icon -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(79,70,229,0.15);">
                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"/>
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
            <h1 style="margin: 0 0 10px; font-size: 24px; font-weight: 700; color: #111827; letter-spacing: -0.4px;">
                Join {{ organization }}! 🎉
            </h1>
            <p style="margin: 0 0 28px; font-size: 15px; color: #6b7280; line-height: 1.6;">
                You have been invited by <strong style="color: #374151;">{{ invited_by }}</strong> to join <strong style="color: #374151;">{{ organization }}</strong>.
            </p>

            <!-- Details Card -->
            <div style="background: #f5f3ff; border: 1px solid #ede9fe; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; text-align: left;">
                <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                    <div>
                        <p style="margin: 0 0 2px; font-size: 10px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Your Email Address</p>
                        <p style="margin: 0; font-size: 13px; font-weight: 600; color: #1f2937; word-break: break-all;">{{ email }}</p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <p style="margin: 0 0 2px; font-size: 10px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Assigned Role</p>
                            <p style="margin: 0; font-size: 13px; font-weight: 600; color: #1f2937;">{{ role }}</p>
                        </div>
                        <div>
                            <p style="margin: 0 0 2px; font-size: 10px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Organization</p>
                            <p style="margin: 0; font-size: 13px; font-weight: 600; color: #1f2937;">{{ organization }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <button @click="acceptInvitation"
               style="width: 100%; border: none; cursor: pointer; display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: white; font-size: 14px; font-weight: 600; padding: 14px 32px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 14px rgba(82,68,228,0.35); margin-bottom: 12px; transition: opacity 0.2s;">
                Accept Invitation & Join →
            </button>
            <br>
            <a href="/"
               style="font-size: 13px; color: #6b7280; text-decoration: none; display: inline-block; margin-top: 8px;">
                Decline & Go back
            </a>

            <p style="margin: 32px 0 0; font-size: 12px; color: #d1d5db;">
                © 2026 Portal FMIKOM WorkOS
            </p>
        </div>
    </div>
</template>
