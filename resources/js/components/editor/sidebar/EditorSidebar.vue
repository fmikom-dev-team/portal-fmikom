<script setup>
import {
	Calendar,
	ChevronDown,
	ChevronUp,
	Image as ImageIcon,
	LayoutList,
	Link2,
	PlusCircle,
	Search,
	Tag,
	Text,
	X,
} from "lucide-vue-next";
import { ref, watch } from "vue";
import ThumbnailSettings from "../media/ThumbnailSettings.vue";
import SeoSettings from "../seo/SeoSettings.vue";

const props = defineProps({
	form: Object,
	categories: Array,
	isOpen: {
		type: Boolean,
		default: true,
	},
});

const emit = defineEmits(["close", "update:thumbnail", "update:ogImage"]);

const sections = ref({
	labels: true,
	schedule: true,
	permalink: true,
	seo: true,
	thumbnail: true,
	excerpt: true,
});

const toggleSection = (section) => {
	sections.value[section] = !sections.value[section];
};

const handleFileChange = (e, type) => {
	const file = e.target.files[0];
	if (file) {
		if (type === "thumbnail") emit("update:thumbnail", file);
		if (type === "ogImage") emit("update:ogImage", file);
	}
};

const removeImage = (type) => {
	if (type === "thumbnail") emit("update:thumbnail", null);
	if (type === "ogImage") emit("update:ogImage", null);
};

// Tag handling
const tagInput = ref("");
const addTag = () => {
	if (tagInput.value.trim()) {
		if (!props.form.tags) props.form.tags = [];
		if (!props.form.tags.includes(tagInput.value.trim())) {
			props.form.tags.push(tagInput.value.trim());
		}
		tagInput.value = "";
	}
};
const removeTag = (index) => {
	props.form.tags.splice(index, 1);
};
</script>

<template>
    <!-- Mobile Overlay -->
    <transition name="fade">
        <div v-if="isOpen" @click="$emit('close')" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-[70] lg:hidden"></div>
    </transition>

    <aside 
        :class="[
            'fixed lg:relative inset-y-0 right-0 z-[80] lg:z-0 w-80 bg-white border-l border-gray-100 h-full overflow-y-auto transition-all duration-300 transform shadow-[-20px_0_40px_rgba(0,0,0,0.1)] lg:shadow-none',
            isOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'
        ]"
    >
        <div class="lg:hidden p-5 border-b border-gray-50 flex items-center justify-between bg-white sticky top-0 z-10">
            <span class="text-xs font-black text-slate-900 uppercase tracking-widest">Pengaturan Berita</span>
            <button @click="$emit('close')" class="p-2 hover:bg-gray-50 rounded-full transition-colors">
                <X class="w-4 h-4 text-slate-400" />
            </button>
        </div>
        <div class="p-5 space-y-6">
            <!-- Labels -->
            <div class="space-y-3">
                <button @click="toggleSection('labels')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <Tag class="w-3.5 h-3.5" />
                        Kategori & Tag
                    </div>
                    <ChevronUp v-if="sections.labels" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.labels" class="space-y-4 animate-in fade-in slide-in-from-top-1 duration-200">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">KATEGORI</label>
                        <select v-model="form.category_id" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50">
                            <option :value="null">Pilih Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">TAGS</label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <span v-for="(tag, index) in form.tags || []" :key="index" class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded flex items-center gap-1">
                                {{ tag }}
                                <button @click="removeTag(index)" class="text-gray-400 hover:text-red-500"><X class="w-3 h-3" /></button>
                            </span>
                        </div>
                        <input type="text" v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Ketik tag & Enter" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50" />
                    </div>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="space-y-3">
                <button @click="toggleSection('excerpt')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <Text class="w-3.5 h-3.5" />
                        Kutipan (Excerpt)
                    </div>
                    <ChevronUp v-if="sections.excerpt" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.excerpt" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                    <textarea v-model="form.excerpt" rows="3" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50 p-3" placeholder="Ringkasan singkat artikel..."></textarea>
                </div>
            </div>

            <!-- Schedule -->
            <div class="space-y-3">
                <button @click="toggleSection('schedule')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-3.5 h-3.5" />
                        Jadwal Publikasi
                    </div>
                    <ChevronUp v-if="sections.schedule" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.schedule" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                    <input type="datetime-local" v-model="form.published_at" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50" />
                    <p class="text-[10px] text-slate-400 italic">Kosongkan untuk segera publikasi.</p>
                </div>
            </div>

            <!-- Permalink -->
            <div class="space-y-3">
                <button @click="toggleSection('permalink')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <Link2 class="w-3.5 h-3.5" />
                        Tautan Permanen
                    </div>
                    <ChevronUp v-if="sections.permalink" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.permalink" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold">/</span>
                        <input type="text" v-model="form.slug" class="w-full pl-6 text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50" placeholder="url-berita-anda" />
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="space-y-3">
                <button @click="toggleSection('seo')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <Search class="w-3.5 h-3.5" />
                        SEO & Meta
                    </div>
                    <ChevronUp v-if="sections.seo" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.seo" class="space-y-4 animate-in fade-in slide-in-from-top-1 duration-200">
                    <SeoSettings :modelValue="form" @update:modelValue="val => Object.assign(form, val)" />

                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-2 block">OG IMAGE (SOCIAL MEDIA)</label>
                        <ThumbnailSettings 
                            :previewUrl="form.og_image_preview || form.og_image_url" 
                            label="Upload OG Image"
                            aspectRatio="aspect-[1200/630]"
                            @update="file => handleFileChange({target: {files: [file]}}, 'ogImage')"
                            @remove="removeImage('ogImage')"
                        />
                    </div>
                </div>
            </div>

            <!-- Thumbnail -->
            <div class="space-y-3 pb-10">
                <button @click="toggleSection('thumbnail')" class="flex items-center justify-between w-full text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <ImageIcon class="w-3.5 h-3.5" />
                        Thumbnail Utama
                    </div>
                    <ChevronUp v-if="sections.thumbnail" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.thumbnail" class="space-y-3 animate-in fade-in slide-in-from-top-1 duration-200">
                    <ThumbnailSettings 
                        :previewUrl="form.thumbnail_preview || form.thumbnail_url" 
                        label="Unggah Gambar"
                        aspectRatio="aspect-[16/9]"
                        recommendation="Rekomendasi: 1200x675px (16:9)"
                        @update="file => handleFileChange({target: {files: [file]}}, 'thumbnail')"
                        @remove="removeImage('thumbnail')"
                    />
                </div>
            </div>
        </div>
    </aside>
</template>
