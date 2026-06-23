<script setup>
import { Link } from '@inertiajs/vue3'
import { UserCircle, Search } from 'lucide-vue-next' 
import Button from '@/components/ui/button/Button.vue';
import { usePage } from '@inertiajs/vue3';
import HeroBannerText from './HeroBannerText.vue';
import HeroBannerCta from './HeroBannerCta.vue';

const linkCta = [
  {
    title: 'Lihat Profil',
    href: '/profile-alumni',
    icon: UserCircle,
    variant: 'default',
  },
  {
    title: 'Lihat Lowongan',
    href: '/lowongan',
    icon: Search,
    variant: 'outline', 
  }
]
</script>

<template>
  <section class="relative flex flex-col gap-8 overflow-hidden bg-accent/40 p-8 border border-border/40 md:p-12">
    <HeroBannerText/>
    <HeroBannerCta :link-cta="linkCta"/>
    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#B6FF00]/15 blur-[100px]"></div>
    <div class="absolute -left-24 -bottom-24 h-72 w-72 rounded-full bg-[#2563EB]/15 blur-[80px]"></div>
  </section>
</template>

<style scoped>
.font-manrope { font-family: 'Manrope', sans-serif; }
.font-inter { font-family: 'Inter', sans-serif; }
</style>