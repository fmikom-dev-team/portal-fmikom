<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import type { ProfilAlumni, CareerHistory } from "@/types/trace";
import { ArrowLeft, GraduationCap } from "lucide-vue-next";
import TraceAdminLayout from "@/layouts/TraceAdminLayout.vue";
import { TPageHeader } from "@/components/Trace";
import AlumniHeroCard from "./components/alumni/AlumniHeroCard.vue";
import AlumniPersonalCard from "./components/alumni/AlumniPersonalCard.vue";
import AlumniAddressCard from "./components/alumni/AlumniAddressCard.vue";
import AlumniCurrentStatusCard from "./components/alumni/AlumniCurrentStatusCard.vue";
import AlumniCareerHistory from "./components/alumni/AlumniCareerHistory.vue";
import AlumniEducationHistory from "./components/alumni/AlumniEducationHistory.vue";

const props = defineProps<{
    alumni: ProfilAlumni;
    currentCareer: CareerHistory | null;
    currentEducation: CareerHistory | null;
    careerHistory: CareerHistory[];
    educationHistory: CareerHistory[];
}>();

const breadcrumbs = [
    { title: "Kelola Alumni", href: "/trace/admin/alumni" },
    { title: props.alumni?.nama_lengkap || "Detail Alumni", href: "#" },
];

const currentStatus = props.currentCareer?.status || "mencari_kerja";
</script>

<template>
    <Head :title="`Detail Alumni — ${alumni?.nama_lengkap || 'Alumni'}`" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-full bg-slate-50/50 p-4 md:p-6 lg:p-8 dark:bg-slate-950/50"
        >
            <div class="mx-auto max-w-7xl space-y-6">
                <div class="flex items-center justify-between">
                    <Link
                        href="/trace/admin/alumni"
                        class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition-colors hover:text-slate-800 dark:hover:text-white"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Kelola Alumni
                    </Link>
                </div>

                <TPageHeader
                    :title="alumni?.nama_lengkap || 'Detail Alumni'"
                    :description="`${alumni?.program_studi || ''} · Angkatan ${alumni?.angkatan || ''}`"
                    :icon="GraduationCap"
                />

                <!-- ===== HERO CARD ===== -->
                <AlumniHeroCard
                    :alumni="alumni"
                    :current-status="currentStatus"
                />

                <!-- ===== ROW 1: BASIC INFO GRID (3 COLUMNS) ===== -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <AlumniPersonalCard :alumni="alumni" />
                    <AlumniAddressCard :alumni="alumni" />
                    <AlumniCurrentStatusCard
                        :alumni="alumni"
                        :current-career="currentCareer"
                        :current-education="currentEducation"
                    />
                </div>

                <!-- ===== ROW 2: HISTORY SECTIONS GRID (2 COLUMNS) ===== -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <AlumniCareerHistory :careers="careerHistory" />
                    <AlumniEducationHistory :education-history="educationHistory" />
                </div>
            </div>
        </div>
    </TraceAdminLayout>
</template>
