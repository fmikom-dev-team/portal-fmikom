<script setup lang="ts">
import { ref, computed } from "vue"
import {
  addMonths,
  eachDayOfInterval,
  endOfMonth,
  endOfWeek,
  format,
  isEqual,
  isSameMonth,
  isToday,
  startOfMonth,
  startOfToday,
  startOfWeek,
} from "date-fns"
import {
  ChevronLeft,
  ChevronRight,
  Search,
  Calendar as CalendarIcon,
  CalendarDays,
  Clock,
  X,
  LayoutGrid,
  List as ListIcon,
  ArrowRight,
  CalendarCheck2,
} from "lucide-vue-next"
import { Button } from "@/components/ui/button"

export interface AcademicCalendarItem {
  id: number
  title: string
  description?: string | null
  start_date: string
  end_date?: string | null
  category: string
  color?: string
}

const props = withDefaults(
  defineProps<{
    events?: AcademicCalendarItem[]
  }>(),
  {
    events: () => [],
  }
)

const safeEvents = computed<AcademicCalendarItem[]>(() => {
  return Array.isArray(props.events) ? props.events : []
})

const categoryLabels: Record<string, string> = {
  akademik: "Perkuliahan Efektif",
  kegiatan: "Kegiatan / Acara",
  libur: "Libur Nasional & Cuti",
  ujian: "Ujian / Evaluasi",
  registrasi: "Registrasi & KRS",
}

const categoryDefaultColors: Record<string, string> = {
  akademik: "emerald",
  kegiatan: "blue",
  libur: "rose",
  ujian: "purple",
  registrasi: "amber",
}

const colorPillThemes: Record<
  string,
  {
    pill: string
    dot: string
    text: string
    bgSoft: string
    borderSoft: string
  }
> = {
  emerald: {
    pill: "bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs",
    dot: "bg-emerald-500",
    text: "text-emerald-700 dark:text-emerald-300",
    bgSoft: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300",
    borderSoft: "border-emerald-200 dark:border-emerald-800",
  },
  green: {
    pill: "bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs",
    dot: "bg-emerald-500",
    text: "text-emerald-700 dark:text-emerald-300",
    bgSoft: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300",
    borderSoft: "border-emerald-200 dark:border-emerald-800",
  },
  rose: {
    pill: "bg-rose-600 hover:bg-rose-700 text-white shadow-xs",
    dot: "bg-rose-500",
    text: "text-rose-700 dark:text-rose-300",
    bgSoft: "bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300",
    borderSoft: "border-rose-200 dark:border-rose-800",
  },
  red: {
    pill: "bg-rose-600 hover:bg-rose-700 text-white shadow-xs",
    dot: "bg-rose-500",
    text: "text-rose-700 dark:text-rose-300",
    bgSoft: "bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300",
    borderSoft: "border-rose-200 dark:border-rose-800",
  },
  blue: {
    pill: "bg-blue-600 hover:bg-blue-700 text-white shadow-xs",
    dot: "bg-blue-500",
    text: "text-blue-700 dark:text-blue-300",
    bgSoft: "bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300",
    borderSoft: "border-blue-200 dark:border-blue-800",
  },
  purple: {
    pill: "bg-purple-600 hover:bg-purple-700 text-white shadow-xs",
    dot: "bg-purple-500",
    text: "text-purple-700 dark:text-purple-300",
    bgSoft: "bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300",
    borderSoft: "border-purple-200 dark:border-purple-800",
  },
  amber: {
    pill: "bg-amber-500 hover:bg-amber-600 text-white shadow-xs",
    dot: "bg-amber-500",
    text: "text-amber-700 dark:text-amber-300",
    bgSoft: "bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300",
    borderSoft: "border-amber-200 dark:border-amber-800",
  },
  orange: {
    pill: "bg-orange-500 hover:bg-orange-600 text-white shadow-xs",
    dot: "bg-orange-500",
    text: "text-orange-700 dark:text-orange-300",
    bgSoft: "bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300",
    borderSoft: "border-orange-200 dark:border-orange-800",
  },
  indigo: {
    pill: "bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs",
    dot: "bg-indigo-500",
    text: "text-indigo-700 dark:text-indigo-300",
    bgSoft: "bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300",
    borderSoft: "border-indigo-200 dark:border-indigo-800",
  },
  teal: {
    pill: "bg-teal-600 hover:bg-teal-700 text-white shadow-xs",
    dot: "bg-teal-500",
    text: "text-teal-700 dark:text-teal-300",
    bgSoft: "bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300",
    borderSoft: "border-teal-200 dark:border-teal-800",
  },
}

function getEventColor(ev: AcademicCalendarItem) {
  const colorKey = ev.color || categoryDefaultColors[ev.category] || "emerald"
  return colorPillThemes[colorKey] || colorPillThemes.emerald
}

// Calendar State
const today = startOfToday()
const activeMonthDate = ref<Date>(today)
const selectedDay = ref<Date>(today)
const searchQuery = ref("")
const isSearchDropdownOpen = ref(false)
const viewMode = ref<"calendar" | "list">("calendar")

const firstDayCurrentMonth = computed(() => startOfMonth(activeMonthDate.value))

const days = computed(() => {
  try {
    return eachDayOfInterval({
      start: startOfWeek(firstDayCurrentMonth.value, { weekStartsOn: 0 }),
      end: endOfWeek(endOfMonth(firstDayCurrentMonth.value), { weekStartsOn: 0 }),
    })
  } catch (err) {
    console.error("Error computing calendar days:", err)
    return []
  }
})

function previousMonth() {
  activeMonthDate.value = addMonths(activeMonthDate.value, -1)
}

function nextMonth() {
  activeMonthDate.value = addMonths(activeMonthDate.value, 1)
}

function goToToday() {
  activeMonthDate.value = today
  selectedDay.value = today
}

function selectDate(day: Date) {
  selectedDay.value = day
}

function getCleanDateString(dateVal: string | null | undefined): string {
  if (!dateVal) return ""
  return dateVal.substring(0, 10)
}

function isEventOnDay(event: AcademicCalendarItem, day: Date): boolean {
  try {
    const dateStr = format(day, "yyyy-MM-dd")
    const start = getCleanDateString(event.start_date)
    const end = getCleanDateString(event.end_date) || start
    return dateStr >= start && dateStr <= end
  } catch {
    return false
  }
}

function getEventsForDay(day: Date): AcademicCalendarItem[] {
  let list = safeEvents.value.filter((ev) => isEventOnDay(ev, day))

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(
      (ev) =>
        (ev.title && ev.title.toLowerCase().includes(q)) ||
        (ev.description && ev.description.toLowerCase().includes(q))
    )
  }

  return list
}

const selectedDayEvents = computed(() => getEventsForDay(selectedDay.value))

// Detail Modal
const activeModalEvent = ref<AcademicCalendarItem | null>(null)
const isDetailModalOpen = ref(false)

function openEventDetail(ev: AcademicCalendarItem) {
  activeModalEvent.value = ev
  isDetailModalOpen.value = true
}

function closeDetailModal() {
  isDetailModalOpen.value = false
  activeModalEvent.value = null
}

// Instant Search
const searchResults = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return []
  return safeEvents.value.filter(
    (ev) =>
      (ev.title && ev.title.toLowerCase().includes(q)) ||
      (ev.description && ev.description.toLowerCase().includes(q))
  )
})

function navigateToEvent(ev: AcademicCalendarItem) {
  try {
    const dateStr = getCleanDateString(ev.start_date)
    const parts = dateStr.split("-")
    if (parts.length === 3) {
      const year = parseInt(parts[0], 10)
      const month = parseInt(parts[1], 10) - 1
      const day = parseInt(parts[2], 10)
      const targetDate = new Date(year, month, day)
      activeMonthDate.value = targetDate
      selectedDay.value = targetDate
    }
  } catch (err) {
    console.error("Navigation error:", err)
  }
  isSearchDropdownOpen.value = false
  openEventDetail(ev)
}

const allFilteredEvents = computed(() => {
  let list = safeEvents.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(
      (ev) =>
        (ev.title && ev.title.toLowerCase().includes(q)) ||
        (ev.description && ev.description.toLowerCase().includes(q))
    )
  }

  return [...list].sort(
    (a, b) => new Date(a.start_date).getTime() - new Date(b.start_date).getTime()
  )
})

// Upcoming highlights (Next 4 upcoming events)
const upcomingEvents = computed(() => {
  const todayStr = format(today, "yyyy-MM-dd")
  return [...safeEvents.value]
    .filter((e) => getCleanDateString(e.end_date || e.start_date) >= todayStr)
    .sort((a, b) => new Date(a.start_date).getTime() - new Date(b.start_date).getTime())
    .slice(0, 4)
})

function formatRange(start: string, end?: string | null) {
  try {
    const sClean = getCleanDateString(start)
    const eClean = getCleanDateString(end) || sClean
    const sDate = new Date(sClean + "T00:00:00")
    if (sClean === eClean) {
      return sDate.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
      })
    }
    const eDate = new Date(eClean + "T00:00:00")
    return `${sDate.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "short",
    })} - ${eDate.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "short",
      year: "numeric",
    })}`
  } catch {
    return start
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Main Academic Calendar Component -->
    <div class="flex flex-1 flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-visible transition-colors">
      
      <!-- Toolbar Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-4 sm:p-5 gap-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/40 rounded-t-2xl">
        <!-- Left Month Block -->
        <div class="flex items-center gap-3.5 shrink-0">
          <div class="flex w-13 h-13 flex-col items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 shadow-xs shrink-0 select-none overflow-hidden">
            <span class="w-full text-center py-0.5 text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
              {{ format(today, "MMM") }}
            </span>
            <div class="flex flex-1 items-center justify-center text-lg font-black text-slate-800 dark:text-white leading-none">
              {{ format(today, "d") }}
            </div>
          </div>

          <div class="flex flex-col">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
              {{ format(firstDayCurrentMonth, "MMMM yyyy") }}
            </h2>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-0.5">
              {{ format(firstDayCurrentMonth, "d MMM yyyy") }} — {{ format(endOfMonth(firstDayCurrentMonth), "d MMM yyyy") }}
            </p>
          </div>
        </div>

        <!-- Right Controls Block -->
        <div class="flex flex-wrap lg:flex-nowrap items-center gap-2.5 sm:gap-3">
          <!-- Search Bar with Live Popover -->
          <div class="relative w-full sm:w-56 lg:w-64">
            <input
              v-model="searchQuery"
              @focus="isSearchDropdownOpen = true"
              type="text"
              placeholder="Cari jadwal perkuliahan, ujian..."
              class="h-9 w-full pl-8 pr-7 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-2xs"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''; isSearchDropdownOpen = false"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            >
              <X class="w-3.5 h-3.5" />
            </button>

            <!-- Search Dropdown Popover -->
            <div
              v-if="searchQuery && isSearchDropdownOpen"
              class="absolute left-0 right-0 top-11 z-50 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl p-2 max-h-64 overflow-y-auto"
            >
              <div class="text-[10px] font-black uppercase text-slate-400 px-2 py-1 tracking-wider">
                Hasil Pencarian ({{ searchResults.length }})
              </div>

              <div v-if="searchResults.length > 0" class="space-y-1 mt-1">
                <button
                  v-for="ev in searchResults"
                  :key="ev.id"
                  type="button"
                  @click="navigateToEvent(ev)"
                  class="w-full text-left p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between gap-2 group"
                >
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                      <span :class="[getEventColor(ev).dot, 'size-1.5 rounded-full shrink-0']" />
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">
                        {{ ev.title }}
                      </span>
                    </div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">
                      {{ formatRange(ev.start_date, ev.end_date) }}
                    </div>
                  </div>
                  <ArrowRight class="w-3 h-3 text-slate-300 group-hover:text-blue-500 shrink-0" />
                </button>
              </div>

              <div v-else class="p-3 text-center text-xs text-slate-400">
                Tidak ada agenda yang cocok
              </div>
            </div>
          </div>

          <!-- Nav Month Buttons -->
          <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-0.5 shadow-2xs">
            <Button
              @click="previousMonth"
              variant="ghost"
              size="icon-sm"
              class="h-8 w-8 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
              aria-label="Bulan Sebelumnya"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <Button
              @click="goToToday"
              variant="ghost"
              size="sm"
              class="h-8 px-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"
            >
              Hari Ini
            </Button>
            <Button
              @click="nextMonth"
              variant="ghost"
              size="icon-sm"
              class="h-8 w-8 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
              aria-label="Bulan Berikutnya"
            >
              <ChevronRight class="w-4 h-4" />
            </Button>
          </div>

          <!-- View Switcher -->
          <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 p-0.5 shadow-2xs">
            <button
              @click="viewMode = 'calendar'"
              :class="[
                viewMode === 'calendar'
                  ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-xs font-bold'
                  : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-semibold',
                'flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs transition-all'
              ]"
            >
              <LayoutGrid class="w-3.5 h-3.5" />
              <span>Kalender</span>
            </button>
            <button
              @click="viewMode = 'list'"
              :class="[
                viewMode === 'list'
                  ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-xs font-bold'
                  : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-semibold',
                'flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs transition-all'
              ]"
            >
              <ListIcon class="w-3.5 h-3.5" />
              <span>Agenda</span>
            </button>
          </div>
        </div>
      </div>

      <!-- CALENDAR VIEW -->
      <div v-if="viewMode === 'calendar'" class="flex flex-1 flex-col">
        <!-- Week Days Header -->
        <div class="grid grid-cols-7 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/60 text-center text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5 text-rose-500 dark:text-rose-400">Min</div>
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5">Sen</div>
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5">Sel</div>
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5">Rab</div>
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5">Kam</div>
          <div class="border-r border-slate-200/60 dark:border-slate-800 py-2.5">Jum</div>
          <div class="py-2.5">Sab</div>
        </div>

        <!-- Desktop Calendar Grid (Large screens) -->
        <div class="hidden lg:grid grid-cols-7 auto-rows-fr min-h-[580px] bg-slate-200/40 dark:bg-slate-800/40 gap-px">
          <div
            v-for="(day, dayIdx) in days"
            :key="dayIdx"
            @click="selectDate(day)"
            :class="[
              isSameMonth(day, firstDayCurrentMonth)
                ? 'bg-white dark:bg-slate-900'
                : 'bg-slate-50/70 dark:bg-slate-900/40 text-slate-400 dark:text-slate-600',
              isEqual(day, selectedDay) && 'ring-2 ring-blue-500 ring-inset z-10',
              'group relative flex flex-col p-2 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-all cursor-pointer select-none min-h-[115px]'
            ]"
          >
            <!-- Cell Header: Date Number -->
            <div class="flex items-center justify-between mb-1.5">
              <span
                :class="[
                  isToday(day)
                    ? 'bg-blue-600 text-white font-black shadow-md shadow-blue-500/30'
                    : isEqual(day, selectedDay)
                      ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900 font-black'
                      : isSameMonth(day, firstDayCurrentMonth)
                        ? dayIdx % 7 === 0 ? 'text-rose-500 font-bold' : 'text-slate-800 dark:text-slate-200 font-bold'
                        : 'text-slate-400 dark:text-slate-600 font-medium',
                  'size-6.5 rounded-full flex items-center justify-center text-xs transition-transform group-hover:scale-105'
                ]"
              >
                {{ format(day, "d") }}
              </span>
            </div>

            <!-- Clean Event Pills -->
            <div class="flex-1 space-y-1 overflow-y-auto max-h-[85px] scrollbar-none">
              <template v-if="getEventsForDay(day).length > 0">
                <div
                  v-for="ev in getEventsForDay(day).slice(0, 2)"
                  :key="ev.id"
                  @click.stop="openEventDetail(ev)"
                  :class="[
                    getEventColor(ev).pill,
                    'group/item rounded-md px-2 py-1 text-[11px] font-bold leading-tight transition-all hover:scale-[1.01] hover:brightness-105 cursor-pointer truncate select-none'
                  ]"
                  :title="ev.title"
                >
                  {{ ev.title }}
                </div>

                <div
                  v-if="getEventsForDay(day).length > 2"
                  class="text-[9.5px] font-bold text-slate-500 dark:text-slate-400 px-1 py-0.5 hover:text-blue-600 dark:hover:text-blue-400"
                >
                  + {{ getEventsForDay(day).length - 2 }} lainnya
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Mobile & Tablet Calendar Grid (Compact Touch View) -->
        <div class="lg:hidden flex flex-col">
          <!-- Compact Days Grid -->
          <div class="grid grid-cols-7 auto-rows-[64px] gap-px bg-slate-200/50 dark:bg-slate-800/50">
            <button
              v-for="(day, dayIdx) in days"
              :key="dayIdx"
              type="button"
              @click="selectDate(day)"
              :class="[
                isSameMonth(day, firstDayCurrentMonth)
                  ? 'bg-white dark:bg-slate-900'
                  : 'bg-slate-50/70 dark:bg-slate-900/40 text-slate-400 dark:text-slate-600',
                isEqual(day, selectedDay) && 'bg-blue-50/60 dark:bg-blue-950/30 ring-2 ring-blue-500 ring-inset',
                'flex flex-col items-center justify-between p-1.5 transition-all text-xs font-semibold'
              ]"
            >
              <span
                :class="[
                  isToday(day)
                    ? 'bg-blue-600 text-white font-black shadow-2xs'
                    : isEqual(day, selectedDay)
                      ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900 font-bold'
                      : isSameMonth(day, firstDayCurrentMonth)
                        ? dayIdx % 7 === 0 ? 'text-rose-500' : 'text-slate-800 dark:text-slate-200'
                        : 'text-slate-400 dark:text-slate-600',
                  'size-6 rounded-full flex items-center justify-center text-xs'
                ]"
              >
                {{ format(day, "d") }}
              </span>

              <!-- Event Dots -->
              <div class="flex items-center gap-0.5 mt-auto pb-1 max-w-full overflow-hidden">
                <span
                  v-for="ev in getEventsForDay(day).slice(0, 3)"
                  :key="ev.id"
                  :class="[getEventColor(ev).dot, 'size-1.5 rounded-full']"
                />
                <span
                  v-if="getEventsForDay(day).length > 3"
                  class="text-[8px] font-bold text-slate-400"
                >
                  +
                </span>
              </div>
            </button>
          </div>

          <!-- Mobile Selected Day Drawer -->
          <div class="p-4 bg-slate-50 dark:bg-slate-900/90 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 mb-3">
              <CalendarIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                Agenda {{ format(selectedDay, "d MMMM yyyy") }}
              </h4>
            </div>

            <!-- Event Cards for Selected Day -->
            <div v-if="selectedDayEvents.length > 0" class="space-y-2">
              <div
                v-for="ev in selectedDayEvents"
                :key="ev.id"
                @click="openEventDetail(ev)"
                class="bg-white dark:bg-slate-800 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between gap-3 cursor-pointer hover:border-blue-300 transition-all"
              >
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span :class="[getEventColor(ev).bgSoft, 'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider']">
                      {{ categoryLabels[ev.category] || ev.category }}
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                      {{ formatRange(ev.start_date, ev.end_date) }}
                    </span>
                  </div>
                  <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                    {{ ev.title }}
                  </h5>
                  <p v-if="ev.description" class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">
                    {{ ev.description }}
                  </p>
                </div>
                <ArrowRight class="w-4 h-4 text-slate-300 shrink-0" />
              </div>
            </div>

            <div
              v-else
              class="py-5 text-center bg-white dark:bg-slate-800/60 rounded-xl border border-dashed border-slate-200 dark:border-slate-700/80"
            >
              <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                Tidak ada agenda pada tanggal ini
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- LIST VIEW -->
      <div v-else class="p-4 sm:p-5 space-y-3">
        <div v-if="allFilteredEvents.length > 0" class="space-y-2.5">
          <div
            v-for="ev in allFilteredEvents"
            :key="ev.id"
            @click="openEventDetail(ev)"
            class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs hover:shadow-xs hover:border-blue-300 dark:hover:border-blue-800 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer"
          >
            <div class="flex items-start gap-3.5">
              <div
                :class="[
                  getEventColor(ev).bgSoft,
                  'size-10 rounded-xl flex flex-col items-center justify-center shrink-0'
                ]"
              >
                <CalendarDays class="w-5 h-5" />
              </div>

              <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span
                    :class="[
                      getEventColor(ev).bgSoft,
                      'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider'
                    ]"
                  >
                    {{ categoryLabels[ev.category] || ev.category }}
                  </span>
                  <span class="text-xs font-bold text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    <Clock class="w-3 h-3" />
                    {{ formatRange(ev.start_date, ev.end_date) }}
                  </span>
                </div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white hover:text-blue-600 transition-colors">
                  {{ ev.title }}
                </h4>
                <p
                  v-if="ev.description"
                  class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium leading-relaxed max-w-2xl"
                >
                  {{ ev.description }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 self-end sm:self-center shrink-0">
              <span>Detail</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </div>
          </div>
        </div>

        <div
          v-else
          class="py-14 text-center bg-slate-50/50 dark:bg-slate-900/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800"
        >
          <CalendarDays class="w-6 h-6 text-slate-400 mx-auto mb-2" />
          <h4 class="text-slate-900 dark:text-white text-xs font-bold">
            Tidak ada agenda yang cocok
          </h4>
          <p class="text-slate-500 text-[11px] mt-1 font-medium">
            Coba ubah kata kunci pencarian Anda.
          </p>
        </div>
      </div>
    </div>

    <!-- 4. Upcoming Highlights Widget (Agenda Mendatang) -->
    <div v-if="upcomingEvents.length > 0" class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <CalendarCheck2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
            Agenda Terdekat Mendatang
          </h3>
        </div>
        <span class="text-xs font-bold text-slate-400">
          {{ upcomingEvents.length }} Kegiatan
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="item in upcomingEvents"
          :key="item.id"
          @click="openEventDetail(item)"
          class="p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800 transition-all cursor-pointer flex items-start gap-3"
        >
          <div :class="[getEventColor(item).bgSoft, 'size-10 rounded-xl flex flex-col items-center justify-center shrink-0']">
            <Clock class="w-4 h-4" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-0.5">
              <span :class="[getEventColor(item).bgSoft, 'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider']">
                {{ categoryLabels[item.category] || item.category }}
              </span>
              <span class="text-[10px] font-bold text-slate-400">
                {{ formatRange(item.start_date, item.end_date) }}
              </span>
            </div>
            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
              {{ item.title }}
            </h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Read-Only Detail Event Modal -->
    <transition name="modal-fade">
      <div
        v-if="isDetailModalOpen && activeModalEvent"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all"
        @click.self="closeDetailModal"
      >
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-100">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-2.5">
              <div :class="[getEventColor(activeModalEvent).bgSoft, 'size-8 rounded-lg flex items-center justify-center']">
                <CalendarDays class="w-4 h-4" />
              </div>
              <h3 class="text-sm font-black text-slate-900 dark:text-white">
                Detail Agenda Akademik
              </h3>
            </div>
            <button
              @click="closeDetailModal"
              class="size-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 space-y-4">
            <div>
              <span :class="[getEventColor(activeModalEvent).bgSoft, 'px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider inline-block mb-2']">
                {{ categoryLabels[activeModalEvent.category] || activeModalEvent.category }}
              </span>
              <h4 class="text-base font-black text-slate-900 dark:text-white">
                {{ activeModalEvent.title }}
              </h4>
            </div>

            <!-- Date range info card -->
            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/70 dark:border-slate-700/60 flex items-center gap-3 text-xs">
              <Clock class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" />
              <div>
                <span class="text-slate-400 text-[10px] font-bold block uppercase">Waktu / Jadwal Pelaksanaan</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">
                  {{ formatRange(activeModalEvent.start_date, activeModalEvent.end_date) }}
                </span>
              </div>
            </div>

            <!-- Description -->
            <div v-if="activeModalEvent.description" class="space-y-1">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Deskripsi & Keterangan</span>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                {{ activeModalEvent.description }}
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-3.5 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <Button
              size="sm"
              variant="outline"
              @click="closeDetailModal"
              class="rounded-xl text-xs font-bold"
            >
              Tutup
            </Button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>
