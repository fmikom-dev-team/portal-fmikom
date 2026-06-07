<script setup lang="ts">
import { ref, watch } from "vue";
import Logs from "./Logs.vue";
import SMTP from "./SMTP.vue";
import Templates from "./Templates.vue";

const props = defineProps<{
	smtpConfig: any;
}>();

const activeTab = ref("smtp");

const tabs = [
	{ id: "smtp", label: "SMTP & Providers" },
	{ id: "templates", label: "Templates" },
	{ id: "logs", label: "Logs" },
];

const DEFAULT_LOGS = [
	{
		id: "msg_01J8G5S2Y9T",
		recipient: "maruf@fmikom.org",
		subject: "Verify your email address",
		type: "Verification Email",
		status: "Delivered",
		sentAt: "May 24, 2026, 08:32 PM",
		provider: "Postmark API",
		events: [
			{ time: "08:32:10 PM", event: "Enqueued for delivery" },
			{ time: "08:32:11 PM", event: "Processed by Postmark SMTP relay" },
			{ time: "08:32:12 PM", event: "Delivered to target mailserver (250 OK)" },
			{ time: "08:34:02 PM", event: "Opened by recipient" },
		],
		variables: {
			name: "Ma'ruf Muchlisin",
			otp_code: "482093",
			link: "https://fmikom.suntree.my.id/verify-otp?code=482093",
			expiry: "10 minutes",
		},
		body: `
			<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
				<h2 style="color: #111827; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Verify your email address</h2>
				<p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">Hello Ma'ruf Muchlisin,</p>
				<p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">Thank you for registering on Portal FMIKOM. To complete your login process, please use the following One-Time Password (OTP) verification code:</p>
				<div style="background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; padding: 16px; text-align: center; margin-bottom: 24px;">
					<span style="font-family: monospace; font-size: 24px; font-weight: 700; letter-spacing: 4px; color: #4f46e5;">482093</span>
				</div>
				<p style="color: #4b5563; font-size: 12px; line-height: 1.5;">This verification code is only valid for 10 minutes. If you did not request this email, you can safely ignore it.</p>
			</div>
		`,
	},
	{
		id: "msg_01J8G5S2YAA",
		recipient: "mitra.kerja@corporate.com",
		subject: "You have been invited to join Web Dev",
		type: "Member Invitation",
		status: "Delivered",
		sentAt: "May 24, 2026, 06:14 PM",
		provider: "Postmark API",
		events: [
			{ time: "06:14:02 PM", event: "Enqueued for delivery" },
			{ time: "06:14:04 PM", event: "Delivered to target mailserver (250 OK)" },
		],
		variables: {
			orgName: "Web Dev",
			inviter: "Ma'ruf Muchlisin",
			role: "Developer",
			link: "https://fmikom.suntree.my.id/workos/organizations/invitation",
		},
		body: `
			<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
				<h2 style="color: #111827; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Organization Invitation</h2>
				<p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">You have been invited to join the organization <strong>Web Dev</strong> as a <strong>Developer</strong>.</p>
				<div style="text-align: center; margin-bottom: 24px;">
					<a href="https://fmikom.suntree.my.id/workos/organizations/invitation" style="background-color: #4f46e5; color: #ffffff; padding: 10px 20px; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-block;">Accept Invitation</a>
				</div>
				<p style="color: #4b5563; font-size: 12px; line-height: 1.5;">Invited by Ma'ruf Muchlisin (system@fmikom.org).</p>
			</div>
		`,
	},
	{
		id: "msg_01J8G5S2YBB",
		recipient: "hacker@evil.com",
		subject: "Password Reset Alert",
		type: "Password Alert",
		status: "Bounced",
		sentAt: "May 23, 2026, 11:45 PM",
		provider: "AWS SES Relay",
		events: [
			{ time: "11:45:00 PM", event: "Enqueued for delivery" },
			{ time: "11:45:01 PM", event: "Dispatched through AWS SES cluster" },
			{
				time: "11:45:03 PM",
				event: "Rejected by remote server (550 User Unknown)",
			},
			{ time: "11:45:04 PM", event: "Logged as Hard Bounce" },
		],
		variables: {
			name: "Hacker Evil",
			link: "https://fmikom.suntree.my.id/password/reset?token=abcde",
		},
		body: `
			<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
				<h2 style="color: #dc2626; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Password Reset Alert</h2>
				<p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">Hello Hacker,</p>
				<p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">We received a request to reset your password. Click the link below to verify:</p>
				<div style="text-align: center; margin-bottom: 24px;">
					<a href="#" style="background-color: #dc2626; color: #ffffff; padding: 10px 20px; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-block;">Reset Password</a>
				</div>
			</div>
		`,
	},
];

const getStoredLogs = () => {
	if (typeof window !== "undefined") {
		const stored = localStorage.getItem("workos_email_logs");
		if (stored !== null) {
			try {
				return JSON.parse(stored);
			} catch (e) {
				console.error(e);
			}
		}
	}
	return DEFAULT_LOGS;
};

const mockLogs = ref(getStoredLogs());

const saveLogs = () => {
	if (typeof window !== "undefined") {
		localStorage.setItem("workos_email_logs", JSON.stringify(mockLogs.value));
	}
};

watch(
	mockLogs,
	() => {
		saveLogs();
	},
	{ deep: true },
);

function clearLogs() {
	mockLogs.value = [];
}

function handleEmailSent(newLog: any) {
	mockLogs.value.unshift(newLog);
}
</script>

<template>
  <div style="font-family: var(--wos-font)" class="p-6 md:p-8 space-y-5 bg-white min-h-[calc(100vh-52px)]">
    <!-- Toolbar / Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-3 gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto wos-scroll -mb-3.5">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['h-[38px] px-3 pb-3 text-[13px] font-semibold border-b-2 transition-colors whitespace-nowrap', activeTab === tab.id ? 'border-indigo-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900']"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>
        <div class="text-[11.5px] text-gray-500 flex items-center gap-1.5 shrink-0 bg-gray-50 border border-gray-150 px-2.5 py-1 rounded-md">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
            SMTP status: {{ smtpConfig?.status || 'Active' }}
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="mt-4">
        <SMTP v-if="activeTab === 'smtp'" :smtp-config="smtpConfig" @email-sent="handleEmailSent" />
        <Templates v-else-if="activeTab === 'templates'" />
        <Logs v-else-if="activeTab === 'logs'" :logs="mockLogs" @clear-logs="clearLogs" />
    </div>
  </div>
</template>