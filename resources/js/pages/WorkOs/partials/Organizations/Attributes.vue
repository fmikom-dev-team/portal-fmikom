<script setup lang="ts">
import { reactive, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

// biome-ignore lint/suspicious/noExplicitAny: config
defineProps<{ organization?: any }>();

const isCustomized = ref(false);

const visibility = reactive({
	directorySync: true,
	sso: true,
});

const defaultMappings = [
	{ target: "email", source: "profile.email", isCustom: false },
	{ target: "firstName", source: "profile.firstName", isCustom: false },
	{ target: "lastName", source: "profile.lastName", isCustom: false },
	{ target: "groups", source: "profile.groups", isCustom: false },
];

const mappings = ref<
	Array<{ target: string; source: string; isCustom: boolean }>
>(defaultMappings.map((m) => ({ ...m })));

const showAddModal = ref(false);
const newMapping = reactive({
	target: "",
	source: "",
});

function handleCustomize() {
	isCustomized.value = true;
}

function openAddModal() {
	newMapping.target = "";
	newMapping.source = "";
	showAddModal.value = true;
}

function addMapping() {
	if (!newMapping.target || !newMapping.source) return;
	mappings.value.push({
		target: newMapping.target,
		source: newMapping.source,
		isCustom: true,
	});
	showAddModal.value = false;
	toast("Custom mapping added.", "success");
}

function removeMapping(index: number) {
	mappings.value.splice(index, 1);
	toast("Mapping removed.", "success");
}

function saveChanges() {
	toast("Attribute mappings saved successfully.", "success");
}

function resetToDefault() {
	mappings.value = defaultMappings.map((m) => ({ ...m }));
	visibility.directorySync = true;
	visibility.sso = true;
	isCustomized.value = false;
	toast("Reset to environment defaults.", "success");
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Custom attribute mapping card -->
        <div class="rounded-xl bg-white p-5 ring-1 ring-gray-900/[0.04]">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1.5">
                        <p class="text-[14px] font-semibold text-[#111827]">Custom attribute mapping</p>
                        <span v-if="!isCustomized" class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f3f4f6] text-[#4b5563] border border-[#e5e7eb]">
                            Environment Default
                        </span>
                        <span v-else class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Customized
                        </span>
                    </div>
                    <p class="text-[12.5px] text-[#6b7280] leading-relaxed mb-4">
                        Allows IT contacts to define custom attribute mappings. When customized, these mappings apply only to this organization instead of the environment default.
                    </p>

                    <!-- Visibility Configuration -->
                    <div class="grid grid-cols-[120px_1fr] items-center gap-y-3 text-[13px] border-t border-[#f3f4f6] pt-4 mb-4">
                        <span class="text-[#6b7280] font-medium">Visibility</span>
                        <div class="flex items-center gap-4">
                            <label for="directory_sync" class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    id="directory_sync"
                                    v-model="visibility.directorySync" 
                                    type="checkbox" 
                                    :disabled="!isCustomized"
                                    class="w-4 h-4 text-indigo-600 border-[#d1d5db] rounded focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                                />
                                <span class="text-[#374151] select-none">Directory Sync</span>
                            </label>
                            <label for="sso_visibility" class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    id="sso_visibility"
                                    v-model="visibility.sso" 
                                    type="checkbox" 
                                    :disabled="!isCustomized"
                                    class="w-4 h-4 text-indigo-600 border-[#d1d5db] rounded focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                                />
                                <span class="text-[#374151] select-none">Single Sign-On</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Enabled status -->
                <div class="shrink-0 flex items-center gap-1.5 text-[13px] font-medium text-[#10b981]">
                    <span class="w-2 h-2 rounded-full bg-[#10b981]"></span>
                    Active
                </div>
            </div>

            <!-- Customization Trigger -->
            <div v-if="!isCustomized" class="mt-2 border-t border-[#f3f4f6] pt-4">
                <button
                    class="h-[32px] px-3.5 border border-[#d1d5db] rounded-md text-[13px] font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors bg-white shadow-sm"
                    @click="handleCustomize"
                >
                    Customize for this organization
                </button>
            </div>
        </div>

        <!-- Mappings Table Section -->
        <div class="rounded-lg bg-white overflow-hidden ring-1 ring-gray-900/[0.04]">
            <div class="px-5 py-4 border-b border-[#e5e7eb] flex items-center justify-between">
                <div>
                    <h3 class="text-[14px] font-semibold text-[#111827]">Attribute Mappings</h3>
                    <p class="text-[12px] text-[#6b7280] mt-0.5">Map target attributes to source profile fields.</p>
                </div>
                <button 
                    v-if="isCustomized"
                    class="h-[30px] px-3 bg-indigo-600 text-white rounded-md text-[12px] font-semibold hover:bg-indigo-700 transition-colors shadow-sm"
                    @click="openAddModal"
                >
                    Add mapping
                </button>
            </div>
            
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151]">WorkOS Target Attribute</th>
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151]">IdP Source Attribute</th>
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151] w-28 text-center">Type</th>
                        <th v-if="isCustomized" class="px-5 py-3 text-[12px] font-semibold text-[#374151] w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb]">
                    <tr v-for="(mapping, idx) in mappings" :key="idx" class="hover:bg-[#f9fafb] transition-colors group">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-[12.5px] text-[#111827] bg-slate-50 px-1.5 py-0.5 border border-slate-200 rounded">
                                {{ mapping.target }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-[12.5px] text-[#4b5563]">
                                {{ mapping.source }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span :class="['inline-block px-2 py-0.5 rounded text-[11px] font-semibold', mapping.isCustom ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200']">
                                {{ mapping.isCustom ? 'Custom' : 'System' }}
                            </span>
                        </td>
                        <td v-if="isCustomized" class="px-5 py-3.5 text-right">
                            <button 
                                class="p-1 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                                @click="removeMapping(idx)"
                                title="Remove Mapping"
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

        <!-- Sticky Form Actions (Only in customization mode) -->
        <div v-if="isCustomized" class="flex items-center justify-between border-t border-[#e5e7eb] pt-4 mt-6">
            <button
                class="h-[34px] px-3.5 border border-red-200 text-red-700 rounded-md text-[13px] font-semibold hover:bg-red-50 transition-colors bg-white"
                @click="resetToDefault"
            >
                Reset to Default
            </button>
            <div class="flex items-center gap-2">
                <button
                    class="h-[34px] px-3.5 border border-[#d1d5db] rounded-md text-[13px] font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors bg-white shadow-sm"
                    @click="isCustomized = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm"
                    @click="saveChanges"
                >
                    Save changes
                </button>
            </div>
        </div>

        <!-- Add Mapping Modal -->
        <AppModal
            :show="showAddModal"
            title="Add attribute mapping"
            @close="showAddModal = false"
        >
            <template #description>
                Define a new attribute mapping for sync payloads.
            </template>
            
            <div class="space-y-4">
                <div>
                    <label for="target_attribute" class="block text-[13px] font-semibold text-[#374151] mb-1.5">WorkOS Target Attribute</label>
                    <input 
                        id="target_attribute"
                        v-model="newMapping.target"
                        type="text"
                        placeholder="e.g. employeeId, title, department"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-[#111827] placeholder:text-slate-400 bg-white"
                    />
                </div>
                <div>
                    <label for="source_attribute" class="block text-[13px] font-semibold text-[#374151] mb-1.5">IdP Source Attribute</label>
                    <input 
                        id="source_attribute"
                        v-model="newMapping.source"
                        type="text"
                        placeholder="e.g. profile.employeeId, profile.title"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-[#111827] placeholder:text-slate-400 bg-white"
                    />
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                    @click="showAddModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50"
                    :disabled="!newMapping.target || !newMapping.source"
                    @click="addMapping"
                >
                    Add mapping
                </button>
            </template>
        </AppModal>
    </div>
</template>
