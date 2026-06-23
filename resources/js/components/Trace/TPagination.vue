<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

/* ───── Types ───── */
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

/* ───── Computed ───── */
const shouldRender = computed(() => props.links.length > 3);

const prevLink = computed(() => props.links[0]);
const nextLink = computed(() => props.links[props.links.length - 1]);
const pageLinks = computed(() => props.links.slice(1, -1));

const currentPage = computed(() => {
    const active = pageLinks.value.find((l) => l.active);
    if (!active) return 1;
    const num = parseInt(active.label.replace(/[^0-9]/g, ''), 10);
    return isNaN(num) ? 1 : num;
});

const totalPages = computed(() => pageLinks.value.length);

/* ───── Styles ───── */
const navBtnBase = 'inline-flex items-center gap-1.5 rounded-lg px-3 h-9 text-sm font-medium transition-colors';
const navBtnDisabled = 'text-slate-300 dark:text-slate-600 pointer-events-none';
const navBtnEnabled = 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800';

const pageBtnBase = 'flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-bold transition-colors';
const pageBtnActive = 'bg-[#0C447C] text-white shadow-sm';
const pageBtnInactive = 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800';
const pageBtnDisabled = 'text-slate-300 dark:text-slate-600 pointer-events-none';
</script>

<template>
    <nav
        v-if="shouldRender"
        :class="cn('flex items-center justify-between gap-4', props.class)"
    >
        <!-- Previous -->
        <component
            :is="prevLink.url ? Link : 'span'"
            :href="prevLink.url ?? undefined"
            :preserve-state="prevLink.url ? true : undefined"
            :preserve-scroll="prevLink.url ? true : undefined"
            :class="cn(navBtnBase, prevLink.url ? navBtnEnabled : navBtnDisabled)"
        >
            <ChevronLeft class="h-4 w-4" />
            <span class="hidden sm:inline">Sebelumnya</span>
        </component>

        <!-- Mobile: Page X dari Y -->
        <span class="text-sm font-medium text-slate-600 dark:text-slate-400 sm:hidden">
            Halaman {{ currentPage }} dari {{ totalPages }}
        </span>

        <!-- Desktop: Page numbers -->
        <div class="hidden sm:flex items-center gap-1">
            <template v-for="(link, idx) in pageLinks" :key="idx">
                <component
                    :is="link.url ? Link : 'span'"
                    :href="link.url ?? undefined"
                    :preserve-state="link.url ? true : undefined"
                    :preserve-scroll="link.url ? true : undefined"
                    :class="cn(
                        pageBtnBase,
                        link.active
                            ? pageBtnActive
                            : link.url
                                ? pageBtnInactive
                                : pageBtnDisabled,
                    )"
                    v-html="link.label"
                />
            </template>
        </div>

        <!-- Next -->
        <component
            :is="nextLink.url ? Link : 'span'"
            :href="nextLink.url ?? undefined"
            :preserve-state="nextLink.url ? true : undefined"
            :preserve-scroll="nextLink.url ? true : undefined"
            :class="cn(navBtnBase, nextLink.url ? navBtnEnabled : navBtnDisabled)"
        >
            <span class="hidden sm:inline">Selanjutnya</span>
            <ChevronRight class="h-4 w-4" />
        </component>
    </nav>
</template>
