<script setup lang="ts">
import { ArrowLeft, Send, X } from "lucide-vue-next";
import { getInitialsAvatar } from "@/composables/useInitials";
import { getToolSlug } from "./editorSuggestions";

const props = defineProps<{
	form: any;
	coverPreview: string | null;
	isCoverVideo: boolean;
	coverFit: "cover" | "contain";
	categoryTags: string[];
	categoryInput: string;
	showCategoryDropdown: boolean;
	filteredCategorySuggestions: any[];
	toolsTags: string[];
	toolsInput: string;
	showToolsDropdown: boolean;
	filteredToolsSuggestions: any[];
	collaboratorInput: string;
	showCollaboratorDropdown: boolean;
	collaboratorSuggestions: any[];
	isLoadingCollaborators: boolean;
}>();

const emit = defineEmits<{
	(e: "update:coverFit", val: "cover" | "contain"): void;
	(e: "update:categoryInput", val: string): void;
	(e: "update:showCategoryDropdown", val: boolean): void;
	(e: "update:toolsInput", val: string): void;
	(e: "update:showToolsDropdown", val: boolean): void;
	(e: "update:collaboratorInput", val: string): void;
	(e: "update:showCollaboratorDropdown", val: boolean): void;
	(e: "close"): void;
	(e: "trigger-file-input", type: "cover"): void;
	(e: "add-category-tag", val: string): void;
	(e: "remove-category-tag", idx: number): void;
	(e: "add-tool-tag", val: string): void;
	(e: "remove-tool-tag", idx: number): void;
	(e: "handle-collaborator-search"): void;
	(e: "add-collaborator-chip", name: string): void;
	(e: "remove-collaborator-chip", idx: number): void;
	(e: "save-draft"): void;
	(e: "publish"): void;
}>();

const delayBlur = (callback: () => void) => {
	globalThis.setTimeout(callback, 200);
};

const getCollabUsername = (c: any) => {
	if (!c) return "";
	if (typeof c === "string") return c.replace(/^@/, "");
	return c.pagi_username || c.name || "";
};

const getCollabAvatar = (c: any) => {
	if (!c) return null;
	const path = typeof c === "string" ? c : (c.foto_path || c.avatar);
	if (!path || path === "null" || path === "undefined") return null;
	if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("data:")) return path;
	const clean = path.replace(/^\/?(storage\/)+/, "");
	return "/storage/" + clean;
};
</script>

<template>
	<div class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-0 md:p-4 overflow-hidden">
		<!-- CONTAINER: Full Screen on Mobile (< md), Centered Modal on Desktop (>= md) -->
		<div class="bg-white dark:bg-slate-900 border-none md:border md:border-slate-200 md:dark:border-slate-800 rounded-none md:rounded-2xl shadow-2xl w-full max-w-5xl h-full md:h-auto md:max-h-[90vh] flex flex-col overflow-hidden relative">
			
			<!-- TOP HEADER (Mobile Step 2 Header & Desktop Close Header) -->
			<div class="h-14 md:h-16 px-4 md:px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 shrink-0">
				<button @click="emit('close')" class="inline-flex items-center gap-1.5 text-xs font-extrabold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors border-none bg-transparent cursor-pointer">
					<ArrowLeft class="w-4 h-4" />
					<span>Kembali ke Editor</span>
				</button>
				
				<div class="flex flex-col items-center">
					<span class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">Detail Karya</span>
					<span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Langkah 2 dari 2</span>
				</div>

				<button @click="emit('close')" class="p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors border-none bg-transparent cursor-pointer">
					<X class="h-5 w-5" />
				</button>
			</div>

			<!-- BODY: Responsive Columns (Stack on Mobile, Grid on Desktop) -->
			<div class="flex-1 flex flex-col md:flex-row gap-6 md:gap-10 overflow-y-auto p-4 md:p-8">
				
				<!-- LEFT COLUMN: Cover Image & Fit Selector -->
				<div class="w-full md:w-2/5 flex flex-col gap-4 shrink-0">
					<div class="flex items-center justify-between">
						<span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Sampul Karya <span class="text-red-500 font-bold">*</span></span>
						<span class="text-[10px] text-slate-400 font-medium">Foto / Video</span>
					</div>

					<div class="aspect-4/3 w-full border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center rounded-2xl relative overflow-hidden group shadow-inner">
						<!-- Blurred backdrop (contain fit) -->
						<img
							v-if="coverPreview && coverFit === 'contain' && !isCoverVideo"
							:src="coverPreview"
							class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none select-none"
							alt="Backdrop blur shadow"
						/>

						<video
							v-if="coverPreview && isCoverVideo"
							:src="coverPreview"
							class="relative z-10 w-full h-full transition-all duration-200"
							:class="coverFit === 'contain' ? 'object-contain max-h-full mx-auto bg-slate-950/40' : 'object-cover absolute inset-0 w-full h-full'"
							autoplay
							loop
							muted
							playsinline
						>
							<track kind="captions" />
						</video>
						<img
							v-else-if="coverPreview"
							:src="coverPreview"
							class="relative z-10 w-full h-full transition-all duration-200"
							:class="coverFit === 'contain' ? 'object-contain max-h-full mx-auto bg-slate-950/40' : 'object-cover absolute inset-0 w-full h-full'"
							alt="Project cover preview"
						/>

						<div v-if="!coverPreview" class="relative z-10 flex flex-col items-center text-center p-4">
							<button @click="emit('trigger-file-input', 'cover')" class="bg-indigo-600 dark:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-indigo-700 transition-all mb-2 shadow-md active:scale-95 cursor-pointer border-none">
								Unggah Foto / Video Sampul
							</button>
							<p class="text-[10px] text-slate-500 dark:text-slate-400 max-w-[200px] leading-relaxed">Maksimal ukuran video 20MB & durasi 1 menit.</p>
						</div>

						<!-- Ubah Cover Button -->
						<button
							v-else
							type="button"
							@click="emit('trigger-file-input', 'cover')"
							class="absolute bottom-3 right-3 z-25 bg-slate-950/80 hover:bg-slate-950 text-white px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md transition-all cursor-pointer border-none"
						>
							Ubah Sampul
						</button>
					</div>

					<!-- Cover Fit Switcher -->
					<div v-if="coverPreview" class="flex flex-col gap-1.5 animate-fade-in">
						<span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tampilan Cover (Fit Mode)</span>
						<div class="bg-slate-100 dark:bg-slate-950 p-1 rounded-xl flex items-center gap-1 border border-slate-200/60 dark:border-slate-800">
							<button
								type="button"
								@click="emit('update:coverFit', 'cover')"
								class="flex-1 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer border-none bg-transparent"
								:class="coverFit === 'cover' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400'"
							>
								Potong Penuh (Crop)
							</button>
							<button
								type="button"
								@click="emit('update:coverFit', 'contain')"
								class="flex-1 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer border-none bg-transparent"
								:class="coverFit === 'contain' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400'"
							>
								Pas Utuh (Contain)
							</button>
						</div>
					</div>
				</div>

				<!-- RIGHT COLUMN: Project Info Form -->
				<div class="w-full md:w-3/5 flex flex-col gap-5">
					<h3 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-2">Informasi Proyek</h3>

					<!-- Title -->
					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-title" class="text-xs font-bold text-slate-800 dark:text-slate-200">Judul Karya <span class="text-red-500">*</span></label>
						<input id="editor-pub-title" v-model="form.title" type="text" placeholder="Berikan judul menarik untuk karya Anda" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-xs font-medium focus:border-indigo-600 dark:focus:border-indigo-400 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 shadow-3xs" />
					</div>

					<!-- Description -->
					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-description" class="text-xs font-bold text-slate-800 dark:text-slate-200">Deskripsi Ringkas <span class="text-red-500">*</span></label>
						<textarea id="editor-pub-description" v-model="form.description" rows="3" placeholder="Tuliskan latar belakang singkat karya atau proses pembuatannya..." class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-xs font-medium focus:border-indigo-600 dark:focus:border-indigo-400 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 resize-none shadow-3xs leading-relaxed"></textarea>
					</div>

					<!-- Tags -->
					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-tags" class="text-xs font-bold text-slate-800 dark:text-slate-200">Tags Kata Kunci <span class="text-red-500">*</span></label>
						<input id="editor-pub-tags" v-model="form.tags" type="text" placeholder="Tambahkan hingga 10 tag dipisahkan koma (misal: ui, mobile, redesign)" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-xs font-medium focus:border-indigo-600 dark:focus:border-indigo-400 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 shadow-3xs" />
					</div>

					<!-- Category Autocomplete Chips -->
					<div class="flex flex-col gap-1.5 relative">
						<label for="editor-pub-category" class="text-xs font-bold text-slate-800 dark:text-slate-200">Kategori <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(Maksimal 3)</span></label>
						<div class="w-full min-h-[44px] p-2 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-3xs">
							<span v-for="(tag, idx) in categoryTags" :key="idx" class="h-7 px-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-xs font-bold text-indigo-700 dark:text-indigo-300 inline-flex items-center gap-1.5 border border-indigo-200/60 dark:border-indigo-800/60">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`"
									 class="w-3.5 h-3.5 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tag }}</span>
								<X class="w-3.5 h-3.5 text-indigo-400 hover:text-indigo-700 cursor-pointer shrink-0" @click="emit('remove-category-tag', idx)" />
							</span>
							<input id="editor-pub-category" :value="categoryInput" @input="emit('update:categoryInput', ($event.target as HTMLInputElement).value)" type="text" :disabled="categoryTags.length >= 3" placeholder="Ketik kategori..." @focus="emit('update:showCategoryDropdown', true)" @blur="delayBlur(() => emit('update:showCategoryDropdown', false))" @keydown.enter.prevent="emit('add-category-tag', categoryInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
						</div>
						<div v-if="showCategoryDropdown && filteredCategorySuggestions.length > 0 && categoryTags.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto">
							<button v-for="cat in filteredCategorySuggestions" :key="cat.name" type="button" @mousedown="emit('add-category-tag', cat.name)" class="w-full h-9 px-3 flex items-center gap-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${cat.slug}/default.svg`"
									 class="w-4 h-4 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ cat.name }}</span>
							</button>
						</div>
					</div>

					<!-- Tools Autocomplete Chips -->
					<div class="flex flex-col gap-1.5 relative">
						<label for="editor-pub-tools" class="text-xs font-bold text-slate-800 dark:text-slate-200">Tools / Software <span class="text-red-500">*</span></label>
						<div class="w-full min-h-[44px] p-2 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-3xs">
							<span v-for="(tag, idx) in toolsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/60 dark:border-slate-700/60">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`"
									 class="w-3.5 h-3.5 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tag }}</span>
								<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="emit('remove-tool-tag', idx)" />
							</span>
							<input id="editor-pub-tools" :value="toolsInput" @input="emit('update:toolsInput', ($event.target as HTMLInputElement).value)" type="text" :disabled="toolsTags.length >= 10" placeholder="Ketik software (misal Figma, VSCode)..." @focus="emit('update:showToolsDropdown', true)" @blur="delayBlur(() => emit('update:showToolsDropdown', false))" @keydown.enter.prevent="emit('add-tool-tag', toolsInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
						</div>
						<div v-if="showToolsDropdown && filteredToolsSuggestions.length > 0 && toolsTags.length < 10" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto">
							<button v-for="tool in filteredToolsSuggestions" :key="tool.name" type="button" @mousedown="emit('add-tool-tag', tool.name)" class="w-full h-9 px-3 flex items-center gap-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tool.name)}/default.svg`"
									 class="w-4 h-4 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tool.name }}</span>
							</button>
						</div>
					</div>

					<!-- Collaborators Field -->
					<div class="flex flex-col gap-1.5 relative">
						<div class="flex justify-between">
							<label for="editor-pub-collab" class="text-xs font-bold text-slate-800 dark:text-slate-200">Kolaborator Tim <span class="text-slate-400 font-normal">(Opsional, Maks 3)</span></label>
							<span class="text-xs text-slate-500 font-bold">{{ form.collaborators.length }}/3</span>
						</div>
						<div class="w-full min-h-[44px] p-2 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-3xs">
							<span v-for="(tag, idx) in form.collaborators" :key="idx" class="h-7 pl-1.5 pr-2.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-black inline-flex items-center gap-1.5 border border-indigo-200/80 dark:border-indigo-800/60" :title="'Username: @' + getCollabUsername(tag)">
								<div class="w-4.5 h-4.5 rounded-full overflow-hidden border border-indigo-300/80 dark:border-indigo-700 shrink-0 bg-indigo-200 dark:bg-indigo-900 flex items-center justify-center">
									<img v-if="getCollabAvatar(tag)" :src="getCollabAvatar(tag)" class="w-full h-full object-cover" />
									<span v-else class="text-[8px] font-black text-indigo-800 dark:text-indigo-200 leading-none">{{ getCollabUsername(tag).charAt(0).toUpperCase() }}</span>
								</div>
								<span>@{{ getCollabUsername(tag) }}</span>
								<X class="w-3.5 h-3.5 text-indigo-400 hover:text-indigo-700 cursor-pointer shrink-0" @click="emit('remove-collaborator-chip', Number(idx))" />
							</span>
							<input id="editor-pub-collab" :value="collaboratorInput" @input="emit('update:collaboratorInput', ($event.target as HTMLInputElement).value); emit('handle-collaborator-search')" type="text" :disabled="form.collaborators.length >= 3" placeholder="Cari kolaborator (@username)..." @focus="emit('update:showCollaboratorDropdown', true)" @blur="delayBlur(() => emit('update:showCollaboratorDropdown', false))" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
						</div>
						<div v-if="showCollaboratorDropdown && form.collaborators.length < 3 && collaboratorInput.trim().length >= 1" class="absolute top-full left-0 right-0 mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl py-1.5 z-150 max-h-52 overflow-y-auto animate-fade-in">
							<div v-if="isLoadingCollaborators" class="px-4 py-3 flex items-center justify-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 select-none">
								<span class="inline-block w-4 h-4 border-2 border-slate-300 dark:border-slate-600 border-t-indigo-600 rounded-full animate-spin"></span>
								<span>Mencari username...</span>
							</div>

							<template v-else-if="collaboratorSuggestions.length > 0">
								<button v-for="u in collaboratorSuggestions" :key="u.id" type="button" @mousedown.prevent="emit('add-collaborator-chip', u)" class="w-full px-4 py-2.5 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer border-none bg-transparent border-b border-slate-100 dark:border-slate-800/60 last:border-none">
									<div class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
										<img v-if="getCollabAvatar(u)" :src="getCollabAvatar(u)!" class="w-full h-full object-cover" alt="Collaborator avatar" />
										<span v-else class="text-slate-800 dark:text-slate-200 font-bold text-xs">{{ (u.pagi_username || u.name).charAt(0).toUpperCase() }}</span>
									</div>
									<div class="min-w-0 flex-1">
										<div class="flex items-center gap-1.5">
											<p class="text-xs font-black text-indigo-600 dark:text-indigo-400 truncate leading-none">@{{ u.pagi_username || u.name }}</p>
											<span v-if="u.is_self" class="px-1.5 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-[9px] font-bold">Anda</span>
										</div>
										<p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate mt-1 leading-none">
											Nama: <span class="font-normal text-slate-600 dark:text-slate-400">{{ u.name }}</span>
										</p>
									</div>
								</button>
							</template>

							<div v-else class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 select-none">
								Tidak ada pengguna ditemukan untuk "<span class="font-bold text-slate-800 dark:text-slate-200">{{ collaboratorInput.trim() }}</span>"
							</div>
						</div>
					</div>

					<!-- Visibility Selector -->
					<div class="flex flex-col gap-1.5 mb-6">
						<label for="editor-pub-visibility" class="text-xs font-bold text-slate-800 dark:text-slate-200">Visibilitas Akses <span class="text-red-500">*</span></label>
						<select id="editor-pub-visibility" v-model="form.visibility" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-xs font-semibold focus:border-indigo-600 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
							<option>Everyone</option>
							<option>Private</option>
						</select>
					</div>
				</div>
			</div>

			<!-- FOOTER ACTION BAR (Sticky on Bottom) -->
			<div class="border-t border-slate-200 dark:border-slate-800 p-4 md:p-5 flex items-center justify-between bg-slate-50 dark:bg-slate-950 shrink-0">
				<button :disabled="form.processing" @click="emit('save-draft')" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all disabled:opacity-50 shadow-3xs cursor-pointer">
					<span v-if="form.processing && !form.is_published">Menyimpan...</span>
					<span v-else>Simpan Draf</span>
				</button>
				
				<button :disabled="form.processing" @click="emit('publish')" class="px-6 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer border-none">
					<span v-if="form.processing && form.is_published" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
					<span>🚀 {{ form.processing && form.is_published ? 'Mempublikasikan...' : 'Publikasikan Sekarang' }}</span>
				</button>
			</div>
		</div>
	</div>
</template>
