<script setup>
/**
 * PublicLayout.vue — Master layout wrapper for all public-facing portal pages.
 * Combines PublicNavbar + slot (main content) + PublicFooter.
 *
 * Usage:
 *   <PublicLayout title="Tentang FMIKOM" :breadcrumbs="[{label:'Profil'},{label:'Tentang FMIKOM'}]">
 *     <!-- your page content here -->
 *   </PublicLayout>
 */

import { Head } from "@inertiajs/vue3";
import { ChevronRight, Home } from "lucide-vue-next";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";

const props = defineProps({
	title: {
		type: String,
		default: "Portal FMIKOM",
	},
	description: {
		type: String,
		default: "",
	},
	breadcrumbs: {
		type: Array,
		default: () => [],
	},
	showBreadcrumb: {
		type: Boolean,
		default: true,
	},
	heroTitle: String,
	heroSubtitle: String,
	heroClass: {
		type: String,
		default: "bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800",
	},
	hideHero: {
		type: Boolean,
		default: false,
	},
	hideContainer: {
		type: Boolean,
		default: false,
	},
});
</script>

<template>
    <Head>
        <title>{{ title + ' — Portal FMIKOM' }}</title>
        <meta v-if="description" name="description" :content="description">
    </Head>

    <div class="min-h-screen bg-white font-sans antialiased text-slate-900">
        <!-- Navbar (sticky glass) -->
        <PublicNavbar />

        <!-- Hero / Page Header -->
        <div v-if="!hideHero && (heroTitle || title)" :class="['relative overflow-hidden py-14 lg:py-20 text-white', heroClass]">
            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC40Ij48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00em0tMTYgMGMwLTIuMiAxLjgtNCA0LTRzNCAxLjggNCA0LTEuOCA0LTQgNC00LTEuOC00LTR6TTQgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+')]"></div>
            <div class="relative mx-auto max-w-7xl px-4 xl:px-0">
                <!-- Breadcrumbs -->
                <div v-if="showBreadcrumb" class="flex items-center gap-2 text-sm text-blue-200 mb-5">
                    <a href="/" class="hover:text-white transition-colors flex items-center gap-1"><Home class="w-3.5 h-3.5"/>Home</a>
                    <template v-for="(crumb, i) in breadcrumbs" :key="i">
                        <ChevronRight class="w-3.5 h-3.5 opacity-60"/>
                        <a v-if="crumb.href" :href="crumb.href" class="hover:text-white transition-colors">{{ crumb.label }}</a>
                        <span v-else class="text-white font-medium">{{ crumb.label }}</span>
                    </template>
                </div>
                <h1 class="text-3xl lg:text-5xl font-black tracking-tight">{{ heroTitle || title }}</h1>
                <p v-if="heroSubtitle" class="mt-3 text-lg text-blue-100 max-w-2xl">{{ heroSubtitle }}</p>
            </div>
        </div>

        <!-- Main Content -->
        <main v-if="hideContainer" class="w-full">
            <slot />
        </main>
        <main v-else class="mx-auto max-w-7xl px-4 xl:px-0 py-12 lg:py-16">
            <slot />
        </main>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>
