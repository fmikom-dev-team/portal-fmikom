<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import {
    Bell,
    ChevronRight,
    Clock3,
    CircleCheckBig,
    CircleX,
    AlertCircle,
    FileText,
} from 'lucide-vue-next';

type NotificationItem = {
    id: number | string;
    notification_key?: string;
    title: string;
    message: string;
    href: string;
    time?: string | null;
    readAt?: string | null;
    tone?: 'amber' | 'blue' | 'green' | 'rose' | 'slate';
};

const props = withDefaults(
    defineProps<{
        items: NotificationItem[];
        count?: number;
        ariaLabel?: string;
    }>(),
    {
        count: 0,
        ariaLabel: 'Notifikasi',
    },
);

const open = ref(false);
const root = ref<HTMLElement | null>(null);

const visibleItems = computed(() =>
    [...props.items]
        .sort((left, right) => {
            const leftTime = left.time ? new Date(left.time).getTime() : 0;
            const rightTime = right.time ? new Date(right.time).getTime() : 0;

            if (leftTime !== rightTime) {
                return rightTime - leftTime;
            }

            const leftId = typeof left.id === 'number' ? left.id : parseInt(String(left.id), 10) || 0;
            const rightId = typeof right.id === 'number' ? right.id : parseInt(String(right.id), 10) || 0;

            return rightId - leftId;
        })
        .slice(0, 6),
);

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}

function openItem(item: NotificationItem) {
    if (item.readAt) {
        close();
        router.visit(item.href);
        return;
    }

    router.post(
        `/notifications/${item.id}/read`,
        { redirect_to: item.href },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => close(),
        },
    );
}

function markAllAsRead() {
    if (props.count <= 0) {
        close();
        return;
    }

    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => close(),
        },
    );
}

function handleDocumentClick(event: MouseEvent) {
    if (!root.value) return;
    if (!root.value.contains(event.target as Node)) {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleDocumentClick);
});

function toneClasses(tone?: NotificationItem['tone']) {
    return {
        amber: 'border-amber-100 bg-amber-50 text-amber-600',
        blue: 'border-blue-100 bg-blue-50 text-blue-600',
        green: 'border-emerald-100 bg-emerald-50 text-emerald-600',
        rose: 'border-rose-100 bg-rose-50 text-rose-600',
        slate: 'border-slate-200 bg-slate-100 text-slate-600',
    }[tone ?? 'slate'];
}

function iconForTone(tone?: NotificationItem['tone']) {
    return {
        amber: AlertCircle,
        blue: Clock3,
        green: CircleCheckBig,
        rose: CircleX,
        slate: FileText,
    }[tone ?? 'slate'];
}

function formatTime(value?: string | null) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const diff = Date.now() - date.getTime();
    const minute = 60 * 1000;
    const hour = 60 * minute;
    const day = 24 * hour;

    if (diff < minute) return 'Baru saja';
    if (diff < hour) return `${Math.max(1, Math.floor(diff / minute))} menit lalu`;
    if (diff < day) return `${Math.max(1, Math.floor(diff / hour))} jam lalu`;
    if (diff < 7 * day) return `${Math.max(1, Math.floor(diff / day))} hari lalu`;

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="relative rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 transition-colors hover:bg-slate-50"
            :aria-label="ariaLabel"
            :aria-expanded="open"
            @click.stop="toggle"
        >
            <Bell class="size-4" />
            <span
                v-if="count > 0"
                class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white"
            >
                {{ count > 9 ? '9+' : count }}
            </span>
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <div
                v-if="open"
                class="absolute right-0 z-50 mt-2 w-[22rem] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ ariaLabel }}</p>
                        <p class="text-xs text-slate-400">
                            {{ count > 0 ? `${count} pembaruan terbaru` : 'Tidak ada pembaruan baru' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="count > 0"
                            type="button"
                            class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100"
                            @click.stop="markAllAsRead"
                        >
                            Tandai semua dibaca
                        </button>
                        <Bell class="size-4 text-slate-400" />
                    </div>
                </div>

                <div class="max-h-[26rem] overflow-y-auto">
                    <template v-if="visibleItems.length">
                        <button
                            v-for="item in visibleItems"
                            :key="item.id"
                            type="button"
                            class="flex w-full gap-3 border-b border-slate-50 px-4 py-3 text-left transition hover:bg-slate-50"
                            @click="openItem(item)"
                        >
                            <div
                                class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl border"
                                :class="toneClasses(item.tone)"
                            >
                                <component :is="iconForTone(item.tone)" class="size-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ item.title }}
                                    </p>
                                    <ChevronRight class="mt-0.5 size-3.5 shrink-0 text-slate-300" />
                                </div>
                                <p class="mt-0.5 line-clamp-2 text-xs leading-5 text-slate-500">
                                    {{ item.message }}
                                </p>
                                <div class="mt-1 flex items-center gap-2">
                                    <p v-if="formatTime(item.time)" class="text-[10px] text-slate-400">
                                        {{ formatTime(item.time) }}
                                    </p>
                                    <span
                                        v-if="!item.readAt"
                                        class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700"
                                    >
                                        Baru
                                    </span>
                                </div>
                            </div>
                        </button>
                    </template>

                    <div v-else class="px-4 py-10 text-center">
                        <div
                            class="mx-auto mb-3 grid size-11 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400"
                        >
                            <Bell class="size-5" />
                        </div>
                        <p class="text-sm font-medium text-slate-900">Belum ada notifikasi</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Notifikasi akan muncul saat ada pembaruan surat.
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
