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
// ACADEMIC CALENDAR LOGIC (Public Read-Only)
// ==========================================
const currentDate = ref(new Date());
const currentMonth = ref(currentDate.value.getMonth());
const currentYear = ref(currentDate.value.getFullYear());

const monthNames = [
	"Januari",
	"Februari",
	"Maret",
	"April",
	"Mei",
	"Juni",
	"Juli",
	"Agustus",
	"September",
	"Oktober",
	"November",
	"Desember",
];

const categoryLabels = {
	akademik: "Akademik",
	kegiatan: "Kegiatan / Acara",
	libur: "Libur Nasional",
	ujian: "Ujian / Asesmen",
	registrasi: "Registrasi & KRS",
};

const categoryColors = {
	akademik: {
		bg: "bg-blue-500",
		pill: "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400",
	},
	kegiatan: {
		bg: "bg-green-500",
		pill: "bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400",
	},
	libur: {
		bg: "bg-red-500",
		pill: "bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400",
	},
	ujian: {
		bg: "bg-purple-500",
		pill: "bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400",
	},
	registrasi: {
		bg: "bg-amber-500",
		pill: "bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
	},
};

const firstDayIndex = computed(() =>
	new Date(currentYear.value, currentMonth.value, 1).getDay(),
);
const daysInMonth = computed(() =>
	new Date(currentYear.value, currentMonth.value + 1, 0).getDate(),
);
const daysInPrevMonth = computed(() =>
	new Date(currentYear.value, currentMonth.value, 0).getDate(),
);

const calendarCells = computed(() => {
	const cells = [];
	// Prev month overlap
	for (let i = firstDayIndex.value - 1; i >= 0; i--) {
		cells.push({
			day: daysInPrevMonth.value - i,
			currentMonth: false,
			dateString: formatDateString(
				currentYear.value,
				currentMonth.value - 1,
				daysInPrevMonth.value - i,
			),
		});
	}
	// Current month
	for (let i = 1; i <= daysInMonth.value; i++) {
		cells.push({
			day: i,
			currentMonth: true,
			dateString: formatDateString(currentYear.value, currentMonth.value, i),
		});
	}
	// Next month overlap
	const totalCells = cells.length;
	const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
	for (let i = 1; i <= remaining; i++) {
		cells.push({
			day: i,
			currentMonth: false,
			dateString: formatDateString(
				currentYear.value,
				currentMonth.value + 1,
				i,
			),
		});
	}
	return cells;
});

const formatDateString = (year, month, day) => {
	const date = new Date(year, month, day);
	const y = date.getFullYear();
	const m = String(date.getMonth() + 1).padStart(2, "0");
	const d = String(date.getDate()).padStart(2, "0");
	return `${y}-${m}-${d}`;
};

const getEventsForDay = (dateString) => {
	return props.academicCalendars.filter((e) => {
		if (!e.start_date) return false;
		const start = e.start_date;
		const end = e.end_date || start;
		return dateString >= start && dateString <= end;
	});
};

const prevMonth = () => {
	if (currentMonth.value === 0) {
		currentMonth.value = 11;
		currentYear.value--;
	} else {
		currentMonth.value--;
	}
};

const nextMonth = () => {
	if (currentMonth.value === 11) {
		currentMonth.value = 0;
		currentYear.value++;
	} else {
		currentMonth.value++;
	}
};

const calendarViewMode = ref("calendar"); // calendar, list
const calendarFilter = ref("all");

const filteredCalendarList = computed(() => {
	let list = props.academicCalendars;
	if (calendarFilter.value !== "all") {
		list = list.filter((e) => e.category === calendarFilter.value);
	}
	return list;
});

// Selected day details for popover/modal
const selectedDayEvents = ref([]);
const selectedDayDate = ref("");
const isDayModalOpen = ref(false);

const handleDayClick = (cell) => {
	const dayEvents = getEventsForDay(cell.dateString);
	if (dayEvents.length > 0) {
		selectedDayEvents.value = dayEvents;
		selectedDayDate.value = cell.dateString;
		isDayModalOpen.value = true;
	}
};

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
            <div class="space-y-6">
                <!-- Academic Calendar Controls -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 md:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    
                    <!-- Month Selector -->
                    <div class="flex items-center gap-3">
                        <button @click="prevMonth" class="w-10 h-10 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                            <ChevronLeft class="w-5 h-5" />
                        </button>
                        <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-white min-w-[160px] text-center uppercase tracking-wider">
                            {{ monthNames[currentMonth] }} {{ currentYear }}
                        </h3>
                        <button @click="nextMonth" class="w-10 h-10 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                            <ChevronRight class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- View Toggle & Category Dropdown -->
                    <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                        <div class="bg-slate-50 dark:bg-slate-900 p-1 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center">
                            <button 
                                @click="calendarViewMode = 'calendar'" 
                                :class="[calendarViewMode === 'calendar' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-4 py-2 rounded-xl text-xs font-bold transition-all']"
                            >
                                Kalender
                            </button>
                            <button 
                                @click="calendarViewMode = 'list'" 
                                :class="[calendarViewMode === 'list' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-4 py-2 rounded-xl text-xs font-bold transition-all']"
                            >
                                Daftar Agenda
                            </button>
                        </div>

                        <!-- Dropdown filter for list mode -->
                        <select 
                            v-if="calendarViewMode === 'list'"
                            v-model="calendarFilter"
                            class="bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-white rounded-2xl px-4 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="all">Semua Kategori</option>
                            <option value="akademik">Akademik</option>
                            <option value="kegiatan">Kegiatan</option>
                            <option value="libur">Libur Nasional</option>
                            <option value="ujian">Ujian</option>
                            <option value="registrasi">Registrasi</option>
                        </select>
                    </div>
                </div>

                <!-- Calendar Grid Layout -->
                <div v-if="calendarViewMode === 'calendar'" class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
                    <!-- Day Labels -->
                    <div class="grid grid-cols-7 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                        <div v-for="dayName in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="dayName" class="py-3 text-center text-xs font-black text-slate-400 uppercase tracking-widest">
                            {{ dayName }}
                        </div>
                    </div>

                    <!-- Month Days -->
                    <div class="grid grid-cols-7 auto-rows-[100px] md:auto-rows-[120px]">
                        <div 
                            v-for="(cell, idx) in calendarCells" 
                            :key="idx" 
                            @click="handleDayClick(cell)"
                            :class="[
                                cell.currentMonth ? 'bg-white dark:bg-slate-800' : 'bg-slate-50/50 dark:bg-slate-900/10 text-slate-300 dark:text-slate-650',
                                'border-r border-b border-slate-100 dark:border-slate-800 p-2 flex flex-col justify-between group hover:bg-slate-50/30 dark:hover:bg-slate-900/30 transition-all cursor-pointer overflow-hidden'
                            ]"
                        >
                            <span :class="[cell.currentMonth ? 'text-slate-700 dark:text-slate-300 font-bold' : 'text-slate-300 dark:text-slate-650', 'text-xs']">
                                {{ cell.day }}
                            </span>

                            <!-- Day Events -->
                            <div class="mt-2 space-y-1 overflow-y-auto max-h-[60px] md:max-h-[80px] scrollbar-none">
                                <div 
                                    v-for="ev in getEventsForDay(cell.dateString)" 
                                    :key="ev.id"
                                    :class="[
                                        categoryColors[ev.category]?.bg || 'bg-blue-500',
                                        'text-white text-[9px] font-black rounded px-1.5 py-0.5 truncate'
                                    ]"
                                    :title="ev.title"
                                >
                                    {{ ev.title }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar List Layout -->
                <div v-else class="space-y-4">
                    <div 
                        v-for="ev in filteredCalendarList" 
                        :key="ev.id" 
                        class="group bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    >
                        <div class="flex items-start gap-4">
                            <div :class="[categoryColors[ev.category]?.pill || 'bg-blue-50 text-blue-700', 'w-12 h-12 rounded-2xl flex flex-col items-center justify-center shrink-0 border border-slate-100 dark:border-slate-800']">
                                <CalendarDays class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span :class="[categoryColors[ev.category]?.pill || 'bg-blue-50 text-blue-700', 'px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider']">
                                        {{ categoryLabels[ev.category] || ev.category }}
                                    </span>
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px] font-bold">
                                        {{ ev.start_date === ev.end_date ? new Date(ev.start_date).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) : `${new Date(ev.start_date).toLocaleDateString('id-ID', { day:'numeric', month:'short' })} - ${new Date(ev.end_date).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' })}` }}
                                    </span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors">
                                    {{ ev.title }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed font-medium">
                                    {{ ev.description || 'Tidak ada deskripsi detail.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div 
                        v-if="filteredCalendarList.length === 0" 
                        class="py-20 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700"
                    >
                        <CalendarDays class="w-8 h-8 text-slate-300 mx-auto mb-2 opacity-50" />
                        <p class="text-slate-800 dark:text-white text-sm font-bold">Tidak ada agenda akademik saat ini</p>
                    </div>
                </div>
            </div>

            <!-- Read Only Detail Day Popover Modal -->
            <transition name="fade">
                <div 
                    v-if="isDayModalOpen" 
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    @click.self="isDayModalOpen = false"
                >
                    <div class="bg-white dark:bg-slate-800 rounded-[28px] border border-slate-100 dark:border-slate-700 shadow-2xl w-full max-w-md overflow-hidden p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                            <h3 class="text-base font-black text-slate-800 dark:text-white">
                                Agenda Tanggal: {{ new Date(selectedDayDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                            </h3>
                            <button @click="isDayModalOpen = false" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-1">
                            <div 
                                v-for="ev in selectedDayEvents" 
                                :key="ev.id"
                                class="p-4 rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20"
                            >
                                <span :class="[categoryColors[ev.category]?.pill || 'bg-blue-50 text-blue-700', 'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider mb-2 inline-block']">
                                    {{ categoryLabels[ev.category] || ev.category }}
                                </span>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">{{ ev.title }}</h4>
                                <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ ev.description || 'Tidak ada deskripsi detail.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
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
