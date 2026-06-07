<script setup>
import { computed } from "vue";

const props = defineProps({ data: Object });

const imageUrl = computed(() => props.data?.file?.url || props.data?.url || "");
const caption = computed(() => props.data?.caption || "");
const isStretched = computed(() => props.data?.stretched);
const withBorder = computed(() => props.data?.withBorder);
const withBackground = computed(() => props.data?.withBackground);
</script>

<template>
    <figure :class="['my-12', withBackground ? 'bg-slate-50 p-6 md:p-8 rounded-[2.5rem]' : '']">
        <div :class="[
            'overflow-hidden shadow-2xl shadow-slate-200/60',
            isStretched ? 'w-full' : 'max-w-full mx-auto',
            withBorder ? 'border border-slate-100' : '',
            'rounded-[2rem]'
        ]">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="caption"
                class="w-full h-auto block transform hover:scale-[1.02] transition-transform duration-700"
                loading="lazy"
            />
            <div v-else class="aspect-video bg-slate-100 rounded-[2rem] flex items-center justify-center text-slate-400 text-sm">
                Gambar tidak tersedia
            </div>
        </div>
        <figcaption v-if="caption" class="text-center text-[13px] font-medium text-slate-400 mt-4 leading-relaxed max-w-2xl mx-auto px-4" v-html="caption">
        </figcaption>
    </figure>
</template>
