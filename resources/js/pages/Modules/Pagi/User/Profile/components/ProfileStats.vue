<script setup lang="ts">
const props = withDefaults(
	defineProps<{
		projectCount: number;
		totalLikes: number;
		followersCount: number;
		followingCount: number;
		isLoading: boolean;
		isStudent?: boolean;
	}>(),
	{
		isStudent: true,
	},
);

const emit = defineEmits(["click-work", "click-followers", "click-following"]);
</script>

<template>
	<div 
		class="w-full rounded-[20px] border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 px-5 py-4 grid gap-2 text-center select-none shadow-2xs"
		:class="isStudent ? 'grid-cols-4 max-w-sm' : 'grid-cols-2 max-w-xs'"
	>
		<!-- Works Stat -->
		<div v-if="isStudent" @click="emit('click-work')" class="cursor-pointer group/stat py-1" role="button" aria-label="Lihat Karya">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ projectCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Karya</span>
		</div>

		<!-- Likes Stat -->
		<div v-if="isStudent" class="border-l border-slate-200/60 dark:border-slate-800/60 py-1">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none">{{ totalLikes }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider">Suka</span>
		</div>

		<!-- Followers Stat -->
		<div @click="emit('click-followers')" class="cursor-pointer group/stat py-1" :class="{ 'border-l border-slate-200/60 dark:border-slate-800/60': isStudent }" role="button" aria-label="Lihat Pengikut">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ followersCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Pengikut</span>
		</div>

		<!-- Following Stat -->
		<div @click="emit('click-following')" class="border-l border-slate-200/60 dark:border-slate-800/60 cursor-pointer group/stat py-1" role="button" aria-label="Lihat Mengikuti">
			<span v-if="isLoading" class="block h-5 w-8 mx-auto bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-1"></span>
			<span v-else class="block text-lg font-black text-slate-900 dark:text-white leading-none group-hover/stat:text-indigo-600 dark:group-hover/stat:text-indigo-400 transition-colors">{{ followingCount }}</span>
			<span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider group-hover/stat:text-indigo-500 dark:group-hover/stat:text-indigo-400 transition-colors">Mengikuti</span>
		</div>
	</div>
</template>
