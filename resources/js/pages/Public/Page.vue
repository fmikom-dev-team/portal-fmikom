<script setup>
/**
 * Public/Page.vue — Dynamic page renderer for /halaman/{slug}
 * Uses PublicLayout for consistent navbar + footer + hero.
 */

import {
	Calendar,
	CalendarDays,
	ChevronLeft,
	ChevronRight,
	Clock,
	ImageIcon,
	Link2,
	MapPin,
	Search,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import BlockRenderer from "@/components/editor/renderer/BlockRenderer.vue";
import { sanitizeRich } from "@/composables/useSanitize";
import PublicLayout from "@/layouts/PublicLayout.vue";

import PublicAcademicCalendar from "@/components/Portal/PublicAcademicCalendar.vue";

const props = defineProps({
	page: Object,
	academicCalendars: {
		type: Array,
		default: () => [],
	},
	events: {
		type: Array,
		default: () => [],
	},
});

const categoryLabel = {
	profil: "Profil",
	akademik: "Akademik",
	media: "Berita & Media",
	layanan: "Layanan",
};

const categoryHeroClass = {
	profil: "bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800",
	akademik: "bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800",
	media: "bg-gradient-to-br from-violet-600 via-purple-700 to-indigo-800",
	layanan: "bg-gradient-to-br from-orange-500 via-orange-600 to-amber-700",
};

const heroClass = computed(
	() =>
		categoryHeroClass[props.page.category] ||
		"bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800",
);
const catLabel = computed(() => categoryLabel[props.page.category] || "");

const parsedContent = computed(() => {
	if (!props.page.content) return null;
	try {
		const parsed = JSON.parse(props.page.content);
		if (parsed?.blocks) return parsed;
	} catch {}
	return null;
});

const breadcrumbs = computed(() => {
	const crumbs = [];
	if (catLabel.value) crumbs.push({ label: catLabel.value });
	crumbs.push({ label: props.page.title });
	return crumbs;
});

// ==========================================
// PUBLIC CAMPUS EVENTS LOGIC
// ==========================================
const eventSearch = ref("");
const filteredEvents = computed(() => {
	return props.events.filter((ev) => {
		if (eventSearch.value.trim() !== "") {
			const query = eventSearch.value.toLowerCase();
			return (
				ev.title?.toLowerCase().includes(query) ||
				ev.description?.toLowerCase().includes(query) ||
				ev.location?.toLowerCase().includes(query)
			);
		}
		return true;
	});
});

const getFormattedDate = (dateStr) => {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	return date.toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};
</script>

<template>
    <PublicLayout
        :title="page.title"
        :description="page.meta_description || page.excerpt"
        :breadcrumbs="breadcrumbs"
        :hero-title="page.title"
        :hero-subtitle="page.excerpt"
        :hero-class="heroClass"
        :hide-hero="page.template === 'full-width'"
        :hide-container="page.template === 'full-width'"
    >
        <!-- Dynamic Content: Academic Calendar -->
        <template v-if="page.slug === 'kalender-akademik'">
            <PublicAcademicCalendar :events="academicCalendars" />
        </template>

        <!-- Dynamic Content: Campus Events -->
        <template v-else-if="page.slug === 'agenda-event'">
            <div class="space-y-6">
                <!-- Search bar -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 md:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white">Daftar Acara & Kegiatan</h3>
                        <p class="text-xs font-semibold text-slate-450 mt-0.5">Temukan workshop, seminar, dan agenda seru FMIKOM.</p>
                    </div>

                    <div class="relative w-full sm:w-[300px]">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <Search class="w-4 h-4" />
                        </span>
                        <input 
                            v-model="eventSearch"
                            type="text" 
                            placeholder="Cari nama acara..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-slate-400 text-slate-700 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Grid list -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="ev in filteredEvents" 
                        :key="ev.id" 
                        class="group bg-white dark:bg-slate-800 rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="relative h-44 overflow-hidden bg-slate-50 dark:bg-slate-900 flex items-center justify-center">
                                <img 
                                    v-if="ev.thumbnail" 
                                    :src="ev.thumbnail" 
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                    :alt="ev.title"
                                >
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-700">
                                    <ImageIcon class="w-12 h-12 opacity-30 mb-2" />
                                    <span class="text-[10px] font-black uppercase text-slate-450">No Image</span>
                                </div>
                            </div>

                            <div class="p-5 space-y-3">
                                <h4 class="text-base font-black text-slate-800 dark:text-white leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ ev.title }}
                                </h4>

                                <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                    <div class="flex items-center gap-2">
                                        <Clock class="w-4 h-4 text-slate-400 shrink-0" />
                                        <span>{{ getFormattedDate(ev.start_time) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2" v-if="ev.location">
                                        <MapPin class="w-4 h-4 text-slate-400 shrink-0" />
                                        <span class="truncate">{{ ev.location }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-450 dark:text-slate-500 line-clamp-3 leading-relaxed mt-2 pt-2 border-t border-slate-50 dark:border-slate-700 font-medium">
                                    {{ ev.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Footer Link button -->
                        <div class="px-5 py-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/10 flex justify-end">
                            <a 
                                v-if="ev.registration_link" 
                                :href="ev.registration_link" 
                                target="_blank"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm active:scale-95"
                            >
                                <Link2 class="w-3.5 h-3.5" />
                                Daftar Sekarang
                            </a>
                            <span v-else class="text-[11px] font-bold text-slate-400">Pendaftaran ditutup/langsung</span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div 
                        v-if="filteredEvents.length === 0" 
                        class="col-span-full py-20 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700"
                    >
                        <Calendar class="w-8 h-8 text-slate-300 mx-auto mb-2 opacity-50" />
                        <p class="text-slate-800 dark:text-white text-sm font-bold">Tidak ada event terdaftar</p>
                    </div>
                </div>
            </div>
        </template>

        <!-- Default template for other normal pages -->
        <template v-else>
            <!-- If content is Editor.js JSON -->
            <template v-if="parsedContent">
                <article class="max-w-3xl mx-auto prose prose-slate prose-lg">
                    <BlockRenderer :content="parsedContent" />
                </article>
            </template>

            <!-- If content is plain HTML or text -->
            <template v-else-if="page.content">
                <div v-if="page.template === 'full-width'" class="w-full" v-html="sanitizeRich(page.content)"></div>
                <article v-else class="max-w-3xl mx-auto prose prose-slate prose-lg" v-html="sanitizeRich(page.content)"></article>
            </template>

            <!-- Empty page placeholder -->
            <template v-else>
                <div class="max-w-3xl mx-auto text-center py-24">
                    <div class="w-20 h-20 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-700 mb-3">Konten Sedang Disiapkan</h2>
                    <p class="text-slate-500 leading-relaxed">Halaman <strong>{{ page.title }}</strong> sedang dalam proses pengisian konten oleh tim kami. Silakan kunjungi kembali dalam waktu dekat.</p>
                    <a href="/" class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors">← Kembali ke Beranda</a>
                </div>
            </template>
        </template>
    </PublicLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
