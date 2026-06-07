<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const props = defineProps<{ organization?: any }>();

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const isSubmitting = ref(false);

const form = reactive({
	name: props.organization?.name || "",
	description: props.organization?.description || "",
});

function submitEdit() {
	if (!props.organization) return;
	isSubmitting.value = true;
	router.patch(
		`/workos/modules/${props.organization.id}`,
		{ name: form.name, description: form.description },
		{
			onSuccess: () => {
				showEditModal.value = false;
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

// 1. Domains State & Modals
const domains = ref([
	{
		id: 1,
		name: props.organization?.domain || "trace.unugha.ac.id",
		status: "Verified",
	},
]);
const showAddDomainModal = ref(false);
const newDomainName = ref("");
const showDeleteDomainModal = ref(false);
const domainToDelete = ref<any>(null);

function addDomain() {
	if (!newDomainName.value) return;
	domains.value.push({
		id: Date.now(),
		name: newDomainName.value,
		status: "Pending verification",
	});
	newDomainName.value = "";
	showAddDomainModal.value = false;
}

function verifyDomain(id: number) {
	const domain = domains.value.find((d) => d.id === id);
	if (domain) {
		domain.status = "Verified";
	}
}

function confirmDeleteDomain(domain: any) {
	domainToDelete.value = domain;
	showDeleteDomainModal.value = true;
}

function deleteDomain() {
	if (!domainToDelete.value) return;
	domains.value = domains.value.filter((d) => d.id !== domainToDelete.value.id);
	domainToDelete.value = null;
	showDeleteDomainModal.value = false;
}

// 2. IT Contacts State
const itContacts = ref([
	{
		id: 1,
		name: "IT Admin",
		email: `it-admin@${props.organization?.domain || "example.com"}`,
	},
]);
const showItContactsModal = ref(false);
const newContactName = ref("");
const newContactEmail = ref("");

function addItContact() {
	if (!newContactName.value || !newContactEmail.value) return;
	itContacts.value.push({
		id: Date.now(),
		name: newContactName.value,
		email: newContactEmail.value,
	});
	newContactName.value = "";
	newContactEmail.value = "";
}

function removeItContact(id: number) {
	itContacts.value = itContacts.value.filter((c) => c.id !== id);
}

// 3. Metadata State
const metadataPairs = ref<{ key: string; value: string }[]>([
	{ key: "environment", value: "production" },
	{ key: "tier", value: "enterprise" },
]);
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
	metadataPairs.value = tempMetadataPairs.value.filter(
		(p) => p.key.trim() !== "" && p.value.trim() !== "",
	);
	showMetadataModal.value = false;
}

// 4. Role Assignment State
const roleMappings = ref([
	{ id: 1, group: "Admins", role: "Admin" },
	{ id: 2, group: "Engineers", role: "Member" },
]);
const showRoleAssignmentModal = ref(false);
const tempMappings = ref<{ id: number; group: string; role: string }[]>([]);

function openRoleAssignmentModal() {
	tempMappings.value = roleMappings.value.map((m) => ({ ...m }));
	showRoleAssignmentModal.value = true;
}

function addMappingRow() {
	tempMappings.value.push({ id: Date.now(), group: "", role: "Member" });
}

function removeMappingRow(index: number) {
	tempMappings.value.splice(index, 1);
}

function saveRoleAssignment() {
	roleMappings.value = tempMappings.value.filter((m) => m.group.trim() !== "");
	showRoleAssignmentModal.value = false;
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">

        <!-- Organization details -->
        <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
            <h3 class="text-[14px] font-semibold text-[#111827] mb-4">Organization details</h3>
            <div class="grid grid-cols-[140px_1fr] gap-y-3 text-[13px] mb-4">
                <span class="text-[#6b7280]">Name</span>
                <span class="text-[#111827] font-medium">{{ organization?.name || '—' }}</span>
                <span class="text-[#6b7280]">External ID</span>
                <span class="text-[#9ca3af]">Not set</span>
            </div>
            <button
                class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-[12px] font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                @click="showEditModal = true"
            >
                Edit details
            </button>
        </div>

        <!-- Domains -->
        <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
            <h3 class="text-[14px] font-semibold text-[#111827] mb-1">Domains</h3>
            <p class="text-[12px] text-[#6b7280] mb-3 leading-relaxed">
                Users with verified domains can sign in through this organization's Single Sign-On connection without needing to verify their email.
                By default, they are managed by the organization's domain policy.
            </p>
            <button 
                @click="showAddDomainModal = true"
                class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-[12px] font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors mb-4"
            >
                Add domain
            </button>
            <!-- Domain table -->
            <div class="rounded-xl overflow-hidden ring-1 ring-gray-900/[0.04]">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                            <th class="px-4 py-2.5 text-[12px] font-semibold text-[#111827]">Domain</th>
                            <th class="px-4 py-2.5 text-[12px] font-semibold text-[#111827]">Status</th>
                            <th class="px-4 py-2.5 w-24 text-right"/>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!domains.length">
                            <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-500">No domains registered.</td>
                        </tr>
                        <tr v-for="dom in domains" :key="dom.id" class="border-t border-[#e5e7eb] hover:bg-[#f9fafb] last:border-b-0">
                            <td class="px-4 py-2.5 text-[13px] text-[#111827]">{{ dom.name }}</td>
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
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded transition-colors"
                                    title="Verify domain now"
                                >
                                    Verify
                                </button>
                                <button 
                                    @click="confirmDeleteDomain(dom)"
                                    class="text-[#9ca3af] hover:text-red-500 transition-colors p-1 rounded hover:bg-red-50"
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
        <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
            <h3 class="text-[14px] font-semibold text-[#111827] mb-1">IT contacts</h3>
            <p class="text-[12px] text-[#6b7280] mb-3">IT contacts receive critical alerts and error notifications directly from WorkOS.</p>
            
            <div v-if="itContacts.length" class="mb-4 space-y-2">
                <div v-for="contact in itContacts" :key="contact.id" class="flex items-center justify-between text-xs bg-gray-50 border border-gray-100 p-2.5 rounded-md">
                    <div>
                        <strong class="text-gray-800 font-medium">{{ contact.name }}</strong> 
                        <span class="text-gray-500 ml-1">({{ contact.email }})</span>
                    </div>
                </div>
            </div>

            <button 
                @click="showItContactsModal = true"
                class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-[12px] font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
            >
                Manage IT contacts
            </button>
        </div>

        <!-- Metadata -->
        <div>
            <h2 class="text-[15px] font-semibold text-[#111827] mb-3">Metadata</h2>
            <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
                <h3 class="text-[14px] font-semibold text-[#111827] mb-1">Custom metadata</h3>
                <p class="text-[12px] text-[#6b7280] mb-3">
                    Store additional information about this organization as key-value pairs.
                </p>

                <!-- Metadata List -->
                <div v-if="metadataPairs.length" class="border border-[#e5e7eb] rounded-md mb-4 overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-[#f9fafb] border-b border-[#e5e7eb] text-gray-500 font-medium">
                                <th class="px-3 py-2">Key</th>
                                <th class="px-3 py-2">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e7eb]">
                            <tr v-for="pair in metadataPairs" :key="pair.key" class="hover:bg-[#f9fafb]">
                                <td class="px-3 py-2 font-mono text-indigo-600 font-medium">{{ pair.key }}</td>
                                <td class="px-3 py-2 font-mono text-gray-800">{{ pair.value }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button 
                    @click="openMetadataModal"
                    class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-[12px] font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                >
                    Edit metadata
                </button>
            </div>
        </div>

        <!-- Admin Portal -->
        <div>
            <h2 class="text-[15px] font-semibold text-[#111827] mb-3">Admin Portal</h2>
            <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-[14px] font-semibold text-[#111827] mb-1">Role assignment in Admin Portal</h3>
                        <p class="text-[12px] text-[#6b7280] mb-3">Allow IT contacts to map user roles based on their identity provider groups.</p>
                        
                        <!-- Role Mappings Summary -->
                        <div v-if="roleMappings.length" class="mb-4 bg-gray-50 border border-gray-100 p-3 rounded-md text-xs space-y-1.5">
                            <span class="block text-gray-500 font-semibold mb-1">Mapped Roles:</span>
                            <div v-for="mapping in roleMappings" :key="mapping.id" class="flex items-center gap-1.5 text-gray-700">
                                <span class="px-1.5 py-0.5 font-mono bg-gray-200 rounded text-gray-800 text-[10px]">{{ mapping.group }}</span>
                                <span>mapped to</span>
                                <strong class="text-indigo-600">{{ mapping.role }}</strong>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                @click="openRoleAssignmentModal"
                                class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-[12px] font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                            >
                                Configure role assignment
                            </button>
                            <button 
                                @click="roleMappings = []"
                                class="h-[30px] px-3 text-[12px] font-medium text-[#6b7280] hover:text-red-600 transition-colors"
                            >
                                Clear mappings
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[13px] font-medium text-[#10b981] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enabled
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div>
            <h2 class="text-[15px] font-semibold text-[#ef4444] mb-3">Danger zone</h2>
            <div class="border border-[#fecaca] rounded-lg bg-white p-5 shadow-sm">
                <h3 class="text-[14px] font-semibold text-[#111827] mb-1">Delete organization</h3>
                <p class="text-[12px] text-[#6b7280] mb-3">
                    Delete this organization and its memberships, connections, and Audit Logs.<br>
                    This action is permanent and cannot be undone.
                </p>
                <button
                    class="h-[30px] px-3 border border-[#fecaca] rounded-md text-[12px] font-medium text-[#ef4444] hover:bg-[#fef2f2] transition-colors"
                    @click="showDeleteModal = true"
                >
                    Delete organization
                </button>
            </div>
        </div>

        <!-- ── MODALS (Premium Backdrop) ── -->

        <!-- Add Domain Modal -->
        <div v-if="showAddDomainModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showAddDomainModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Add Domain</h3>
                <p class="text-xs text-[#6b7280] mb-4">Register a new domain name to associate with this organization.</p>
                <form @submit.prevent="addDomain" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Domain name</label>
                        <input 
                            v-model="newDomainName" 
                            type="text" 
                            required
                            placeholder="e.g. unugha.ac.id"
                            class="w-full h-9 px-3 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        />
                    </div>
                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showAddDomainModal = false">Cancel</button>
                        <button type="submit" class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors">Register Domain</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Domain Confirm Modal -->
        <div v-if="showDeleteDomainModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDeleteDomainModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Delete Domain</h3>
                <p class="text-xs text-[#6b7280] mb-5">
                    Are you sure you want to delete <strong class="text-gray-800">{{ domainToDelete?.name }}</strong>? Users from this domain will no longer be able to log in via SSO.
                </p>
                <div class="flex justify-end gap-2">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showDeleteDomainModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700 transition-colors" @click="deleteDomain">Remove Domain</button>
                </div>
            </div>
        </div>

        <!-- Manage IT Contacts Modal -->
        <div v-if="showItContactsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showItContactsModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Manage IT contacts</h3>
                <p class="text-xs text-[#6b7280] mb-4">Add or remove IT administrators authorized to receive system alerts.</p>
                
                <!-- Existing Contacts List with Delete -->
                <div class="space-y-2 max-h-40 overflow-y-auto mb-5 border border-gray-150 p-2.5 rounded-lg bg-gray-50">
                    <div v-if="!itContacts.length" class="text-center py-4 text-xs text-gray-500">No IT contacts configured.</div>
                    <div v-for="contact in itContacts" :key="contact.id" class="flex items-center justify-between text-xs bg-white border border-gray-150 p-2 rounded-md shadow-sm">
                        <div>
                            <span class="font-medium text-gray-800">{{ contact.name }}</span> 
                            <span class="text-gray-400 ml-1">({{ contact.email }})</span>
                        </div>
                        <button @click="removeItContact(contact.id)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Add Contact Form -->
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <h4 class="text-xs font-semibold text-[#111827]">Add new IT contact</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-[#6b7280] mb-1">Name</label>
                            <input v-model="newContactName" type="text" placeholder="e.g. Andi" class="w-full h-8 px-2 text-xs border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1]"/>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-[#6b7280] mb-1">Email</label>
                            <input v-model="newContactEmail" type="email" placeholder="andi@org.com" class="w-full h-8 px-2 text-xs border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1]"/>
                        </div>
                    </div>
                    <button 
                        @click="addItContact"
                        class="w-full h-8 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-md text-xs font-semibold transition-colors mt-2"
                    >
                        Add Contact
                    </button>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showItContactsModal = false">Close</button>
                </div>
            </div>
        </div>

        <!-- Edit Metadata Modal -->
        <div v-if="showMetadataModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showMetadataModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Edit custom metadata</h3>
                <p class="text-xs text-[#6b7280] mb-4">Add metadata keys and values to store additional info about this organization.</p>
                
                <div class="space-y-2 max-h-60 overflow-y-auto mb-4 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                    <div v-if="!tempMetadataPairs.length" class="text-center py-8 text-xs text-gray-500">No metadata rows. Click "Add row" below.</div>
                    <div v-for="(pair, idx) in tempMetadataPairs" :key="idx" class="flex items-center gap-2">
                        <input 
                            v-model="pair.key" 
                            type="text" 
                            placeholder="Key" 
                            class="flex-1 h-8 px-2.5 text-xs font-mono border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500"
                        />
                        <span class="text-gray-400">:</span>
                        <input 
                            v-model="pair.value" 
                            type="text" 
                            placeholder="Value" 
                            class="flex-1 h-8 px-2.5 text-xs font-mono border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500"
                        />
                        <button @click="removeMetadataRow(idx)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <button 
                        @click="addMetadataRow" 
                        class="h-8 px-3 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-md text-xs font-semibold transition-colors"
                    >
                        Add row
                    </button>
                    <div class="flex gap-2">
                        <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showMetadataModal = false">Cancel</button>
                        <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="saveMetadata">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configure Role Assignment Modal -->
        <div v-if="showRoleAssignmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showRoleAssignmentModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Configure Role Assignment</h3>
                <p class="text-xs text-[#6b7280] mb-4">Map identity provider group names to specific portal roles.</p>
                
                <div class="space-y-2 max-h-60 overflow-y-auto mb-4 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                    <div v-if="!tempMappings.length" class="text-center py-8 text-xs text-gray-500">No role mappings defined.</div>
                    <div v-for="(mapping, idx) in tempMappings" :key="mapping.id" class="flex items-center gap-2">
                        <input 
                            v-model="mapping.group" 
                            type="text" 
                            placeholder="IdP Group Name (e.g. Admins)" 
                            class="flex-1 h-8 px-2.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500 text-gray-800"
                        />
                        <span class="text-gray-400">➔</span>
                        <select 
                            v-model="mapping.role"
                            class="w-36 h-8 px-1 text-xs border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500 text-gray-800"
                        >
                            <option value="Admin">Admin</option>
                            <option value="Member">Member</option>
                        </select>
                        <button @click="removeMappingRow(idx)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <button 
                        @click="addMappingRow" 
                        class="h-8 px-3 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-md text-xs font-semibold transition-colors"
                    >
                        Add mapping
                    </button>
                    <div class="flex gap-2">
                        <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showRoleAssignmentModal = false">Cancel</button>
                        <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="saveRoleAssignment">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Details Modal (Original) -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showEditModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <h2 class="text-[16px] font-semibold text-[#111827] mb-4">Edit details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#374151] mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1]"/>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#374151] mb-1">Description</label>
                        <input v-model="form.description" type="text" class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1]"/>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-[13px] text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showEditModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-[13px] font-semibold hover:bg-[#4f46e5] transition-colors disabled:opacity-50" :disabled="isSubmitting" @click="submitEdit">Save changes</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Modal (Original) -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <h2 class="text-[16px] font-semibold text-[#111827] mb-2">Delete organization</h2>
                <p class="text-[13px] text-[#6b7280] mb-5">
                    Are you sure you want to delete <strong class="text-[#111827]">{{ organization?.name }}</strong>?
                    This will remove all users, roles, and data permanently.
                </p>
                <div class="flex justify-end gap-2">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-[13px] text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showDeleteModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#ef4444] text-white rounded-md text-[13px] font-semibold hover:bg-[#dc2626] transition-colors disabled:opacity-50" :disabled="isSubmitting" @click="submitDelete">Delete organization</button>
                </div>
            </div>
        </div>
    </div>
</template>