import axios from "axios";
import { type Ref, ref } from "vue";

export function useCvActions(
	cvData: Ref<any>,
	isComplete: Ref<boolean>,
	triggerToast: (message: string, type: "success" | "error" | "info") => void,
	authFotoPath: string,
) {
	const isSaving = ref(false);
	const saveStatus = ref<"saved" | "saving" | "error" | "modified">("saved");
	const lastSavedTime = ref("");
	const isSyncing = ref(false);
	const isUploadingPhoto = ref(false);

	const autoSave = async () => {
		if (isSaving.value) return;
		isSaving.value = true;
		saveStatus.value = "saving";

		try {
			const res = await axios.put(`/pagi/cv/${cvData.value.id}`, cvData.value);
			if (res.data.success) {
				saveStatus.value = "saved";
				const date = new Date();
				lastSavedTime.value = date.toLocaleTimeString("id-ID", {
					hour: "2-digit",
					minute: "2-digit",
					second: "2-digit",
				});
			}
		} catch (e) {
			saveStatus.value = "error";
			console.error("Autosave CV failed:", e);
		} finally {
			isSaving.value = false;
		}
	};

	const manualSave = async () => {
		if (isSaving.value) return;
		isSaving.value = true;
		saveStatus.value = "saving";
		triggerToast("Sedang menyimpan...", "info");

		try {
			const res = await axios.put(`/pagi/cv/${cvData.value.id}`, cvData.value);
			if (res.data.success) {
				saveStatus.value = "saved";
				triggerToast("CV Berhasil Disimpan! Mengalihkan...", "success");
				setTimeout(() => {
					window.location.href = "/pagi/cv";
				}, 900);
			} else {
				triggerToast("Gagal menyimpan CV.", "error");
			}
		} catch (e: any) {
			saveStatus.value = "error";
			console.error("Manual CV save failed:", e);
			const errorMsg =
				e.response?.data?.message ||
				"Gagal menyimpan CV. Silakan periksa input Anda.";
			triggerToast(errorMsg, "error");
		} finally {
			isSaving.value = false;
		}
	};

	const syncFromProfile = async () => {
		if (isSyncing.value) return;
		isSyncing.value = true;
		triggerToast("Menyinkronkan data dari profil...", "info");

		try {
			const res = await axios.get("/pagi/cv/profile-data");
			const data = res.data;

			if (data) {
				cvData.value.personal_info = {
					...cvData.value.personal_info,
					...data.personal_info,
				};

				if (data.skills && data.skills.length > 0) {
					cvData.value.skills = [...data.skills];
				}
				if (data.certifications && data.certifications.length > 0) {
					cvData.value.certifications = [...data.certifications];
				}
				if (data.languages && data.languages.length > 0) {
					cvData.value.languages = [...data.languages];
				}

				triggerToast("Data profil berhasil disinkronkan ke CV!", "success");
				saveStatus.value = "modified";
			} else {
				triggerToast("Gagal menyinkronkan data profil.", "error");
			}
		} catch (e) {
			console.error("Profile sync failed:", e);
			triggerToast("Gagal mengambil data profil.", "error");
		} finally {
			isSyncing.value = false;
		}
	};

	const handlePhotoUpload = async (file: File) => {
		const formData = new FormData();
		formData.append("photo", file);

		isUploadingPhoto.value = true;
		try {
			const res = await axios.post(
				`/pagi/cv/${cvData.value.id}/upload-photo`,
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				},
			);
			if (res.data.success) {
				cvData.value.personal_info.foto_path = res.data.path;
				saveStatus.value = "modified";
			}
		} catch (e) {
			console.error("Failed to upload CV photo:", e);
			alert(
				"Gagal mengunggah foto. Pastikan format gambar sesuai dan ukuran maksimal 2MB.",
			);
		} finally {
			isUploadingPhoto.value = false;
		}
	};

	const resetPhotoToDefault = () => {
		cvData.value.personal_info.foto_path = authFotoPath || "";
		saveStatus.value = "modified";
	};

	const downloadPdf = async () => {
		if (!isComplete.value) {
			triggerToast(
				"CV belum lengkap. Isi nama, email, telepon, ringkasan, dan minimal 1 pendidikan atau pengalaman.",
				"error",
			);
			return;
		}
		triggerToast("Menyiapkan file PDF...", "info");
		await autoSave();

		const link = document.createElement("a");
		link.href = `/pagi/cv/${cvData.value.id}/download`;
		link.setAttribute(
			"download",
			`${cvData.value.title.toLowerCase().replace(/[^a-z0-9]+/g, "-")}.pdf`,
		);
		link.setAttribute("rel", "external");
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	};

	return {
		isSaving,
		saveStatus,
		lastSavedTime,
		isSyncing,
		isUploadingPhoto,
		autoSave,
		manualSave,
		syncFromProfile,
		handlePhotoUpload,
		resetPhotoToDefault,
		downloadPdf,
	};
}
