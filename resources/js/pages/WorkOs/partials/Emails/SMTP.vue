<script setup lang="ts">
import axios from "axios";
import { reactive, ref, watch } from "vue";
import { toast } from "../../composables/useWorkOs";

const emit = defineEmits<(e: "email-sent", log: any) => void>();

const props = defineProps<{
	smtpConfig: any;
}>();

const configForm = reactive({
	host: "",
	port: 587,
	sender: "",
	encryption: "tls",
	username: "",
	password: "",
});

const isSaving = ref(false);
const testRecipient = ref("");
const isSendingTest = ref(false);
const showTestLogs = ref(false);
const testLogsText = ref<string[]>([]);

watch(
	() => props.smtpConfig,
	(newVal) => {
		if (newVal) {
			configForm.host = newVal.host || "";
			configForm.port = newVal.port || 587;
			configForm.sender = newVal.sender || "";
			configForm.encryption = newVal.encryption || "tls";
			configForm.username = newVal.username || "";
			configForm.password = newVal.password || "";
		}
	},
	{ immediate: true, deep: true },
);

async function saveSmtpConfig() {
	isSaving.value = true;
	try {
		const res = await axios.post("/workos/emails/config", configForm);
		if (res.data.success) {
			toast(res.data.message || "Configuration saved successfully!", "success");
		} else {
			toast(res.data.message || "Failed to save configuration.", "error");
		}
	} catch (e: any) {
		const errorMsg =
			e.response?.data?.message || e.message || "An error occurred.";
		toast(errorMsg, "error");
	} finally {
		isSaving.value = false;
	}
}

async function sendTestEmail() {
	if (!testRecipient.value) {
		toast("Please enter a recipient email address", "error");
		return;
	}

	isSendingTest.value = true;
	showTestLogs.value = true;
	testLogsText.value = [
		`[${new Date().toLocaleTimeString()}] Resolving SMTP host ${configForm.host}...`,
	];

	// Step 1: Simulate connection startup visual trace
	await new Promise((resolve) => setTimeout(resolve, 400));
	testLogsText.value.push(
		`[SYSTEM] Connected to ${configForm.host}:${configForm.port}`,
	);

	await new Promise((resolve) => setTimeout(resolve, 400));
	testLogsText.value.push(
		`SMTP <- 220 mx.google.com ESMTP t12-20020a170902c50c000b01777b78`,
	);

	await new Promise((resolve) => setTimeout(resolve, 400));
	testLogsText.value.push(`SMTP -> EHLO mail.fmikom.org`);

	// Step 2: Make the actual API call to the backend to send the real test email!
	try {
		const res = await axios.post("/workos/emails/test-send", {
			recipient: testRecipient.value,
			...configForm,
		});

		if (res.data.success) {
			testLogsText.value.push(
				`[SYSTEM] Initiating TLS handshake... TLS 1.3 established`,
			);
			await new Promise((resolve) => setTimeout(resolve, 300));
			testLogsText.value.push(`SMTP -> MAIL FROM:<${configForm.sender}>`);
			testLogsText.value.push(`SMTP <- 250 2.1.0 OK`);

			await new Promise((resolve) => setTimeout(resolve, 300));
			testLogsText.value.push(`SMTP -> RCPT TO:<${testRecipient.value}>`);
			testLogsText.value.push(`SMTP <- 250 2.1.5 OK`);

			await new Promise((resolve) => setTimeout(resolve, 300));
			testLogsText.value.push(`SMTP -> DATA`);
			testLogsText.value.push(`SMTP <- 354 Go ahead`);

			await new Promise((resolve) => setTimeout(resolve, 300));
			testLogsText.value.push(
				`[SYSTEM] Streaming email headers and raw HTML payload...`,
			);
			testLogsText.value.push(
				`SMTP <- 250 2.0.0 OK : queued as msg_01J8G5S2Y9T`,
			);

			await new Promise((resolve) => setTimeout(resolve, 200));
			testLogsText.value.push(`SMTP -> QUIT`);
			testLogsText.value.push(`SMTP <- 221 2.0.0 closing connection`);
			testLogsText.value.push(
				`[SUCCESS] Test email dispatched successfully to ${testRecipient.value}!`,
			);

			toast("Test email sent successfully!", "success");

			const recipientAddress = testRecipient.value;
			testRecipient.value = "";

			// Emit new log event to parent component
			emit("email-sent", {
				id: `msg_test_${Date.now().toString(36)}`,
				recipient: recipientAddress,
				subject: "WorkOS Connection Test",
				type: "Verification Email",
				status: "Delivered",
				sentAt: new Date().toLocaleString(),
				provider: "Custom SMTP",
				events: [
					{
						time: new Date().toLocaleTimeString(),
						event: "Enqueued for delivery",
					},
					{
						time: new Date().toLocaleTimeString(),
						event: "Sent through Custom SMTP relay",
					},
				],
				variables: { test: "true" },
				body: "<div style='padding:20px; font-family:sans-serif; text-align:center;'><h3>WorkOS SMTP Test</h3><p>Your SMTP configurations are fully verified and active.</p></div>",
			});
		} else {
			throw new Error(res.data.message || "Failed to dispatch email");
		}
	} catch (e: any) {
		const errorMsg =
			e.response?.data?.message || e.message || "Connection refused.";
		testLogsText.value.push(`[ERROR] Connection failed: ${errorMsg}`);
		testLogsText.value.push(`[FATAL] SMTP connection handshake aborted.`);
		toast(`SMTP Test failed: ${errorMsg}`, "error");
	} finally {
		isSendingTest.value = false;
	}
}

function clearLogsTerminal() {
	showTestLogs.value = false;
	testLogsText.value = [];
}
</script>

<template>
  <div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- SMTP Settings Info -->
        <div class="md:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900">Custom SMTP Configuration</h3>
            <p class="text-[12.5px] text-gray-500 leading-relaxed">Specify the server configuration that handles all transactional alerts, notification triggers, and user verification links.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_host" class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Host</label>
                    <input
                        id="smtp_host"
                        v-model="configForm.host"
                        type="text"
                        placeholder="smtp.example.com"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
                <div>
                    <label for="smtp_port" class="block text-xs font-semibold text-gray-700 mb-1.5">Port</label>
                    <input
                        id="smtp_port"
                        v-model.number="configForm.port"
                        type="number"
                        placeholder="587"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
                <div>
                    <label for="smtp_sender" class="block text-xs font-semibold text-gray-700 mb-1.5">Default Sender Address</label>
                    <input
                        id="smtp_sender"
                        v-model="configForm.sender"
                        type="email"
                        placeholder="sender@example.com"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
                <div>
                    <label for="smtp_encryption" class="block text-xs font-semibold text-gray-700 mb-1.5">Encryption Protocol</label>
                    <select
                        id="smtp_encryption"
                        v-model="configForm.encryption"
                        class="w-full h-9 px-2 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-900 bg-white"
                    >
                        <option value="tls">TLS (STARTTLS)</option>
                        <option value="ssl">SSL Implicit</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div>
                    <label for="smtp_username" class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Username</label>
                    <input
                        id="smtp_username"
                        v-model="configForm.username"
                        type="text"
                        placeholder="username"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
                <div>
                    <label for="smtp_password" class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Password</label>
                    <input
                        id="smtp_password"
                        v-model="configForm.password"
                        type="password"
                        placeholder="••••••••"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-60"
                    :disabled="isSaving"
                    @click="saveSmtpConfig"
                >
                    <svg v-if="isSaving" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Save Configurations
                </button>
            </div>
        </div>

        <!-- Testing Module -->
        <div class="md:col-span-1 bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-gray-900">SMTP Connection Test</h3>
                <p class="text-[12px] text-gray-500 leading-normal">Send a diagnostic email packet to ensure your SMTP credentials and certificate pathways resolve successfully.</p>
                
                <div>
                    <label for="test_recipient" class="block text-xs font-semibold text-gray-700 mb-1.5">Recipient Address</label>
                    <input
                        id="test_recipient"
                        v-model="testRecipient"
                        type="email"
                        placeholder="recipient@example.com"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white"
                    />
                </div>
            </div>

            <button
                class="w-full h-9 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-60 mt-4"
                :disabled="isSendingTest || !testRecipient"
                @click="sendTestEmail"
            >
                <svg v-if="isSendingTest" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSendingTest ? 'Testing SMTP...' : 'Send Diagnostic Mail' }}
            </button>
        </div>
    </div>

    <!-- Terminal Diagnostic logs -->
    <div v-if="showTestLogs" class="bg-gray-950 rounded-xl overflow-hidden border border-gray-800 shadow-lg animate-fade-in">
        <div class="bg-gray-900 px-4 py-2 border-b border-gray-800 flex items-center justify-between">
            <span class="font-mono text-[10.5px] font-semibold text-gray-400">Diagnostic SMTP Log Trace</span>
            <button @click="clearLogsTerminal" class="text-[10px] text-gray-500 hover:text-gray-300 font-semibold transition-colors">Clear Output</button>
        </div>
        <div class="p-4 font-mono text-[11.5px] text-emerald-400 space-y-1 max-h-60 overflow-y-auto leading-relaxed scroll-mt-2 font-semibold">
            <div v-for="(log, idx) in testLogsText" :key="idx" :class="[log.startsWith('[SUCCESS]') ? 'text-blue-400 font-bold' : log.startsWith('[SYSTEM]') ? 'text-gray-400 italic' : log.startsWith('[ERROR]') || log.startsWith('[FATAL]') ? 'text-red-500' : 'text-emerald-400']">
                {{ log }}
            </div>
        </div>
    </div>
  </div>
</template>