<script setup>
import { computed } from "vue";

const props = defineProps({ data: Object });

const file = computed(() => props.data?.file || {});
const ext = computed(
	() =>
		file.value.extension ||
		file.value.name?.split(".").pop()?.toLowerCase() ||
		"",
);

const iconMap = {
	pdf: { icon: "📄", color: "text-red-500 bg-red-50 border-red-100" },
	doc: { icon: "📝", color: "text-blue-500 bg-blue-50 border-blue-100" },
	docx: { icon: "📝", color: "text-blue-500 bg-blue-50 border-blue-100" },
	xls: { icon: "📊", color: "text-green-600 bg-green-50 border-green-100" },
	xlsx: { icon: "📊", color: "text-green-600 bg-green-50 border-green-100" },
	ppt: { icon: "📑", color: "text-orange-500 bg-orange-50 border-orange-100" },
	pptx: { icon: "📑", color: "text-orange-500 bg-orange-50 border-orange-100" },
	zip: { icon: "🗜️", color: "text-purple-500 bg-purple-50 border-purple-100" },
	txt: { icon: "📃", color: "text-gray-500 bg-gray-50 border-gray-100" },
	csv: { icon: "📈", color: "text-green-600 bg-green-50 border-green-100" },
};
const meta = computed(
	() =>
		iconMap[ext.value] || {
			icon: "📎",
			color: "text-gray-500 bg-gray-50 border-gray-100",
		},
);

const formatSize = (bytes) => {
	if (!bytes) return "";
	if (bytes < 1024) return `${bytes} B`;
	if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
	return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
</script>

<template>
    <a v-if="file.url"
       :href="file.url"
       :download="file.name"
       target="_blank"
       rel="noopener noreferrer"
       class="flex items-center gap-4 my-5 p-4 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50/40 rounded-2xl transition-all duration-150 group no-underline shadow-sm hover:shadow-md">

        <!-- Icon -->
        <div :class="['w-12 h-12 rounded-xl border flex items-center justify-center text-2xl flex-shrink-0', meta.color]">
            {{ meta.icon }}
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-700 transition-colors">
                {{ file.name || data.title || 'File Lampiran' }}
            </p>
            <div class="flex items-center gap-3 mt-0.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase">{{ ext }}</span>
                <span v-if="file.size" class="text-[11px] text-gray-400">{{ formatSize(file.size) }}</span>
            </div>
        </div>

        <!-- Download arrow -->
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 group-hover:bg-blue-600 flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-hover:text-white transition-colors"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </div>
    </a>
    <div v-else class="my-5 p-4 bg-gray-50 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-400 text-center">
        File lampiran tidak tersedia
    </div>
</template>
