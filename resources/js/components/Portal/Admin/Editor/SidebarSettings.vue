<script setup>
import {
	Calendar,
	ChevronDown,
	ChevronUp,
	Image as ImageIcon,
	Link2,
	PlusCircle,
	Search,
	Tag,
	X,
} from "lucide-vue-next";
import { ref } from "vue";

defineProps({
	form: Object,
	categories: Array,
	isOpen: {
		type: Boolean,
		default: true,
	},
});

const emit = defineEmits(["close", "update:thumbnail"]);

const sections = ref({
	labels: true,
	schedule: true,
	permalink: true,
	seo: true,
	thumbnail: true,
});

const toggleSection = (section) => {
	sections.value[section] = !sections.value[section];
};

const handleFileChange = (e) => {
	const file = e.target.files[0];
	if (file) {
		emit("update:thumbnail", file);
	}
};

const removeThumbnail = () => {
	emit("update:thumbnail", null);
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
                        Label / Kategori
                    </div>
                    <ChevronUp v-if="sections.labels" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.labels" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                    <select v-model="form.category_id" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50">
                        <option :value="null">Pilih Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
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
                        Deskripsi SEO
                    </div>
                    <ChevronUp v-if="sections.seo" class="w-3.5 h-3.5" />
                    <ChevronDown v-else class="w-3.5 h-3.5" />
                </button>
                
                <div v-if="sections.seo" class="space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                    <textarea v-model="form.meta_description" rows="4" class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-gray-50/50 p-3" placeholder="Ringkasan untuk mesin pencari..."></textarea>
                    <div class="flex justify-end">
                        <span class="text-[10px] font-bold" :class="form.meta_description?.length > 160 ? 'text-red-500' : 'text-slate-400'">
                            {{ form.meta_description?.length || 0 }}/160
                        </span>
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
                    <div v-if="form.thumbnail_preview || form.thumbnail_url" class="relative group aspect-[16/9] rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                        <img :src="form.thumbnail_preview || form.thumbnail_url" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button @click="removeThumbnail" class="p-2 bg-white/20 backdrop-blur-md text-white rounded-full hover:bg-red-500 transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <label v-else class="flex flex-col items-center justify-center w-full aspect-[16/9] border-2 border-dashed border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-blue-200 transition-all group">
                        <div class="p-3 bg-gray-50 rounded-full mb-2 group-hover:bg-blue-50 transition-colors">
                            <PlusCircle class="w-5 h-5 text-slate-400 group-hover:text-blue-500" />
                        </div>
                        <span class="text-[11px] font-bold text-slate-400 group-hover:text-blue-500 uppercase tracking-wider">Unggah Gambar</span>
                        <input type="file" class="hidden" accept="image/*" @change="handleFileChange" />
                    </label>
                    <p class="text-[10px] text-slate-400 text-center italic">Rekomendasi: 1200x675px (16:9)</p>
                </div>
            </div>
        </div>
    </aside>
</template>
