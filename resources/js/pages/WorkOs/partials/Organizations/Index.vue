<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import { formatDate, toast, toSlug } from "../../composables/useWorkOs";

const props = defineProps<{
	modules: Array<any>;
	searchQuery?: string;
}>();

const emit = defineEmits<(e: "open-detail", module: any) => void>();

const modal = reactive({ createModule: false });
const moduleForm = reactive({ name: "", code: "", description: "" });
const loading = ref(false);

function autoModuleCode() {
	moduleForm.code = moduleForm.name
		.toUpperCase()
		.replace(/\s+/g, "")
		.replace(/[^A-Z0-9]/g, "")
		.substring(0, 10);
}

function resetForm() {
	Object.assign(moduleForm, { name: "", code: "", description: "" });
}

function submitCreateModule() {
	if (loading.value) return;
	loading.value = true;
	router.post(
		"/workos/modules",
		{ ...moduleForm },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.createModule = false;
				resetForm();
				toast("Organisasi berhasil dibuat.", "success");
			},
			onError: () => toast("Gagal membuat organisasi.", "error"),
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}
const filteredModules = computed(() => {
	if (!props.searchQuery) return props.modules;
	const q = props.searchQuery.toLowerCase();
	return props.modules.filter(
		(m) =>
			m.name?.toLowerCase().includes(q) ||
			m.code?.toLowerCase().includes(q)
	);
});
</script>

<template>
    <div class="p-6 md:p-8">
        <!-- Page header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-semibold text-gray-900 tracking-tight mb-1">Organizations</h1>
                <p class="text-[13px] text-gray-500">Kelola modul sistem dalam FMIKOM Portal.</p>
            </div>
            <button
                class="h-9 px-4 rounded-lg text-[13px] font-medium bg-gray-900 hover:bg-black text-white shadow-sm transition-colors flex items-center gap-1.5"
                @click="modal.createModule = true"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Create organization
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto shadow-sm">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Organizations</caption>
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Members</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-4 py-3 w-6" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredModules.length === 0">
                        <td colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                    </svg>
                                </div>
                                <p class="text-[13px] font-medium text-gray-600">No organizations yet</p>
                                <p class="text-[12px] text-gray-400">Create your first organization to get started.</p>
                                <button
                                    class="mt-1 h-8 px-4 rounded-lg text-[12px] font-medium bg-gray-900 text-white hover:bg-black transition-colors"
                                    @click="modal.createModule = true"
                                >
                                    Create organization
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr
                        v-for="m in filteredModules"
                        :key="m.id"
                        class="border-b border-gray-100 last:border-0 hover:bg-gray-50/80 cursor-pointer group transition-colors"
                        @click="emit('open-detail', m)"
                    >
                        <td class="px-4 py-3.5">
                            <p class="text-[13px] font-medium text-gray-900">{{ m.name }}</p>
                            <p v-if="m.description" class="text-[11px] text-gray-400 mt-0.5">{{ m.description }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded border border-gray-200 text-gray-700">
                                {{ m.code }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="flex items-center gap-1.5">
                                <span :class="['w-1.5 h-1.5 rounded-full', m.is_active ? 'bg-emerald-500' : 'bg-gray-300']" />
                                <span class="text-[12px] text-gray-700">{{ m.is_active ? 'Active' : 'Inactive' }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-[13px] text-gray-600 tabular-nums">
                            {{ m.users_count ?? 0 }}
                        </td>
                        <td class="px-4 py-3.5 text-[12px] text-gray-400">{{ formatDate(m.created_at) }}</td>
                        <td class="px-4 py-3.5">
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- CREATE MODAL -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="modal.createModule" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.createModule = false">
                    <Transition
                        enter-from-class="opacity-0 scale-95"
                        enter-active-class="transition-all duration-200 ease-out"
                        leave-to-class="opacity-0 scale-95"
                        leave-active-class="transition-all duration-150"
                    >
                        <div v-if="modal.createModule" class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">
                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-gray-900">Create organization</h2>
                                    <p class="text-[12px] text-gray-400 mt-0.5">Tambahkan modul sistem baru ke FMIKOM Portal.</p>
                                </div>
                                <button class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" @click="modal.createModule = false">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Organization name</label>
                                    <input
                                        v-model="moduleForm.name"
                                        type="text"
                                        placeholder="e.g. Portfolio and Gallery for Interns"
                                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all"
                                        @input="autoModuleCode"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">
                                        Code <span class="font-normal text-gray-400">(uppercase, no spaces)</span>
                                    </label>
                                    <div class="flex h-9 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all">
                                        <span class="px-3 text-[11px] font-mono text-gray-400 bg-gray-50 flex items-center border-r border-gray-200">code:</span>
                                        <input
                                            v-model="moduleForm.code"
                                            type="text"
                                            placeholder="PAGI"
                                            class="flex-1 px-3 text-[12px] font-mono focus:outline-none placeholder:text-gray-300 uppercase"
                                            style="text-transform:uppercase"
                                            @input="moduleForm.code = moduleForm.code.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">
                                        Description <span class="font-normal text-gray-400">(optional)</span>
                                    </label>
                                    <input
                                        v-model="moduleForm.description"
                                        type="text"
                                        placeholder="Short description of the module"
                                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all"
                                    />
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                                <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.createModule = false; resetForm()">
                                    Cancel
                                </button>
                                <button
                                    :disabled="!moduleForm.name || !moduleForm.code || loading"
                                    class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                                    @click="submitCreateModule"
                                >
                                    <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ loading ? 'Creating…' : 'Create organization' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
