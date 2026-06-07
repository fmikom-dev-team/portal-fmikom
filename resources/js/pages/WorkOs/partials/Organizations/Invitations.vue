<script setup lang="ts">
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{ organization?: any }>();

const search = ref("");
const showInviteModal = ref(false);
const showClearConfirmModal = ref(false);
const inviteEmail = ref("");
const inviteRole = ref("member");
const isSubmitting = ref(false);
const isLoading = ref(false);

const inviteList = ref<any[]>([]);

async function fetchInvitations() {
	if (!props.organization?.id) return;
	isLoading.value = true;
	try {
		const response = await axios.get(
			`/workos/modules/${props.organization.id}/invitations`,
		);
		inviteList.value = response.data.invitations || [];
	} catch (e) {
		console.error("Failed to fetch invitations", e);
		toast("Failed to load invitations.", "error");
	} finally {
		isLoading.value = false;
	}
}

async function sendInvitation() {
	if (!inviteEmail.value || !props.organization?.id) return;

	isSubmitting.value = true;
	try {
		await axios.post(`/workos/modules/${props.organization.id}/invitations`, {
			email: inviteEmail.value,
			role: inviteRole.value,
		});
		toast("Invitation sent successfully.", "success");
		inviteEmail.value = "";
		inviteRole.value = "member";
		showInviteModal.value = false;
		await fetchInvitations();
	} catch (e) {
		console.error("Failed to send invitation", e);
		toast("Failed to send invitation.", "error");
	} finally {
		isSubmitting.value = false;
	}
}

async function clearInvitations() {
	if (!props.organization?.id) return;
	try {
		await axios.delete(
			`/workos/modules/${props.organization.id}/invitations/clear`,
		);
		toast("Invitation history cleared.", "success");
		showClearConfirmModal.value = false;
		await fetchInvitations();
	} catch (e) {
		console.error("Failed to clear invitations", e);
		toast("Failed to clear invitations.", "error");
	}
}

async function deleteInvitation(invitationId: number) {
	if (!props.organization?.id) return;
	try {
		await axios.delete(
			`/workos/modules/${props.organization.id}/invitations/${invitationId}`,
		);
		toast("Invitation removed successfully.", "success");
		await fetchInvitations();
	} catch (e) {
		console.error("Failed to delete invitation", e);
		toast("Failed to remove invitation.", "error");
	}
}

onMounted(() => {
	fetchInvitations();
});

watch(
	() => props.organization?.id,
	() => {
		fetchInvitations();
	},
	{ immediate: true },
);

const filtered = () =>
	inviteList.value.filter(
		(i) =>
			!search.value ||
			i.email.toLowerCase().includes(search.value.toLowerCase()),
	);

function formatDate(dateStr: string | null | undefined): string {
	if (!dateStr) return "—";
	try {
		const d = new Date(dateStr);
		return (
			d.toLocaleDateString("en-US", {
				month: "short",
				day: "numeric",
				year: "numeric",
			}) +
			", " +
			d.toLocaleTimeString("en-US", {
				hour: "numeric",
				minute: "2-digit",
				hour12: true,
			})
		);
	} catch {
		return dateStr;
	}
}
</script>

<template>
    <div style="font-family: var(--wos-font)">
        <!-- Toolbar -->
        <div class="flex items-center gap-3 mb-5">
            <div class="relative flex-1 max-w-[480px]">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by email"
                    class="w-full h-[36px] pl-9 pr-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1] placeholder:text-[#9ca3af] text-[#111827] transition-colors"
                />
            </div>
            
            <!-- Clear History & Invite Buttons -->
            <button
                v-if="inviteList.length > 0"
                class="h-[36px] px-3.5 border border-[#d1d5db] text-[#374151] rounded-md text-[13px] font-semibold hover:bg-[#f9fafb] transition-colors ml-auto shadow-sm bg-white"
                @click="showClearConfirmModal = true"
            >
                Remove history
            </button>
            <button
                class="h-[36px] px-4 bg-[#6366f1] text-white rounded-md text-[13px] font-semibold hover:bg-[#4f46e5] transition-colors shadow-sm"
                :class="{ 'ml-auto': inviteList.length === 0, 'ml-3': inviteList.length > 0 }"
                @click="showInviteModal = true"
            >
                Invite user
            </button>
        </div>

        <!-- Table -->
        <div class="rounded-xl overflow-hidden bg-white ring-1 ring-gray-900/[0.04] shadow-sm">
            <div v-if="isLoading" class="p-12 flex items-center justify-center">
                <svg class="animate-spin h-6 w-6 text-[#6366f1]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <table v-else class="w-full text-left">
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                        <th class="px-6 py-3 text-[12px] font-semibold text-[#111827]">Email</th>
                        <th class="px-6 py-3 text-[12px] font-semibold text-[#111827]">Role</th>
                        <th class="px-6 py-3 text-[12px] font-semibold text-[#111827]">Status</th>
                        <th class="px-6 py-3 text-[12px] font-semibold text-[#111827]">Created</th>
                        <th class="px-6 py-3 text-[12px] font-semibold text-[#111827] text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb]">
                    <tr v-if="!filtered().length">
                        <td colspan="5" class="px-6 py-8 text-center text-[13px] text-[#6b7280]">No invitations found.</td>
                    </tr>
                    <tr v-for="invite in filtered()" :key="invite.id" class="hover:bg-[#f9fafb] transition-colors group">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-[#e0e7ff] flex items-center justify-center text-[11px] font-bold text-[#6366f1] shrink-0">
                                    {{ invite.email.charAt(0).toUpperCase() }}
                                </div>
                                <span class="text-[13px] font-medium text-[#111827]">{{ invite.email }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-[13px] text-[#374151] font-medium">{{ invite.role || 'Member' }}</td>
                        <td class="px-6 py-3">
                            <span class="flex items-center gap-1.5 text-[13px] font-medium"
                                :class="invite.status === 'Accepted' ? 'text-[#10b981]' : invite.status === 'Pending' ? 'text-[#f59e0b]' : 'text-[#ef4444]'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full inline-block"
                                    :class="invite.status === 'Accepted' ? 'bg-[#10b981]' : invite.status === 'Pending' ? 'bg-[#f59e0b]' : 'bg-[#ef4444]'"
                                ></span>
                                {{ invite.status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-[13px] text-[#6b7280]">{{ formatDate(invite.created_at) }}</td>
                        
                        <!-- Delete individual button -->
                        <td class="px-6 py-3 text-right">
                            <button 
                                @click="deleteInvitation(invite.id)"
                                class="opacity-0 group-hover:opacity-100 text-[#df5c5f] hover:text-red-700 transition-all p-1 hover:bg-red-50 rounded inline-block"
                                title="Remove invitation"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invite User Modal (Fungsional) -->
        <div v-if="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showInviteModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Invite member</h3>
                <p class="text-xs text-[#6b7280] mb-4">
                    Send an email invitation to invite users to join <strong class="text-gray-800">{{ organization?.name || 'this organization' }}</strong>.
                </p>
                
                <form @submit.prevent="sendInvitation" class="space-y-4">
                    <div>
                        <label for="invite_email" class="block text-xs font-semibold text-[#374151] mb-1.5">Email address</label>
                        <input 
                            id="invite_email"
                            v-model="inviteEmail" 
                            type="email" 
                            required
                            placeholder="user@example.com"
                            class="w-full h-9 px-3 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1] text-[#111827] bg-white"
                        />
                    </div>
                    
                    <div>
                        <label for="invite_role" class="block text-xs font-semibold text-[#374151] mb-1.5">Organization Role</label>
                        <select 
                            id="invite_role"
                            v-model="inviteRole"
                            class="w-full h-9 px-2 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827] bg-white"
                        >
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                            <option value="developer">Developer</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors bg-white" @click="showInviteModal = false">Cancel</button>
                        <button type="submit" class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors disabled:opacity-60 flex items-center justify-center gap-1.5" :disabled="isSubmitting">
                            <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ isSubmitting ? 'Sending...' : 'Send invitation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clear Confirm Modal -->
        <div v-if="showClearConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showClearConfirmModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Clear invitation history</h3>
                <p class="text-xs text-[#6b7280] mb-6">
                    Are you sure you want to permanently clear the invitation history for this organization? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors bg-white" @click="showClearConfirmModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700 transition-colors" @click="clearInvitations">Yes, clear history</button>
                </div>
            </div>
        </div>
    </div>
</template>