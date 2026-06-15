<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user || { name: 'Alumni' });

const firstName = computed(() => user.value.name.split(' ')[0]);
</script>

<template>
  <div class="relative z-10 space-y-6">
    <p class="font-inter text-sm font-bold uppercase tracking-[0.15em] text-primary/80">
      Selamat Datang Kembali, {{ firstName }}!
    </p>

    <div class="space-y-4">
      <h1 class="font-manrope text-4xl font-extrabold leading-[1.1] tracking-tight text-primary md:text-5xl lg:text-6xl text-balance">
        Menghubungkan Alumni, <br class="hidden md:block" />
        <span class="text-[#2563EB]">Memberdayakan</span> Masa Depan
      </h1>
      
      <p class="font-inter max-w-2xl text-lg leading-relaxed text-muted-foreground/90 md:text-xl">
        Platform pelacakan alumni resmi untuk <span class="font-semibold text-foreground">FMIKOM</span>. 
        Bergabunglah dengan komunitas, temukan langkah karier Anda berikutnya, dan bantu kami berkembang melalui <span class="italic">Tracer Study</span>.
      </p>
    </div>
  </div>
</template>

<style scoped>
.text-balance {
  text-wrap: balance;
}
</style>