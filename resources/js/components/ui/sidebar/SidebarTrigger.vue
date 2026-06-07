<script setup lang="ts">
import { ChevronLeft, Menu } from "lucide-vue-next";
import type { HTMLAttributes } from "vue";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useSidebar } from "./utils";

const props = defineProps<{
	class?: HTMLAttributes["class"];
}>();

const { isMobile, state, toggleSidebar } = useSidebar();
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-7 w-7 transition-opacity duration-300', state === 'collapsed' && !isMobile ? 'opacity-0 group-hover/header:opacity-100' : '', props.class)"
    @click="toggleSidebar"
  >
    <!-- Modern panel-left SVG Icon requested by the user -->
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-panel-left-icon lucide-panel-left shrink-0">
      <rect width="18" height="18" x="3" y="3" rx="2"/>
      <path d="M9 3v18"/>
    </svg>
    <span class="sr-only">Toggle sidebar</span>
  </Button>
</template>
