<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    MapPin,
    Phone,
    Mail,
    Linkedin,
    Briefcase,
    GraduationCap,
    Building2,
    Calendar,
    User,
    Hash,
    CreditCard,
    Home,
    Globe,
    DollarSign,
    Info,
    Map,
} from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';

const props = defineProps<{
    alumni: any;
    currentCareer: any;
    currentEducation: any;
}>();

const breadcrumbs = [
    { title: 'Kelola Alumni', href: '/admin/alumni' },
    { title: props.alumni?.nama_lengkap || 'Detail Alumni', href: '#' },
];

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

const formatCurrency = (val: number) => {
    if (!val) {
return '-';
}

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};


const currentStatus =
    props.currentCareer?.status ||
    (props.currentEducation ? 'lanjut_studi' : 'mencari_kerja');
</script>

<template>
    <Head :title="`Detail Alumni — ${alumni?.nama_lengkap || 'Alumni'}`" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-full bg-slate-50/50 p-4 md:p-6 lg:p-8 dark:bg-slate-950/50"
        >
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Back Button & Actions -->
                <div class="flex items-center justify-between">
                    <Link
                        href="/admin/alumni"
                        class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition-colors hover:text-slate-800 dark:hover:text-white"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Kelola Alumni
                    </Link>
                </div>

                <!-- ===== HERO CARD ===== -->
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
                                v-if="alumni.photo_path"
                                :src="
                                    alumni.photo_path.startsWith('http')
                                        ? alumni.photo_path
                                        : `/storage/${alumni.photo_path}`
                                "
                                :alt="alumni.nama_lengkap"
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

                <!-- ===== ROW 1: BASIC INFO GRID (3 COLUMNS) ===== -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Card 1: Data Pribadi -->
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <User class="h-4 w-4 text-blue-500" />
                            <h2
                                class="text-sm font-black text-slate-800 dark:text-white"
                            >
                                Data Pribadi
                            </h2>
                        </div>
                        <div class="flex-1 space-y-4 p-5">
                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <User class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Jenis Kelamin</span
                                    >
                                    <span
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        >{{
                                            alumni.jenis_kelamin === 'L'
                                                ? '♂ Laki-laki'
                                                : alumni.jenis_kelamin === 'P'
                                                  ? '♀ Perempuan'
                                                  : '-'
                                        }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                                >
                                    <CreditCard class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >NIK</span
                                    >
                                    <span
                                        class="font-mono text-sm font-bold tracking-wider text-slate-700 dark:text-slate-300"
                                        >{{ alumni.nik || '-' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-violet-50 p-2 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400"
                                >
                                    <Hash class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >NPWP</span
                                    >
                                    <span
                                        class="font-mono text-sm font-bold tracking-wider text-slate-700 dark:text-slate-300"
                                        >{{ alumni.npwp || '-' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <Mail class="h-4 w-4" />
                                </div>
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Email Akun</span
                                    >
                                    <span
                                        class="truncate text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        :title="alumni.user?.email"
                                        >{{ alumni.user?.email || '-' }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Domisili -->
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <Home class="h-4 w-4 text-emerald-500" />
                            <h2
                                class="text-sm font-black text-slate-800 dark:text-white"
                            >
                                Domisili / Alamat Rumah
                            </h2>
                        </div>
                        <div class="flex-1 space-y-4 p-5">
                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                                >
                                    <Map class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Provinsi</span
                                    >
                                    <span
                                        class="text-sm font-bold text-slate-700 dark:text-slate-300"
                                        >{{ alumni.provinsi?.name || '-' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-teal-50 p-2 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400"
                                >
                                    <MapPin class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Kota / Kabupaten</span
                                    >
                                    <span
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        >{{ alumni.kota?.name || '-' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <Home class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Alamat Lengkap</span
                                    >
                                    <span
                                        class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                                        >{{ alumni.alamat_rumah || '-' }}</span
                                    >
                                </div>
                            </div>

                            <div
                                v-if="alumni.latitude_rumah"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="mt-0.5 rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                                >
                                    <Globe class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Koordinat</span
                                    >
                                    <span
                                        class="mt-0.5 w-max rounded bg-emerald-500/10 px-2 py-0.5 font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{
                                            Number(
                                                alumni.latitude_rumah,
                                            ).toFixed(5)
                                        }},
                                        {{
                                            Number(
                                                alumni.longitude_rumah,
                                            ).toFixed(5)
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Status / Karir Saat Ini -->
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm md:col-span-2 lg:col-span-1 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <Briefcase class="h-4 w-4 text-violet-500" />
                            <h2
                                class="text-sm font-black text-slate-800 dark:text-white"
                            >
                                Status & Karir Saat Ini
                            </h2>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col justify-between p-5">
                            <!-- Bekerja / Wirausaha -->
                            <div
                                v-if="
                                    currentCareer &&
                                    ['bekerja', 'wirausaha'].includes(
                                        currentCareer.status,
                                    )
                                "
                                class="space-y-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Building2 class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Perusahaan</span
                                        >
                                        <span
                                            class="text-sm font-bold text-slate-800 dark:text-white"
                                            >{{ currentCareer.employment?.nama_perusahaan || '-' }}</span
                                        >
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                                    >
                                        <Briefcase class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Jabatan</span
                                        >
                                        <span
                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                            >{{ currentCareer.employment?.jabatan || '-' }}</span
                                        >
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-violet-50 p-2 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400"
                                    >
                                        <DollarSign class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Rentang Gaji</span
                                        >
                                        <span
                                            class="text-sm font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                currentCareer.employment?.gaji_min
                                                    ? `${formatCurrency(currentCareer.employment.gaji_min)} – ${formatCurrency(currentCareer.employment.gaji_max)}`
                                                    : '-'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                    >
                                        <MapPin class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Lokasi</span
                                        >
                                        <span
                                            class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                                            >{{ currentCareer.employment?.alamat_perusahaan || '-' }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Lanjut Studi -->
                            <div
                                v-else-if="
                                    (currentCareer &&
                                        currentCareer.status ===
                                            'lanjut_studi') ||
                                    currentEducation
                                "
                                class="space-y-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400"
                                    >
                                        <Building2 class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Perguruan Tinggi</span
                                        >
                                        <span
                                            class="text-sm font-bold text-slate-800 dark:text-white"
                                        >
                                            {{
                                                currentCareer?.status === 'lanjut_studi'
                                                    ? currentCareer.education?.nama_universitas
                                                    : currentEducation?.education?.nama_universitas || '-'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-fuchsia-50 p-2 text-fuchsia-600 dark:bg-fuchsia-950/50 dark:text-fuchsia-400"
                                    >
                                        <GraduationCap class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Program Studi & Jenjang</span
                                        >
                                        <span
                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                currentCareer?.status === 'lanjut_studi'
                                                    ? `${currentCareer.education?.jenjang_pendidikan || ''} - ${currentCareer.education?.program_studi_lanjutan || ''}`
                                                    : `${currentEducation?.education?.jenjang_pendidikan || ''} - ${currentEducation?.education?.program_studi_lanjutan || ''}`
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-pink-50 p-2 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400"
                                    >
                                        <DollarSign class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Sumber Biaya</span
                                        >
                                        <span
                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                currentCareer?.status === 'lanjut_studi'
                                                    ? currentCareer.education?.sumber_biaya
                                                    : currentEducation?.education?.sumber_biaya || '-'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                    >
                                        <MapPin class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Alamat Kampus</span
                                        >
                                        <span
                                            class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                currentCareer?.status === 'lanjut_studi'
                                                    ? currentCareer.education?.alamat_universitas
                                                    : currentEducation?.education?.alamat_universitas || '-'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Belum Bekerja -->
                            <div
                                v-else
                                class="flex flex-1 flex-col items-center justify-center py-6 text-center"
                            >
                                <div
                                    class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400"
                                >
                                    <Info class="h-6 w-6" />
                                </div>
                                <h3
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300"
                                >
                                    Sedang Mencari Kerja
                                </h3>
                                <p
                                    class="mt-1 max-w-[200px] text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Alumni ini belum memiliki karir atau
                                    pendidikan lanjut aktif saat ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== ROW 2: HISTORY SECTIONS GRID (2 COLUMNS) ===== -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Riwayat Karir -->
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <Building2 class="h-4 w-4 text-blue-500" />
                            <h2
                                class="text-sm font-black text-slate-800 dark:text-white"
                            >
                                Riwayat Karir
                            </h2>
                            <Badge
                                variant="secondary"
                                class="ml-auto border-none bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/20 dark:text-blue-400"
                            >
                                {{
                                    alumni.careers?.filter((c: any) =>
                                        ['bekerja', 'wirausaha'].includes(
                                            c.status,
                                        ),
                                    ).length || 0
                                }}
                                entri
                            </Badge>
                        </div>

                        <div class="flex-1 p-6">
                            <div
                                v-if="
                                    alumni.careers?.some((c: any) =>
                                        ['bekerja', 'wirausaha'].includes(
                                            c.status,
                                        ),
                                    )
                                "
                                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                            >
                                <div
                                    v-for="career in alumni.careers.filter(
                                        (c: any) =>
                                            ['bekerja', 'wirausaha'].includes(
                                                c.status,
                                            ),
                                    )"
                                    :key="career.id"
                                    class="relative flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/40 p-4 transition-all duration-200 hover:border-blue-200 hover:bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-blue-900/50 dark:hover:bg-slate-900/60"
                                    :class="{
                                        'border-blue-500/30 bg-blue-50/10 shadow-sm shadow-blue-500/5 dark:border-blue-500/20 dark:bg-blue-950/10':
                                            career.is_current,
                                    }"
                                >
                                    <div class="space-y-1.5">
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs leading-tight font-bold text-slate-800 dark:text-white"
                                            >
                                                {{
                                                    career.employment?.nama_perusahaan &&
                                                    career.employment.nama_perusahaan !== '-'
                                                        ? career.employment.nama_perusahaan
                                                        : 'Instansi Tidak Disebutkan'
                                                }}
                                            </span>
                                            <span
                                                v-if="career.is_current"
                                                class="flex shrink-0 items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-black tracking-wider text-blue-700 uppercase dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                <span
                                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600 dark:bg-blue-400"
                                                ></span>
                                                Saat ini
                                            </span>
                                        </div>
                                        <div
                                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                career.employment?.jabatan &&
                                                career.employment.jabatan !== '-'
                                                    ? career.employment.jabatan
                                                    : 'Staf / Pegawai'
                                            }}
                                        </div>
                                        <div
                                            v-if="
                                                career.employment?.sektor_industri &&
                                                career.employment.sektor_industri !== '-'
                                            "
                                            class="text-[10px] text-slate-400"
                                        >
                                            Sektor: {{ career.employment.sektor_industri }}
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between border-t border-slate-100 pt-2 text-[9px] font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800/80"
                                    >
                                        <span
                                            >Status:
                                            {{
                                                career.status?.replace(
                                                    /_/g,
                                                    ' ',
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div
                                v-else
                                class="flex flex-col items-center justify-center py-12 text-center text-slate-400"
                            >
                                <Building2
                                    class="mb-3 h-10 w-10 text-slate-300 dark:text-slate-700"
                                />
                                <p class="text-sm font-bold">
                                    Belum ada riwayat karir
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Data riwayat karir alumni belum diisi atau
                                    kosong.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Pendidikan Lanjut -->
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <GraduationCap class="h-4 w-4 text-purple-500" />
                            <h2
                                class="text-sm font-black text-slate-800 dark:text-white"
                            >
                                Riwayat Pendidikan Lanjut
                            </h2>
                            <Badge
                                variant="secondary"
                                class="ml-auto border-none bg-purple-50 px-2.5 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-900/20 dark:text-purple-400"
                            >
                                {{
                                    alumni.careers?.filter(
                                        (c: any) => c.status === 'lanjut_studi',
                                    ).length || 0
                                }}
                                entri
                            </Badge>
                        </div>

                        <div class="flex-1 p-6">
                            <div
                                v-if="
                                    alumni.careers?.some(
                                        (c: any) => c.status === 'lanjut_studi',
                                    )
                                "
                                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                            >
                                <div
                                    v-for="edu in alumni.careers.filter(
                                        (c: any) => c.status === 'lanjut_studi',
                                    )"
                                    :key="edu.id"
                                    class="relative flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/40 p-4 transition-all duration-200 hover:border-purple-200 hover:bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-purple-900/50 dark:hover:bg-slate-900/60"
                                    :class="{
                                        'border-purple-500/30 bg-purple-50/10 shadow-sm shadow-purple-500/5 dark:border-purple-500/20 dark:bg-purple-950/10':
                                            edu.is_current,
                                    }"
                                >
                                    <div class="space-y-1.5">
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs leading-tight font-bold text-slate-800 dark:text-white"
                                            >
                                                {{
                                                    edu.education
                                                        .nama_universitas ||
                                                    'Universitas tidak disebutkan'
                                                }}
                                            </span>
                                            <span
                                                v-if="edu.is_current"
                                                class="flex shrink-0 items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-[9px] font-black tracking-wider text-purple-700 uppercase dark:bg-purple-900/30 dark:text-purple-300"
                                            >
                                                <span
                                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-purple-600 dark:bg-purple-400"
                                                ></span>
                                                Saat ini
                                            </span>
                                        </div>
                                        <div
                                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                edu.education
                                                    .program_studi_lanjutan ||
                                                '-'
                                            }}
                                            <span
                                                v-if="
                                                    edu.education
                                                        .jenjang_pendidikan
                                                "
                                                class="text-slate-400"
                                                >({{
                                                    edu.education
                                                        .jenjang_pendidikan
                                                }})</span
                                            >
                                        </div>
                                        <div
                                            v-if="edu.education.sumber_biaya"
                                            class="text-[10px] text-slate-400"
                                        >
                                            Biaya:
                                            {{ edu.education.sumber_biaya }}
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between border-t border-slate-100 pt-2 text-[9px] font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800/80"
                                    >
                                        <span
                                            >Mulai:
                                            {{
                                                edu.tanggal_mulai
                                                    ? new Date(
                                                          edu.tanggal_mulai,
                                                      ).getFullYear()
                                                    : '-'
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div
                                v-else
                                class="flex flex-col items-center justify-center py-12 text-center text-slate-400"
                            >
                                <GraduationCap
                                    class="mb-3 h-10 w-10 text-slate-300 dark:text-slate-700"
                                />
                                <p class="text-sm font-bold">
                                    Belum ada riwayat pendidikan
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Data riwayat pendidikan tinggi lanjut belum
                                    diisi atau kosong.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TraceAdminLayout>
</template>
