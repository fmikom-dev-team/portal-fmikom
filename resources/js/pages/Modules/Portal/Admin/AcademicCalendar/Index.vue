<script setup lang="ts">
import { ref, type PropType } from "vue"
import { router, useForm } from "@inertiajs/vue3"
import {
  CalendarDays,
  Sparkles,
  X,
  AlertCircle,
  Check,
  Palette,
} from "lucide-vue-next"
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue"
import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue"
import { FullScreenCalendar, type CalendarEvent } from "@/components/ui/fullscreen-calendar"
import { Button } from "@/components/ui/button"

const props = defineProps({
  events: {
    type: Array as PropType<CalendarEvent[]>,
    default: () => [],
  },
})

// Modal Logic
const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = useForm({
  title: "",
  description: "",
  start_date: "",
  end_date: "",
  category: "akademik",
  color: "emerald",
})

// Color Presets for Modal Swatches
const availableColors = [
  { key: "emerald", name: "Hijau (Masuk/Akademik)", bgClass: "bg-emerald-500", ringClass: "ring-emerald-500" },
  { key: "rose", name: "Merah (Hari Libur)", bgClass: "bg-rose-500", ringClass: "ring-rose-500" },
  { key: "blue", name: "Biru (Kegiatan)", bgClass: "bg-blue-500", ringClass: "ring-blue-500" },
  { key: "purple", name: "Ungu (Ujian)", bgClass: "bg-purple-500", ringClass: "ring-purple-500" },
  { key: "amber", name: "Amber (Registrasi/KRS)", bgClass: "bg-amber-500", ringClass: "ring-amber-500" },
  { key: "orange", name: "Oranye", bgClass: "bg-orange-500", ringClass: "ring-orange-500" },
  { key: "indigo", name: "Indigo", bgClass: "bg-indigo-500", ringClass: "ring-indigo-500" },
  { key: "teal", name: "Teal", bgClass: "bg-teal-500", ringClass: "ring-teal-500" },
]

// Automatic color assignment on category change
const syncCategoryColor = () => {
  const map: Record<string, string> = {
    akademik: "emerald", // Hari masuk / perkuliahan -> Hijau
    libur: "rose",       // Hari libur -> Merah
    kegiatan: "blue",    // Kegiatan / Acara -> Biru
    ujian: "purple",     // Ujian -> Ungu
    registrasi: "amber", // Registrasi KRS -> Amber / Kuning
  }
  form.color = map[form.category] || "emerald"
}

const openCreateModal = (dateString?: string) => {
  form.reset()
  isEditing.value = false
  editingId.value = null
  if (dateString) {
    form.start_date = dateString
    form.end_date = dateString
  } else {
    const today = new Date().toISOString().split("T")[0]
    form.start_date = today
    form.end_date = today
  }
  syncCategoryColor()
  isModalOpen.value = true
}

const openEditModal = (event: CalendarEvent) => {
  isEditing.value = true
  editingId.value = event.id
  form.title = event.title
  form.description = event.description || ""
  form.start_date = event.start_date
  form.end_date = event.end_date || event.start_date
  form.category = event.category
  form.color = event.color || "emerald"
  isModalOpen.value = true
}

const submitForm = () => {
  if (isEditing.value && editingId.value) {
    form.put(`/portal-admin/academic-calendars/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  } else {
    form.post("/portal-admin/academic-calendars", {
      onSuccess: () => closeModal(),
    })
  }
}

const isDeleteCalendarModalOpen = ref(false)
const deleteCalendarId = ref<number | null>(null)

const deleteEvent = (id: number) => {
  deleteCalendarId.value = id
  isDeleteCalendarModalOpen.value = true
}

const handleDeleteCalendar = () => {
  if (deleteCalendarId.value !== null) {
    router.delete(`/portal-admin/academic-calendars/${deleteCalendarId.value}`, {
      onSuccess: () => {
        isDeleteCalendarModalOpen.value = false
        deleteCalendarId.value = null
        if (isModalOpen.value) {
          closeModal()
        }
      },
    })
  }
}

const closeModal = () => {
  isModalOpen.value = false
  form.reset()
}
</script>

<template>
  <PortalAdminLayout title="Kalender Akademik">
    <div class="space-y-5">
      <!-- Clean Top Page Banner & Header (Without duplicate Add button) -->
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
            <Sparkles class="w-3 h-3 text-blue-500 animate-pulse" /> SIM AKADEMIK
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
          Kalender Akademik
        </h1>
        <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
          Kelola agenda perkuliahan, registrasi mahasiswa, masa ujian, dan jadwal libur akademik fakultas.
        </p>
      </div>

      <!-- Modern Fullscreen Calendar Component -->
      <FullScreenCalendar
        :events="props.events"
        @add-event="openCreateModal"
        @edit-event="openEditModal"
        @delete-event="deleteEvent"
      />
    </div>

    <!-- Create / Edit Event Modal with Modern Form & Color Picker -->
    <transition name="modal-fade">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all"
        @click.self="closeModal"
      >
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
          
          <!-- Modal Header -->
          <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50 shrink-0">
            <div class="flex items-center gap-3">
              <div class="size-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center shadow-2xs">
                <CalendarDays class="w-4.5 h-4.5" />
              </div>
              <div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                  {{ isEditing ? 'Edit Jadwal Akademik' : 'Tambah Jadwal Akademik' }}
                </h3>
                <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                  {{ isEditing ? 'Perbarui informasi agenda akademik' : 'Tambahkan agenda perkuliahan ke kalender' }}
                </p>
              </div>
            </div>
            
            <button
              @click="closeModal"
              class="size-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Modal Body Form -->
          <form @submit.prevent="submitForm" class="p-6 space-y-4 overflow-y-auto flex-1">
            
            <!-- Judul Kegiatan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                Judul Kegiatan <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                placeholder="Contoh: Perkuliahan Semester Ganjil / Libur Nasional"
                class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white placeholder:text-slate-400 transition-all shadow-2xs"
              />
              <p v-if="form.errors.title" class="text-rose-500 text-[11px] font-bold flex items-center gap-1 mt-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.title }}
              </p>
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                  Tanggal Mulai <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.start_date"
                  type="date"
                  required
                  class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white transition-all shadow-2xs"
                />
                <p v-if="form.errors.start_date" class="text-rose-500 text-[11px] font-bold flex items-center gap-1 mt-1">
                  <AlertCircle class="w-3 h-3" /> {{ form.errors.start_date }}
                </p>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                  Tanggal Selesai
                </label>
                <input
                  v-model="form.end_date"
                  type="date"
                  class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white transition-all shadow-2xs"
                />
                <p v-if="form.errors.end_date" class="text-rose-500 text-[11px] font-bold flex items-center gap-1 mt-1">
                  <AlertCircle class="w-3 h-3" /> {{ form.errors.end_date }}
                </p>
              </div>
            </div>

            <!-- Kategori Event -->
            <div class="space-y-1.5">
              <label class="block text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                Kategori Agenda <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.category"
                @change="syncCategoryColor"
                class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white transition-all shadow-2xs"
              >
                <option value="akademik">Akademik / Perkuliahan (Masuk)</option>
                <option value="libur">Libur Nasional & Cuti</option>
                <option value="kegiatan">Kegiatan / Acara Kampus</option>
                <option value="ujian">Ujian / Evaluasi / Sidang</option>
                <option value="registrasi">Registrasi, KRS & Pembayaran</option>
              </select>
              <p v-if="form.errors.category" class="text-rose-500 text-[11px] font-bold flex items-center gap-1 mt-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.category }}
              </p>
            </div>

            <!-- Pilihan Warna Agenda (Color Picker Swatches) -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider flex items-center gap-1.5">
                  <Palette class="w-3.5 h-3.5 text-slate-400" /> Warna Penanda
                </label>
                <span class="text-[10px] font-semibold text-slate-400 capitalize">
                  {{ availableColors.find(c => c.key === form.color)?.name || form.color }}
                </span>
              </div>

              <!-- Color Swatch Circle Buttons -->
              <div class="grid grid-cols-4 sm:grid-cols-8 gap-2 p-2.5 bg-slate-50/80 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                <button
                  v-for="c in availableColors"
                  :key="c.key"
                  type="button"
                  @click="form.color = c.key"
                  :class="[
                    c.bgClass,
                    form.color === c.key ? 'ring-2 ring-offset-2 ring-slate-900 dark:ring-white dark:ring-offset-slate-900 scale-110 shadow-sm' : 'opacity-85 hover:opacity-100 hover:scale-105',
                    'size-7 rounded-full flex items-center justify-center transition-all cursor-pointer'
                  ]"
                  :title="c.name"
                >
                  <Check v-if="form.color === c.key" class="w-3.5 h-3.5 text-white stroke-[3]" />
                </button>
              </div>
            </div>

            <!-- Deskripsi Detail -->
            <div class="space-y-1.5">
              <label class="block text-xs font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                Deskripsi / Keterangan
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Penjelasan detail atau panduan terkait agenda akademik ini..."
                class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white placeholder:text-slate-400 transition-all shadow-2xs resize-none"
              ></textarea>
              <p v-if="form.errors.description" class="text-rose-500 text-[11px] font-bold flex items-center gap-1 mt-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.description }}
              </p>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
              <div>
                <Button
                  v-if="isEditing"
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="deleteEvent(editingId!)"
                  class="text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg font-bold text-xs"
                >
                  Hapus Agenda
                </Button>
              </div>

              <div class="flex items-center gap-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  @click="closeModal"
                  class="rounded-lg font-bold text-xs"
                >
                  Batal
                </Button>
                <Button
                  type="submit"
                  size="sm"
                  :disabled="form.processing"
                  class="rounded-lg font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 active:scale-95"
                >
                  {{ isEditing ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                </Button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmModal
      :show="isDeleteCalendarModalOpen"
      title="Hapus Jadwal Akademik"
      message="Apakah Anda yakin ingin menghapus jadwal akademik ini? Data yang sudah dihapus tidak dapat dipulihkan."
      @confirm="handleDeleteCalendar"
      @cancel="isDeleteCalendarModalOpen = false"
    />
  </PortalAdminLayout>
</template>

<style scoped>
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
