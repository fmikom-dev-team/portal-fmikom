<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";

const selectedTemplate = ref("verification");
const previewMode = ref("preview"); // 'preview' | 'code'
const showFullscreenModal = ref(false);

const templates = {
	verification: {
		name: "Verification Email",
		description: "Sent to verify user email using OTP or custom code block.",
		subject: "Verify your email address - Portal FMIKOM",
		vars: ["name", "otp_code", "link", "expiry"],
		code: `<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #111827; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Verify your email address</h2>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">Hello {{ name }},</p>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">To complete your login process, please use the following One-Time Password (OTP) verification code:</p>
    <div style="background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; padding: 16px; text-align: center; margin-bottom: 24px;">
        <span style="font-family: monospace; font-size: 24px; font-weight: 700; letter-spacing: 4px; color: #1d4ed8;">{{ otp_code }}</span>
    </div>
    <p style="color: #4b5563; font-size: 12px; line-height: 1.5;">This verification code is only valid for {{ expiry }}. If you did not request this email, you can safely ignore it.</p>
</div>`,
	},
	invitation: {
		name: "Member Invitation",
		description:
			"Sent to external developers or administrators invited to organizations.",
		subject: "You have been invited to join {{ orgName }}",
		vars: ["orgName", "inviter", "role", "link"],
		code: `<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #111827; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Organization Invitation</h2>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">You have been invited to join the organization <strong>{{ orgName }}</strong> as a <strong>{{ role }}</strong>.</p>
    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ link }}" style="background-color: #1d4ed8; color: #ffffff; padding: 10px 20px; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-block;">Accept Invitation</a>
    </div>
    <p style="color: #4b5563; font-size: 12px; line-height: 1.5;">Invited by {{ inviter }}.</p>
</div>`,
	},
	reset: {
		name: "Password Reset Alert",
		description: "Sent to notify users of password recovery request triggers.",
		subject: "Password Reset Request - Portal FMIKOM",
		vars: ["name", "link"],
		code: `<div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: auto; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #dc2626; font-size: 20px; font-weight: 600; margin-bottom: 16px;">Password Reset Alert</h2>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">Hello {{ name }},</p>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">We received a request to reset your password. Click the link below to verify:</p>
    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ link }}" style="background-color: #dc2626; color: #ffffff; padding: 10px 20px; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-block;">Reset Password</a>
    </div>
</div>`,
	},
};

const variables = reactive<Record<string, string>>({
	name: "Ma'ruf Muchlisin",
	otp_code: "482093",
	link: "https://fmikom.suntree.my.id/verify-otp?code=482093",
	expiry: "10 minutes",
	orgName: "Web Dev",
	inviter: "Ma'ruf Muchlisin",
	role: "Developer",
});

const renderedCode = computed(() => {
	const template = (templates as any)[selectedTemplate.value];
	if (!template) return "";
	let html = template.code;
	template.vars.forEach((v: string) => {
		const val = variables[v] || `{{ ${v} }}`;
		html = html.replaceAll(`{{ ${v} }}`, val);
		html = html.replaceAll(`{{${v}}}`, val);
	});
	return html;
});

const renderedSubject = computed(() => {
	const template = (templates as any)[selectedTemplate.value];
	if (!template) return "";
	let subject = template.subject;
	template.vars.forEach((v: string) => {
		const val = variables[v] || `{{ ${v} }}`;
		subject = subject.replaceAll(`{{ ${v} }}`, val);
		subject = subject.replaceAll(`{{${v}}}`, val);
	});
	return subject;
});
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Sidebar Templates List -->
    <div class="lg:col-span-1 space-y-2">
        <button
            v-for="(t, key) in templates"
            :key="key"
            :class="['w-full text-left p-4 rounded-xl border transition-all flex flex-col gap-1', selectedTemplate === key ? 'bg-blue-50/50 border-blue-200 ring-1 ring-blue-500/20' : 'bg-white border-gray-200 hover:bg-gray-50']"
            @click="selectedTemplate = key"
        >
            <span class="text-[13px] font-bold text-gray-900">{{ t.name }}</span>
            <span class="text-[11.5px] text-gray-500 leading-normal">{{ t.description }}</span>
        </button>
    </div>

    <!-- Template Editor/Viewer -->
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-5">
        <div class="border-b border-gray-100 pb-3 flex flex-col gap-1.5">
            <h3 class="text-sm font-bold text-gray-900">{{ (templates as any)[selectedTemplate].name }}</h3>
            <p class="text-[12px] text-gray-500">
                Email Subject: 
                <span class="font-mono bg-gray-50 border border-gray-150 px-2 py-0.5 rounded text-[11px] text-gray-700 font-semibold select-all">
                    {{ renderedSubject }}
                </span>
            </p>
        </div>

        <!-- Variable Editor Panel -->
        <div class="bg-slate-50/50 border border-slate-150 rounded-xl p-4 space-y-3">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Live Variables Payload Editor</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="v in (templates as any)[selectedTemplate].vars" :key="v" class="space-y-1">
                    <label :for="'var_' + v" class="block text-[11px] font-bold text-blue-600 font-mono">{{ v }}</label>
                    <input
                        :id="'var_' + v"
                        v-model="variables[v]"
                        type="text"
                        class="w-full h-8 px-2.5 text-[12px] border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
            </div>
        </div>

        <!-- Rendered Code vs Live Preview Tabs -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-1">
                <div class="flex items-center gap-4">
                    <button
                        :class="['pb-2 text-[12px] font-semibold border-b-2 transition-colors', previewMode === 'preview' ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900']"
                        @click="previewMode = 'preview'"
                    >
                        Live Rendered Preview
                    </button>
                    <button
                        :class="['pb-2 text-[12px] font-semibold border-b-2 transition-colors', previewMode === 'code' ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900']"
                        @click="previewMode = 'code'"
                    >
                        HTML Code Source
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div v-if="previewMode === 'code'" class="space-y-1.5 animate-fade-in">
                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-[11.5px] overflow-x-auto max-h-[350px] border border-gray-800 leading-relaxed select-all">{{ (templates as any)[selectedTemplate].code }}</pre>
            </div>

            <div v-else class="space-y-2 animate-fade-in">
                <!-- Mock Browser Shell -->
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">
                    <div class="bg-gray-50 border-b border-gray-150 px-4 py-2.5 flex items-center justify-between gap-3 select-none">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                        </div>
                        <div class="flex-1 max-w-md bg-gray-200/60 border border-gray-300/40 rounded px-3 py-0.5 text-[10.5px] text-gray-500 text-center font-mono truncate select-all">
                            http://fmikom.org/emails/preview/{{ selectedTemplate }}
                        </div>
                        <button
                            id="fullscreen_preview_button"
                            class="flex items-center gap-1.5 h-[26px] px-2 text-[11px] font-semibold text-gray-600 bg-white border border-gray-200 rounded hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm cursor-pointer shrink-0"
                            @click="showFullscreenModal = true"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 20v-4m0 4h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            Fullscreen
                        </button>
                    </div>
                    <div class="p-6 bg-slate-100 max-h-[400px] overflow-y-auto wos-scroll flex justify-center">
                        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" v-html="renderedCode"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FULLSCREEN PREVIEW MODAL -->
    <AppModal
        :show="showFullscreenModal"
        title="Email Template Fullscreen Preview"
        size="6xl"
        @close="showFullscreenModal = false"
    >
        <template #description>
            Full viewport preview of the live rendered email markup with active variables payload.
        </template>

        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">
            <div class="bg-gray-50 border-b border-gray-150 px-4 py-2.5 flex items-center justify-between gap-3 select-none">
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                </div>
                <div class="flex-1 max-w-lg bg-gray-200/60 border border-gray-300/40 rounded px-3 py-0.5 text-[11px] text-gray-500 text-center font-mono truncate select-all">
                    http://fmikom.org/emails/preview/{{ selectedTemplate }}?fullscreen=true
                </div>
                <div class="w-16"></div>
            </div>
            <div class="p-8 bg-slate-100 min-h-[500px] max-h-[calc(100vh-250px)] overflow-y-auto wos-scroll flex justify-center">
                <div class="w-full max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" v-html="renderedCode"></div>
            </div>
        </div>

        <template #footer>
            <button
                class="h-[34px] px-5 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors bg-white shadow-sm"
                @click="showFullscreenModal = false"
            >
                Close Preview
            </button>
        </template>
    </AppModal>
  </div>
</template>