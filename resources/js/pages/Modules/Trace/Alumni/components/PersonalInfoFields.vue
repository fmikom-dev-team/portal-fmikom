<script setup lang="ts">
import { ChevronDown, Globe } from "lucide-vue-next";
import type { InertiaForm } from "@inertiajs/vue3";

interface PersonalInfoFormData {
    bio: string;
    website: string;
    linkedin: string;
    github: string;
    instagram: string;
    twitter: string;
    [key: string]: unknown;
}

defineProps<{
    form: InertiaForm<PersonalInfoFormData>;
}>();
</script>

<template>
    <!-- ══════════════════ BIO & MEDIA SOSIAL ══════════════════ -->
    <div
        class="bg-slate-50/50 dark:bg-slate-950/10 rounded-2xl border border-slate-100 dark:border-slate-850 p-5 space-y-4"
    >
        <div class="flex items-center gap-2 mb-2">
            <Globe class="w-4 h-4 text-green-500" />
            <span
                class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider"
                >Biodata & Media Sosial</span
            >
        </div>

        <div class="space-y-4">
            <!-- Biodata Singkat -->
            <div class="space-y-1.5">
                <label
                    class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                    >Bio</label
                >
                <textarea
                    v-model="form.bio"
                    rows="3"
                    placeholder=""
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"
                ></textarea>
                <p
                    v-if="form.errors.bio"
                    class="text-xs font-medium text-rose-500"
                >
                    {{ form.errors.bio }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Website -->
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >Website URL</label
                    >
                    <input
                        v-model="form.website"
                        type="url"
                        placeholder="https://example.com"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <p
                        v-if="form.errors.website"
                        class="text-xs font-medium text-rose-500"
                    >
                        {{ form.errors.website }}
                    </p>
                </div>

                <!-- LinkedIn -->
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >LinkedIn URL / Username</label
                    >
                    <input
                        v-model="form.linkedin"
                        type="text"
                        placeholder="Username LinkedIn"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <p
                        v-if="form.errors.linkedin"
                        class="text-xs font-medium text-rose-500"
                    >
                        {{ form.errors.linkedin }}
                    </p>
                </div>

                <!-- GitHub -->
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >GitHub Username</label
                    >
                    <input
                        v-model="form.github"
                        type="text"
                        placeholder="Username Github"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <p
                        v-if="form.errors.github"
                        class="text-xs font-medium text-rose-500"
                    >
                        {{ form.errors.github }}
                    </p>
                </div>

                <!-- Instagram -->
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >Username Instagram</label
                    >
                    <input
                        v-model="form.instagram"
                        type="text"
                        placeholder="Instagram Username"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <p
                        v-if="form.errors.instagram"
                        class="text-xs font-medium text-rose-500"
                    >
                        {{ form.errors.instagram }}
                    </p>
                </div>

                <!-- Twitter -->
                <div class="space-y-1.5">
                    <label
                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >Twitter Username</label
                    >
                    <input
                        v-model="form.twitter"
                        type="text"
                        placeholder="Twitter Username"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <p
                        v-if="form.errors.twitter"
                        class="text-xs font-medium text-rose-500"
                    >
                        {{ form.errors.twitter }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
