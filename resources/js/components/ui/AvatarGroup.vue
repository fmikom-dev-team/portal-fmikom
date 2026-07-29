<script setup lang="ts">
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

export interface AvatarItem {
	id?: number | string;
	src?: string | null;
	name: string;
	pagi_username?: string | null;
}

const props = withDefaults(
	defineProps<{
		avatars: AvatarItem[];
		maxVisible?: number;
		size?: number;
		overlap?: number;
	}>(),
	{
		maxVisible: 4,
		size: 28,
		overlap: 8,
	},
);

const hoveredIdx = ref<number | null>(null);

const profileHref = (item: AvatarItem) => {
	if (item.pagi_username) return `/pagi/${item.pagi_username}`;
	if (item.id) return `/pagi/profile/${item.id}`;
	return "#";
};
</script>

<template>
	<div class="flex items-center select-none">
		<div class="flex items-center">
			<div
				v-for="(item, idx) in avatars.slice(0, maxVisible)"
				:key="item.id || idx"
				class="relative transition-all duration-300 ease-out group/avatar cursor-pointer shrink-0"
				:style="{
					width: `${size}px`,
					height: `${size}px`,
					marginLeft: idx > 0 ? `-${overlap}px` : '0px',
					zIndex: hoveredIdx === idx ? 40 : avatars.length - idx,
					transform: hoveredIdx === idx ? 'translateY(-4px) scale(1.1)' : 'translateY(0) scale(1)',
				}"
				@mouseenter="hoveredIdx = idx"
				@mouseleave="hoveredIdx = null"
			>
				<Link :href="profileHref(item)" @click.stop class="block w-full h-full">
					<img
						v-if="item.src"
						:src="item.src"
						:alt="item.name"
						class="w-full h-full rounded-full object-cover border-2 border-white dark:border-zinc-900 shadow-xs"
					/>
					<div
						v-else
						class="w-full h-full rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 border-2 border-white dark:border-zinc-900 flex items-center justify-center text-white font-black shadow-xs"
						:style="{ fontSize: `${size * 0.36}px` }"
					>
						{{ (item.name || '?').charAt(0).toUpperCase() }}
					</div>
				</Link>

				<!-- Floating Tooltip Name -->
				<Transition
					enter-active-class="transition duration-200 ease-out"
					enter-from-class="opacity-0 translate-y-2 scale-90"
					enter-to-class="opacity-100 translate-y-0 scale-100"
					leave-active-class="transition duration-150 ease-in"
					leave-from-class="opacity-100 translate-y-0 scale-100"
					leave-to-class="opacity-0 translate-y-2 scale-90"
				>
					<div
						v-if="hoveredIdx === idx && item.name"
						class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 px-2 py-0.5 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-bold rounded-md shadow-xl whitespace-nowrap pointer-events-none z-50 tracking-tight"
					>
						{{ item.name }}
					</div>
				</Transition>
			</div>

			<!-- Extra count badge (+N) -->
			<div
				v-if="avatars.length > maxVisible"
				class="rounded-full bg-indigo-600 text-white font-black border-2 border-white dark:border-zinc-900 flex items-center justify-center shadow-xs shrink-0"
				:style="{
					width: `${size}px`,
					height: `${size}px`,
					marginLeft: `-${overlap}px`,
					fontSize: `${size * 0.35}px`,
					zIndex: 0,
				}"
			>
				+{{ avatars.length - maxVisible }}
			</div>
		</div>
	</div>
</template>
