<script setup lang="ts">
import { ClipboardList, LayoutDashboard, Users } from 'lucide-vue-next';
import WimsRoleLayoutShell from '@/layouts/Wims/Shared/RoleLayoutShell.vue';

const navigationItems = [
    {
        label: 'Dashboard',
        href: '/wims/mitra/dashboard',
        icon: LayoutDashboard,
        description: 'Ringkasan mitra',
    },
    {
        label: 'Monitoring',
        href: '/wims/mitra/monitoring',
        icon: Users,
        matches: ['/wims/mitra/monitoring'],
        description: 'Kehadiran mahasiswa',
    },
    {
        label: 'Penilaian',
        href: '/wims/mitra/penilaian-mahasiswa',
        icon: ClipboardList,
        matches: ['/wims/mitra/penilaian-mahasiswa'],
        description: 'Evaluasi mahasiswa',
    },
];
</script>

<template>
    <WimsRoleLayoutShell
        brand="WIMS Mitra"
        subtitle="Operasional mahasiswa magang perusahaan"
        :navigation-items="navigationItems"
        brand-style="company"
    >
        <slot />
    </WimsRoleLayoutShell>
</template>
