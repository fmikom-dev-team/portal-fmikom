<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    CalendarClock,
    CheckCircle2,
    ClipboardList,
    Inbox,
    Mail,
    Users,
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';
import wimsRoutes from '@/routes/wims';

defineOptions({
    layout: WimsAdminLayout,
});

type SummaryProps = {
    active_students?: number;
    companies?: number;
    pending_registrations?: number;
    approved_registrations?: number;
    dokumen_requested?: number;
    dokumen_generated?: number;
    dokumen_failed?: number;
};

type PendingRegistrationItem = {
    id: number;
    student?: {
        name?: string | null;
        email?: string | null;
    } | null;
    company?: {
        name?: string | null;
    } | null;
    date_range?: string | null;
    status?: string | null;
};

const props = defineProps<{
    summary: SummaryProps;
    pendingRegistrations: PendingRegistrationItem[];
}>();

const statCards = computed(() => [
    {
        label: 'Pendaftaran Menunggu',
        value: props.summary.pending_registrations ?? 0,
        hint: 'Baru',
        icon: ClipboardList,
        iconBox: 'text-blue-600',
        valueClass: 'text-blue-600',
    },
    {
        label: 'Mahasiswa Aktif',
        value: props.summary.active_students ?? 0,
        hint: null,
        icon: Users,
        iconBox: 'text-zinc-700',
        valueClass: 'text-zinc-950',
    },
    {
        label: 'Disetujui',
        value: props.summary.approved_registrations ?? 0,
        hint: null,
        icon: CheckCircle2,
        iconBox: 'text-zinc-700',
        valueClass: 'text-zinc-950',
    },
    {
        label: 'Perusahaan Mitra',
        value: props.summary.companies ?? 0,
        hint: null,
        icon: BriefcaseBusiness,
        iconBox: 'text-zinc-700',
        valueClass: 'text-zinc-950',
    },
]);

const registrationStatusLabel = (value?: string | null) => {
    if (value === 'approved') return 'Disetujui';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';
    if (value === 'aktif') return 'Aktif';
    if (value === 'selesai') return 'Selesai';
    return 'Menunggu';
};

const hasPendingRegistrations = computed(
    () => props.pendingRegistrations.length > 0,
);
const featuredRegistration = computed(
    () => props.pendingRegistrations[0] ?? null,
);
const remainingRegistrations = computed(() =>
    props.pendingRegistrations.slice(1),
);

const initials = (name?: string | null) => {
    if (!name) {
        return 'AW';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};
</script>

<template>
    <Head title="Dashboard Admin WIMS" />

    <div
        class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8"
    >
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card
                v-for="card in statCards"
                :key="card.label"
                class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none"
            >
                <CardContent class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500">
                                {{ card.label }}
                            </p>
                            <p
                                class="mt-2 text-[22px] font-bold tracking-tight sm:text-[24px]"
                                :class="card.valueClass"
                            >
                                {{ card.value }}
                            </p>
                        </div>
                        <div
                            class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50"
                        >
                            <component
                                :is="card.icon"
                                class="size-4"
                                :class="card.iconBox"
                            />
                        </div>
                    </div>
                    <p v-if="card.hint" class="mt-2 text-xs font-bold text-blue-600">
                        {{ card.hint }}
                    </p>
                </CardContent>
            </Card>
        </section>

        <div class="grid gap-5">
            <Card
                class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none"
            >
                <CardHeader
                    class="border-b border-zinc-200 px-5 py-4"
                >
                    <div>
                        <div>
                            <CardTitle
                                class="text-[15px] font-bold text-slate-950"
                            >
                                Antrian Pendaftaran
                            </CardTitle>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Pengajuan mahasiswa yang menunggu verifikasi
                                admin.
                            </p>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="px-0 py-0">
                    <template
                        v-if="hasPendingRegistrations && featuredRegistration"
                    >
                        <div class="border-b border-zinc-200 px-5 py-5">
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="flex min-w-0 items-start gap-3.5">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-sm font-bold text-white"
                                    >
                                        {{
                                            initials(
                                                featuredRegistration.student?.name,
                                            )
                                        }}
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <p
                                                class="text-sm font-bold text-zinc-950"
                                            >
                                                {{
                                                    featuredRegistration.student?.name ||
                                                    '-'
                                                }}
                                            </p>
                                            <Badge
                                                class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 shadow-none"
                                            >
                                                {{ registrationStatusLabel(featuredRegistration.status) }}
                                            </Badge>
                                        </div>

                                        <div
                                            class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-zinc-600"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Mail
                                                    class="size-4 text-zinc-400"
                                                />
                                                <span class="truncate">{{
                                                    featuredRegistration.student?.email ||
                                                    '-'
                                                }}</span>
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CalendarClock
                                                    class="size-4 text-zinc-400"
                                                />
                                                <span>{{
                                                    featuredRegistration.date_range ||
                                                    '-'
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center gap-2 self-end lg:self-start"
                                >
                                    <Button
                                        as-child
                                        class="h-9 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]"
                                    >
                                        <Link
                                            :href="
                                                wimsRoutes.admin.registrations.index()
                                                    .url
                                            "
                                        >
                                            Detail
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="remainingRegistrations.length"
                            class="overflow-x-auto"
                        >
                            <div class="min-w-full">
                                <div
                                    class="grid grid-cols-[minmax(0,1fr)_140px] border-b border-zinc-200 bg-zinc-50 px-4 py-2 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase sm:px-5"
                                >
                                    <span>Mahasiswa</span>
                                    <span class="text-right">Status</span>
                                </div>

                                <div
                                    v-for="item in remainingRegistrations"
                                    :key="item.id"
                                    class="grid grid-cols-[minmax(0,1fr)_140px] items-center gap-3 border-b border-zinc-100 px-4 py-3 transition-colors last:border-b-0 hover:bg-zinc-50 sm:px-5"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-bold text-zinc-900"
                                        >
                                            {{ item.student?.name || '-' }}
                                        </p>
                                        <p
                                            class="mt-0.5 truncate text-xs text-zinc-500"
                                        >
                                            {{ item.student?.email || '-' }}
                                        </p>
                                    </div>
                                    <div class="flex justify-end">
                                        <Badge
                                            class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 shadow-none"
                                        >
                                            {{ registrationStatusLabel(item.status) }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="px-5 py-6 text-center">
                            <div
                                class="mx-auto flex size-10 items-center justify-center rounded-full bg-zinc-100"
                            >
                                <Inbox class="size-5 text-zinc-400" />
                            </div>
                            <p class="mt-3 text-sm text-zinc-500">
                                Menunggu pendaftaran lainnya masuk ke sistem.
                            </p>
                        </div>
                    </template>

                    <div v-else class="px-5 py-7 text-center">
                        <div
                            class="mx-auto flex size-10 items-center justify-center rounded-full bg-zinc-100"
                        >
                                <Inbox class="size-5 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500">
                            Tidak ada pendaftaran menunggu saat ini.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
