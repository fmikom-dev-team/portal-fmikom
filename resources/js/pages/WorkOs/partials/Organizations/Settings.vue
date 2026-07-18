<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { reactive, ref, watch } from "vue";

const props = defineProps<{ organization?: any }>();

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const isSubmitting = ref(false);

const form = reactive({
	name: props.organization?.name || "",
	description: props.organization?.description || "",
});

const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(props.organization?.logo_path || null);
const fileInputRef = ref<HTMLInputElement | null>(null);

function handleLogoChange(e: Event) {
	const files = (e.target as HTMLInputElement).files;
	if (files && files.length > 0) {
		const file = files[0];
		logoFile.value = file;
		logoPreview.value = URL.createObjectURL(file);
	}
}

watch(
	() => props.organization,
	(newVal) => {
		if (newVal) {
			form.name = newVal.name || "";
			form.description = newVal.description || "";
			logoPreview.value = newVal.logo_path || null;
		}
	},
	{ deep: true },
);

function submitEdit() {
	if (!props.organization) return;
	isSubmitting.value = true;

	const data: any = {
		_method: "PATCH",
		name: form.name,
		description: form.description,
	};
	if (logoFile.value) {
		data.logo_file = logoFile.value;
	}

	router.post(
		`/workos/modules/${props.organization.id}`,
		data,
		{
			onSuccess: () => {
				showEditModal.value = false;
				logoFile.value = null;
			},
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

function submitDelete() {
	if (!props.organization) return;
	isSubmitting.value = true;
	router.delete(`/workos/modules/${props.organization.id}`, {
		onSuccess: () => {
			showDeleteModal.value = false;
		},
		onFinish: () => {
			isSubmitting.value = false;
		},
	});
}

// 1. Core Settings State & API Integration
const domains = ref<any[]>([]);
const itContacts = ref<any[]>([]);
const metadataPairs = ref<any[]>([]);

function initSettingsData() {
	const data = props.organization?.settings_data || {};
	
	domains.value = data.domains || [
		{
			id: 1,
			name: props.organization?.domain || "trace.unugha.ac.id",
			status: "Verified",
		},
	];
	
	itContacts.value = data.it_contacts || [
		{
			id: 1,
			name: "IT Admin",
			email: `it-admin@${props.organization?.domain || "example.com"}`,
		},
	];
	
	metadataPairs.value = data.metadata || [
		{ key: "environment", value: "production" },
		{ key: "tier", value: "enterprise" },
	];
}

watch(
	() => props.organization,
	() => {
		initSettingsData();
	},
	{ deep: true, immediate: true }
);

function saveSettingsPayload(payload: any, successMsg: string) {
	isSubmitting.value = true;
	router.post(
		`/workos/modules/${props.organization.id}/settings-data`,
		payload,
		{
			preserveScroll: true,
			onSuccess: () => {
				toast(successMsg, "success");
			},
			onError: () => toast("Gagal memperbarui pengaturan.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		}
	);
}

const showAddDomainModal = ref(false);
const newDomainName = ref("");
const showDeleteDomainModal = ref(false);
const domainToDelete = ref<any>(null);

function addDomain() {
	if (!newDomainName.value) return;
	const updated = [...domains.value, {
		id: Date.now(),
		name: newDomainName.value,
		status: "Pending verification",
	}];
	newDomainName.value = "";
	showAddDomainModal.value = false;
	saveSettingsPayload({ domains: updated }, "Domain baru berhasil didaftarkan.");
}

function verifyDomain(id: number) {
	const updated = domains.value.map((d) => {
		if (d.id === id) {
			return { ...d, status: "Verified" };
		}
		return d;
	});
	saveSettingsPayload({ domains: updated }, "Domain berhasil diverifikasi.");
}

function confirmDeleteDomain(domain: any) {
	domainToDelete.value = domain;
	showDeleteDomainModal.value = true;
}

function deleteDomain() {
	if (!domainToDelete.value) return;
	const updated = domains.value.filter((d) => d.id !== domainToDelete.value.id);
	domainToDelete.value = null;
	showDeleteDomainModal.value = false;
	saveSettingsPayload({ domains: updated }, "Domain berhasil dihapus.");
}

// 2. IT Contacts State
const showItContactsModal = ref(false);
const newContactName = ref("");
const newContactEmail = ref("");

function addItContact() {
	if (!newContactName.value || !newContactEmail.value) return;
	const updated = [...itContacts.value, {
		id: Date.now(),
		name: newContactName.value,
		email: newContactEmail.value,
	}];
	newContactName.value = "";
	newContactEmail.value = "";
	saveSettingsPayload({ it_contacts: updated }, "Kontak IT berhasil ditambahkan.");
}

function removeItContact(id: number) {
	const updated = itContacts.value.filter((c) => c.id !== id);
	saveSettingsPayload({ it_contacts: updated }, "Kontak IT berhasil dihapus.");
}

// 3. Metadata State
const showMetadataModal = ref(false);
const tempMetadataPairs = ref<{ key: string; value: string }[]>([]);

function openMetadataModal() {
	tempMetadataPairs.value = metadataPairs.value.map((p) => ({ ...p }));
	showMetadataModal.value = true;
}

function addMetadataRow() {
	tempMetadataPairs.value.push({ key: "", value: "" });
}

function removeMetadataRow(index: number) {
	tempMetadataPairs.value.splice(index, 1);
}

function saveMetadata() {
	const validPairs = tempMetadataPairs.value.filter(
		(p) => p.key.trim() !== "" && p.value.trim() !== "",
	);
	showMetadataModal.value = false;
	saveSettingsPayload({ metadata: validPairs }, "Metadata berhasil disimpan.");
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">

        <!-- Organization details -->
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-4">Organization details</h3>
            <div class="grid grid-cols-[140px_1fr] gap-y-3 text-[13px] mb-4">
                <span class="text-[#6b7280] dark:text-zinc-400">Name</span>
                <span class="text-[#111827] dark:text-zinc-100 font-medium">{{ organization?.name || '—' }}</span>
                <span class="text-[#6b7280] dark:text-zinc-400">External ID</span>
                <span class="text-[#9ca3af] dark:text-zinc-600">Not set</span>
            </div>
            <button
                class="h-[30px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[12px] font-medium text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
                @click="showEditModal = true"
            >
                Edit details
            </button>
        </div>

        <!-- Domains -->
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Domains</h3>
            <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mb-3 leading-relaxed">
                Users with verified domains can sign in through this organization's Single Sign-On connection without needing to verify their email.
                By default, they are managed by the organization's domain policy.
            </p>
            <button 
                @click="showAddDomainModal = true"
                class="h-[30px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[12px] font-medium text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors mb-4 bg-white dark:bg-zinc-900"
            >
                Add domain
            </button>
            <!-- Domain table -->
            <div class="rounded-xl overflow-hidden ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
                <table class="w-full text-left">
                    <caption class="sr-only">Domains</caption>
                    <thead>
                        <tr class="bg-[#f9fafb] dark:bg-zinc-800/60 border-b border-[#e5e7eb] dark:border-zinc-800">
                            <th class="px-4 py-2.5 text-[12px] font-semibold text-[#111827] dark:text-zinc-300 font-medium">Domain</th>
                            <th class="px-4 py-2.5 text-[12px] font-semibold text-[#111827] dark:text-zinc-300 font-medium">Status</th>
                            <th class="px-4 py-2.5 w-24 text-right"/>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!domains.length">
                            <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-500 dark:text-zinc-500 bg-white dark:bg-zinc-900">No domains registered.</td>
                        </tr>
                        <tr v-for="dom in domains" :key="dom.id" class="border-t border-[#e5e7eb] dark:border-zinc-800 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 last:border-b-0">
                            <td class="px-4 py-2.5 text-[13px] text-[#111827] dark:text-zinc-100 font-mono">{{ dom.name }}</td>
                            <td class="px-4 py-2.5">
                                <span 
                                    class="text-[12px] font-medium"
                                    :class="dom.status === 'Verified' ? 'text-[#10b981]' : 'text-amber-500'"
                                >
                                    {{ dom.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right flex items-center justify-end gap-2">
                                <button 
                                    v-if="dom.status === 'Pending verification'"
                                    @click="verifyDomain(dom.id)"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 bg-blue-50 dark:bg-blue-950/45 hover:bg-blue-100 rounded transition-colors"
                                    title="Verify domain now"
                                >
                                    Verify
                                </button>
                                <button 
                                    @click="confirmDeleteDomain(dom)"
                                    class="text-[#9ca3af] dark:text-zinc-500 hover:text-red-500 transition-colors p-1 rounded hover:bg-red-50 dark:hover:bg-red-950/30"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- IT contacts -->
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">IT contacts</h3>
            <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mb-3">IT contacts receive critical alerts and error notifications directly from WorkOS.</p>
            
            <div v-if="itContacts.length" class="mb-4 space-y-2">
                <div v-for="contact in itContacts" :key="contact.id" class="flex items-center justify-between text-xs bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-2.5 rounded-md">
                    <div>
                        <strong class="text-gray-800 dark:text-zinc-200 font-medium">{{ contact.name }}</strong> 
                        <span class="text-gray-500 dark:text-zinc-400 ml-1">({{ contact.email }})</span>
                    </div>
                </div>
            </div>

            <button 
                @click="showItContactsModal = true"
                class="h-[30px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[12px] font-medium text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
            >
                Manage IT contacts
            </button>
        </div>

        <!-- Metadata -->
        <div>
            <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-3">Metadata</h2>
            <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
                <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Custom metadata</h3>
                <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mb-3">
                    Store additional information about this organization as key-value pairs.
                </p>

                <!-- Metadata List -->
                <div v-if="metadataPairs.length" class="border border-[#e5e7eb] dark:border-zinc-800 rounded-md mb-4 overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <caption class="sr-only">Metadata</caption>
                        <thead>
                            <tr class="bg-[#f9fafb] dark:bg-zinc-800/60 border-b border-[#e5e7eb] dark:border-zinc-800 text-gray-500 dark:text-zinc-400 font-medium">
                                <th class="px-3 py-2">Key</th>
                                <th class="px-3 py-2">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e7eb] dark:divide-zinc-800">
                            <tr v-for="pair in metadataPairs" :key="pair.key" class="hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-2 font-mono text-blue-600 dark:text-blue-400 font-medium">{{ pair.key }}</td>
                                <td class="px-3 py-2 font-mono text-gray-800 dark:text-zinc-300">{{ pair.value }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button 
                    @click="openMetadataModal"
                    class="h-[30px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[12px] font-medium text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
                >
                    Edit metadata
                </button>
            </div>
        </div>



        <!-- Danger Zone -->
        <div>
            <h2 class="text-[15px] font-semibold text-[#ef4444] mb-3">Danger zone</h2>
            <div class="border border-[#fecaca] dark:border-red-950/60 rounded-lg bg-white dark:bg-zinc-900/40 p-5 shadow-sm dark:shadow-none">
                <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Delete organization</h3>
                <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mb-3">
                    Delete this organization and its memberships, connections, and Audit Logs.<br>
                    This action is permanent and cannot be undone.
                </p>
                <button
                    class="h-[30px] px-3 border border-[#fecaca] dark:border-red-900/60 rounded-md text-[12px] font-medium text-[#ef4444] dark:text-red-400 hover:bg-[#fef2f2] dark:hover:bg-red-950/30 transition-colors bg-white dark:bg-zinc-900/40"
                    @click="showDeleteModal = true"
                >
                    Delete organization
                </button>
            </div>
        </div>

        <!-- ── MODALS (Premium Backdrop) ── -->

        <!-- Add Domain Modal -->
        <div v-if="showAddDomainModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showAddDomainModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100 dark:border-zinc-800 dark:shadow-none">
                <h3 class="text-base font-semibold text-[#111827] dark:text-zinc-100 mb-2">Add Domain</h3>
                <p class="text-xs text-[#6b7280] dark:text-zinc-400 mb-4">Register a new domain name to associate with this organization.</p>
                <form @submit.prevent="addDomain" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Domain name</label>
                        <input 
                            v-model="newDomainName" 
                            type="text" 
                            required
                            placeholder="e.g. unugha.ac.id"
                            class="w-full h-9 px-3 text-sm border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-900"
                        />
                    </div>
                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <button type="button" class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-xs font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showAddDomainModal = false">Cancel</button>
                        <button type="submit" class="h-9 px-4 bg-[#2563eb] text-white rounded-md text-xs font-semibold hover:bg-[#1d4ed8] transition-colors">Register Domain</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Domain Confirm Modal -->
        <div v-if="showDeleteDomainModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDeleteDomainModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100 dark:border-zinc-800 dark:shadow-none">
                <h3 class="text-base font-semibold text-[#111827] dark:text-zinc-100 mb-2">Delete Domain</h3>
                <p class="text-xs text-[#6b7280] dark:text-zinc-400 mb-5">
                    Are you sure you want to delete <strong class="text-gray-800 dark:text-zinc-300">{{ domainToDelete?.name }}</strong>? Users from this domain will no longer be able to log in via SSO.
                </p>
                <div class="flex justify-end gap-2">
                    <button class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-xs font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showDeleteDomainModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700 transition-colors" @click="deleteDomain">Remove Domain</button>
                </div>
            </div>
        </div>

        <!-- Manage IT Contacts Modal -->
        <div v-if="showItContactsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showItContactsModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100 dark:border-zinc-800 dark:shadow-none">
                <h3 class="text-base font-semibold text-[#111827] dark:text-zinc-100 mb-2">Manage IT contacts</h3>
                <p class="text-xs text-[#6b7280] dark:text-zinc-400 mb-4">Add or remove IT administrators authorized to receive system alerts.</p>
                
                <!-- Existing Contacts List with Delete -->
                <div class="space-y-2 max-h-40 overflow-y-auto mb-5 border border-gray-150 dark:border-zinc-800 p-2.5 rounded-lg bg-gray-50 dark:bg-zinc-800/40">
                    <div v-if="!itContacts.length" class="text-center py-4 text-xs text-gray-500 dark:text-zinc-500">No IT contacts configured.</div>
                    <div v-for="contact in itContacts" :key="contact.id" class="flex items-center justify-between text-xs bg-white dark:bg-zinc-905 border border-gray-150 dark:border-zinc-800 p-2 rounded-md shadow-sm dark:shadow-none">
                        <div>
                            <span class="font-medium text-gray-800 dark:text-zinc-200">{{ contact.name }}</span> 
                            <span class="text-gray-400 dark:text-zinc-500 ml-1">({{ contact.email }})</span>
                        </div>
                        <button @click="removeItContact(contact.id)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 dark:hover:bg-red-950/30 rounded bg-transparent border-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Add Contact Form -->
                <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 space-y-3">
                    <h4 class="text-xs font-semibold text-[#111827] dark:text-zinc-100">Add new IT contact</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-[#6b7280] dark:text-zinc-400 mb-1">Name</label>
                            <input v-model="newContactName" type="text" placeholder="e.g. Andi" class="w-full h-8 px-2 text-xs border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-850 dark:text-zinc-100 bg-white dark:bg-zinc-900"/>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-[#6b7280] dark:text-zinc-400 mb-1">Email</label>
                            <input v-model="newContactEmail" type="email" placeholder="andi@org.com" class="w-full h-8 px-2 text-xs border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-850 dark:text-zinc-100 bg-white dark:bg-zinc-900"/>
                        </div>
                    </div>
                    <button 
                        @click="addItContact"
                        class="w-full h-8 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 rounded-md text-xs font-semibold transition-colors mt-2 border-0 cursor-pointer"
                    >
                        Add Contact
                    </button>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800">
                    <button class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-xs font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showItContactsModal = false">Close</button>
                </div>
            </div>
        </div>

        <!-- Edit Metadata Modal -->
        <div v-if="showMetadataModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showMetadataModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 border border-gray-100 dark:border-zinc-800 dark:shadow-none">
                <h3 class="text-base font-semibold text-[#111827] dark:text-zinc-100 mb-2">Edit custom metadata</h3>
                <p class="text-xs text-[#6b7280] dark:text-zinc-400 mb-4">Add metadata keys and values to store additional info about this organization.</p>
                
                <div class="space-y-2 max-h-60 overflow-y-auto mb-4 p-2 bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-800 rounded-lg">
                    <div v-if="!tempMetadataPairs.length" class="text-center py-8 text-xs text-gray-500 dark:text-zinc-500">No metadata rows. Click "Add row" below.</div>
                    <div v-for="(pair, idx) in tempMetadataPairs" :key="idx" class="flex items-center gap-2">
                        <input 
                            v-model="pair.key" 
                            type="text" 
                            placeholder="Key" 
                            class="flex-1 h-8 px-2.5 text-xs font-mono border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                        />
                        <span class="text-gray-400 dark:text-zinc-600">:</span>
                        <input 
                            v-model="pair.value" 
                            type="text" 
                            placeholder="Value" 
                            class="flex-1 h-8 px-2.5 text-xs font-mono border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                        />
                        <button @click="removeMetadataRow(idx)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 dark:hover:bg-red-950/30 rounded bg-transparent border-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
                    <button 
                        @click="addMetadataRow" 
                        class="h-8 px-3 border border-blue-200 dark:border-blue-900/40 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-md text-xs font-semibold transition-colors bg-white dark:bg-zinc-900 cursor-pointer"
                    >
                        Add row
                    </button>
                    <div class="flex gap-2">
                        <button class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-xs font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showMetadataModal = false">Cancel</button>
                        <button class="h-9 px-4 bg-[#2563eb] text-white rounded-md text-xs font-semibold hover:bg-[#1d4ed8] transition-colors" @click="saveMetadata">Save changes</button>
                    </div>
                </div>
            </div>
        </div>



        <!-- Edit Details Modal (Original) -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showEditModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border dark:border-zinc-800 dark:shadow-none">
                <h2 class="text-[16px] font-semibold text-[#111827] dark:text-zinc-100 mb-4">Edit details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#374151] dark:text-zinc-300 mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-900"/>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#374151] dark:text-zinc-300 mb-1">Description</label>
                        <input v-model="form.description" type="text" class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-900"/>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Module Logo / Image</label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 flex items-center justify-center text-xs font-semibold overflow-hidden shrink-0">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo Module" class="w-full h-full object-cover" />
                                <span v-else class="text-gray-400 dark:text-zinc-500">No logo</span>
                            </div>
                            <input type="file" ref="fileInputRef" accept="image/*" class="hidden" @change="handleLogoChange" />
                            <button type="button" @click="fileInputRef?.click()" class="h-8 px-3 border border-gray-300 dark:border-zinc-700 rounded-md text-xs font-semibold hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 cursor-pointer">Choose Image</button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showEditModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#2563eb] text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] transition-colors disabled:opacity-50" :disabled="isSubmitting" @click="submitEdit">Save changes</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Modal (Original) -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDeleteModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border dark:border-zinc-800 dark:shadow-none">
                <h2 class="text-[16px] font-semibold text-[#111827] dark:text-zinc-100 mb-2">Delete organization</h2>
                <p class="text-[13px] text-[#6b7280] dark:text-zinc-400 mb-5">
                    Are you sure you want to delete <strong class="text-[#111827] dark:text-zinc-200">{{ organization?.name }}</strong>?
                    This will remove all users, roles, and data permanently.
                </p>
                <div class="flex justify-end gap-2">
                    <button class="h-9 px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showDeleteModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#ef4444] text-white rounded-md text-[13px] font-semibold hover:bg-[#dc2626] transition-colors disabled:opacity-50" :disabled="isSubmitting" @click="submitDelete">Delete organization</button>
                </div>
            </div>
        </div>
    </div>
</template>