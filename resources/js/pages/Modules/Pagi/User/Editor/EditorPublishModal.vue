<script setup lang="ts">
import { X } from "lucide-vue-next";
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
</script>

<template>
	<div class="fixed inset-0 z-100 flex items-center justify-center bg-black/60 p-4">
		<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col max-h-[90vh] overflow-hidden relative">
			<!-- Modal Header -->
			<button @click="emit('close')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700">
				<X class="h-6 w-6" />
			</button>
			<div class="flex-1 flex flex-col md:flex-row gap-10 overflow-hidden" style="max-height: calc(90vh - 120px);">
				<!-- Left: Cover (Sticky) -->
				<div class="w-full md:w-2/5 p-10 pb-4 md:pr-0 flex flex-col gap-4 shrink-0 md:sticky md:top-0">
					<span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Project Cover <span class="text-slate-400 font-normal">(required)</span></span>
					<div class="aspect-4/3 w-full border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center rounded-xl relative overflow-hidden group">
						<!-- Blurred cinematic backdrop (only in contain mode) -->
						<img
							v-if="coverPreview && coverFit === 'contain' && !isCoverVideo"
							:src="coverPreview"
							class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none select-none"
							alt="Backdrop blur shadow"
						/>

						<video
							v-if="coverPreview && isCoverVideo"
							:src="coverPreview"
							class="relative z-10 w-full h-full object-cover"
							:class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'absolute inset-0 w-full h-full object-cover'"
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
							class="relative z-10"
							:class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'absolute inset-0 w-full h-full object-cover'"
							alt="Project cover preview"
						/>

						<div v-if="!coverPreview" class="relative z-10 flex flex-col items-center text-center p-4">
							<button @click="emit('trigger-file-input', 'cover')" class="bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 px-5 py-2.5 rounded-xl font-semibold text-xs hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors mb-2 shadow-sm">
								Upload Image/Video
							</button>
							<p class="text-xs text-slate-550 max-w-[200px] bg-white/90 dark:bg-slate-900/90 px-2.5 py-1 rounded-lg">Maksimal ukuran video adalah 20MB & durasi 1 menit.</p>
						</div>

						<!-- Ubah Cover Button overlay if uploaded -->
						<button
							v-else
							type="button"
							@click="emit('trigger-file-input', 'cover')"
							class="absolute bottom-3 right-3 z-25 bg-slate-950/80 hover:bg-slate-950 dark:bg-white/80 dark:hover:bg-white text-white dark:text-slate-950 px-3.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider shadow-md transition-all cursor-pointer border-none"
						>
							Ubah Cover
						</button>
					</div>

					<!-- Cover Fit Selector Switch -->
					<div v-if="coverPreview" class="flex flex-col gap-2 mt-1 animate-fade-in">
						<span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tampilan Cover (Fit Mode)</span>
						<div class="bg-slate-100 dark:bg-slate-950 p-1 rounded-xl flex items-center gap-1 border border-slate-200/40 dark:border-slate-800">
							<button
								type="button"
								@click="emit('update:coverFit', 'cover')"
								class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
								:class="coverFit === 'cover' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
							>
								Penuh / Potong (Crop)
							</button>
							<button
								type="button"
								@click="emit('update:coverFit', 'contain')"
								class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
								:class="coverFit === 'contain' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
							>
								Pas / Utuh (Contain)
							</button>
						</div>
					</div>
				</div>

				<!-- Right: Info (Scrollable) -->
				<div class="w-full md:w-3/5 p-10 pt-4 md:pt-10 md:pl-0 overflow-y-auto flex flex-col gap-6" style="max-height: calc(90vh - 120px);">
					<h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">Project Information</h3>

					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-title" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Title <span class="text-slate-400 font-normal">(required)</span></label>
						<input id="editor-pub-title" v-model="form.title" type="text" placeholder="Give your project a title" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
					</div>

					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-description" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Project Description <span class="text-slate-400 font-normal">(required)</span></label>
						<textarea id="editor-pub-description" v-model="form.description" rows="3" placeholder="Briefly describe what this project is about..." class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 resize-none font-medium"></textarea>
					</div>

					<div class="flex flex-col gap-1.5">
						<label for="editor-pub-tags" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Tags <span class="text-slate-400 font-normal">(required, limit of 10)</span></label>
						<input id="editor-pub-tags" v-model="form.tags" type="text" placeholder="Add up to 10 keywords separated by comma (e.g. website, app, ui)" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
					</div>

					<!-- Category Chip Autocomplete Selector -->
					<div class="flex flex-col gap-1.5 relative">
						<label for="editor-pub-category" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Category <span class="text-slate-400 font-normal">(required, limit of 3)</span></label>
						<div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
							<span v-for="(tag, idx) in categoryTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`"
									 class="w-3.5 h-3.5 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tag }}</span>
								<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="emit('remove-category-tag', idx)" />
							</span>
							<input id="editor-pub-category" :value="categoryInput" @input="emit('update:categoryInput', ($event.target as HTMLInputElement).value)" type="text" :disabled="categoryTags.length >= 3" placeholder="Add category..." @focus="emit('update:showCategoryDropdown', true)" @blur="delayBlur(() => emit('update:showCategoryDropdown', false))" @keydown.enter.prevent="emit('add-category-tag', categoryInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
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

					<!-- Tools Chip Autocomplete Selector -->
					<div class="flex flex-col gap-1.5 relative">
						<label for="editor-pub-tools" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Tools Used <span class="text-slate-400 font-normal">(required)</span></label>
						<div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
							<span v-for="(tag, idx) in toolsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`"
									 class="w-3.5 h-3.5 object-contain"
									 alt=""
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tag }}</span>
								<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="emit('remove-tool-tag', idx)" />
							</span>
							<input id="editor-pub-tools" :value="toolsInput" @input="emit('update:toolsInput', ($event.target as HTMLInputElement).value)" type="text" :disabled="toolsTags.length >= 10" placeholder="Add tools..." @focus="emit('update:showToolsDropdown', true)" @blur="delayBlur(() => emit('update:showToolsDropdown', false))" @keydown.enter.prevent="emit('add-tool-tag', toolsInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
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

					<!-- Collaborators selector field -->
					<div class="flex flex-col gap-1.5 relative">
						<div class="flex justify-between">
							<label for="editor-pub-collab" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Collaborators <span class="text-slate-400 font-normal">(optional, limit of 3)</span></label>
							<span class="text-xs text-slate-550 font-semibold">{{ form.collaborators.length }}/3</span>
						</div>
						<div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
							<span v-for="(tag, idx) in form.collaborators" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
								<span>{{ tag }}</span>
								<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="emit('remove-collaborator-chip', Number(idx))" />
							</span>
							<input id="editor-pub-collab" :value="collaboratorInput" @input="emit('update:collaboratorInput', ($event.target as HTMLInputElement).value); emit('handle-collaborator-search')" type="text" :disabled="form.collaborators.length >= 3" placeholder="Search collaborator..." @focus="emit('update:showCollaboratorDropdown', true)" @blur="delayBlur(() => emit('update:showCollaboratorDropdown', false))" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
						</div>
						<div v-if="showCollaboratorDropdown && collaboratorSuggestions.length > 0 && form.collaborators.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto animate-fade-in">
							<button v-for="u in collaboratorSuggestions" :key="u.id" type="button" @mousedown="emit('add-collaborator-chip', u.name)" class="w-full h-11 px-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
								<img :src="u.foto_path ? (u.foto_path.startsWith('http') ? u.foto_path : '/storage/' + u.foto_path) : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name)" class="w-6 h-6 rounded-full object-cover" alt="Collaborator avatar" />
								<div class="flex flex-col">
									<span>{{ u.name }}</span>
									<span class="text-[10px] text-slate-400 font-normal">@{{ u.pagi_username || u.email }}</span>
								</div>
							</button>
						</div>
					</div>

					<div class="flex flex-col gap-1.5 w-1/2">
						<label for="editor-pub-visibility" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Visibility <span class="text-slate-400 font-normal">(required)</span></label>
						<select id="editor-pub-visibility" v-model="form.visibility" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
							<option>Everyone</option>
							<option>Private</option>
						</select>
						<p class="text-xs text-slate-550 mt-1">
							{{ form.visibility === 'Private' ? 'Hanya dapat dilihat oleh Anda' : 'Dapat diakses dan ditemukan oleh semua orang' }}
						</p>
					</div>
				</div>
			</div>

			<!-- Modal Footer -->
			<div class="border-t border-slate-200 dark:border-slate-800 p-6 flex justify-end gap-3 bg-slate-50 dark:bg-slate-950">
				<button :disabled="form.processing" @click="emit('close')" class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-900 rounded-xl transition-colors disabled:opacity-50">
					Cancel
				</button>
				<button :disabled="form.processing" @click="emit('save-draft')" class="px-5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors disabled:opacity-50">
					<span v-if="form.processing && !form.is_published">Saving...</span>
					<span v-else>Save as Draft</span>
				</button>
				<button :disabled="form.processing" @click="emit('publish')" class="px-5 py-2 text-sm font-semibold text-white bg-[#1769ff] hover:bg-[#1255cc] rounded-xl transition-colors shadow-sm disabled:opacity-50 flex items-center justify-center min-w-[90px]">
					<span v-if="form.processing && form.is_published" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
					<span v-if="form.processing && form.is_published">Publishing...</span>
					<span v-else>Publish</span>
				</button>
			</div>
		</div>
	</div>
</template>
