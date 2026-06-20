import { computed, ref } from "vue";
import { categorySuggestions, toolsSuggestions } from "./editorSuggestions";

export function useEditorTags() {
	const categoryInput = ref("");
	const categoryTags = ref<string[]>([]);
	const showCategoryDropdown = ref(false);

	const toolsInput = ref("");
	const toolsTags = ref<string[]>([]);
	const showToolsDropdown = ref(false);

	const addCategoryTag = (catName: string) => {
		const clean = catName.trim();
		if (
			clean &&
			categoryTags.value.length < 3 &&
			!categoryTags.value.includes(clean)
		) {
			categoryTags.value.push(clean);
		}
		categoryInput.value = "";
		showCategoryDropdown.value = false;
	};

	const removeCategoryTag = (idx: number) => {
		categoryTags.value.splice(idx, 1);
	};

	const filteredCategorySuggestions = computed(() => {
		const q = categoryInput.value.toLowerCase().trim();
		if (!q)
			return categorySuggestions.filter(
				(c) => !categoryTags.value.includes(c.name),
			);
		return categorySuggestions.filter(
			(c) =>
				c.name.toLowerCase().includes(q) &&
				!categoryTags.value.includes(c.name),
		);
	});

	const addToolTag = (toolName: string) => {
		const clean = toolName.trim();
		if (
			clean &&
			toolsTags.value.length < 10 &&
			!toolsTags.value.includes(clean)
		) {
			toolsTags.value.push(clean);
		}
		toolsInput.value = "";
		showToolsDropdown.value = false;
	};

	const removeToolTag = (idx: number) => {
		toolsTags.value.splice(idx, 1);
	};

	const filteredToolsSuggestions = computed(() => {
		const q = toolsInput.value.toLowerCase().trim();
		if (!q)
			return toolsSuggestions.filter((t) => !toolsTags.value.includes(t.name));
		return toolsSuggestions.filter(
			(t) =>
				t.name.toLowerCase().includes(q) && !toolsTags.value.includes(t.name),
		);
	});

	return {
		categoryInput,
		categoryTags,
		showCategoryDropdown,
		toolsInput,
		toolsTags,
		showToolsDropdown,
		filteredCategorySuggestions,
		filteredToolsSuggestions,
		addCategoryTag,
		removeCategoryTag,
		addToolTag,
		removeToolTag,
	};
}
