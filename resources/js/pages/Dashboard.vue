<script setup lang="ts">
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { dashboard } from "@/routes";
import type { BreadcrumbItem } from "@/types";

const props = defineProps({
	accessList: {
		type: Array as () => Array<any>,
		default: () => [],
	},
});

const page = usePage();
const user = computed(
	() => page.props.auth?.user || { name: "User", email: "" },
);
const flash = computed(() => (page.props.flash || {}) as Record<string, any>);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: "Portal FMIKOM",
		href: dashboard(),
	},
];

// Data Modul dan SVG Ilustrasi
const allModules = [
	{
		code: "PAGI",
		name: "PAGI",
		fullName: "Portfolio & Gallery for Interns",
		desc: "Galeri portofolio interaktif mahasiswa FMIKOM.",
		image: "/illustrations/Online resume-amico.svg",
	},
	{
		code: "WIMS",
		name: "WIMS",
		fullName: "Web Information Management",
		desc: "Manajemen Informasi dan Magang FMIKOM.",
		image: "/illustrations/New employee-amico.svg",
	},
	{
		code: "FAST",
		name: "FAST",
		fullName: "Fmikom Academic System",
		desc: "Sistem Akademik FMIKOM.",
		image: "/illustrations/Recommendation letter-bro.svg",
	},
	{
		code: "TRACE",
		name: "TRACE",
		fullName: "Tracer Study System",
		desc: "Sistem pendataan alumni FMIKOM.",
		image: "/illustrations/Location tracking-bro.svg",
	},
];

// Mapping roles
const userRolesByModule = computed(() => {
	const map: Record<string, any[]> = {};
	props.accessList.forEach((access) => {
		if (!map[access.module.code]) {
			map[access.module.code] = [];
		}
		map[access.module.code].push(access.role);
	});
	return map;
});

// Loading state for skeletons
const isLoading = ref(true);

onMounted(() => {
	setTimeout(() => {
		isLoading.value = false;
	}, 800);
});

// State untuk Flip Card
const flippedModuleCode = ref<string | null>(null);
const selectedRoleSlug = ref<Record<string, string>>({}); // { 'FAST': 'dosen', 'WIMS': 'super-admin' }

const setInitialRole = (moduleCode: string) => {
	const roles = userRolesByModule.value[moduleCode];
	if (roles && roles.length > 0 && !selectedRoleSlug.value[moduleCode]) {
		selectedRoleSlug.value[moduleCode] = roles[0].slug;
	}
};

const flipCard = (moduleCode: string) => {
	// Cek akses
	if (
		!userRolesByModule.value[moduleCode] ||
		userRolesByModule.value[moduleCode].length === 0
	)
		return;

	// Set auto select role
	setInitialRole(moduleCode);

	if (flippedModuleCode.value === moduleCode) {
		flippedModuleCode.value = null; // Flip back
	} else {
		flippedModuleCode.value = moduleCode; // Flip open
	}
};

const enterSystem = (moduleCode: string) => {
	const roleSlug = selectedRoleSlug.value[moduleCode];
	if (!roleSlug) return;

	router.post("/select-module", {
		module_code: moduleCode,
		role_slug: roleSlug,
	});
};
</script>

<template>
    <Head title="Portal SSO" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Main Ambient Background -->
        <div class="relative flex min-h-[calc(100vh-4rem)] flex-1 flex-col overflow-hidden rounded-xl max-md:rounded-none p-4 sm:p-6 lg:p-10 bg-[#f8f9fc] max-md:bg-transparent dark:bg-zinc-950 max-md:dark:bg-transparent">
            <!-- Decorative Blobs Behind Content -->
            <div class="pointer-events-none absolute -left-40 top-0 h-96 w-96 rounded-full bg-[#2563EB]/10 blur-[100px]"></div>
            <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-[#B6FF00]/10 blur-[80px]"></div>
        
            <!-- LOADING SKELETON HEADER -->
            <div v-if="isLoading" class="relative z-10 mb-8 sm:mb-12 max-w-3xl animate-pulse">
                <div class="mb-3 flex items-center gap-2 sm:gap-3">
                    <div class="h-10 sm:h-12 lg:h-[3.25rem] w-48 bg-slate-200 dark:bg-zinc-800 rounded-xl"></div>
                </div>
                <div class="mb-4 sm:mb-5 space-y-2">
                    <div class="h-9 sm:h-11 lg:h-[3rem] w-[80%] max-w-lg bg-slate-200 dark:bg-zinc-800 rounded-xl"></div>
                    <div class="h-9 sm:h-11 lg:h-[3rem] w-[60%] max-w-md bg-slate-200 dark:bg-zinc-800 rounded-xl"></div>
                </div>
                <div class="space-y-1.5 mt-4">
                    <div class="h-4 w-[90%] max-w-xl bg-slate-200 dark:bg-zinc-800 rounded-lg"></div>
                    <div class="h-4 w-[50%] max-w-sm bg-slate-200 dark:bg-zinc-800 rounded-lg"></div>
                </div>
            </div>

            <!-- LOADED HEADER -->
            <div v-else class="relative z-10 mb-8 sm:mb-12 max-w-3xl">
                <!-- Title Row: Hi, Name! + Badges -->
                <div class="mb-1 flex items-center gap-2 sm:gap-3">
                    <h1 class="text-3xl sm:text-4xl lg:text-[3.25rem] font-bold tracking-tight text-[#0f172a] dark:text-white leading-none">
                        Hi, {{ user.name.split(' ')[0] }}!
                    </h1>
                </div>

                <!-- Big Subheading -->
                <h2 class="mb-4 sm:mb-5 text-3xl sm:text-4xl lg:text-[3.25rem] font-bold tracking-tight text-[#0f172a] dark:text-gray-200 leading-tight lg:leading-[1.1]">
                    What are your plans <br class="hidden sm:block" /> for today?
                </h2>

                <!-- Subtle Description text -->
                <p class="text-sm sm:text-base font-medium sm:font-bold text-[#64748b] dark:text-gray-400 lg:text-lg max-w-2xl leading-relaxed">
                    This platform is designed to revolutionize the way you organize and access your university modules.
                </p>
            </div>

            <!-- Pesan Info/Error Controller -->
            <div v-if="!isLoading && flash.status" class="relative z-10 rounded-xl bg-[#B6FF00]/20 p-4 mb-6 text-sm font-semibold text-green-900 border border-[#B6FF00]/50 backdrop-blur-md dark:text-[#B6FF00]">
                {{ flash.status }}
            </div>
            <div v-if="!isLoading && flash.error" class="relative z-10 rounded-xl bg-red-50 p-4 mb-6 text-sm font-semibold text-red-800 border border-red-200 backdrop-blur-md">
                {{ flash.error }}
            </div>

            <!-- GRID FLIP CARDS SKELETON -->
            <div v-if="isLoading" class="relative z-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4">
                <div 
                    v-for="i in 4" :key="i"
                    class="h-[380px] sm:h-[400px] w-full rounded-[1.5rem] bg-white p-2 shadow-xl ring-1 ring-black/5 dark:bg-zinc-900 dark:ring-white/10 flex flex-col justify-between animate-pulse"
                >
                    <!-- Top Header Box Skeleton -->
                    <div class="relative h-[220px] w-full bg-slate-50 dark:bg-zinc-800/40 rounded-[1rem] flex items-center justify-center border border-slate-100 dark:border-zinc-800">
                        <div class="h-28 w-28 rounded-full bg-slate-200/60 dark:bg-zinc-700/60"></div>
                        <div class="absolute left-4 top-4 h-6 w-28 bg-slate-200/80 dark:bg-zinc-700/80 rounded-full"></div>
                    </div>
                    <!-- Bottom Detail Skeleton -->
                    <div class="flex flex-1 flex-col p-4 pt-5">
                        <div class="h-6 w-20 bg-slate-200 dark:bg-zinc-800 rounded-lg mb-3"></div>
                        <div class="space-y-2 mt-2">
                            <div class="h-3 w-full bg-slate-200 dark:bg-zinc-800 rounded-md"></div>
                            <div class="h-3 w-[85%] bg-slate-200 dark:bg-zinc-800 rounded-md"></div>
                        </div>
                        <div class="mt-auto pt-6">
                            <div class="h-[48px] w-full bg-slate-200 dark:bg-zinc-800 rounded-xl"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRID FLIP CARDS -->
            <div v-else class="relative z-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4">
                
                <div 
                    v-for="mod in allModules" :key="mod.code"
                    class="perspective-1000 h-[380px] sm:h-[400px] w-full"
                >
                    <!-- Wrapper Penahan 3D Transform -->
                    <div 
                        class="preserve-3d relative h-full w-full rounded-[1.5rem] transition-transform duration-[600ms] ease-[cubic-bezier(0.23,1,0.32,1)]"
                        :class="flippedModuleCode === mod.code ? 'rotate-y-180' : ''"
                    >
                    
                        <!-- =========================
                             DEPANG (FRONT LAYER 1) 
                             ========================= -->
                        <div 
                            class="backface-hidden absolute inset-0 flex flex-col overflow-hidden rounded-[1.5rem] bg-white p-2 shadow-xl ring-1 ring-black/5 dark:bg-zinc-900 dark:ring-white/10"
                            :class="(!userRolesByModule[mod.code] || userRolesByModule[mod.code].length === 0) ? 'opacity-80 grayscale cursor-not-allowed' : 'cursor-pointer hover:-translate-y-2 hover:shadow-2xl hover:ring-[#2563EB]/50 transition-all duration-300'"
                            @click="(!userRolesByModule[mod.code] || userRolesByModule[mod.code].length === 0) ? null : flipCard(mod.code)"
                        >
                            <!-- Top Header Box -->
                            <div class="relative h-[220px] w-full overflow-hidden rounded-[1rem] bg-slate-50 dark:bg-white/5 border border-white/40 dark:border-white/10 shadow-inner">
                                <!-- Subtle Glow Base under SVG -->
                                <div class="absolute inset-0 flex items-center justify-center p-6">
                                    <div class="absolute h-40 w-40 rounded-full bg-[#2563EB]/10 blur-3xl transition-all duration-500"></div>
                                    <img :src="mod.image" :alt="mod.name" class="relative z-10 max-h-[140px] max-w-[80%] object-contain transition-transform duration-500 hover:scale-105" />
                                </div>
                                <!-- Status Overlay -->
                                <div class="absolute left-4 top-4 z-20">
                                    <span v-if="userRolesByModule[mod.code] && userRolesByModule[mod.code].length > 0" class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-[#2563EB] shadow-sm backdrop-blur-md">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                        Akses Tersedia
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-zinc-800/80 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm backdrop-blur-md">
                                        🔒 Terkunci
                                    </span>
                                </div>
                            </div>
                            <!-- Bottom Detail -->
                            <div class="flex flex-1 flex-col p-4 pt-5">
                                <h2 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent dark:from-white dark:to-gray-400">{{ mod.name }}</h2>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2 line-clamp-3">{{ mod.desc }}</p>
                                <!-- Action Button (Dummy, action on card click) -->
                                <div class="mt-auto pt-6">
                                    <button 
                                        :disabled="!userRolesByModule[mod.code] || userRolesByModule[mod.code].length === 0"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2563EB] py-3.5 text-sm font-bold text-white shadow-lg shadow-[#2563EB]/25 transition-all hover:bg-blue-700 disabled:bg-gray-200 disabled:shadow-none dark:disabled:bg-zinc-800 dark:disabled:text-zinc-600"
                                    >
                                        Akses {{ mod.name }} <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- =========================
                             BELAKANG (BACK LAYER 2) 
                             ========================= -->
                        <div 
                            class="backface-hidden rotate-y-180 absolute inset-0 flex flex-col overflow-hidden rounded-[1.5rem] bg-white p-6 shadow-xl ring-1 ring-black/5 dark:bg-zinc-900 dark:ring-white/10"
                        >
                            <!-- Profile / Top Area -->
                            <div class="flex shrink-0 items-center gap-3 border-b border-gray-100 pb-4 dark:border-zinc-800">
                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800 shadow-inner overflow-hidden border border-gray-200 dark:border-zinc-700">
                                    <!-- Initial Username Avatar -->
                                    <span class="text-[22px] font-black text-[#2563EB]">{{ user.name.charAt(0) }}</span>
                                </div>
                                <div class="flex flex-1 flex-col overflow-hidden">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate text-base font-bold leading-tight text-gray-900 dark:text-white">
                                            {{ user.name }}
                                        </h3>
                                        <!-- Verified Badge -->
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded bg-green-50 px-1.5 py-0.5 text-[9px] font-bold text-green-600 border border-green-200 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-400">
                                            <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                            Verified
                                        </span>
                                    </div>
                                    <p class="mt-0.5 truncate text-[13px] font-medium text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>

                            <!-- List Peranan (Middle Content) -->
                            <div class="my-3 flex-1 overflow-y-auto pr-1">
                                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Pilih Otorisasi Akses</h4>
                                
                                <div class="space-y-2.5">
                                    <label 
                                        v-for="(role, idx) in userRolesByModule[mod.code]" :key="idx"
                                        class="flex cursor-pointer items-center justify-between rounded-xl border p-3 transition-all duration-200"
                                        :class="selectedRoleSlug[mod.code] === role.slug 
                                            ? 'border-[#2563EB] bg-[#2563EB]/5 shadow-sm ring-1 ring-[#2563EB]/30' 
                                            : 'border-gray-100/80 bg-gray-50/50 hover:bg-[#2563EB]/5 hover:border-[#2563EB]/40 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:bg-[#2563EB]/10 border-transparent'"
                                    >
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <!-- Custom Radio -->
                                            <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-colors" 
                                                :class="selectedRoleSlug[mod.code] === role.slug ? 'border-[#2563EB]' : 'border-gray-300 dark:border-gray-600'">
                                                <div class="h-2 w-2 rounded-full bg-[#2563EB] transition-transform" :class="selectedRoleSlug[mod.code] === role.slug ? 'scale-100' : 'scale-0'"></div>
                                            </div>
                                            
                                            <!-- Role Info -->
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="truncate text-sm font-bold text-gray-900 dark:text-gray-100">{{ role.nama || role.name || role.slug }}</span>
                                                <span class="truncate text-[10px] text-gray-500">Konteks: {{ mod.name }}</span>
                                            </div>
                                        </div>
                                        
                                        <input type="radio" :value="role.slug" v-model="selectedRoleSlug[mod.code]" class="hidden" />
                                    </label>
                                </div>
                            </div>

                            <!-- Bottom Action Buttons -->
                            <div class="mt-auto flex shrink-0 items-center gap-2 pt-2">
                                <button 
                                    @click="flipCard(mod.code)"
                                    class="flex w-[35%] shrink-0 items-center justify-center rounded-xl bg-gray-100 py-3 text-xs sm:text-[13px] font-bold text-gray-700 transition hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-300 dark:hover:bg-zinc-700"
                                >
                                    Batal
                                </button>
                                <button 
                                    @click="enterSystem(mod.code)"
                                    class="flex flex-1 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-xl bg-[#B6FF00] py-3 text-xs sm:text-[13px] font-extrabold text-black shadow-[0_4px_12px_-4px_rgba(182,255,0,0.4)] transition-transform hover:scale-[1.02]"
                                >
                                    Masuk Sistem
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </button>
                            </div>

                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* CSS Pendukung Untuk 3D Flip Card Effect */
.perspective-1000 {
    perspective: 1000px;
}
.preserve-3d {
    transform-style: preserve-3d;
}
.backface-hidden {
    backface-visibility: hidden;
}
.rotate-y-180 {
    transform: rotateY(180deg);
}
</style>
