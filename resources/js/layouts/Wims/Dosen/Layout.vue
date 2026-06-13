<script setup lang="ts">
import { ClipboardList, LayoutDashboard, Users } from 'lucide-vue-next';
import WimsRoleLayoutShell from '@/layouts/Wims/Shared/RoleLayoutShell.vue';

const navigationItems = [
    {
        label: 'Dashboard',
        href: '/wims/dosen/dashboard',
        icon: LayoutDashboard,
        description: 'Ringkasan bimbingan',
    },
    {
        label: 'Monitoring',
        href: '/wims/dosen/monitoring',
        icon: Users,
        matches: ['/wims/dosen/monitoring'],
        description: 'Presensi dan logbook',
    },
    {
        label: 'Penilaian',
        href: '/wims/dosen/penilaian-mahasiswa',
        icon: ClipboardList,
        matches: ['/wims/dosen/penilaian-mahasiswa'],
        description: 'Penilaian mahasiswa',
    },
];
</script>

<template>
    <WimsRoleLayoutShell
        brand="WIMS Dosen"
        subtitle="Monitoring dan penilaian mahasiswa bimbingan"
        :navigation-items="navigationItems"
    >
        <slot />
    </WimsRoleLayoutShell>
</template>
