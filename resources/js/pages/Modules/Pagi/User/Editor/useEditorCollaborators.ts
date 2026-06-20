import axios from "axios";
import { ref } from "vue";

export function useEditorCollaborators(form: any) {
	const collaboratorInput = ref("");
	const showCollaboratorDropdown = ref(false);
	const collaboratorSuggestions = ref<any[]>([]);
	const isLoadingCollaborators = ref(false);
	let searchTimeout: any = null;

	const handleCollaboratorSearch = () => {
		if (searchTimeout) clearTimeout(searchTimeout);
		const q = collaboratorInput.value.trim();
		if (q.length < 1) {
			collaboratorSuggestions.value = [];
			showCollaboratorDropdown.value = false;
			return;
		}
		showCollaboratorDropdown.value = true;
		isLoadingCollaborators.value = true;
		searchTimeout = setTimeout(async () => {
			try {
				const res = await axios.get(
					`/pagi/users/search?q=${encodeURIComponent(q)}`,
				);
				collaboratorSuggestions.value = res.data || [];
			} catch (e) {
				console.error(e);
			} finally {
				isLoadingCollaborators.value = false;
			}
		}, 300);
	};

	const addCollaboratorChip = (username: string) => {
		if (
			form.collaborators.length < 3 &&
			!form.collaborators.includes(username)
		) {
			form.collaborators.push(username);
		}
		collaboratorInput.value = "";
		collaboratorSuggestions.value = [];
		showCollaboratorDropdown.value = false;
	};

	const removeCollaboratorChip = (idx: number) => {
		form.collaborators.splice(idx, 1);
	};

	return {
		collaboratorInput,
		showCollaboratorDropdown,
		collaboratorSuggestions,
		isLoadingCollaborators,
		handleCollaboratorSearch,
		addCollaboratorChip,
		removeCollaboratorChip,
	};
}
