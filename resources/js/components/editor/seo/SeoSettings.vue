<script setup>
import { Search } from "lucide-vue-next";

const props = defineProps({
	modelValue: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["update:modelValue"]);

const updateField = (field, value) => {
	emit("update:modelValue", { ...props.modelValue, [field]: value });
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="text-[10px] font-bold text-slate-500 mb-1 block">META TITLE</label>
            <input 
                type="text" 
                :value="modelValue.meta_title"
                @input="e => updateField('meta_title', e.target.value)"
                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50" 
                placeholder="Judul SEO..." 
            />
            <div class="flex justify-end mt-1">
                <span class="text-[10px] font-bold" :class="modelValue.meta_title?.length > 60 ? 'text-red-500' : 'text-slate-400'">
                    {{ modelValue.meta_title?.length || 0 }}/60
                </span>
            </div>
        </div>
        
        <div>
            <label class="text-[10px] font-bold text-slate-500 mb-1 block">META DESCRIPTION</label>
            <textarea 
                :value="modelValue.meta_description"
                @input="e => updateField('meta_description', e.target.value)"
                rows="3" 
                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50 p-3" 
                placeholder="Deskripsi meta pencarian..."
            ></textarea>
            <div class="flex justify-end mt-1">
                <span class="text-[10px] font-bold" :class="modelValue.meta_description?.length > 160 ? 'text-red-500' : 'text-slate-400'">
                    {{ modelValue.meta_description?.length || 0 }}/160
                </span>
            </div>
        </div>
    </div>
</template>
