<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	AlignLeft,
	Calendar,
	CalendarDays,
	Check,
	ChevronLeft,
	ChevronRight,
	Edit,
	Info,
	ListFilter,
	MapPin,
	Plus,
	Sparkles,
	Trash2,
	X,
} from "lucide-vue-next";
import { computed, type PropType, ref } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";
import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue";

interface CalendarEvent {
	id: number;
	title: string;
	description?: string | null;
	start_date: string;
	end_date?: string | null;
	category: string;
	color?: string;
}

const props = defineProps({
	events: {
		type: Array as PropType<CalendarEvent[]>,
		default: () => [],
	},
});

// Month & Year controls
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

const categoryLabels: Record<string, string> = {
	akademik: "Akademik",
	kegiatan: "Kegiatan / Acara",
	libur: "Libur Nasional",
	ujian: "Ujian / Asesmen",
	registrasi: "Registrasi & KRS",
};

const categoryColors: Record<
	string,
	{ bg: string; border: string; text: string; pill: string }
> = {
	akademik: {
		bg: "bg-blue-500",
		border: "border-blue-200",
		text: "text-blue-600",
		pill: "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400",
	},
	kegiatan: {
		bg: "bg-green-500",
		border: "border-green-200",
		text: "text-green-600",
		pill: "bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400",
	},
	libur: {
		bg: "bg-red-500",
		border: "border-red-200",
		text: "text-red-600",
		pill: "bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400",
	},
	ujian: {
		bg: "bg-purple-500",
		border: "border-purple-200",
		text: "text-purple-600",
		pill: "bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400",
	},
	registrasi: {
		bg: "bg-amber-500",
		border: "border-amber-200",
		text: "text-amber-600",
		pill: "bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
	},
};

// Calendar Grid Calculations
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
	// Previous month overlap
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
	// Current month days
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

const formatDateString = (year: number, month: number, day: number) => {
	const date = new Date(year, month, day);
	const y = date.getFullYear();
	const m = String(date.getMonth() + 1).padStart(2, "0");
	const d = String(date.getDate()).padStart(2, "0");
	return `${y}-${m}-${d}`;
};

// Check events on day
const getEventsForDay = (dateString: string) => {
	return props.events.filter((e) => {
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

// Active list filters
const viewMode = ref("calendar"); // calendar, list
const activeCategoryFilter = ref("all");

const filteredEventsList = computed(() => {
	let list = props.events;
	if (activeCategoryFilter.value !== "all") {
		list = list.filter((e) => e.category === activeCategoryFilter.value);
	}
	return list;
});

// Modal Logic
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
	title: "",
	description: "",
	start_date: "",
	end_date: "",
	category: "akademik",
	color: "blue",
});

const openCreateModal = (dateString?: string) => {
	form.reset();
	isEditing.value = false;
	editingId.value = null;
	if (dateString) {
		form.start_date = dateString;
		form.end_date = dateString;
	} else {
		const today = new Date().toISOString().split("T")[0];
		form.start_date = today;
		form.end_date = today;
	}
	isModalOpen.value = true;
};

const openEditModal = (event: any) => {
	isEditing.value = true;
	editingId.value = event.id;
	form.title = event.title;
	form.description = event.description || "";
	form.start_date = event.start_date;
	form.end_date = event.end_date || event.start_date;
	form.category = event.category;
	form.color = event.color || "blue";
	isModalOpen.value = true;
};

// Sync color based on category automatically
const syncCategoryColor = () => {
	const map: Record<string, string> = {
		akademik: "blue",
		kegiatan: "green",
		libur: "red",
		ujian: "purple",
		registrasi: "amber",
	};
	form.color = map[form.category] || "blue";
};

const submitForm = () => {
	if (isEditing.value && editingId.value) {
		form.put(`/portal-admin/academic-calendars/${editingId.value}`, {
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/academic-calendars", {
			onSuccess: () => closeModal(),
		});
	}
};

const isDeleteCalendarModalOpen = ref(false);
const deleteCalendarId = ref<number | null>(null);

const deleteEvent = (id: number) => {
	deleteCalendarId.value = id;
	isDeleteCalendarModalOpen.value = true;
};

const handleDeleteCalendar = () => {
	if (deleteCalendarId.value !== null) {
		router.delete(`/portal-admin/academic-calendars/${deleteCalendarId.value}`, {
			onSuccess: () => {
				isDeleteCalendarModalOpen.value = false;
				deleteCalendarId.value = null;
				closeModal();
			},
		});
	}
};

const closeModal = () => {
	isModalOpen.value = false;
	form.reset();
};
</script>

<template>
    <PortalAdminLayout title="Kalender Akademik">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                        <Sparkles class="w-3 h-3 animate-pulse" /> SIM Akademik
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kalender Akademik</h2>
                <p class="text-xs md:text-sm font-medium text-slate-500 mt-1 dark:text-slate-400">Atur agenda perkuliahan, registrasi mahasiswa, masa ujian, dan libur akademik.</p>
            </div>
            
            <button 
                @click="openCreateModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl text-[13px] font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 w-full md:w-auto active:scale-95 shrink-0 select-none"
            >
                <Plus class="w-4 h-4" />
                Tambah Jadwal
            </button>
        </div>

        <!-- Controls Toolbar -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 md:p-6 border border-slate-100 dark:border-slate-800 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            
            <!-- Calendar Navigation -->
            <div class="flex items-center gap-3">
                <button @click="prevMonth" class="w-9 h-9 rounded-xl border border-slate-150 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                    <ChevronLeft class="w-4 h-4" />
                </button>
                <h3 class="text-base font-black text-slate-800 dark:text-white min-w-[150px] text-center uppercase tracking-wider">
                    {{ monthNames[currentMonth] }} {{ currentYear }}
                </h3>
                <button @click="nextMonth" class="w-9 h-9 rounded-xl border border-slate-150 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>

            <!-- View Modes & Filtering -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <!-- View Switcher -->
                <div class="bg-slate-50 dark:bg-slate-900 p-1 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center">
                    <button 
                        @click="viewMode = 'calendar'" 
                        :class="[viewMode === 'calendar' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all']"
                    >
                        Calendar
                    </button>
                    <button 
                        @click="viewMode = 'list'" 
                        :class="[viewMode === 'list' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-3 py-1.5 rounded-xl text-xs font-bold transition-all']"
                    >
                        Daftar Agenda
                    </button>
                </div>

                <!-- Category Filters (List View Only) -->
                <select 
                    v-if="viewMode === 'list'"
                    v-model="activeCategoryFilter"
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

        <!-- Mode Calendar -->
        <div v-if="viewMode === 'calendar'" class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
            <!-- Day Labels -->
            <div class="grid grid-cols-7 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                <div v-for="dayName in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="dayName" class="py-3 text-center text-xs font-black text-slate-400 uppercase tracking-widest">
                    {{ dayName }}
                </div>
            </div>

            <!-- Calendar Cells -->
            <div class="grid grid-cols-7 auto-rows-[120px]">
                <div 
                    v-for="(cell, idx) in calendarCells" 
                    :key="idx" 
                    :class="[
                        cell.currentMonth ? 'bg-white dark:bg-slate-800' : 'bg-slate-50/50 dark:bg-slate-900/10 text-slate-300 dark:text-slate-600',
                        'border-r border-b border-slate-100 dark:border-slate-800 p-2 flex flex-col justify-between group hover:bg-slate-50/30 dark:hover:bg-slate-900/30 transition-all relative overflow-hidden'
                    ]"
                >
                    <!-- Date Number & Quick Add Button -->
                    <div class="flex items-center justify-between">
                        <span 
                            :class="[
                                cell.currentMonth ? 'text-slate-700 dark:text-slate-300 font-bold' : 'text-slate-300 dark:text-slate-600 font-medium',
                                'text-xs'
                            ]"
                        >
                            {{ cell.day }}
                        </span>
                        
                        <button 
                            @click="openCreateModal(cell.dateString)"
                            class="opacity-0 group-hover:opacity-100 w-5 h-5 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center transition-all hover:scale-105 active:scale-95"
                            title="Tambah jadwal pada tanggal ini"
                        >
                            <Plus class="w-3 h-3" />
                        </button>
                    </div>

                    <!-- Events list inside day cell -->
                    <div class="mt-2 space-y-1 overflow-y-auto max-h-[80px] scrollbar-none">
                        <div 
                            v-for="ev in getEventsForDay(cell.dateString)" 
                            :key="ev.id"
                            @click.stop="openEditModal(ev)"
                            :class="[
                                categoryColors[ev.category]?.bg || 'bg-blue-500',
                                'text-white text-[9px] font-black rounded-lg px-1.5 py-0.5 truncate cursor-pointer transition-transform hover:-translate-y-px shadow-sm select-none'
                            ]"
                            :title="ev.title"
                        >
                            {{ ev.title }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mode List -->
        <div v-else class="space-y-4">
            <div 
                v-for="ev in filteredEventsList" 
                :key="ev.id" 
                class="group bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-start gap-4">
                    <div :class="[categoryColors[ev.category]?.pill || 'bg-blue-50 text-blue-700', 'w-12 h-12 rounded-2xl flex flex-col items-center justify-center shrink-0 border border-slate-100 dark:border-slate-800']">
                        <CalendarDays class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span :class="[categoryColors[ev.category]?.pill || 'bg-blue-50 text-blue-700', 'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider']">
                                {{ categoryLabels[ev.category] || ev.category }}
                            </span>
                            <span class="text-slate-400 dark:text-slate-500 text-[11px] font-bold">
                                {{ ev.start_date === ev.end_date ? new Date(ev.start_date).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) : `${new Date(ev.start_date).toLocaleDateString('id-ID', { day:'numeric', month:'short' })} - ${new Date(ev.end_date || ev.start_date).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' })}` }}
                            </span>
                        </div>
                        <h4 class="text-base font-black text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors">
                            {{ ev.title }}
                        </h4>
                        <p class="text-xs text-slate-450 dark:text-slate-500 mt-0.5 font-medium leading-relaxed">
                            {{ ev.description || 'Tidak ada deskripsi detail.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <button 
                        @click="openEditModal(ev)" 
                        class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all"
                        title="Edit"
                    >
                        <Edit class="w-4.5 h-4.5" />
                    </button>
                    <button 
                        @click="deleteEvent(ev.id)" 
                        class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all"
                        title="Hapus"
                    >
                        <Trash2 class="w-4.5 h-4.5" />
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-if="filteredEventsList.length === 0" 
                class="py-20 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700"
            >
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <CalendarDays class="w-8 h-8 text-slate-400 opacity-60" />
                </div>
                <p class="text-slate-900 dark:text-white text-base font-black">Tidak ada agenda akademik</p>
                <p class="text-slate-500 text-xs mt-1 font-bold">Tambahkan jadwal baru untuk menampilkan agenda.</p>
            </div>
        </div>

        <!-- Custom Slide Modal (Glassmorphism Overlay) -->
        <transition name="fade">
            <div 
                v-if="isModalOpen" 
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all"
                @click.self="closeModal"
            >
                <div class="bg-white dark:bg-slate-800 rounded-[28px] border border-slate-100 dark:border-slate-700 shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-100">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                                <CalendarDays class="w-5 h-5" />
                            </span>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">
                                {{ isEditing ? 'Edit Jadwal Akademik' : 'Tambah Jadwal Akademik' }}
                            </h3>
                        </div>
                        <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 transition-colors">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Judul Kegiatan</label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                required
                                placeholder="Contoh: Ujian Tengah Semester Ganjil"
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            />
                            <p v-if="form.errors.title" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Date range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Tanggal Mulai</label>
                                <input 
                                    v-model="form.start_date" 
                                    type="date" 
                                    required
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                                <p v-if="form.errors.start_date" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Tanggal Selesai</label>
                                <input 
                                    v-model="form.end_date" 
                                    type="date" 
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                                <p v-if="form.errors.end_date" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Kategori</label>
                            <select 
                                v-model="form.category" 
                                @change="syncCategoryColor"
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            >
                                <option value="akademik">Akademik / Perkuliahan</option>
                                <option value="kegiatan">Kegiatan / Acara</option>
                                <option value="libur">Libur Nasional</option>
                                <option value="ujian">Ujian / Evaluasi</option>
                                <option value="registrasi">Registrasi & KRS</option>
                            </select>
                            <p v-if="form.errors.category" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.category }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Deskripsi / Keterangan</label>
                            <textarea 
                                v-model="form.description" 
                                rows="3"
                                placeholder="Penjelasan singkat mengenai agenda akademik ini..."
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <button 
                                    v-if="isEditing" 
                                    type="button" 
                                    @click="deleteEvent(editingId!)"
                                    class="px-4 py-3 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-2xl transition-all"
                                >
                                    Hapus Jadwal
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button" 
                                    @click="closeModal" 
                                    class="px-4 py-3 text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 rounded-2xl transition-all"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl text-xs font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95 disabled:opacity-50"
                                >
                                    {{ isEditing ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
        <DeleteConfirmModal
            :show="isDeleteCalendarModalOpen"
            title="Hapus Jadwal Akademik"
            message="Apakah Anda yakin ingin menghapus jadwal akademik ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="handleDeleteCalendar"
            @cancel="isDeleteCalendarModalOpen = false"
        />
    </PortalAdminLayout>
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
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
