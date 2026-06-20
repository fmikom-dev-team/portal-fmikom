<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
	ExternalLink,
	FileText,
	LogOut,
	User as UserIcon,
} from "lucide-vue-next";

defineProps<{
	show: boolean;
	user: any;
	currentRoleSlug: string;
	computedRoleName: string;
	align: "left" | "right";
}>();

const emit = defineEmits<{
	(e: "logout"): void;
	(e: "close"): void;
}>();

const handleLogout = () => {
	emit("logout");
};
</script>

<template>
	<Transition
		enter-active-class="transition ease-out duration-250"
		enter-from-class="opacity-0 translate-y-2 scale-95"
		enter-to-class="opacity-100 translate-y-0 scale-100"
		leave-active-class="transition ease-in duration-200"
		leave-from-class="opacity-100 translate-y-0 scale-100"
		leave-to-class="opacity-0 translate-y-2 scale-95"
	>
		<div
			v-show="show"
			class="absolute mt-2.5 w-72 rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl shadow-[0_24px_50px_-12px_rgba(0,0,0,0.18)] p-4 select-none z-50"
			:class="align === 'left' ? 'left-0' : 'right-0'"
		>
			<!-- Cover Banner -->
			<div class="h-20 w-full relative rounded-xl overflow-hidden bg-gradient-to-r from-[#6366f1] via-[#a855f7] to-[#ec4899] bg-cover bg-center shadow-xs">
				<template v-if="user.banner_path && user.banner_path !== 'null'">
					<video
						v-if="user.banner_path.endsWith('.mp4') || user.banner_path.endsWith('.webm') || user.banner_path.endsWith('.ogg')"
						:src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)"
						class="w-full h-full object-cover"
						autoplay
						loop
						muted
						playsinline
					></video>
					<img v-else :src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)" alt="Banner Profil" class="w-full h-full object-cover" />
				</template>
				<template v-else>
					<div class="absolute inset-0 opacity-90"></div>
					<div class="absolute -top-6 -left-6 w-16 h-16 rounded-full bg-cyan-300/30 blur-md"></div>
					<div class="absolute -bottom-6 -right-6 w-16 h-16 rounded-full bg-yellow-300/20 blur-md"></div>
				</template>
			</div>

			<!-- Profile Info Area -->
			<div class="relative z-10 flex flex-col items-center -mt-10 mb-3 px-2">
				<div class="h-20 w-20 rounded-full border-[3px] border-white dark:border-zinc-950 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shadow-md mb-2 relative z-20">
					<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
					<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
					<span v-else class="text-slate-700 dark:text-slate-200 text-xl font-bold">{{ user.name.charAt(0) }}</span>
				</div>
				<h3 class="text-base font-black text-slate-800 dark:text-zinc-150 text-center truncate w-full px-2 tracking-tight leading-snug uppercase">{{ user.name }}</h3>
				<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold text-center mt-0.5 truncate w-full px-2 leading-none">@{{ user.pagi_username || 'username' }}</p>
			</div>

			<!-- Stats Grid -->
			<div v-if="currentRoleSlug === 'mahasiswa'" class="w-full grid grid-cols-3 rounded-2xl border border-slate-100/85 dark:border-zinc-900 bg-slate-50/50 dark:bg-zinc-900/35 py-3.5 mb-4 mt-3 select-none">
				<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
					<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.works_count ?? 0 }}</span>
					<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Karya</span>
				</div>
				<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
					<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.certificates_count ?? 2 }}</span>
					<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Sertifikat</span>
				</div>
				<div class="text-center">
					<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.followers_count ?? (user.metadata?.followers?.length ?? 0) }}</span>
					<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Pengikut</span>
				</div>
			</div>

			<!-- Action Menu Items -->
			<div class="space-y-1.5">
				<Link v-if="currentRoleSlug === 'mahasiswa'" href="/pagi/profile" class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-950 hover:bg-indigo-600 dark:bg-slate-50 dark:text-slate-950 dark:hover:bg-indigo-500 dark:hover:text-white py-2.5 text-xs font-black text-white transition-all shadow-md active:scale-97 cursor-pointer">
					<UserIcon class="h-3.5 w-3.5 text-slate-450" /> Lihat Profil PAGI
				</Link>

				<Link v-if="currentRoleSlug === 'mahasiswa' || currentRoleSlug === 'alumni'" href="/pagi/cv" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
					<FileText class="h-3.5 w-3.5 text-slate-450" /> CV Builder
				</Link>

				<Link href="/dashboard" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
					<ExternalLink class="h-3.5 w-3.5 text-slate-450" /> Kembali ke Portal
				</Link>

				<button @click="handleLogout" class="w-full flex items-center justify-center gap-2 rounded-xl border border-red-200/60 dark:border-red-950/40 hover:bg-red-50 dark:hover:bg-red-950/20 py-2.5 text-xs font-bold text-red-600 dark:text-red-400 transition-all active:scale-97 cursor-pointer">
					<LogOut class="h-3.5 w-3.5" /> Keluar (Logout)
				</button>
			</div>
		</div>
	</Transition>
</template>
