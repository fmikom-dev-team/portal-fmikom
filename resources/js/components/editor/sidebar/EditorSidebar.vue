<script setup>
import {
	Calendar,
	ChevronDown,
	ChevronUp,
	Image as ImageIcon,
	Link2,
	Search,
	Tag,
	Text,
	X,
} from "lucide-vue-next";
import axios from "axios";
import { ref, watch } from "vue";
import ThumbnailSettings from "../media/ThumbnailSettings.vue";
import SeoSettings from "../seo/SeoSettings.vue";
import MediaPickerModal from "../media/MediaPickerModal.vue";

const props = defineProps({
	form: Object,
	categories: Array,
	isOpen: {
		type: Boolean,
		default: true,
	},
});

const newCategoryName = ref("");
const isAddingCategory = ref(false);
const localCategories = ref([]);

const showMediaPicker = ref(false);
const activeMediaTarget = ref(""); // 'thumbnail' | 'ogImage'

const openMediaPicker = (target) => {
	activeMediaTarget.value = target;
	showMediaPicker.value = true;
};

const handleMediaSelect = (path) => {
	if (activeMediaTarget.value === "thumbnail") {
		emit("update:thumbnail", path);
	} else if (activeMediaTarget.value === "ogImage") {
		emit("update:ogImage", path);
	}
	showMediaPicker.value = false;
};

watch(() => props.categories, (newVal) => {
	if (newVal) {
		localCategories.value = [...newVal];
	}
}, { deep: true, immediate: true });

const submitNewCategory = async () => {
	if (!newCategoryName.value.trim()) return;
	try {
		const slug = newCategoryName.value
			.toLowerCase()
			.replace(/[^\w\s-]/g, "")
			.replace(/[\s_-]+/g, "-")
			.replace(/(?:^-+)|(?:-+$)/g, "");
			
		const response = await axios.post("/portal-admin/categories", {
			name: newCategoryName.value.trim(),
			slug: slug,
		}, {
			headers: { 'Accept': 'application/json' }
		});
		
		if (response.data.success) {
			const newCat = response.data.category;
			localCategories.value.push(newCat);
			props.form.category_id = newCat.id;
			newCategoryName.value = "";
			isAddingCategory.value = false;
		}
	} catch (error) {
		console.error("Gagal menambahkan kategori:", error);
		alert(error.response?.data?.message || "Gagal menambahkan kategori.");
	}
};

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
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500 block">KATEGORI</label>
                            <button 
                                v-if="!isAddingCategory" 
                                type="button" 
                                @click="isAddingCategory = true" 
                                class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition-colors"
                            >
                                + Tambah Kategori
                            </button>
                        </div>
                        
                        <div v-if="isAddingCategory" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-150">
                            <input 
                                type="text" 
                                v-model="newCategoryName" 
                                placeholder="Nama kategori baru..." 
                                @keydown.enter.prevent="submitNewCategory"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50" 
                            />
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="submitNewCategory"
                                    class="px-2.5 py-1 text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors cursor-pointer"
                                >
                                    Simpan
                                </button>
                                <button 
                                    type="button" 
                                    @click="isAddingCategory = false; newCategoryName = ''"
                                    class="px-2.5 py-1 text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>

                        <select v-else v-model="form.category_id" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50">
                            <option :value="null">Pilih Kategori</option>
                            <option v-for="cat in localCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
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
                        <button 
                            type="button" 
                            @click="openMediaPicker('ogImage')"
                            class="w-full mt-2 py-2 px-3 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-[11px] font-bold text-slate-600 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <ImageIcon class="w-3.5 h-3.5 text-slate-400" />
                            Pilih dari Galeri
                        </button>
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
                    <button 
                        type="button" 
                        @click="openMediaPicker('thumbnail')"
                        class="w-full py-2 px-3 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-[11px] font-bold text-slate-600 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <ImageIcon class="w-3.5 h-3.5 text-slate-400" />
                        Pilih dari Galeri
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <MediaPickerModal 
        v-if="showMediaPicker" 
        @select="handleMediaSelect" 
        @close="showMediaPicker = false" 
    />
</template>
