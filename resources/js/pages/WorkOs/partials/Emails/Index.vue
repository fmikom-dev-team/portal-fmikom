<script setup lang="ts">
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import Logs from "./Logs.vue";
import SMTP from "./SMTP.vue";
import Templates from "./Templates.vue";

const props = defineProps<{
	smtpConfig: any;
	emailLogs: any[];
}>();

const activeTab = ref("smtp");

const tabs = [
	{ id: "smtp", label: "SMTP & Providers" },
	{ id: "templates", label: "Templates" },
	{ id: "logs", label: "Logs" },
];

const mockLogs = ref<any[]>(props.emailLogs || []);

watch(
	() => props.emailLogs,
	(newVal) => {
		mockLogs.value = newVal || [];
	},
	{ immediate: true }
);

function clearLogs() {
	router.post("/workos/emails/logs/clear", {}, {
		preserveState: true,
		preserveScroll: true,
		onSuccess: () => {
			mockLogs.value = [];
		}
	});
}

function handleEmailSent() {
	router.reload({ only: ['emailLogs'] });
}
</script>

<template>
  <div style="font-family: var(--wos-font)" class="p-6 md:p-8 space-y-5 bg-white dark:bg-zinc-900 min-h-[calc(100vh-52px)]">
    <!-- Toolbar / Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 dark:border-zinc-700 pb-3 gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto wos-scroll -mb-3.5">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['h-[38px] px-3 pb-3 text-[13px] font-semibold border-b-2 transition-colors whitespace-nowrap', activeTab === tab.id ? 'border-blue-600 text-gray-900 dark:text-zinc-100' : 'border-transparent text-gray-500 dark:text-zinc-400 hover:text-gray-900']"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>
        <div class="text-[11.5px] text-gray-500 dark:text-zinc-400 flex items-center gap-1.5 shrink-0 bg-gray-50 dark:bg-zinc-900 border border-gray-150 px-2.5 py-1 rounded-md">
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