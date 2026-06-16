<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import type { Alumni } from '@/types/alumni';

interface Props {
    alumni: Alumni[];
}

const props = defineProps<Props>();

const getStatusVariant = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'bekerja':
        case 'employed':
            return 'default';
        case 'wirausaha':
        case 'freelance':
        case 'freelancing':
            return 'secondary';
        case 'mencari_kerja':
        case 'mencari kerja':
        case 'seeking':
            return 'destructive';
        case 'studi_lanjut':
        case 'studi lanjut':
            return 'outline';
        default:
            return 'outline';
    }
};

const formatStatus = (status: string) => {
    if (!status) {
return 'Belum Bekerja';
}

    return status
        .replace(/_/g, ' ')
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getDisplayName = (fullname: string) => {
    if (!fullname) return '??';
    return fullname
        .split(' ')
        .map((n) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
};

const getCurrentCareer = (item: Alumni) => {
    if (!item.careers?.length) return null;
    return item.careers.find((c) => c.is_current) || item.careers[0];
};

const getCurrentStatus = (item: Alumni) => {
    const current = getCurrentCareer(item);
    return current?.status || 'mencari_kerja';
};

const getCurrentCompany = (item: Alumni) => {
    const current = getCurrentCareer(item);
    if (!current) return '-';
    return current.employment?.nama_perusahaan || '-';
};
</script>

<template>
    <div
        class="overflow-hidden rounded-3xl border border-border/40 bg-background/50 shadow-sm"
    >
        <Table>
            <TableHeader class="bg-muted/30">
                <TableRow class="hover:bg-transparent">
                    <TableHead class="py-5 font-bold text-foreground"
                        >NAMA LENGKAP</TableHead
                    >
                    <TableHead class="font-bold text-foreground"
                        >TAHUN LULUS</TableHead
                    >
                    <TableHead class="font-bold text-foreground"
                        >STATUS</TableHead
                    >
                    <TableHead class="font-bold text-foreground"
                        >INSTANSI / PERUSAHAAN</TableHead
                    >
                    <TableHead class="font-bold text-foreground"
                        >KELENGKAPAN</TableHead
                    >
                    <TableHead class="text-right font-bold text-foreground"
                        >AKSI</TableHead
                    >
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in alumni" :key="item.id">
                    <TableCell>
                        <div class="flex items-center gap-3">
                            <Avatar class="h-10 w-10 border border-border/50">
                                <AvatarImage
                                    v-if="item.photo_path"
                                    :src="
                                        item.photo_path.startsWith('http')
                                            ? item.photo_path
                                            : `/storage/${item.photo_path}`
                                    "
                                    :alt="item.nama_lengkap || item.user?.name"
                                />
                                <AvatarFallback
                                    class="bg-primary/10 text-xs font-bold text-primary"
                                >
                                    {{
                                        getDisplayName(
                                            item.nama_lengkap ||
                                                item.user?.name,
                                        )
                                    }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col">
                                <span class="font-bold text-foreground">{{
                                    item.nama_lengkap || item.user?.name
                                }}</span>
                                <span class="text-xs text-muted-foreground"
                                    >{{ item.nim }} -
                                    {{ item.program_studi }}</span
                                >
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="font-medium text-muted-foreground">{{
                        item.tahun_lulus
                    }}</TableCell>
                    <TableCell>
                        <Badge
                            :variant="getStatusVariant(getCurrentStatus(item))"
                            class="rounded-full px-3 py-0.5 text-[10px] font-bold tracking-wider uppercase"
                        >
                            {{ formatStatus(getCurrentStatus(item)) }}
                        </Badge>
                    </TableCell>
                    <TableCell class="font-medium text-foreground">
                        {{ getCurrentCompany(item) }}
                    </TableCell>
                    <TableCell>
                        <Badge
                            v-if="item.completeness_percentage === 100"
                            class="rounded-full border border-emerald-100/30 bg-emerald-50 px-3 py-0.5 text-[10px] font-black tracking-wider text-emerald-700 uppercase hover:bg-emerald-50/80 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-400"
                        >
                            Lengkap
                        </Badge>
                        <Badge
                            v-else
                            class="rounded-full border border-amber-100/30 bg-amber-50 px-3 py-0.5 text-[10px] font-black tracking-wider text-amber-700 uppercase hover:bg-amber-50/80 dark:border-amber-900/30 dark:bg-amber-950/30 dark:text-amber-400"
                        >
                            Belum Lengkap
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="`/trace/admin/alumni/${item.id}`"
                                class="px-2 text-sm font-bold text-primary hover:underline"
                            >
                                Lihat Detail
                            </Link>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="alumni.length === 0">
                    <TableCell
                        colspan="6"
                        class="h-32 text-center font-medium text-muted-foreground"
                    >
                        Tidak ada data alumni ditemukan.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
