<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
	defineProps<{
		modelValue: string | number;
		label?: string;
		type?: string;
		placeholder?: string;
		error?: string;
		hint?: string;
		disabled?: boolean;
		required?: boolean;
		id?: string;
	}>(),
	{
		type: "text",
	},
);

const emit = defineEmits<(e: "update:modelValue", value: string) => void>();

const uid = computed(
	() => props.id || `wos-input-${Math.random().toString(36).slice(2, 7)}`,
);

const inputCls = computed(() => {
	const base =
		"w-full h-8 px-3 text-[13px] rounded-lg border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0";
	const state = props.error
		? "border-[#fca5a5] bg-white focus:ring-[#ef4444]/20 focus:border-[#ef4444]"
		: "border-[#d1d5db] bg-white focus:ring-[#2563eb]/15 focus:border-[#2563eb]";
	const dis = props.disabled
		? "bg-[#f9fafb] text-[#9ca3af] cursor-not-allowed"
		: "text-[#111827]";
	return `${base} ${state} ${dis}`;
});
</script>

<template>
    <div class="flex flex-col gap-1" style="font-family: var(--wos-font)">
        <label
            v-if="label"
            :for="uid"
            class="text-[12.5px] font-semibold text-[#374151]"
        >
            {{ label }}
            <span v-if="required" class="text-[#ef4444] ml-0.5" aria-hidden="true">*</span>
        </label>

        <div class="relative">
            <div v-if="$slots.left" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9ca3af]">
                <slot name="left" />
            </div>
            <input
                :id="uid"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :aria-invalid="!!error"
                :aria-describedby="error ? `${uid}-error` : hint ? `${uid}-hint` : undefined"
                :class="[inputCls, $slots.left ? 'pl-9' : '', $slots.right ? 'pr-9' : '']"
                @input="e => emit('update:modelValue', (e.target as HTMLInputElement).value)"
            />
            <div v-if="$slots.right" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9ca3af]">
                <slot name="right" />
            </div>
        </div>

        <p v-if="error" :id="`${uid}-error`" class="text-[11.5px] text-[#dc2626] font-medium" role="alert">
            {{ error }}
        </p>
        <p v-else-if="hint" :id="`${uid}-hint`" class="text-[11.5px] text-[#6b7280]">
            {{ hint }}
        </p>
    </div>
</template>
