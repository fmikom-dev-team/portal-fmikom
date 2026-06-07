<script setup lang="ts">
import { Check, Copy, Link2, Share2, X } from "lucide-vue-next";
import { computed, onUnmounted, ref, watch } from "vue";

export interface ShareProject {
	id: number;
	title: string;
	image?: string | null;
	cover_fit?: string;
}

export interface ShareUser {
	name: string;
	foto_path?: string | null;
	pagi_username?: string | null;
}

const props = defineProps<{
	show: boolean;
	project: ShareProject | null;
	user: ShareUser;
	shareUrl?: string;
}>();

const emit = defineEmits<{
	(e: "close"): void;
	(e: "shareToFeed"): void;
}>();

const copied = ref(false);

const isVideoUrl = (url: string | null | undefined): boolean => {
	if (!url) return false;
	const ext = url.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov"].includes(ext || "");
};

const activeUrl = computed(() => {
	return props.shareUrl || window.location.href;
});

const copyLink = async () => {
	try {
		await navigator.clipboard.writeText(activeUrl.value);
		copied.value = true;
		setTimeout(() => {
			copied.value = false;
		}, 2000);
	} catch {}
};

const whatsAppUrl = computed(() => {
	const text = props.project
		? `Lihat karya "${props.project.title}" oleh ${props.user.name} di FMIKOM Portal: `
		: `Lihat portofolio kreatif ${props.user.name} di FMIKOM Portal: `;
	return `https://api.whatsapp.com/send?text=${encodeURIComponent(text + activeUrl.value)}`;
});

const telegramUrl = computed(() => {
	const text = props.project
		? `Lihat karya "${props.project.title}" oleh ${props.user.name} di FMIKOM Portal`
		: `Lihat portofolio kreatif ${props.user.name} di FMIKOM Portal`;
	return `https://t.me/share/url?url=${encodeURIComponent(activeUrl.value)}&text=${encodeURIComponent(text)}`;
});

const linkedInUrl = computed(
	() =>
		`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(activeUrl.value)}`,
);

const twitterUrl = computed(
	() =>
		`https://twitter.com/intent/tweet?text=${encodeURIComponent(`Saya baru saja memposting karya baru di FMIKOM Portal: ${props.project?.title || ""}`)}`,
);

// Body scroll lock — single instance, no counter needed
watch(
	() => props.show,
	(val) => {
		document.body.style.overflow = val ? "hidden" : "";
	},
	{ immediate: true },
);

onUnmounted(() => {
	document.body.style.overflow = "";
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[20000] bg-black/40 backdrop-blur-[3px]"
                @click.self="$emit('close')"
            />
        </Transition>

        <!-- Modal Panel -->
        <Transition
            enter-active-class="transition ease-out duration-250"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-180"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[20001] flex items-center justify-center p-4 pointer-events-none"
            >
                <div class="w-full max-w-[420px] bg-white dark:bg-zinc-950 rounded-3xl shadow-2xl shadow-black/20 pointer-events-auto overflow-hidden">

                    <!-- Header: avatar + name + project title + close -->
                    <div class="flex items-start gap-3 px-5 pt-5 pb-4">
                        <!-- Avatar -->
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 shrink-0 border border-slate-200/60">
                            <img
                                v-if="user.foto_path"
                                :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path"
                                class="w-full h-full object-cover"
                                alt="Avatar"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-600 font-bold text-sm">
                                {{ user.name?.charAt(0)?.toUpperCase() }}
                            </div>
                        </div>

                        <!-- Name + meta -->
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-black text-slate-900 dark:text-white leading-tight">
                                {{ user.name }}
                                <span class="font-normal text-slate-400 dark:text-zinc-500"> · now</span>
                            </p>
                            <p v-if="project" class="text-[12px] text-slate-500 dark:text-zinc-400 font-semibold truncate mt-0.5">
                                {{ project.title }}
                            </p>
                        </div>

                        <!-- Close button -->
                        <button
                            @click="$emit('close')"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-colors shrink-0 -mt-0.5 -mr-1"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Project thumbnail -->
                    <div
                        v-if="project?.image"
                        class="mx-5 mb-5 rounded-2xl overflow-hidden bg-slate-100 dark:bg-zinc-900 aspect-[16/9] relative"
                    >
                        <video
                            v-if="isVideoUrl(project.image)"
                            :src="project.image"
                            autoplay muted loop playsinline
                            class="w-full h-full object-cover"
                        />
                        <img
                            v-else
                            :src="project.image"
                            :class="['w-full h-full', project.cover_fit === 'contain' ? 'object-contain' : 'object-cover']"
                            alt="Project"
                        />
                        <!-- Subtle gradient overlay at bottom -->
                        <div class="absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/20 to-transparent pointer-events-none" />
                    </div>

                    <!-- Divider + text + actions -->
                    <div class="px-5 pb-6">
                        <div class="flex items-center gap-2 mb-1.5">
                            <Share2 class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" />
                            <h3 class="text-[18px] font-black text-slate-900 dark:text-white tracking-tight">
                                Share your work
                            </h3>
                        </div>
                        <p class="text-[12px] text-slate-400 dark:text-zinc-500 font-semibold mb-5">
                            Show off what you've built with your community.
                        </p>

                        <!-- Actions row -->
                        <div class="flex items-center gap-2">
                            <!-- Copy link (Prominent Style) -->
                            <button
                                @click="copyLink"
                                class="flex-1 h-11 px-4 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-800 dark:text-zinc-200 font-bold text-[13px] flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer shadow-3xs"
                            >
                                <Check v-if="copied" class="w-4 h-4 text-emerald-500 shrink-0" />
                                <Link2 v-else class="w-4 h-4 text-slate-500 dark:text-zinc-400 shrink-0" />
                                <span>{{ copied ? 'Copied!' : 'Copy Link' }}</span>
                            </button>

                            <!-- WhatsApp -->
                            <a
                                :href="whatsAppUrl"
                                target="_blank"
                                @click="$emit('close')"
                                title="Share on WhatsApp"
                                class="w-11 h-11 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 text-slate-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 flex items-center justify-center transition-all active:scale-95 shrink-0"
                            >
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.457L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.115-2.905-6.99C16.54 1.879 14.07 .846 11.432 .846c-5.437 0-9.862 4.422-9.865 9.867-.001 1.748.475 3.454 1.378 4.965l-.94 3.432 3.527-.925c1.474.805 3.09 1.229 4.625 1.235zM17.487 14.39c-.3-.15-1.782-.88-2.062-.982-.28-.102-.483-.15-.688.15-.204.3-.792.98-.97 1.18-.178.2-.355.22-.655.07-1.353-.679-2.22-1.168-3.09-2.67-.23-.396.23-.367.658-1.22.07-.15.035-.28-.017-.38-.052-.1-.483-1.16-.662-1.59-.175-.42-.35-.36-.483-.37-.123-.007-.265-.008-.405-.008-.14 0-.368.05-.56.26-.192.21-.734.72-.734 1.75s.75 2.03 1.85 2.18c1.1.15 4.3 3.32 6.51 4.2 1.39.55 2.01.4 2.72.22.8-.2 1.782-.88 2.032-1.682.25-.8.25-1.48.175-1.62-.075-.15-.275-.24-.575-.39z"/>
                                </svg>
                            </a>

                            <!-- Telegram -->
                            <a
                                :href="telegramUrl"
                                target="_blank"
                                @click="$emit('close')"
                                title="Share on Telegram"
                                class="w-11 h-11 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 hover:bg-sky-50 dark:hover:bg-sky-950/20 text-slate-500 hover:text-sky-500 dark:text-zinc-400 dark:hover:text-sky-400 flex items-center justify-center transition-all active:scale-95 shrink-0"
                            >
                                <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.47-.52-.17L7.71 13.3l-4.1-1.28c-.89-.28-.91-.89.19-1.33l16.03-6.18c.74-.27 1.39.17 1.13 1.3L18.2 19.1c-.22 1.05-.85 1.31-1.73.81l-4.2-3.1-2.03 1.95c-.22.22-.4.4-.81.4z"/>
                                </svg>
                            </a>

                            <!-- LinkedIn -->
                            <a
                                :href="linkedInUrl"
                                target="_blank"
                                @click="$emit('close')"
                                title="Share on LinkedIn"
                                class="w-11 h-11 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 hover:bg-blue-50 dark:hover:bg-blue-950/20 text-slate-500 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400 flex items-center justify-center transition-all active:scale-95 shrink-0"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>

                            <!-- Twitter / X -->
                            <a
                                :href="twitterUrl"
                                target="_blank"
                                @click="$emit('close')"
                                title="Share on X"
                                class="w-11 h-11 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-500 hover:text-black dark:hover:text-white flex items-center justify-center transition-all active:scale-95 shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>
