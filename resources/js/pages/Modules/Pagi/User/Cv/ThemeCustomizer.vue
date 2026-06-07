<script setup lang="ts">
import {
	ChevronDown,
	ChevronUp,
	Eye,
	EyeOff,
	GripVertical,
	LayoutGrid,
	Palette,
	Sliders,
	Type,
} from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps<{
	customization: any;
	templateId?: string;
}>();

const emit = defineEmits<(e: "update:customization", val: any) => void>();

const isCreativeOrCustom = computed(() => {
	return (
		props.templateId === "creative-minimal" || props.templateId === "custom"
	);
});

const primaryColors = [
	{ value: "#1e3a8a", label: "Indigo Blue" },
	{ value: "#0ea5e9", label: "Sky Cyan" },
	{ value: "#111827", label: "Slate Gray" },
	{ value: "#6b21a8", label: "Purple Accent" },
	{ value: "#10b981", label: "Emerald Green" },
	{ value: "#f43f5e", label: "Rose Pink" },
	{ value: "#f59e0b", label: "Amber Orange" },
];

const fontFamilies = [
	{ value: "GT Standard M", label: "GT Standard (Default)" },
	{ value: "Inter", label: "Inter Sans" },
	{ value: "Arial", label: "Arial Clean" },
	{ value: "Georgia", label: "Georgia Serif" },
];

const fontSizes = [
	{ value: "9pt", label: "Kecil (9pt)" },
	{ value: "10pt", label: "Sedang-Kecil (10pt)" },
	{ value: "11pt", label: "Sedang (11pt)" },
	{ value: "12pt", label: "Besar (12pt)" },
];

const spacingOptions = [
	{ value: "compact", label: "Rapat" },
	{ value: "normal", label: "Normal" },
	{ value: "loose", label: "Longgar" },
];

const sectionLabels: Record<string, string> = {
	summary: "Ringkasan Profesional",
	experience: "Pengalaman Kerja",
	education: "Pendidikan",
	organizations: "Organisasi & Kegiatan",
	skills: "Keahlian",
	certifications: "Sertifikasi",
	trainings: "Pelatihan",
	achievements: "Prestasi",
	languages: "Bahasa",
	references: "Referensi",
};

const updateField = (key: string, value: any) => {
	const nextCustomization = {
		...props.customization,
		[key]: value,
	};
	emit("update:customization", nextCustomization);
};

const toggleVisibility = (sectionKey: string) => {
	const visibility = { ...props.customization.sections_visibility } || {};
	visibility[sectionKey] = visibility[sectionKey] === false;
	updateField("sections_visibility", visibility);
};

const moveSection = (index: number, direction: "up" | "down") => {
	const order = [...(props.customization.section_order || [])];
	if (direction === "up" && index > 0) {
		const temp = order[index];
		order[index] = order[index - 1];
		order[index - 1] = temp;
	} else if (direction === "down" && index < order.length - 1) {
		const temp = order[index];
		order[index] = order[index + 1];
		order[index + 1] = temp;
	}
	updateField("section_order", order);
};

const sectionOrder = computed(() => {
	return (
		props.customization.section_order || [
			"summary",
			"experience",
			"education",
			"organizations",
			"skills",
			"certifications",
			"trainings",
			"achievements",
			"languages",
			"references",
		]
	);
});

const isVisible = (sectionKey: string) => {
	const visibility = props.customization.sections_visibility || {};
	return visibility[sectionKey] !== false;
};
</script>

<template>
    <div class="space-y-6 select-none p-4">
        <!-- 1. Accent Color -->
        <div class="space-y-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-550 flex items-center gap-1.5">
                <Palette class="w-4 h-4" />
                Warna Aksen
            </h3>
            
            <div class="flex flex-wrap gap-2">
                <button 
                    v-for="color in primaryColors" 
                    :key="color.value"
                    @click="updateField('primary_color', color.value)"
                    class="w-7 h-7 rounded-full border-2 transition-all relative cursor-pointer"
                    :style="{ backgroundColor: color.value, borderColor: customization.primary_color === color.value ? '#3b82f6' : 'transparent' }"
                    :title="color.label"
                >
                    <span 
                        v-show="customization.primary_color === color.value" 
                        class="absolute inset-0 flex items-center justify-center text-white text-[10px] font-black"
                    >
                        ✓
                    </span>
                </button>
            </div>
            
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[10px] text-slate-500 font-semibold">Custom HEX:</span>
                <input 
                    type="text" 
                    :value="customization.primary_color"
                    @input="updateField('primary_color', ($event.target as HTMLInputElement).value)"
                    class="w-20 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded px-1.5 py-0.5 text-[10px] text-center font-mono focus:outline-none"
                />
            </div>
        </div>

        <div class="h-px bg-slate-100 dark:bg-zinc-800"></div>

        <!-- 2. Typography Settings -->
        <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-550 flex items-center gap-1.5">
                <Type class="w-4 h-4" />
                Tipografi & Spasi
            </h3>

            <!-- Font Family -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500">Gaya Font</label>
                <select 
                    :value="customization.font_family" 
                    @change="updateField('font_family', ($event.target as HTMLSelectElement).value)"
                    class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none"
                >
                    <option v-for="f in fontFamilies" :key="f.value" :value="f.value">
                        {{ f.label }}
                    </option>
                </select>
            </div>

            <!-- Font Size -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500">Ukuran Font</label>
                <select 
                    :value="customization.font_size" 
                    @change="updateField('font_size', ($event.target as HTMLSelectElement).value)"
                    class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none"
                >
                    <option v-for="s in fontSizes" :key="s.value" :value="s.value">
                        {{ s.label }}
                    </option>
                </select>
            </div>

            <!-- Line Height & Margin spacing -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500">Kepadatan Halaman</label>
                <select 
                    :value="customization.spacing" 
                    @change="updateField('spacing', ($event.target as HTMLSelectElement).value)"
                    class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none"
                >
                    <option v-for="o in spacingOptions" :key="o.value" :value="o.value">
                        {{ o.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="h-px bg-slate-100 dark:bg-zinc-800"></div>

        <!-- 3. Section Visibility & Order -->
        <div class="space-y-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-550 flex items-center gap-1.5">
                <LayoutGrid class="w-4 h-4" />
                Urutan & Visibilitas
            </h3>

            <div class="space-y-1 border border-slate-100 dark:border-zinc-850 rounded-2xl overflow-hidden divide-y divide-slate-100 dark:divide-zinc-850">
                <div 
                    v-for="(secKey, index) in sectionOrder" 
                    :key="secKey"
                    class="flex items-center justify-between p-2.5 bg-white dark:bg-zinc-900"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <button 
                            @click="toggleVisibility(secKey)"
                            class="p-1 rounded text-slate-400 hover:text-slate-800 dark:hover:text-zinc-200 bg-transparent border-none cursor-pointer"
                            :title="isVisible(secKey) ? 'Sembunyikan Bagian' : 'Tampilkan Bagian'"
                        >
                            <Eye v-if="isVisible(secKey)" class="w-3.5 h-3.5 text-indigo-500" />
                            <EyeOff v-else class="w-3.5 h-3.5 text-slate-300" />
                        </button>
                        <span 
                            class="text-[11px] font-bold truncate"
                            :class="isVisible(secKey) ? 'text-slate-700 dark:text-zinc-200' : 'text-slate-400 dark:text-zinc-500 line-through'"
                        >
                            {{ sectionLabels[secKey] || secKey }}
                        </span>
                    </div>

                    <!-- Move Controls -->
                    <div class="flex items-center gap-0.5">
                        <button 
                            @click="moveSection(index, 'up')"
                            :disabled="index === 0"
                            class="p-1 rounded text-slate-400 hover:text-slate-800 dark:hover:text-zinc-200 bg-transparent border-none cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <ChevronUp class="w-3.5 h-3.5" />
                        </button>
                        <button 
                            @click="moveSection(index, 'down')"
                            :disabled="index === sectionOrder.length - 1"
                            class="p-1 rounded text-slate-400 hover:text-slate-800 dark:hover:text-zinc-200 bg-transparent border-none cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <ChevronDown class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Skills Display Style (Creative/Custom template only) -->
        <div v-if="isCreativeOrCustom" class="space-y-4">
            <div class="h-px bg-slate-100 dark:bg-zinc-800"></div>
            
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-550 flex items-center gap-1.5">
                <Sliders class="w-4 h-4" />
                Tampilan Keahlian
            </h3>

            <div class="space-y-2">
                <!-- Toggle 1: Checklist -->
                <label class="flex items-center justify-between px-3 py-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 rounded-xl cursor-pointer transition-all select-none">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-200">Tampilkan Checklist (✓)</span>
                    <input 
                        type="checkbox" 
                        :checked="!!customization.skills_show_checkbox"
                        @change="updateField('skills_show_checkbox', !customization.skills_show_checkbox)"
                        class="w-4 h-4 accent-indigo-600 cursor-pointer"
                    />
                </label>

                <!-- Toggle 2: Logo Aplikasi -->
                <label class="flex items-center justify-between px-3 py-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 rounded-xl cursor-pointer transition-all select-none">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-200">Tampilkan Logo Aplikasi</span>
                    <input 
                        type="checkbox" 
                        :checked="!!customization.skills_show_logo"
                        @change="updateField('skills_show_logo', !customization.skills_show_logo)"
                        class="w-4 h-4 accent-indigo-600 cursor-pointer"
                    />
                </label>

                <!-- Toggle 3: Bar Persentase -->
                <label class="flex items-center justify-between px-3 py-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 rounded-xl cursor-pointer transition-all select-none">
                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-200">Tampilkan Bar Persentase</span>
                    <input 
                        type="checkbox" 
                        :checked="!!customization.skills_show_percentage"
                        @change="updateField('skills_show_percentage', !customization.skills_show_percentage)"
                        class="w-4 h-4 accent-indigo-600 cursor-pointer"
                    />
                </label>
            </div>
        </div>
    </div>
</template>
