<script setup lang="ts">
import { ref } from "vue";

const props = defineProps<{ organization?: any }>();

// 1. IT Contact State
const itContact = ref({
	status: "Awaiting setup", // 'Awaiting setup', 'Invited', 'Active'
	email: "",
});
const showInviteItModal = ref(false);
const inviteItEmail = ref("");

// 2. Domain Policy State
const domainPolicy = ref({
	allowedMethods: [
		"Email + Password",
		"Sign In with Apple",
		"GitHub OAuth",
		"Google OAuth",
		"Microsoft OAuth",
		"Magic Auth",
		"Passkey",
	],
	mfaRequired: false,
	autoMembership: true,
});
const showDomainPolicyModal = ref(false);
const tempDomainPolicy = ref({ ...domainPolicy.value });

// 3. Organization Policy State
const orgPolicy = ref({
	ssoDomainRequired: false,
	ssoGuestRequired: false,
	orgMfaRequired: false,
});
const showOrgPolicyModal = ref(false);
const tempOrgPolicy = ref({ ...orgPolicy.value });

// 4. Directory Sync State
const dirSync = ref({
	status: "Awaiting setup", // 'Awaiting setup', 'Active'
	provider: "",
	directoryName: "",
});
const showDirSyncModal = ref(false);
const tempDirSync = ref({ ...dirSync.value });

// 5. JIT Provisioning State
const jitProvisioning = ref({
	enabled: true,
});
const showJitModal = ref(false);
const tempJitEnabled = ref(jitProvisioning.value.enabled);

// 6. Audit Logs State
const auditLogs = ref({
	retentionPeriod: "30 days",
	status: "No events received", // 'No events received', 'Active'
});
const showAuditLogsModal = ref(false);
const tempAuditRetention = ref(auditLogs.value.retentionPeriod);

// 7. Log Streams State
const logStreams = ref({
	status: "Awaiting setup", // 'Awaiting setup', 'Active'
	destination: "",
	endpointUrl: "",
});
const showLogStreamsModal = ref(false);
const tempLogStreams = ref({ ...logStreams.value });

const availableProviders = [
	"Okta",
	"Azure AD (Entra ID)",
	"Google Workspace",
	"PingIdentity",
	"OneLogin",
	"JumpCloud",
];
const availableDestinations = [
	"Datadog",
	"Splunk",
	"Elasticsearch",
	"AWS S3",
	"Sumo Logic",
	"Webhook Endpoint",
];
const availableAuthMethods = [
	"Email + Password",
	"Sign In with Apple",
	"GitHub OAuth",
	"Google OAuth",
	"Microsoft OAuth",
	"Magic Auth",
	"Passkey",
];

// Submit functions
function submitInviteIt() {
	if (!inviteItEmail.value) return;
	itContact.value.status = "Invited";
	itContact.value.email = inviteItEmail.value;
	showInviteItModal.value = false;
}

function submitDomainPolicy() {
	domainPolicy.value = { ...tempDomainPolicy.value };
	showDomainPolicyModal.value = false;
}

function toggleMethod(method: string) {
	const idx = tempDomainPolicy.value.allowedMethods.indexOf(method);
	if (idx > -1) {
		tempDomainPolicy.value.allowedMethods.splice(idx, 1);
	} else {
		tempDomainPolicy.value.allowedMethods.push(method);
	}
}

function submitOrgPolicy() {
	orgPolicy.value = { ...tempOrgPolicy.value };
	showOrgPolicyModal.value = false;
}

function submitDirSync() {
	dirSync.value = { ...tempDirSync.value };
	dirSync.value.status = tempDirSync.value.provider
		? "Active"
		: "Awaiting setup";
	showDirSyncModal.value = false;
}

function submitJit() {
	jitProvisioning.value.enabled = tempJitEnabled.value;
	showJitModal.value = false;
}

function submitAuditLogs() {
	auditLogs.value.retentionPeriod = tempAuditRetention.value;
	auditLogs.value.status = "Active";
	showAuditLogsModal.value = false;
}

function submitLogStreams() {
	logStreams.value = { ...tempLogStreams.value };
	logStreams.value.status = tempLogStreams.value.destination
		? "Active"
		: "Awaiting setup";
	showLogStreamsModal.value = false;
}

function openDomainPolicyModal() {
	tempDomainPolicy.value = {
		...domainPolicy.value,
		allowedMethods: [...domainPolicy.value.allowedMethods],
	};
	showDomainPolicyModal.value = true;
}

function openOrgPolicyModal() {
	tempOrgPolicy.value = { ...orgPolicy.value };
	showOrgPolicyModal.value = true;
}

function openDirSyncModal() {
	tempDirSync.value = { ...dirSync.value };
	showDirSyncModal.value = true;
}

function openJitModal() {
	tempJitEnabled.value = jitProvisioning.value.enabled;
	showJitModal.value = true;
}

function openAuditLogsModal() {
	tempAuditRetention.value = auditLogs.value.retentionPeriod;
	showAuditLogsModal.value = true;
}

function openLogStreamsModal() {
	tempLogStreams.value = { ...logStreams.value };
	showLogStreamsModal.value = true;
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-0 text-sm">

        <!-- Invite IT contact banner -->
        <div class="rounded-xl p-5 mb-6 flex items-start justify-between gap-4 bg-white ring-1 ring-gray-900/[0.04] transition-all duration-300">
            <div class="flex-1">
                <p class="text-sm font-semibold text-[#111827] mb-1">Invite an IT contact to set up this organization</p>
                <p class="text-sm text-[#6b7280] leading-relaxed">
                    Notify this organization's IT contact to set up: Domain Verification, Single Sign-On, Directory Sync, Log Streams and Bring Your Own Key.
                </p>
                
                <div v-if="itContact.status === 'Invited'" class="mt-3 p-3 bg-indigo-50 border border-indigo-100 rounded-md text-xs text-indigo-700 flex items-center gap-2 max-w-fit">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    Invitation sent to <strong class="font-semibold">{{ itContact.email }}</strong>
                </div>

                <div class="flex items-center gap-3 mt-4">
                    <button 
                        @click="showInviteItModal = true"
                        class="h-[32px] px-3 bg-[#6366f1] text-white rounded-md text-sm font-medium hover:bg-[#4f46e5] transition-colors shadow-sm"
                    >
                        {{ itContact.status === 'Invited' ? 'Reinvite IT contact' : 'Invite IT contact' }}
                    </button>
                    <button 
                        v-if="itContact.status === 'Invited'"
                        @click="itContact.status = 'Awaiting setup'; itContact.email = ''"
                        class="h-[32px] px-3 border border-red-200 rounded-md text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                    >
                        Cancel Invitation
                    </button>
                    <button 
                        v-else
                        @click="showInviteItModal = true"
                        class="h-[32px] px-3 border border-[#d1d5db] rounded-md text-sm font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                    >
                        Manage
                    </button>
                    
                    <!-- Awaiting Setup Status -->
                    <span 
                        v-if="itContact.status === 'Awaiting setup'"
                        class="flex items-center gap-1.5 text-sm font-medium text-[#f59e0b]"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Awaiting setup
                    </span>
                    <!-- Invited / Active Status -->
                    <span 
                        v-else-if="itContact.status === 'Invited'"
                        class="flex items-center gap-1.5 text-sm font-medium text-indigo-500"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"/></svg>
                        Invited
                    </span>
                </div>
            </div>
        </div>


        <!-- User Provisioning Section -->
        <h2 class="text-base font-semibold text-[#111827] mb-3 mt-6">User provisioning</h2>
        <div class="rounded-xl bg-white mb-4 divide-y divide-gray-100 ring-1 ring-gray-900/[0.04]">
            <!-- Directory Sync -->
            <div class="p-5">
                <p class="text-sm font-semibold text-[#111827] mb-1">Directory Sync</p>
                <p class="text-sm text-[#6b7280] mb-4">
                    <span v-if="dirSync.status === 'Active'">
                        Synced via <strong class="text-[#111827]">{{ dirSync.provider }}</strong> (Directory: <span class="font-mono text-xs bg-gray-100 px-1 py-0.5 rounded">{{ dirSync.directoryName || 'default_dir' }}</span>)
                    </span>
                    <span v-else>Wait for the IT contact to complete setup, or configure this directory manually.</span>
                </p>
                
                <div class="flex items-center gap-3">
                    <button 
                        @click="openDirSyncModal"
                        class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-sm font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                    >
                        {{ dirSync.status === 'Active' ? 'Edit Connection' : 'Configure manually' }}
                    </button>
                    
                    <span 
                        v-if="dirSync.status === 'Awaiting setup'"
                        class="flex items-center gap-1.5 text-sm font-medium text-[#f59e0b]"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Awaiting setup
                    </span>
                    <span 
                        v-else-if="dirSync.status === 'Active'"
                        class="flex items-center gap-1.5 text-sm font-medium text-emerald-500"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Active
                    </span>
                </div>
            </div>
            <!-- JIT Provisioning -->
            <div class="p-5">
                <p class="text-sm font-semibold text-[#111827] mb-1">Just-in-time provisioning</p>
                <p class="text-sm text-[#6b7280] mb-2">Automatically provision these users and add them as members the next time they sign in.</p>
                <p class="text-sm text-[#6b7280] mb-4">JIT-provision SSO users
                    <span 
                        v-if="jitProvisioning.enabled"
                        class="inline-flex items-center gap-1.5 ml-1 text-[#10b981] font-medium"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Enabled
                    </span>
                    <span 
                        v-else
                        class="inline-flex items-center gap-1.5 ml-1 text-gray-400 font-medium"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        Disabled
                    </span>
                </p>
                <button 
                    @click="openJitModal"
                    class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-sm font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                >
                    Configure
                </button>
            </div>
        </div>

        <!-- Audit Logs Section -->
        <h2 class="text-base font-semibold text-[#111827] mb-3 mt-6">Audit Logs</h2>
        <div class="rounded-xl bg-white mb-4 divide-y divide-gray-100 ring-1 ring-gray-900/[0.04]">
            <div class="p-5">
                <p class="text-sm font-semibold text-[#111827] mb-1">Audit Logs</p>
                <p class="text-sm text-[#6b7280] mb-2">Start ingesting audit log events in WorkOS.</p>
                <div class="grid grid-cols-[200px_1fr] gap-y-2 text-sm mb-4">
                    <span class="text-[#6b7280]">Retention period</span>
                    <span class="text-[#111827] font-medium">{{ auditLogs.retentionPeriod }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="openAuditLogsModal"
                        class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-sm font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                    >
                        Configure audit logs
                    </button>
                    <span class="flex items-center gap-1.5 text-sm font-medium" :class="auditLogs.status === 'Active' ? 'text-emerald-500' : 'text-gray-500'">
                        <svg v-if="auditLogs.status === 'Active'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <svg v-else class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                        {{ auditLogs.status === 'Active' ? 'Receiving events' : 'No events received' }}
                    </span>
                </div>
            </div>
            <div class="p-5">
                <p class="text-sm font-semibold text-[#111827] mb-1">Log Streams</p>
                <p class="text-sm text-[#6b7280] mb-4">
                    <span v-if="logStreams.status === 'Active'">
                        Streaming events to <strong class="text-[#111827]">{{ logStreams.destination }}</strong>: <span class="font-mono text-xs text-gray-600">{{ logStreams.endpointUrl }}</span>
                    </span>
                    <span v-else>Wait for the IT contact to complete setup, or configure log streaming manually.</span>
                </p>
                
                <div class="flex items-center gap-3">
                    <button 
                        @click="openLogStreamsModal"
                        class="h-[30px] px-3 border border-[#d1d5db] rounded-md text-sm font-medium text-[#374151] hover:bg-[#f9fafb] transition-colors"
                    >
                        {{ logStreams.status === 'Active' ? 'Edit Log Stream' : 'Configure manually' }}
                    </button>
                    
                    <span 
                        v-if="logStreams.status === 'Awaiting setup'"
                        class="flex items-center gap-1.5 text-sm font-medium text-[#f59e0b]"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Awaiting setup
                    </span>
                    <span 
                        v-else-if="logStreams.status === 'Active'"
                        class="flex items-center gap-1.5 text-sm font-medium text-emerald-500"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Keys Section -->
        <h2 class="text-base font-semibold text-[#111827] mb-3 mt-6">Keys</h2>
        <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
            <p class="text-sm font-semibold text-[#111827] mb-1">Key details</p>
            <p class="text-sm text-[#6b7280]">
                {{ itContact.status === 'Invited' ? 'IT contact invited. Waiting for key generation.' : 'Wait for the IT contact to complete setup.' }}
            </p>
        </div>

        <!-- ── MODALS (Premium Glassmorphism Backdrop) ── -->

        <!-- Invite IT Contact Modal -->
        <div v-if="showInviteItModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showInviteItModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100 transition-transform duration-300">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Invite an IT contact</h3>
                <p class="text-xs text-[#6b7280] mb-4">
                    Send an invitation to the IT administrator responsible for managing single sign-on or directory synchronization.
                </p>
                <div class="mb-4">
                    <label for="invite_it_email" class="block text-xs font-semibold text-[#374151] mb-1.5">Email address</label>
                    <input 
                        id="invite_it_email"
                        v-model="inviteItEmail" 
                        type="email" 
                        placeholder="admin@organization.com"
                        class="w-full h-9 px-3 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1] text-[#111827]"
                    />
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showInviteItModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitInviteIt">Send invitation</button>
                </div>
            </div>
        </div>

        <!-- Edit Domain Policy Modal -->
        <div v-if="showDomainPolicyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDomainPolicyModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Edit Domain Policy</h3>
                <p class="text-xs text-[#6b7280] mb-4">Configure authentication methods and security controls for users signing in from verified domains.</p>
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-semibold text-[#374151] mb-2">Allowed Authentication Methods</span>
                        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <label v-for="method in availableAuthMethods" :key="method" :for="'auth_method_' + method.toLowerCase().replace(/[^a-z0-9]/g, '_')" class="flex items-center gap-2 text-xs text-[#111827] cursor-pointer hover:bg-gray-100 p-1.5 rounded transition-colors">
                                <input 
                                    :id="'auth_method_' + method.toLowerCase().replace(/[^a-z0-9]/g, '_')"
                                    type="checkbox" 
                                    :checked="tempDomainPolicy.allowedMethods.includes(method)"
                                    @change="toggleMethod(method)"
                                    class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-3.5 h-3.5"
                                />
                                {{ method }}
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <div>
                            <label for="domain_mfa_required" class="block text-xs font-semibold text-[#111827] cursor-pointer">Require Multi-Factor Authentication (MFA)</label>
                            <span class="block text-[11px] text-[#6b7280]">Force users with domain accounts to complete MFA challenge.</span>
                        </div>
                        <input 
                            id="domain_mfa_required"
                            type="checkbox" 
                            v-model="tempDomainPolicy.mfaRequired"
                            class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <div>
                            <label for="domain_auto_membership" class="block text-xs font-semibold text-[#111827] cursor-pointer">Automatic Membership</label>
                            <span class="block text-[11px] text-[#6b7280]">Automatically add newly registered domain users to this organization.</span>
                        </div>
                        <input 
                            id="domain_auto_membership"
                            type="checkbox" 
                            v-model="tempDomainPolicy.autoMembership"
                            class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 border-t border-gray-100 pt-4">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showDomainPolicyModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitDomainPolicy">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Edit Organization Policy Modal -->
        <div v-if="showOrgPolicyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showOrgPolicyModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Edit Organization Policy</h3>
                <p class="text-xs text-[#6b7280] mb-4">Set access requirements for all members accessing this organization's assets.</p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="org_sso_domain" class="block text-xs font-semibold text-[#111827] cursor-pointer">SSO for Domain Members</label>
                            <span class="block text-[11px] text-[#6b7280]">Mandate SSO for users with matching domain names.</span>
                        </div>
                        <input 
                            id="org_sso_domain"
                            type="checkbox" 
                            v-model="tempOrgPolicy.ssoDomainRequired"
                            class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <div>
                            <label for="org_sso_guest" class="block text-xs font-semibold text-[#111827] cursor-pointer">SSO for Guest Members</label>
                            <span class="block text-[11px] text-[#6b7280]">Require external guest users to authenticate via SSO.</span>
                        </div>
                        <input 
                            id="org_sso_guest"
                            type="checkbox" 
                            v-model="tempOrgPolicy.ssoGuestRequired"
                            class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <div>
                            <label for="org_mfa_required" class="block text-xs font-semibold text-[#111827] cursor-pointer">Require Multi-Factor Authentication</label>
                            <span class="block text-[11px] text-[#6b7280]">Enable org-wide MFA requirement for all user logins.</span>
                        </div>
                        <input 
                            id="org_mfa_required"
                            type="checkbox" 
                            v-model="tempOrgPolicy.orgMfaRequired"
                            class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 border-t border-gray-100 pt-4">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showOrgPolicyModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitOrgPolicy">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Directory Sync Modal -->
        <div v-if="showDirSyncModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showDirSyncModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Configure Directory Sync</h3>
                <p class="text-xs text-[#6b7280] mb-4">Manually configure the user directory synchronization via SCIM protocol.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="directory_provider" class="block text-xs font-semibold text-[#374151] mb-1.5">Directory Provider</label>
                        <select 
                            id="directory_provider"
                            v-model="tempDirSync.provider"
                            class="w-full h-9 px-2 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        >
                            <option value="">Select a provider...</option>
                            <option v-for="prov in availableProviders" :key="prov" :value="prov">{{ prov }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="directory_name" class="block text-xs font-semibold text-[#374151] mb-1.5">Directory Name</label>
                        <input 
                            id="directory_name"
                            v-model="tempDirSync.directoryName"
                            type="text"
                            placeholder="e.g. Okta Main Directory"
                            class="w-full h-9 px-3 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showDirSyncModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitDirSync">Save Config</button>
                </div>
            </div>
        </div>

        <!-- JIT Modal -->
        <div v-if="showJitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showJitModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Configure JIT Provisioning</h3>
                <p class="text-xs text-[#6b7280] mb-5">Just-in-time provisioning creates memberships automatically on successful SSO assertion.</p>

                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <label for="jit_provisioning" class="block text-xs font-semibold text-[#111827] cursor-pointer">JIT-provision SSO users</label>
                        <span class="block text-[11px] text-[#6b7280]">Enable dynamic account provisioning.</span>
                    </div>
                    <input 
                        id="jit_provisioning"
                        type="checkbox" 
                        v-model="tempJitEnabled"
                        class="rounded text-[#6366f1] focus:ring-[#6366f1] border-gray-300 w-4 h-4 cursor-pointer"
                    />
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showJitModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitJit">Save Config</button>
                </div>
            </div>
        </div>

        <!-- Audit Logs Modal -->
        <div v-if="showAuditLogsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showAuditLogsModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Configure Audit Logs</h3>
                <p class="text-xs text-[#6b7280] mb-4">Define security logs retention period for the organization's actions.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="retention_period" class="block text-xs font-semibold text-[#374151] mb-1.5">Retention Period</label>
                        <select 
                            id="retention_period"
                            v-model="tempAuditRetention"
                            class="w-full h-9 px-2 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        >
                            <option value="30 days">30 days (Free tier)</option>
                            <option value="90 days">90 days (Growth)</option>
                            <option value="180 days">180 days (Professional)</option>
                            <option value="365 days">365 days (Enterprise)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showAuditLogsModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitAuditLogs">Save Config</button>
                </div>
            </div>
        </div>

        <!-- Log Streams Modal -->
        <div v-if="showLogStreamsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showLogStreamsModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-gray-100">
                <h3 class="text-base font-semibold text-[#111827] mb-2">Configure Log Stream</h3>
                <p class="text-xs text-[#6b7280] mb-4">Stream all security events to a third-party SIEM or log analytics platform.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="log_destination" class="block text-xs font-semibold text-[#374151] mb-1.5">Destination Platform</label>
                        <select 
                            id="log_destination"
                            v-model="tempLogStreams.destination"
                            class="w-full h-9 px-2 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        >
                            <option value="">Select a platform...</option>
                            <option v-for="dest in availableDestinations" :key="dest" :value="dest">{{ dest }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="endpoint_url" class="block text-xs font-semibold text-[#374151] mb-1.5">Endpoint URL</label>
                        <input 
                            id="endpoint_url"
                            v-model="tempLogStreams.endpointUrl"
                            type="url"
                            placeholder="https://http-intake.logs.datadoghq.com/v1/input"
                            class="w-full h-9 px-3 text-sm border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#6366f1] text-[#111827]"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button class="h-9 px-4 border border-[#d1d5db] rounded-md text-xs font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors" @click="showLogStreamsModal = false">Cancel</button>
                    <button class="h-9 px-4 bg-[#6366f1] text-white rounded-md text-xs font-semibold hover:bg-[#4f46e5] transition-colors" @click="submitLogStreams">Save Stream</button>
                </div>
            </div>
        </div>

    </div>
</template>
