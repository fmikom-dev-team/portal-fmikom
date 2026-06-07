<script setup lang="ts">
defineProps<{
	projectCount: number;
	totalLikes: number;
	followersCount: number;
	followingCount: number;
	isLoading: boolean;
}>();

const emit = defineEmits(["click-work", "click-followers", "click-following"]);
</script>

<template>
	<div class="w-full max-w-sm rounded-[20px] border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 px-5 py-4 grid grid-cols-4 gap-2 text-center select-none shadow-2xs">
		<!-- Works Stat -->
		<div @click="emit('click-work')" class="cursor-pointer group/stat py-1">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ projectCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Karya</span>
		</div>

		<!-- Likes Stat -->
		<div class="border-l border-slate-200/60 dark:border-slate-800/60 py-1">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none">{{ totalLikes }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider">Suka</span>
		</div>

		<!-- Followers Stat -->
		<div @click="emit('click-followers')" class="border-l border-slate-200/60 dark:border-slate-800/60 cursor-pointer group/stat py-1">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ followersCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Pengikut</span>
		</div>

		<!-- Following Stat -->
		<div @click="emit('click-following')" class="border-l border-slate-200/60 dark:border-slate-800/60 cursor-pointer group/stat py-1">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ followingCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Mengikuti</span>
		</div>
	</div>
</template>
