<script setup lang="ts">
import { Trash2, PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { OpsiJawaban } from '@/types/trace';

const props = defineProps<{
    options: OpsiJawaban[];
    questionTipe: string;
}>();

const emit = defineEmits<{
    (e: 'add'): void;
    (e: 'remove', index: number): void;
}>();
</script>

<template>
    <div
        v-if="['radio', 'checkbox', 'dropdown'].includes(questionTipe)"
        class="space-y-2 pl-4"
    >
        <div
            v-for="(option, oIndex) in options"
            :key="oIndex"
            class="grid grid-cols-[auto_minmax(0,1fr)_5rem_auto] items-center gap-3"
        >
            <div
                class="h-4 w-4 rounded-full border border-slate-300 dark:border-slate-700"
                :class="
                    questionTipe === 'checkbox'
                        ? 'rounded-sm'
                        : ''
                "
            ></div>
            <Input
                v-model="option.label"
                class="h-9 border-none bg-transparent px-0 font-medium focus-visible:ring-0"
            />
            <Input
                v-model.number="option.nilai"
                type="number"
                placeholder="Skor"
                class="h-8 rounded-lg border-slate-200 bg-white px-2 text-xs font-bold dark:bg-slate-900"
                title="Nilai numerik untuk analitik dan export"
            />
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-muted-foreground/30 opacity-0 transition-opacity group-hover/question:opacity-100 hover:text-destructive"
                @click="emit('remove', Number(oIndex))"
            >
                <Trash2 class="h-3 w-3" />
            </Button>
        </div>
        <Button
            variant="ghost"
            size="sm"
            class="h-8 gap-2 px-0 text-[11px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
            @click="emit('add')"
        >
            <PlusCircle class="h-3.5 w-3.5" />
            TAMBAH OPSI
        </Button>
    </div>
</template>
