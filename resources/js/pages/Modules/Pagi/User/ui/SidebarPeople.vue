<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import {
	ChevronLeft,
	ChevronRight,
	ExternalLink,
	MapPin,
	Send,
	X,
} from "lucide-vue-next";
import { computed } from "vue";
import OptimizedImage from "./OptimizedImage.vue";
import VideoLazy from "./VideoLazy.vue";

export interface PersonItem {
	id: number;
	name: string;
	pagi_username?: string | null;
	loc: string;
	prodi: string;
	avatar: string;
	banner?: string | null;
	skills: string[];
	appr: string;
	fol: string;
	proj: number;
	imgs: string[];
	coverGrad: string;
}

const props = defineProps<{
	person: PersonItem | null;
	currentIndex: number;
	total: number;
}>();

const emit = defineEmits<{
	(e: "close"): void;
	(e: "navigate", dir: number): void;
}>();

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const ext = url.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov"].includes(ext || "");
};

// Cover: profile banner first → fallback to gradient (portfolio images used separately in grid)
const coverSrc = computed(() => {
	if (!props.person) return null;
	if (props.person.banner) return props.person.banner;
	return null;
});

const profileHref = computed(() => {
	if (!props.person) return "#";
	return props.person.pagi_username
		? `/pagi/${props.person.pagi_username}`
		: `/pagi/profile/${props.person.id}`;
});

const startChat = () => {
	if (!props.person) return;
	router.visit(`/pagi/messages?chat=${props.person.id}`);
};

const page = usePage();
const isSelf = computed(() => {
	return (
		props.person &&
		page.props.auth?.user &&
		props.person.id === page.props.auth.user.id
	);
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="person"
                class="fixed inset-0 z-[60] bg-black/55 backdrop-blur-[2px]"
                @click.self="$emit('close')"
            />
        </Transition>

        <!-- Floating Panel -->
        <Transition
            enter-active-class="transition ease-out duration-250"
            enter-from-class="opacity-0 translate-x-8 scale-[0.97]"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition ease-in duration-180"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-8 scale-[0.97]"
        >
            <div
                v-if="person"
                class="fixed top-4 right-4 bottom-4 z-[61] w-[380px] max-w-[calc(100vw-2rem)] flex flex-col rounded-2xl bg-white dark:bg-zinc-950 shadow-2xl shadow-black/30 overflow-hidden"
            >
                <!-- ── COVER + AVATAR SECTION (no overflow-hidden so avatar is visible) ── -->
                <div class="relative shrink-0">

                    <!-- Cover banner -->
                    <div class="w-full h-[148px] overflow-hidden rounded-t-2xl">
                        <template v-if="coverSrc">
                            <VideoLazy
                                v-if="isVideoUrl(coverSrc)"
                                :src="coverSrc"
                                :autoplay="true"
                                :loop="true"
                                :muted="true"
                                :playsinline="true"
                                className="w-full h-full object-cover"
                            />
                            <OptimizedImage
                                v-else
                                :src="coverSrc"
                                className="w-full h-full object-cover"
                                alt="Cover"
                            />
                            <div class="absolute inset-0 h-[148px] bg-gradient-to-t from-black/40 via-black/10 to-black/20 rounded-t-2xl pointer-events-none" />
                        </template>
                        <template v-else>
                            <!-- Gradient fallback -->
                            <div :class="['w-full h-full bg-gradient-to-br', person.coverGrad]" />
                            <div class="absolute inset-0 h-[148px] bg-[radial-gradient(rgba(255,255,255,0.07)_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none" />
                        </template>
                    </div>

                    <!-- Close button -->
                    <button
                        @click="$emit('close')"
                        class="absolute top-3 right-3 z-20 flex h-7 w-7 items-center justify-center rounded-full bg-black/35 text-white hover:bg-black/60 transition-colors backdrop-blur-sm"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>

                    <!-- Avatar — absolutely positioned at bottom-center of cover, half overlapping -->
                    <div class="absolute left-1/2 -translate-x-1/2 z-20" style="bottom: -36px;">
                        <div class="h-[72px] w-[72px] rounded-full bg-white dark:bg-zinc-950 p-[3px] shadow-lg">
                            <div class="h-full w-full rounded-full overflow-hidden">
                                <OptimizedImage
                                    :src="person.avatar"
                                    className="h-full w-full object-cover"
                                    alt="Avatar"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── SCROLLABLE CONTENT ── -->
                <!-- pt-12 to leave space for the overlapping avatar (36px half-overlap + 12px gap) -->
                <div class="flex-1 overflow-y-auto pt-12" style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">

                    <!-- Name + username + location -->
                    <div class="text-center px-5 mb-3">
                        <div class="flex items-center justify-center gap-1.5 mb-0.5">
                            <h2 class="text-[16px] font-black text-slate-900 dark:text-zinc-100 leading-tight tracking-tight">
                                {{ person.name }}
                            </h2>
                            <img src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Akun Terverifikasi" alt="Verified" />
                        </div>
                        <span v-if="person.pagi_username" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5">
                            @{{ person.pagi_username }}
                        </span>
                        <div class="flex items-center justify-center gap-1 text-[11px] text-slate-400 dark:text-zinc-500">
                            <MapPin class="h-2.5 w-2.5 shrink-0" />
                            <span>{{ person.loc }}</span>
                        </div>
                        <!-- Prodi role pill -->
                        <span class="inline-block mt-2 px-3 py-0.5 rounded-full bg-slate-100 dark:bg-zinc-800 text-[10px] font-semibold text-slate-500 dark:text-zinc-400">
                            {{ person.prodi }}
                        </span>
                    </div>

                    <!-- Skills tags -->
                    <div v-if="person.skills && person.skills.length > 0"
                        class="flex items-center justify-center flex-wrap gap-1.5 px-5 mb-4">
                        <span
                            v-for="skill in person.skills"
                            :key="skill"
                            class="rounded-full border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-300 px-2.5 py-0.5 text-[11px] font-semibold tracking-tight"
                        >
                            {{ skill }}
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 px-5 mb-4">
                        <button
                            v-if="!isSelf"
                            @click="startChat"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-[#1769ff] py-2.5 text-[13px] font-bold text-white hover:bg-blue-700 active:scale-95 transition-all shadow-sm shadow-blue-200 dark:shadow-blue-900/30"
                        >
                            <Send class="h-3.5 w-3.5" /> Send Inquiry
                        </button>
                        <Link
                            :href="profileHref"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 dark:border-zinc-700 py-2.5 text-[13px] font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-900 active:scale-95 transition-all"
                        >
                            <ExternalLink class="h-3.5 w-3.5" /> View Profile
                        </Link>
                    </div>

                    <!-- Portfolio Grid — ALL works in 2-col grid -->
                    <div v-if="person.imgs && person.imgs.length > 0" class="px-5 pb-3">
                        <p class="text-[9px] font-black text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-2">Latest Work</p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div
                                v-for="(img, i) in person.imgs"
                                :key="i"
                                class="aspect-video overflow-hidden rounded-lg cursor-pointer group bg-slate-100 dark:bg-zinc-900"
                            >
                                <VideoLazy
                                    v-if="isVideoUrl(img)"
                                    :src="img"
                                    :autoplay="true"
                                    :loop="true"
                                    :muted="true"
                                    :playsinline="true"
                                    className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none"
                                />
                                <OptimizedImage
                                    v-else
                                    :src="img"
                                    className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    alt="Project cover"
                                />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── BOTTOM NAV — prev/next ── -->
                <div class="shrink-0 flex items-center justify-center gap-3 border-t border-slate-100 dark:border-zinc-800 py-3 px-5 bg-white dark:bg-zinc-950 rounded-b-2xl">
                    <button
                        @click="$emit('navigate', -1)"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 hover:border-[#1769ff] hover:text-[#1769ff] transition-colors"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </button>
                    <span class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 tabular-nums select-none">
                        {{ currentIndex + 1 }} / {{ total }}
                    </span>
                    <button
                        @click="$emit('navigate', 1)"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 hover:border-[#1769ff] hover:text-[#1769ff] transition-colors"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
