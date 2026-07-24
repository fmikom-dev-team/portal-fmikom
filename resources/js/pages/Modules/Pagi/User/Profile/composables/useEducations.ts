import axios from "axios";
import { ref } from "vue";

export function useEducations(
	getInitialEducations: () => any[],
	emitAddToast: (message: string, type: string) => void,
	emitUpdateEducations: (list: any[]) => void,
) {
	// Mutable local list of educations
	const localEducations = ref<any[]>([...getInitialEducations()]);

	// Modals toggles
	const showAddModal = ref(false);
	const showEditModal = ref(false);

	// Unified form state
	const educationForm = ref({
		id: null as number | null,
		level: "",
		institution: "",
		major: "",
		start_date: "",
		end_date: "",
		description: "",
	});

	const isSubmitting = ref(false);

	const resetForm = () => {
		educationForm.value = {
			id: null,
			level: "",
			institution: "",
			major: "",
			start_date: "",
			end_date: "",
			description: "",
		};
	};

	// Open modals helpers
	const openAddModal = () => {
		resetForm();
		showAddModal.value = true;
	};

	const openEditModal = (edu: any) => {
		educationForm.value = {
			id: edu.id,
			level: edu.level || "",
			institution: edu.institution || "",
			major: edu.major || "",
			start_date: edu.start_date || "",
			end_date: edu.end_date || "",
			description: edu.description || "",
		};
		showEditModal.value = true;
	};

	// CRUD API Functions
	const storeEducation = async () => {
		if (
			!educationForm.value.level ||
			!educationForm.value.institution ||
			!educationForm.value.start_date ||
			!educationForm.value.end_date
		) {
			emitAddToast("Harap isi semua field wajib.", "error");
			return;
		}

		isSubmitting.value = true;
		try {
			const res = await axios.post("/pagi/education", {
				level: educationForm.value.level,
				institution: educationForm.value.institution,
				major: educationForm.value.major,
				start_date: educationForm.value.start_date,
				end_date: educationForm.value.end_date,
				description: educationForm.value.description,
			});

			if (res.data?.success) {
				localEducations.value = res.data.educations;
				emitUpdateEducations(res.data.educations);
				emitAddToast(
					res.data.message || "Riwayat pendidikan berhasil ditambahkan!",
					"success",
				);
				showAddModal.value = false;
				resetForm();
			} else {
				emitAddToast(
					res.data?.message || "Gagal menambahkan riwayat pendidikan.",
					"error",
				);
			}
		} catch (err: any) {
			console.error("Store education error:", err);
			emitAddToast(
				err.response?.data?.message ||
					"Gagal menambahkan data. Periksa jaringan Anda.",
				"error",
			);
		} finally {
			isSubmitting.value = false;
		}
	};

	const updateEducation = async () => {
		const id = educationForm.value.id;
		if (!id) return;

		if (
			!educationForm.value.level ||
			!educationForm.value.institution ||
			!educationForm.value.start_date ||
			!educationForm.value.end_date
		) {
			emitAddToast("Harap isi semua field wajib.", "error");
			return;
		}

		isSubmitting.value = true;
		try {
			const res = await axios.put(`/pagi/education/${id}`, {
				level: educationForm.value.level,
				institution: educationForm.value.institution,
				major: educationForm.value.major,
				start_date: educationForm.value.start_date,
				end_date: educationForm.value.end_date,
				description: educationForm.value.description,
			});

			if (res.data?.success) {
				localEducations.value = res.data.educations;
				emitUpdateEducations(res.data.educations);
				emitAddToast(
					res.data.message || "Riwayat pendidikan berhasil diperbarui!",
					"success",
				);
				showEditModal.value = false;
				resetForm();
			} else {
				emitAddToast(
					res.data?.message || "Gagal memperbarui riwayat pendidikan.",
					"error",
				);
			}
		} catch (err: any) {
			console.error("Update education error:", err);
			emitAddToast(
				err.response?.data?.message ||
					"Gagal memperbarui data. Periksa jaringan Anda.",
				"error",
			);
		} finally {
			isSubmitting.value = false;
		}
	};

	const deleteEducation = async (id: number) => {
		if (!confirm("Apakah Anda yakin ingin menghapus riwayat pendidikan ini?")) {
			return;
		}

		try {
			const res = await axios.delete(`/pagi/education/${id}`);
			if (res.data?.success) {
				localEducations.value = res.data.educations;
				emitUpdateEducations(res.data.educations);
				emitAddToast(
					res.data.message || "Riwayat pendidikan berhasil dihapus!",
					"success",
				);
			} else {
				emitAddToast(
					res.data?.message || "Gagal menghapus riwayat pendidikan.",
					"error",
				);
			}
		} catch (err: any) {
			console.error("Delete education error:", err);
			emitAddToast(
				err.response?.data?.message ||
					"Gagal menghapus data. Periksa jaringan Anda.",
				"error",
			);
		}
	};

	return {
		localEducations,
		showAddModal,
		showEditModal,
		educationForm,
		isSubmitting,
		openAddModal,
		openEditModal,
		storeEducation,
		updateEducation,
		deleteEducation,
	};
}
