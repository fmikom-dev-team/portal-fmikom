<script setup lang="ts">
import { Target, TrendingUp, Award, CheckCircle2 } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    summaries: Array<{
        name: string;
        score: number | string;
        detail: string;
    }>;
}>();

const getIcon = (index: number) => {
    const icons = [Target, Award, CheckCircle2, TrendingUp];

    return icons[index % icons.length];
};

const getColor = (index: number) => {
    const colors = [
        'bg-blue-500',
        'bg-emerald-500',
        'bg-amber-500',
        'bg-purple-500',
    ];

    return colors[index % colors.length];
};
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card
            v-for="(item, index) in summaries"
            :key="index"
            class="overflow-hidden border-none shadow-sm transition-all hover:shadow-md"
        >
            <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">
                    {{ item.name }}
                </CardTitle>
                <div :class="`flex h-8 w-8 items-center justify-center rounded-xl ${getColor(index)} text-white shadow-lg`">
                    <component :is="getIcon(index)" class="h-4 w-4" />
                </div>
            </CardHeader>
            <CardContent>
                <div class="text-3xl font-black tracking-tight">
                    {{ item.score }}
                </div>
                <div class="mt-1 flex items-center gap-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                        {{ item.detail }}
                    </span>
                </div>
            </CardContent>
        </Card>

        <div 
            v-if="summaries.length === 0"
            class="col-span-full flex flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 dark:border-slate-800 dark:bg-slate-900/20"
        >
            <p class="text-sm font-medium text-muted-foreground">
                Belum ada data indikator (IKU/LAM) yang terhubung.
            </p>
        </div>
    </div>
</template>
