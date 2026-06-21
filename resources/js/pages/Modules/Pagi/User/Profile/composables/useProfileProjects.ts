import { computed, onUnmounted, ref, watch } from "vue";

export function useProfileProjects(
	props: any,
	page: any,
	addToast: (msg: string, type?: string) => void,
	_triggerWarning: (title: string, msg: string) => void,
) {
	const localProjects = ref<any[]>([...(props.projects || [])]);
	watch(
		() => props.projects,
		(newVal) => {
			localProjects.value = [...(newVal || [])];
		},
		{ deep: true },
	);

	const projects = computed(() => {
		return localProjects.value;
	});

	const showAddWorkModal = ref(false);
	const showShareModal = ref(false);
	const newCreatedProject = ref<any>(null);
	const isSubmittingWork = ref(false);

	const activeProjectMenu = ref<number | null>(null);
	const toggleProjectMenu = (id: number) => {
		activeProjectMenu.value = activeProjectMenu.value === id ? null : id;
	};

	const cloneProject = (title: string) => {
		addToast(`Cloned project: "${title}" successfully!`, "success");
		activeProjectMenu.value = null;
	};

	const shareProject = (project: any) => {
		newCreatedProject.value = project;
		showShareModal.value = true;
	};

	const getProjectShareUrl = (project: any) => {
		if (!project) return "";
		const username = props.user?.pagi_username;
		const ownerId = props.user?.id;
		const baseUrl = username
			? `${window.location.origin}/pagi/${username}`
			: `${window.location.origin}/pagi/profile/${ownerId}`;
		return `${baseUrl}?project=${project.id}`;
	};

	const deleteProject = async (id: number, title: string) => {
		activeProjectMenu.value = null;
		const prevProjects = [...localProjects.value];
		localProjects.value = localProjects.value.filter((p) => p.id !== id);
		addToast(`Project "${title}" has been deleted.`, "success");

		try {
			const csrfToken = (
				document.querySelector("meta[name=csrf-token]") as HTMLMetaElement
			)?.content;
			const res = await fetch(`/pagi/editor/${id}`, {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": csrfToken || "",
					Accept: "application/json",
				},
			});
			const data = await res.json();
			if (!res.ok) throw new Error(data.error || "Failed");
		} catch (e) {
			localProjects.value = prevProjects;
			addToast("Gagal menghapus project. Coba lagi.", "error");
		}
	};

	const viewingProject = ref<any>(null);

	const activeProjectSettings = computed(() => {
		if (!viewingProject.value?.content) {
			return { globalSpacing: 50, canvasBgColor: "", canvasTextColor: "" };
		}
		return (
			viewingProject.value.content.find((b: any) => b.type === "settings") || {
				globalSpacing: 50,
				canvasBgColor: "",
				canvasTextColor: "",
			}
		);
	});

	const openProjectModal = (p: any) => {
		if (!page.props.auth?.user) {
			addToast(
				"Anda belum login. Silakan login terlebih dahulu untuk melihat karya.",
				"info",
			);
			return;
		}
		viewingProject.value = p;
		document.body.style.overflow = "hidden";
	};

	const closeProjectModal = () => {
		viewingProject.value = null;
		document.body.style.overflow = "";
	};

	onUnmounted(() => {
		document.body.style.overflow = "";
	});

	const toggleLikeProject = async () => {
		if (!viewingProject.value) return;
		const originalLiked = viewingProject.value.liked;
		const originalLikesCount = viewingProject.value.likes;

		// Optimistic Update
		viewingProject.value.liked = !originalLiked;
		viewingProject.value.likes = originalLiked
			? originalLikesCount - 1
			: originalLikesCount + 1;

		try {
			const csrfToken = (
				document.querySelector("meta[name=csrf-token]") as HTMLMetaElement
			)?.content;
			const res = await fetch(`/pagi/preview/${viewingProject.value.id}/like`, {
				method: "POST",
				headers: {
					"X-CSRF-TOKEN": csrfToken || "",
					Accept: "application/json",
				},
			});
			const data = await res.json();
			if (!res.ok) throw new Error(data.error || "Failed");

			// sync back to localProjects list
			const proj = localProjects.value.find(
				(p: any) => p.id === viewingProject.value.id,
			);
			if (proj) {
				proj.liked = data.liked;
				proj.likes = data.likes_count;
				localProjects.value = [...localProjects.value];
			}
		} catch (e) {
			// Rollback on failure
			viewingProject.value.liked = originalLiked;
			viewingProject.value.likes = originalLikesCount;
			addToast("Gagal menyukai karya. Coba lagi.", "error");
		}
	};

	const submitComment = async (commentBody: string) => {
		if (!viewingProject.value || !commentBody.trim()) return;
		try {
			const csrfToken = (
				document.querySelector("meta[name=csrf-token]") as HTMLMetaElement
			)?.content;
			const res = await fetch(
				`/pagi/preview/${viewingProject.value.id}/comment`,
				{
					method: "POST",
					headers: {
						"Content-Type": "application/json",
						"X-CSRF-TOKEN": csrfToken || "",
						Accept: "application/json",
					},
					body: JSON.stringify({ body: commentBody }),
				},
			);
			const data = await res.json();
			if (!res.ok) throw new Error(data.error || "Failed");

			viewingProject.value.comments = data.comments;

			// sync comments to localProjects list
			const proj = localProjects.value.find(
				(p: any) => p.id === viewingProject.value.id,
			);
			if (proj) {
				proj.comments = data.comments;
				localProjects.value = [...localProjects.value];
			}
		} catch (e) {
			addToast("Gagal mengirim komentar. Coba lagi.", "error");
		}
	};

	const editingQuickWorkId = ref<number | null>(null);
	const isEditingQuickWork = ref(false);
	const editingProject = ref<any>(null);

	const openAddWorkModal = () => {
		isEditingQuickWork.value = false;
		editingQuickWorkId.value = null;
		editingProject.value = null;
		showAddWorkModal.value = true;
	};

	const openEditQuickWorkModal = (project: any) => {
		isEditingQuickWork.value = true;
		editingQuickWorkId.value = project.id;
		editingProject.value = project;
		showAddWorkModal.value = true;
	};

	const handleQuickStoreSuccess = (project: any, message: string) => {
		showAddWorkModal.value = false;
		if (isEditingQuickWork.value && editingQuickWorkId.value) {
			const idx = localProjects.value.findIndex(
				(p) => p.id === editingQuickWorkId.value,
			);
			if (idx !== -1) {
				localProjects.value[idx] = project;
			}
			addToast(message || "Karya berhasil diperbarui!", "success");
		} else {
			localProjects.value = [project, ...localProjects.value];
			addToast(message || "Karya berhasil disimpan!", "success");
			setTimeout(() => {
				newCreatedProject.value = project;
				showShareModal.value = true;
			}, 300);
		}
		isEditingQuickWork.value = false;
		editingQuickWorkId.value = null;
		editingProject.value = null;
	};

	const handleLikeUpdated = (data: {
		id: number;
		liked: boolean;
		count: number;
	}) => {
		const proj = localProjects.value?.find((p: any) => p.id === data.id);
		if (proj) {
			proj.liked = data.liked;
			proj.likes = data.count;
			localProjects.value = [...localProjects.value];
		}
	};

	const handleGalleryItemUpdated = (updatedProject: any) => {
		const idx = localProjects.value.findIndex(
			(p) => p.id === updatedProject.id,
		);
		if (idx !== -1) {
			const existing = localProjects.value[idx];
			localProjects.value[idx] = {
				...existing,
				title: updatedProject.title,
				content: updatedProject.content,
			};
			localProjects.value = [...localProjects.value];
		}
	};

	const getProjectFit = (project: any) => {
		if (!project?.content || !Array.isArray(project.content)) return "cover";
		const settings = project.content.find(
			(b: any) => b && b.type === "settings",
		);
		return settings?.canvasFit || "cover";
	};

	const totalViews = computed(() => {
		return (
			localProjects.value?.reduce(
				(acc, p) => acc + (Number(p.views) || 0),
				0,
			) || 518
		);
	});

	const totalLikes = computed(() => {
		return (
			localProjects.value?.reduce((acc, p) => {
				const likesCount = Array.isArray(p.likes)
					? p.likes.length
					: Number(p.likes) || 0;
				return acc + likesCount;
			}, 0) || 0
		);
	});

	const projectCount = computed(() => {
		return (
			localProjects.value?.filter((p) => {
				if (!p?.content || !Array.isArray(p.content)) return true;
				return !p.content.some((b: any) => b && b.type === "gallery_item");
			}).length || 0
		);
	});

	return {
		localProjects,
		projects,
		showAddWorkModal,
		showShareModal,
		newCreatedProject,
		isSubmittingWork,
		activeProjectMenu,
		toggleProjectMenu,
		cloneProject,
		shareProject,
		getProjectShareUrl,
		deleteProject,
		viewingProject,
		activeProjectSettings,
		openProjectModal,
		closeProjectModal,
		toggleLikeProject,
		submitComment,
		editingQuickWorkId,
		isEditingQuickWork,
		editingProject,
		openAddWorkModal,
		openEditQuickWorkModal,
		handleQuickStoreSuccess,
		handleLikeUpdated,
		handleGalleryItemUpdated,
		getProjectFit,
		totalViews,
		totalLikes,
		projectCount,
	};
}
