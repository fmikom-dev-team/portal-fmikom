<script setup lang="ts">
import {
    Hash,
    Phone,
    Mail,
    Linkedin,
} from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { ProfilAlumni } from '@/types/trace';

defineProps<{
    alumni: ProfilAlumni;
    currentStatus: string;
}>();

const getDisplayName = (name: string) => {
    if (!name) {
return '??';
}

    return name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
};

const formatStatus = (status: string) => {
    if (!status) {
return 'Belum Bekerja';
}

    const map: Record<string, string> = {
        bekerja: '💼 Bekerja',
        wirausaha: '🏪 Wirausaha',
        mencari_kerja: '🔍 Mencari Kerja',
        lanjut_studi: '🎓 Lanjut Studi',
    };

    return map[status] || status;
};

const getStatusColor = (status: string) => {
    const map: Record<string, string> = {
        bekerja:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        wirausaha:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        mencari_kerja:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        lanjut_studi:
            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    };

    return map[status] || 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <div
        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 shadow-xl shadow-blue-600/10"
    >
        <!-- Background pattern decoration -->
        <div
            class="pointer-events-none absolute inset-0 opacity-10"
        >
            <div
                class="absolute top-0 right-0 h-96 w-96 translate-x-1/2 -translate-y-1/2 rounded-full bg-white"
            ></div>
            <div
                class="absolute bottom-0 left-0 h-64 w-64 -translate-x-1/2 translate-y-1/2 rounded-full bg-white"
            ></div>
        </div>

        <div
            class="relative flex flex-col items-start gap-6 p-6 md:flex-row md:items-center md:p-8"
        >
            <!-- Avatar -->
            <Avatar
                class="h-24 w-24 flex-shrink-0 border-4 border-white/20 shadow-2xl"
            >
                <AvatarImage
                    v-if="alumni.user?.foto_path"
                    :src="
                        alumni.user.foto_path.startsWith('http')
                            ? alumni.user.foto_path
                            : `/storage/${alumni.user.foto_path}`
                    "
                    :alt="alumni.user?.name"
                />
                <AvatarFallback
                    class="bg-white/20 text-2xl font-black text-white backdrop-blur-sm"
                >
                    {{
                        getDisplayName(
                            alumni.nama_lengkap ||
                                alumni.user?.name,
                        )
                    }}
                </AvatarFallback>
            </Avatar>

            <!-- Info -->
            <div class="flex-1 space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <h1
                        class="text-2xl font-black tracking-tight text-white md:text-3xl"
                    >
                        {{
                            alumni.nama_lengkap ||
                            alumni.user?.name ||
                            'Nama Tidak Tersedia'
                        }}
                    </h1>
                    <span
                        :class="getStatusColor(currentStatus)"
                        class="rounded-full px-3.5 py-1 text-xs font-black tracking-wider uppercase shadow-sm"
                    >
                        {{ formatStatus(currentStatus) }}
                    </span>
                </div>
                <p class="text-sm font-medium text-blue-100">
                    {{ alumni.program_studi }} · Angkatan
                    {{ alumni.angkatan }} · Lulus
                    {{ alumni.tahun_lulus || '-' }}
                </p>

                <div
                    class="flex flex-wrap gap-x-6 gap-y-2 border-t border-white/10 pt-2"
                >
                    <div
                        v-if="alumni.nim"
                        class="flex items-center gap-2 text-xs font-bold text-blue-100"
                    >
                        <Hash class="h-4 w-4 text-blue-300" />
                        <span
                            >NIM:
                            <span class="font-mono text-white">{{
                                alumni.nim
                            }}</span></span
                        >
                    </div>
                    <div
                        v-if="alumni.user?.no_telepon"
                        class="flex items-center gap-2 text-xs font-bold text-blue-100"
                    >
                        <Phone class="h-4 w-4 text-blue-300" />
                        <span class="text-white">{{
                            alumni.user.no_telepon
                        }}</span>
                    </div>
                    <div
                        v-if="alumni.user?.email"
                        class="flex items-center gap-2 text-xs font-bold text-blue-100"
                    >
                        <Mail class="h-4 w-4 text-blue-300" />
                        <span class="text-white">{{
                            alumni.user.email
                        }}</span>
                    </div>
                    <a
                        v-if="alumni.user?.linkedin"
                        :href="alumni.user.linkedin"
                        target="_blank"
                        class="group flex items-center gap-2 text-xs font-bold text-blue-100 transition-colors hover:text-white"
                    >
                        <Linkedin
                            class="h-4 w-4 text-blue-300 group-hover:text-white"
                        />
                        <span
                            class="text-white underline decoration-blue-400 group-hover:decoration-white"
                            >LinkedIn Profil</span
                        >
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
