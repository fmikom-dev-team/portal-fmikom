<script setup lang="ts">
import { computed, ref } from "vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{
	notifications: any[];
}>();

const emit = defineEmits<{
	(e: "mark-all-read"): void;
	(e: "clear-feed"): void;
	(e: "toggle-read", notif: any): void;
}>();

const activeTab = ref("feed");

const tabs = [
	{ id: "feed", label: "Activity Feed" },
	{ id: "webhooks", label: "Webhook Config" },
];

// ─────────────────────────────────────────────────────────────────────────────
// Activity Feed State & Actions
// ─────────────────────────────────────────────────────────────────────────────
const severityFilter = ref("all");

const filteredNotifications = computed(() => {
	return props.notifications.filter((n) => {
		if (severityFilter.value === "all") return true;
		return n.severity === severityFilter.value;
	});
});

const unreadCount = computed(() => {
	return props.notifications.filter((n) => !n.read).length;
});

function markAllAsRead() {
	emit("mark-all-read");
	toast("All notifications marked as read", "success");
}

function clearAllFeed() {
	emit("clear-feed");
	toast("Activity feed cleared", "success");
}

function toggleRead(notif: any) {
	emit("toggle-read", notif);
}

// ─────────────────────────────────────────────────────────────────────────────
// Webhook Config State & Actions
// ─────────────────────────────────────────────────────────────────────────────
const webhookUrl = ref("https://api.fmikom.org/webhooks");
const isEditingWebhook = ref(false);
const webhookSecret = ref("whsec_a58f6089c6c793f9f8b63b3c19e8c531");
const showSecret = ref(false);
const isSavingWebhook = ref(false);

const subscribedEvents = ref<Record<string, boolean>>({
	"user.created": true,
	"user.deleted": false,
	"sso.connected": true,
	"radar.threat_blocked": true,
	"organization.created": false,
});

const mockDeliveries = ref([
	{
		id: "whd_01J8G5S2Y9T",
		event: "radar.threat_blocked",
		url: "https://api.fmikom.org/webhooks",
		status: 200,
		statusText: "OK",
		latency: "142ms",
		time: "10 mins ago",
		isRedelivering: false,
	},
	{
		id: "whd_01J8G5S2YAA",
		event: "sso.connected",
		url: "https://api.fmikom.org/webhooks",
		status: 500,
		statusText: "Internal Error",
		latency: "450ms",
		time: "1 hour ago",
		isRedelivering: false,
	},
]);

function copySecret() {
	navigator.clipboard.writeText(webhookSecret.value);
	toast("Webhook secret copied to clipboard", "success");
}

function saveWebhookConfig() {
	isSavingWebhook.value = true;
	setTimeout(() => {
		isSavingWebhook.value = false;
		isEditingWebhook.value = false;
		toast("Webhook configurations saved successfully", "success");
	}, 800);
}

function triggerRedeliver(delivery: any) {
	delivery.isRedelivering = true;
	toast(`Redelivering event ${delivery.event}...`, "info");

	setTimeout(() => {
		delivery.isRedelivering = false;
		delivery.status = 200;
		delivery.statusText = "OK";
		delivery.latency = "120ms";
		delivery.time = "Just now";
		toast(
			`Event ${delivery.event} redelivered successfully (200 OK)!`,
			"success",
		);
	}, 1000);
}
</script>

<template>
  <div style="font-family: var(--wos-font)" class="p-6 md:p-8 space-y-5">
    <!-- Toolbar / Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-3 gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto wos-scroll -mb-3.5">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['h-[38px] px-3 pb-3 text-[13px] font-semibold border-b-2 transition-colors whitespace-nowrap', activeTab === tab.id ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900']"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
                <span v-if="tab.id === 'feed' && unreadCount > 0" class="ml-1 px-1.5 py-0.2 text-[10px] font-bold text-white bg-blue-600 rounded-full">
                    {{ unreadCount }}
                </span>
            </button>
        </div>
    </div>

    <!-- FEED TAB -->
    <div v-if="activeTab === 'feed'" class="space-y-4">
        <!-- Controls -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <select
                    v-model="severityFilter"
                    class="h-[32px] px-2.5 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] bg-white text-gray-700"
                >
                    <option value="all">All Alerts</option>
                    <option value="error">Errors</option>
                    <option value="warning">Warnings</option>
                    <option value="success">Success</option>
                    <option value="info">Info</option>
                </select>
            </div>
            <div class="flex items-center gap-2" v-if="props.notifications.length > 0">
                <button
                    class="h-[32px] px-3 border border-gray-200 rounded-md text-[12.5px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors shadow-sm"
                    @click="markAllAsRead"
                >
                    Mark all read
                </button>
                <button
                    class="h-[32px] px-3 border border-red-200 rounded-md text-[12.5px] font-semibold text-red-600 bg-white hover:bg-red-50 transition-colors shadow-sm"
                    @click="clearAllFeed"
                >
                    Clear log
                </button>
            </div>
        </div>

        <!-- Feed List -->
        <div class="space-y-2.5">
            <div v-if="filteredNotifications.length === 0" class="bg-white border border-gray-200 rounded-xl p-12 text-center text-gray-500 shadow-sm">
                No activity notifications found.
            </div>
            <div
                v-for="notif in filteredNotifications"
                :key="notif.id"
                :class="['p-4 rounded-xl border transition-all flex items-start gap-3 bg-white border-gray-200', !notif.read ? 'ring-1 ring-blue-500/10 bg-blue-50/5 border-blue-100 shadow-sm' : '']"
            >
                <!-- Severity Dot -->
                <span
                    :class="[
                        'w-2.5 h-2.5 rounded-full shrink-0 mt-1.5',
                        notif.severity === 'error' ? 'bg-red-500' :
                        notif.severity === 'warning' ? 'bg-yellow-500' :
                        notif.severity === 'success' ? 'bg-emerald-500' : 'bg-blue-500'
                    ]"
                />
                
                <!-- Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[13px] font-bold text-gray-900 leading-tight">{{ notif.title }}</span>
                        <span class="text-[11px] text-gray-400 font-medium shrink-0">{{ notif.time }}</span>
                    </div>
                    <p class="text-[12.5px] text-gray-600 mt-1 leading-normal">{{ notif.description }}</p>
                </div>

                <!-- Read Toggle Button -->
                <button
                    @click="toggleRead(notif)"
                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 transition-colors shrink-0 px-2 py-0.5 hover:bg-blue-50/60 rounded"
                >
                    {{ notif.read ? 'Mark unread' : 'Mark read' }}
                </button>
            </div>
        </div>
    </div>

    <!-- WEBHOOK CONFIG TAB -->
    <div v-if="activeTab === 'webhooks'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Settings Form -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 font-semibold">Endpoint Configuration</h3>
                <button
                    v-if="!isEditingWebhook"
                    class="h-[28px] px-2.5 border border-gray-200 rounded-md text-[11.5px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors bg-white"
                    @click="isEditingWebhook = true"
                >
                    Edit details
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="webhook_url_input" class="block text-xs font-semibold text-gray-700 mb-1.5">Destination URL</label>
                    <input
                        id="webhook_url_input"
                        v-model="webhookUrl"
                        type="url"
                        :disabled="!isEditingWebhook"
                        placeholder="https://yourserver.com/webhook-endpoint"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-900 bg-white disabled:bg-gray-50 disabled:text-gray-500"
                    />
                </div>

                <!-- Webhook Secret key -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Signing Secret Key</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                :type="showSecret ? 'text' : 'password'"
                                :value="webhookSecret"
                                readonly
                                class="w-full h-9 pl-3 pr-10 text-sm border border-gray-200 rounded-md focus:outline-none bg-gray-50 text-gray-600 font-mono"
                            />
                            <button
                                @click="showSecret = !showSecret"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                type="button"
                            >
                                <svg v-if="showSecret" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <button
                            @click="copySecret"
                            class="h-9 px-3.5 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors bg-white"
                            type="button"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                <!-- Event checklist selection -->
                <div class="border-t border-gray-100 pt-4">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-3">Event Subscriptions</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label v-for="(val, key) in subscribedEvents" :key="key" class="flex items-center gap-2.5 cursor-pointer py-1.5 hover:bg-gray-50 px-2 rounded-md transition-colors border border-transparent hover:border-gray-100">
                            <input
                                v-model="subscribedEvents[key]"
                                type="checkbox"
                                :disabled="!isEditingWebhook"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-[#2563eb]"
                            />
                            <span class="font-mono text-[11.5px] text-gray-700">{{ key }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Save buttons -->
            <div v-if="isEditingWebhook" class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors bg-white"
                    @click="isEditingWebhook = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center gap-1.5"
                    :disabled="isSavingWebhook"
                    @click="saveWebhookConfig"
                >
                    <svg v-if="isSavingWebhook" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Save configuration
                </button>
            </div>
        </div>

        <!-- Deliveries history -->
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900 font-semibold">Recent Webhooks</h3>
            <p class="text-[12px] text-gray-500 leading-normal">Logs of recently delivered payloads to your configured endpoint server.</p>

            <div class="space-y-3 pt-2">
                <div v-for="d in mockDeliveries" :key="d.id" class="border border-gray-150 p-3 rounded-lg flex flex-col gap-2 bg-slate-50/30">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10.5px] bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded text-gray-700 truncate max-w-[140px]">{{ d.event }}</span>
                        <span :class="['text-[11.5px] font-bold', d.status === 200 ? 'text-emerald-600' : 'text-red-500']">
                            {{ d.status }} {{ d.statusText }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-400 font-medium">
                        <span>Latency: {{ d.latency }}</span>
                        <span>{{ d.time }}</span>
                    </div>

                    <!-- Redeliver trigger -->
                    <button
                        class="w-full h-7 bg-white hover:bg-gray-50 border border-gray-200 rounded text-[11.5px] font-semibold text-gray-700 transition-colors flex items-center justify-center gap-1.5 shadow-sm mt-1"
                        :disabled="d.isRedelivering"
                        @click="triggerRedeliver(d)"
                    >
                        <svg v-if="d.isRedelivering" class="animate-spin h-3 w-3 text-gray-700" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ d.isRedelivering ? 'Redelivering...' : 'Redeliver Event' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>
