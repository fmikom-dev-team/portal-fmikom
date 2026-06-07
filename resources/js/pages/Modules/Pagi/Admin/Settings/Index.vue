<script setup lang="ts">
import { ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const activeSection = ref<
	"general" | "moderation" | "security" | "notifications"
>("general");

const sections = [
	{ key: "general", label: "Umum" },
	{ key: "moderation", label: "Moderasi" },
	{ key: "security", label: "Keamanan" },
	{ key: "notifications", label: "Notifikasi" },
] as const;

// Mock form states
const settings = ref({
	siteName: "PAGI – Works & Gallery",
	maxUploadSizeMb: 10,
	allowPublicWork: true,
	requireEmailVerification: true,
	autoModeration: false,
	maxWarningsBeforeSuspend: 3,
	rateLimitPerMinute: 60,
	enableActivityLog: true,
	notifyOnReport: true,
	notifyOnNewUser: false,
	notifyOnTakedown: true,
});
</script>

<template>
    <PagiAdminLayout title="Pengaturan">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Pengaturan</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Konfigurasi sistem dan preferensi admin PAGI</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

            <!-- Sidebar Nav -->
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-2 h-fit">
                <button
                    v-for="s in sections"
                    :key="s.key"
                    @click="activeSection = s.key"
                    :class="[
                        'w-full flex items-center rounded-xl px-3.5 py-2.5 text-[13px] font-semibold text-left transition-all',
                        activeSection === s.key
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                    ]"
                >
                    {{ s.label }}
                </button>
            </div>

            <!-- Settings Panel -->
            <div class="lg:col-span-3 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800">
                    <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">
                        {{ sections.find(s => s.key === activeSection)?.label }}
                    </h3>
                </div>

                <div class="px-6 py-5 space-y-5">

                    <!-- General -->
                    <template v-if="activeSection === 'general'">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Nama Platform</label>
                            <input
                                v-model="settings.siteName"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Ukuran Maks Upload (MB)</label>
                            <input
                                v-model="settings.maxUploadSizeMb"
                                type="number"
                                min="1"
                                max="50"
                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="flex items-center justify-between py-3 border-t border-slate-100 dark:border-zinc-800">
                            <div>
                                <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">Karya Publik</p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Izinkan halaman karya dilihat publik tanpa login</p>
                            </div>
                            <button
                                @click="settings.allowPublicWork = !settings.allowPublicWork"
                                :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', settings.allowPublicWork ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', settings.allowPublicWork ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                        </div>
                    </template>

                    <!-- Moderation -->
                    <template v-if="activeSection === 'moderation'">
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">Auto Moderasi</p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Filter konten otomatis menggunakan AI</p>
                            </div>
                            <button
                                @click="settings.autoModeration = !settings.autoModeration"
                                :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200', settings.autoModeration ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', settings.autoModeration ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Maks Peringatan sebelum Suspend</label>
                            <input
                                v-model="settings.maxWarningsBeforeSuspend"
                                type="number"
                                min="1"
                                max="10"
                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </template>

                    <!-- Security -->
                    <template v-if="activeSection === 'security'">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-zinc-300 mb-1.5">Rate Limit (request/menit)</label>
                            <input
                                v-model="settings.rateLimitPerMinute"
                                type="number"
                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-4 py-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="flex items-center justify-between py-3 border-t border-slate-100 dark:border-zinc-800">
                            <div>
                                <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">Log Aktivitas</p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Simpan log semua aksi admin dan moderasi</p>
                            </div>
                            <button
                                @click="settings.enableActivityLog = !settings.enableActivityLog"
                                :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200', settings.enableActivityLog ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', settings.enableActivityLog ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                        </div>
                    </template>

                    <!-- Notifications -->
                    <template v-if="activeSection === 'notifications'">
                        <div v-for="item in [
                            { key: 'notifyOnReport', label: 'Notifikasi Laporan Baru', desc: 'Terima notifikasi saat ada laporan masuk' },
                            { key: 'notifyOnNewUser', label: 'Notifikasi User Baru', desc: 'Terima notifikasi saat mahasiswa baru bergabung' },
                            { key: 'notifyOnTakedown', label: 'Notifikasi Takedown', desc: 'Terima notifikasi saat konten di-takedown' },
                        ]" :key="item.key" class="flex items-center justify-between py-3 border-b border-slate-50 dark:border-zinc-800 last:border-0">
                            <div>
                                <p class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">{{ item.label }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.desc }}</p>
                            </div>
                            <button
                                @click="(settings as any)[item.key] = !(settings as any)[item.key]"
                                :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200', (settings as any)[item.key] ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-700']"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200', (settings as any)[item.key] ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                        </div>
                    </template>

                    <!-- Save Button -->
                    <div class="flex justify-end pt-2">
                        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-[13px] font-bold text-white hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 dark:shadow-none">
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PagiAdminLayout>
</template>
