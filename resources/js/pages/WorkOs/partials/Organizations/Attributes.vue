<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { reactive, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{ organization?: any }>();

const isCustomized = ref(false);
const isSubmitting = ref(false);

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

function initAttributesData() {
	const data = props.organization?.settings_data || {};
	isCustomized.value = data.attribute_customized || false;
	
	if (data.attribute_visibility) {
		visibility.directorySync = data.attribute_visibility.directorySync ?? true;
		visibility.sso = data.attribute_visibility.sso ?? true;
	} else {
		visibility.directorySync = true;
		visibility.sso = true;
	}
	
	if (data.attribute_mappings) {
		mappings.value = data.attribute_mappings.map((m: any) => ({ ...m }));
	} else {
		mappings.value = defaultMappings.map((m) => ({ ...m }));
	}
}

watch(
	() => props.organization,
	() => {
		initAttributesData();
	},
	{ deep: true, immediate: true }
);

function saveAttributesPayload(payload: any, successMsg: string) {
	isSubmitting.value = true;
	router.post(
		`/workos/modules/${props.organization.id}/settings-data`,
		payload,
		{
			preserveScroll: true,
			onSuccess: () => {
				toast(successMsg, "success");
			},
			onError: () => toast("Gagal memperbarui pemetaan atribut.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		}
	);
}

const showAddModal = ref(false);
const newMapping = reactive({
	target: "",
	source: "",
});

function handleCustomize() {
	isCustomized.value = true;
	saveAttributesPayload({
		attribute_customized: true,
		attribute_visibility: { ...visibility },
		attribute_mappings: mappings.value
	}, "Kustomisasi diaktifkan.");
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

// Validation helper to avoid illegal targets or sources
const attributeRegex = /^[a-zA-Z0-9_-]+$/;
const sourceRegex = /^[a-zA-Z0-9_.-]+$/;

function saveChanges() {
	// Sanitize custom mappings
	for (const m of mappings.value) {
		if (m.isCustom) {
			if (!attributeRegex.test(m.target) || !sourceRegex.test(m.source)) {
				toast("Format pemetaan atribut tidak valid.", "error");
				return;
			}
		}
	}
	
	saveAttributesPayload({
		attribute_customized: true,
		attribute_visibility: { ...visibility },
		attribute_mappings: mappings.value
	}, "Pemetaan atribut berhasil disimpan.");
}

function resetToDefault() {
	mappings.value = defaultMappings.map((m) => ({ ...m }));
	visibility.directorySync = true;
	visibility.sso = true;
	isCustomized.value = false;
	saveAttributesPayload({
		attribute_customized: false,
		attribute_visibility: { directorySync: true, sso: true },
		attribute_mappings: defaultMappings.map((m) => ({ ...m }))
	}, "Kembali ke default lingkungan.");
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Custom attribute mapping card -->
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="flex-1 w-full">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <p class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Custom attribute mapping</p>
                        <span v-if="!isCustomized" class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#f3f4f6] dark:bg-zinc-800 text-[#4b5563] dark:text-zinc-400 border border-[#e5e7eb] dark:border-zinc-700 whitespace-nowrap">
                            Environment Default
                        </span>
                        <span v-else class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 whitespace-nowrap">
                            Customized
                        </span>
                    </div>
                    <p class="text-[12.5px] text-[#6b7280] dark:text-zinc-400 leading-relaxed mb-4">
                        Allows IT contacts to define custom attribute mappings. When customized, these mappings apply only to this organization instead of the environment default.
                    </p>

                    <!-- Visibility Configuration -->
                    <div class="grid grid-cols-1 sm:grid-cols-[120px_1fr] items-start sm:items-center gap-y-3 text-[13px] border-t border-[#f3f4f6] dark:border-zinc-800 pt-4 mb-4">
                        <span class="text-[#6b7280] dark:text-zinc-500 font-medium whitespace-nowrap">Visibility</span>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <label for="directory_sync" class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    id="directory_sync"
                                    v-model="visibility.directorySync" 
                                    type="checkbox" 
                                    :disabled="!isCustomized"
                                    class="w-4 h-4 text-blue-600 border-[#d1d5db] dark:border-zinc-700 rounded focus:ring-[#2563eb] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                                />
                                <span class="text-[#374151] dark:text-zinc-300 select-none whitespace-nowrap">Directory Sync</span>
                            </label>
                            <label for="sso_visibility" class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    id="sso_visibility"
                                    v-model="visibility.sso" 
                                    type="checkbox" 
                                    :disabled="!isCustomized"
                                    class="w-4 h-4 text-blue-600 border-[#d1d5db] dark:border-zinc-700 rounded focus:ring-[#2563eb] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                                />
                                <span class="text-[#374151] dark:text-zinc-300 select-none whitespace-nowrap">Single Sign-On</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Enabled status -->
                <div class="shrink-0 flex items-center gap-1.5 text-[13px] font-medium text-[#10b981] whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-[#10b981]"></span>
                    Active
                </div>
            </div>

            <!-- Customization Trigger -->
            <div v-if="!isCustomized" class="mt-2 border-t border-[#f3f4f6] dark:border-zinc-800 pt-4">
                <button
                    class="h-[32px] px-3.5 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none w-full sm:w-auto text-center justify-center flex items-center cursor-pointer whitespace-nowrap"
                    @click="handleCustomize"
                >
                    Customize for this organization
                </button>
            </div>
        </div>

        <!-- Mappings Table Section -->
        <div class="rounded-lg bg-white dark:bg-zinc-900 overflow-x-auto ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <div class="px-5 py-4 border-b border-[#e5e7eb] dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Attribute Mappings</h3>
                    <p class="text-[12px] text-[#6b7280] dark:text-zinc-500 mt-0.5">Map target attributes to source profile fields.</p>
                </div>
                <button 
                    v-if="isCustomized"
                    class="h-[30px] px-3 bg-blue-600 text-white rounded-md text-[12px] font-semibold hover:bg-blue-700 transition-colors shadow-sm dark:shadow-none w-full sm:w-auto text-center justify-center flex items-center cursor-pointer whitespace-nowrap"
                    @click="openAddModal"
                >
                    Add mapping
                </button>
            </div>
            
            <table class="w-full text-left border-collapse">
                <caption class="sr-only">Attribute Mappings</caption>
                <thead>
                    <tr class="bg-[#f9fafb] dark:bg-zinc-800/60 border-b border-[#e5e7eb] dark:border-zinc-800">
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151] dark:text-zinc-300">WorkOS Target Attribute</th>
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151] dark:text-zinc-300">IdP Source Attribute</th>
                        <th class="px-5 py-3 text-[12px] font-semibold text-[#374151] dark:text-zinc-300 w-28 text-center">Type</th>
                        <th v-if="isCustomized" class="px-5 py-3 text-[12px] font-semibold text-[#374151] dark:text-zinc-300 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb] dark:divide-zinc-800">
                    <tr v-for="(mapping, idx) in mappings" :key="idx" class="hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 transition-colors group">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-[12.5px] text-[#111827] dark:text-zinc-100 bg-slate-50 dark:bg-zinc-800 px-1.5 py-0.5 border border-slate-200 dark:border-zinc-700 rounded">
                                {{ mapping.target }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-[12.5px] text-[#4b5563] dark:text-zinc-300">
                                {{ mapping.source }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span :class="['inline-block px-2 py-0.5 rounded text-[11px] font-semibold', mapping.isCustom ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/40' : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700']">
                                {{ mapping.isCustom ? 'Custom' : 'System' }}
                            </span>
                        </td>
                        <td v-if="isCustomized" class="px-5 py-3.5 text-right">
                            <button 
                                class="p-1 rounded text-slate-400 dark:text-zinc-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors opacity-0 group-hover:opacity-100"
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
        <div v-if="isCustomized" class="flex items-center justify-between border-t border-[#e5e7eb] dark:border-zinc-800 pt-4 mt-6">
            <button
                class="h-[34px] px-3.5 border border-red-200 dark:border-red-950/50 text-red-700 dark:text-red-400 rounded-md text-[13px] font-semibold hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors bg-white dark:bg-zinc-900"
                @click="resetToDefault"
            >
                Reset to Default
            </button>
            <div class="flex items-center gap-2">
                <button
                    class="h-[34px] px-3.5 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none"
                    @click="isCustomized = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm dark:shadow-none"
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
                    <label for="target_attribute" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">WorkOS Target Attribute</label>
                    <input 
                        id="target_attribute"
                        v-model="newMapping.target"
                        type="text"
                        placeholder="e.g. employeeId, title, department"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900"
                    />
                </div>
                <div>
                    <label for="source_attribute" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">IdP Source Attribute</label>
                    <input 
                        id="source_attribute"
                        v-model="newMapping.source"
                        type="text"
                        placeholder="e.g. profile.employeeId, profile.title"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900"
                    />
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none"
                    @click="showAddModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 dark:shadow-none"
                    :disabled="!newMapping.target || !newMapping.source"
                    @click="addMapping"
                >
                    Add mapping
                </button>
            </template>
        </AppModal>
    </div>
</template>
