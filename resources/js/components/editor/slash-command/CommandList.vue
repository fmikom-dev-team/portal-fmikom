<script setup>
import * as icons from "lucide-vue-next";
import { computed, ref, watch } from "vue";

const props = defineProps({
	items: {
		type: Array,
		required: true,
	},
	command: {
		type: Function,
		required: true,
	},
});

const selectedIndex = ref(0);

watch(
	() => props.items,
	() => {
		selectedIndex.value = 0;
	},
);

// Group items by category
const groupedItems = computed(() => {
	const groups = {};
	props.items.forEach((item, index) => {
		if (!groups[item.group]) {
			groups[item.group] = [];
		}
		groups[item.group].push({ ...item, originalIndex: index });
	});
	return groups;
});

const onKeyDown = ({ event }) => {
	if (event.key === "ArrowUp") {
		upHandler();
		return true;
	}

	if (event.key === "ArrowDown") {
		downHandler();
		return true;
	}

	if (event.key === "Enter") {
		enterHandler();
		return true;
	}

	return false;
};

const upHandler = () => {
	selectedIndex.value =
		(selectedIndex.value + props.items.length - 1) % props.items.length;
	scrollToSelected();
};

const downHandler = () => {
	selectedIndex.value = (selectedIndex.value + 1) % props.items.length;
	scrollToSelected();
};

const enterHandler = () => {
	selectItem(selectedIndex.value);
};

const selectItem = (index) => {
	const item = props.items[index];
	if (item) {
		props.command(item);
	}
};

const scrollToSelected = () => {
	const el = document.getElementById(`slash-cmd-${selectedIndex.value}`);
	if (el) {
		el.scrollIntoView({ block: "nearest", behavior: "smooth" });
	}
};

defineExpose({
	onKeyDown,
});
</script>

<template>
    <div class="bg-slate-900/95 backdrop-blur-xl border border-white/10 shadow-[0_8px_30px_rgb(0,0,0,0.2)] rounded-xl overflow-hidden py-2 min-w-[320px] max-w-[360px] text-white">
        <div v-if="items.length" class="max-h-[380px] overflow-y-auto px-2 custom-scrollbar">
            <template v-for="(groupItems, groupName) in groupedItems" :key="groupName">
                <div class="px-3 py-1.5 mt-2 mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest first:mt-0 sticky top-0 bg-slate-900/95 backdrop-blur-md z-10">{{ groupName }}</div>
                <div class="space-y-0.5">
                    <button
                        v-for="item in groupItems"
                        :id="`slash-cmd-${item.originalIndex}`"
                        :key="item.originalIndex"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left transition-all duration-150 group"
                        :class="{ 'bg-blue-600': item.originalIndex === selectedIndex, 'hover:bg-white/5': item.originalIndex !== selectedIndex }"
                        @click="selectItem(item.originalIndex)"
                        @mouseover="selectedIndex = item.originalIndex"
                    >
                        <div 
                            class="w-10 h-10 rounded-md flex items-center justify-center shrink-0 transition-colors"
                            :class="{ 'bg-white text-blue-600': item.originalIndex === selectedIndex, 'bg-white/10 text-slate-300': item.originalIndex !== selectedIndex }"
                        >
                            <component :is="icons[item.icon]" class="w-5 h-5" />
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <div class="text-[13px] font-medium" :class="{ 'text-white': item.originalIndex === selectedIndex, 'text-slate-200': item.originalIndex !== selectedIndex }">{{ item.title }}</div>
                            <div class="text-[11px] truncate" :class="{ 'text-blue-100': item.originalIndex === selectedIndex, 'text-slate-400': item.originalIndex !== selectedIndex }">{{ item.description }}</div>
                        </div>
                    </button>
                </div>
            </template>
        </div>
        <div v-else class="px-4 py-6 text-center">
            <p class="text-sm font-medium text-slate-400">No matching commands</p>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
