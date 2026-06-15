<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
const props = defineProps<{
    userName: string;
    roleName: "alumni" | "mitra";
}>();
import { UserIcon, BriefcaseIcon } from "lucide-vue-next";

const config = {
    alumni: {
        desc: "Kembangkan jaringan profesional Anda, isi kuesioner pelacakan karir, dan pantau sebaran alumni sejawat Anda di sini.",
        cta2Label: "Lihat lowongan",
        cta2Route: "/dashboard",
    },
    mitra: {
        desc: "Kelola rekrutmen dan temukan talenta terbaik dari alumni kami.",
        cta2Label: "Cari talenta",
        cta2Route: "/dashboard",
    },
};

const current = computed(() => config[props.roleName]);
</script>

<template>
    <div
        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">
                Selamat datang di Portal Alumni
            </p>
            <h2
                class="text-xl font-medium text-slate-900 dark:text-slate-100 mb-1"
            >
                Halo, {{ userName }}!
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ current.desc }}
            </p>
        </div>
        <div class="flex gap-2.5 flex-wrap shrink-0">
            <Link
                href="/trace/profile-alumni"
                class="flex items-center gap-1.5 text-sm px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
                <UserIcon class="w-4 h-4" />
                Lihat profil
            </Link>
            <Link
                href="/trace/dashboard"
                class="flex items-center gap-1.5 text-sm px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
                <BriefcaseIcon class="w-4 h-4" />
                {{ current.cta2Label }}
            </Link>
        </div>
    </div>
</template>
