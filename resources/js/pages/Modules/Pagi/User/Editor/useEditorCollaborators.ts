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
		const rawQ = collaboratorInput.value.trim();
		const q = rawQ.replace(/^@/, "").trim();
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

	const addCollaboratorChip = (collaborator: any) => {
		let item: any = collaborator;
		if (typeof collaborator === "string") {
			const clean = collaborator.replace(/^@/, "");
			item = { pagi_username: clean, name: clean };
		}
		const handle = item.pagi_username || item.name;
		if (
			form.collaborators.length < 3 &&
			!form.collaborators.some(
				(c: any) =>
					(typeof c === "string" ? c : c.pagi_username || c.name) === handle,
			)
		) {
			form.collaborators.push(item);
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
