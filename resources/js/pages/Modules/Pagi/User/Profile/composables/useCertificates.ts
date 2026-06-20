import axios from "axios";
// Logo imports
import awsIcon from "thesvg/aws";
import canvaIcon from "thesvg/canva";
import ciscoIcon from "thesvg/cisco";
import courseraIcon from "thesvg/coursera";
import credlyIcon from "thesvg/credly";
import googleIcon from "thesvg/google";
import ibmIcon from "thesvg/ibm";
import linkedinIcon from "thesvg/linkedin";
import microsoftIcon from "thesvg/microsoft";
import oracleIcon from "thesvg/oracle";
import redHatIcon from "thesvg/red-hat";
import udemyIcon from "thesvg/udemy";
import { ref, watch } from "vue";
import {
	generatePdfThumbnail,
	generatePdfThumbnailFromUrl,
} from "@/utils/pdfThumbnail";
import { usePagiProgress } from "../../../shared/composables/usePagiProgress";

function toDataUri(icon: any): string {
	const svg = (icon.variants?.default ?? icon.svg) as string;
	const encoded = encodeURIComponent(svg).replace(/%([0-9A-F]{2})/g, (_, p1) =>
		String.fromCharCode(parseInt(p1, 16)),
	);
	return `data:image/svg+xml;base64,${btoa(encoded)}`;
}

export const POPULAR_ORGANIZATIONS = [
	{ name: "Google", hex: "#4285F4", logo: toDataUri(googleIcon) },
	{ name: "Microsoft", hex: "#00A4EF", logo: toDataUri(microsoftIcon) },
	{
		name: "Amazon Web Services (AWS)",
		hex: "#232F3E",
		logo: toDataUri(awsIcon),
	},
	{ name: "Coursera", hex: "#0056D2", logo: toDataUri(courseraIcon) },
	{ name: "Udemy", hex: "#A435F0", logo: toDataUri(udemyIcon) },
	{ name: "Cisco", hex: "#1BA0D7", logo: toDataUri(ciscoIcon) },
	{ name: "Oracle", hex: "#F80000", logo: toDataUri(oracleIcon) },
	{ name: "Red Hat", hex: "#EE0000", logo: toDataUri(redHatIcon) },
	{ name: "IBM", hex: "#1F70C1", logo: toDataUri(ibmIcon) },
	{ name: "LinkedIn", hex: "#0A66C2", logo: toDataUri(linkedinIcon) },
	{ name: "Credly", hex: "#FF6B00", logo: toDataUri(credlyIcon) },
	{ name: "Canva", hex: "#00C4CC", logo: toDataUri(canvaIcon) },
	{ name: "BAN-PT", hex: "#1a56db", logo: null },
	{ name: "FMIKOM Academy", hex: "#7c3aed", logo: null },
];

export const getOrgLogo = (issuerName: string): string | null => {
	if (!issuerName) return null;
	const lc = issuerName.toLowerCase().trim();
	const found = POPULAR_ORGANIZATIONS.find((o) => o.name.toLowerCase() === lc);
	return found?.logo ?? null;
};

export const getOrgHex = (issuerName: string): string => {
	if (!issuerName) return "#6366f1";
	const lc = issuerName.toLowerCase().trim();
	const found = POPULAR_ORGANIZATIONS.find((o) => o.name.toLowerCase() === lc);
	return found?.hex ?? "#6366f1";
};

export function useCertificates(
	getInitialCerts: () => any[],
	emitAddToast: (message: string, type: string) => void,
	emitUpdateCertificates: (list: any[]) => void,
) {
	const { trackUpload } = usePagiProgress();

	// Mutable local list of certificates
	const localCerts = ref<any[]>([...getInitialCerts()]);

	// PDF client-side thumbnail states
	const pdfThumbnails = ref<Record<string, string>>({});
	const generatingPdfPaths = ref<Record<string, boolean>>({});

	// Form state - Add Modal
	const showAddModal = ref(false);
	const formTitle = ref("");
	const formIssuer = ref("");
	const formLogoUrl = ref("");
	const showAddLogoUpload = ref(false);
	const formDate = ref("");
	const formExpirationDate = ref("");
	const formCredentialId = ref("");
	const formCredentialUrl = ref("");
	const formSkills = ref<string[]>([]);
	const formNewMedia = ref<File[]>([]);
	const formNewMediaThumbs = ref<Array<File | null>>([]);
	const formNewMediaPreviews = ref<
		Array<{ name: string; url: string; type: string; isGenerating: boolean }>
	>([]);
	const isSubmittingAdd = ref(false);

	// Form state - Edit Modal
	const showEditModal = ref(false);
	const editingCertId = ref<number | null>(null);
	const editTitle = ref("");
	const editIssuer = ref("");
	const editLogoUrl = ref("");
	const showEditLogoUpload = ref(false);
	const editDate = ref("");
	const editExpirationDate = ref("");
	const editCredentialId = ref("");
	const editCredentialUrl = ref("");
	const editSkills = ref<string[]>([]);
	const editExistingMedia = ref<any[]>([]);
	const editNewMedia = ref<File[]>([]);
	const editNewMediaThumbs = ref<Array<File | null>>([]);
	const editNewMediaPreviews = ref<
		Array<{ name: string; url: string; type: string; isGenerating: boolean }>
	>([]);
	const isSavingEdit = ref(false);

	// Uploading & Checking logo states
	const isUploading = ref(false);
	const isCheckingAddLogo = ref(false);
	const isCheckingEditLogo = ref(false);
	const isUploadingAddLogo = ref(false);
	const isUploadingEditLogo = ref(false);

	// Logo lookup failed cache record
	const failedLogos = ref<Record<string, boolean>>({});

	const loadMissingPdfThumbnails = () => {
		if (!localCerts.value) return;
		localCerts.value.forEach((cert) => {
			if (cert.media) {
				cert.media.forEach((file: any) => {
					const isPdf = file.type === "pdf" || file.path.endsWith(".pdf");
					if (isPdf && !file.thumbnail_path) {
						const path = file.path;
						if (pdfThumbnails.value[path] || generatingPdfPaths.value[path])
							return;

						generatingPdfPaths.value[path] = true;
						const url = `/storage/${path}`;
						generatePdfThumbnailFromUrl(url)
							.then((dataUrl: string) => {
								pdfThumbnails.value[path] = dataUrl;
								generatingPdfPaths.value[path] = false;
							})
							.catch((err: unknown) => {
								console.error(
									"Failed to generate client-side thumbnail for",
									path,
									err,
								);
								generatingPdfPaths.value[path] = false;
							});
					}
				});
			}
		});
	};

	watch(
		localCerts,
		() => {
			loadMissingPdfThumbnails();
		},
		{ deep: true, immediate: true },
	);

	const checkOrganizationLogo = async (name: string, isEdit: boolean) => {
		const cleanName = name.trim();
		if (!cleanName) {
			if (isEdit) {
				editLogoUrl.value = "";
				showEditLogoUpload.value = false;
			} else {
				formLogoUrl.value = "";
				showAddLogoUpload.value = false;
			}
			return;
		}

		const isPopular = POPULAR_ORGANIZATIONS.some(
			(o) => o.name.toLowerCase() === cleanName.toLowerCase(),
		);
		if (isPopular) {
			const popularLogo = getOrgLogo(cleanName);
			if (isEdit) {
				editLogoUrl.value = popularLogo || "";
				showEditLogoUpload.value = false;
			} else {
				formLogoUrl.value = popularLogo || "";
				showAddLogoUpload.value = false;
			}
			return;
		}

		if (isEdit) isCheckingEditLogo.value = true;
		else isCheckingAddLogo.value = true;

		try {
			const res = await axios.get("/pagi/certificates/org-logo", {
				params: { name: cleanName },
			});

			if (res.data.success) {
				if (isEdit) {
					editLogoUrl.value = res.data.url;
					showEditLogoUpload.value = false;
				} else {
					formLogoUrl.value = res.data.url;
					showAddLogoUpload.value = false;
				}
			} else if (isEdit) {
				editLogoUrl.value = "";
				showEditLogoUpload.value = true;
			} else {
				formLogoUrl.value = "";
				showAddLogoUpload.value = true;
			}
		} catch (err) {
			console.error("Failed to check logo:", err);
			if (isEdit) {
				editLogoUrl.value = "";
				showEditLogoUpload.value = true;
			} else {
				formLogoUrl.value = "";
				showAddLogoUpload.value = true;
			}
		} finally {
			if (isEdit) isCheckingEditLogo.value = false;
			else isCheckingAddLogo.value = false;
		}
	};

	let addIssuerTimeout: any = null;
	const onAddIssuerChange = (newVal: string) => {
		if (addIssuerTimeout) clearTimeout(addIssuerTimeout);
		addIssuerTimeout = setTimeout(() => {
			checkOrganizationLogo(newVal, false);
		}, 500);
	};

	let editIssuerTimeout: any = null;
	const onEditIssuerChange = (newVal: string) => {
		if (editIssuerTimeout) clearTimeout(editIssuerTimeout);
		editIssuerTimeout = setTimeout(() => {
			checkOrganizationLogo(newVal, true);
		}, 500);
	};

	const handleLogoUpload = async (e: Event, isEdit: boolean) => {
		const target = e.target as HTMLInputElement;
		const file = target.files?.[0];
		if (!file) return;

		const issuerName = isEdit ? editIssuer.value : formIssuer.value;
		if (!issuerName.trim()) {
			emitAddToast("Please type the organization name first.", "error");
			target.value = "";
			return;
		}

		const allowedMimes = [
			"image/jpeg",
			"image/png",
			"image/webp",
			"image/gif",
			"image/svg+xml",
		];
		if (!allowedMimes.includes(file.type)) {
			emitAddToast(
				"Format logo tidak valid. Gunakan JPEG, PNG, WebP, GIF, atau SVG.",
				"error",
			);
			target.value = "";
			return;
		}

		if (file.size > 2 * 1024 * 1024) {
			emitAddToast("Ukuran logo maksimal adalah 2MB.", "error");
			target.value = "";
			return;
		}

		const formData = new FormData();
		formData.append("name", issuerName.trim());
		formData.append("logo", file);

		if (isEdit) isUploadingEditLogo.value = true;
		else isUploadingAddLogo.value = true;

		try {
			const res = await axios.post("/pagi/certificates/org-logo", formData, {
				headers: { "Content-Type": "multipart/form-data" },
			});

			if (res.data.success) {
				if (isEdit) {
					editLogoUrl.value = res.data.url;
					failedLogos.value[issuerName] = false;
				} else {
					formLogoUrl.value = res.data.url;
				}
				emitAddToast("Logo successfully uploaded and cached!", "success");
			}
		} catch (err: any) {
			emitAddToast(
				err.response?.data?.message || "Failed to upload logo.",
				"error",
			);
		} finally {
			if (isEdit) isUploadingEditLogo.value = false;
			else isUploadingAddLogo.value = false;
			target.value = "";
		}
	};

	const validateAndAddFile = (file: File, isEdit: boolean) => {
		const allowedMimes = [
			"image/jpeg",
			"image/png",
			"image/webp",
			"image/gif",
			"application/pdf",
		];
		if (!allowedMimes.includes(file.type)) {
			emitAddToast(
				"Format file tidak valid. Gunakan JPEG, PNG, WebP, GIF, atau PDF.",
				"error",
			);
			return;
		}
		const maxSize = 20 * 1024 * 1024;
		if (file.size > maxSize) {
			emitAddToast("Ukuran file maksimal adalah 20MB.", "error");
			return;
		}

		const currentCount = isEdit
			? editExistingMedia.value.length + editNewMedia.value.length
			: formNewMedia.value.length;

		if (currentCount >= 3) {
			emitAddToast("Maksimal lampiran adalah 3 file.", "error");
			return;
		}

		const isPdf = file.type === "application/pdf";
		const previewObj = {
			name: file.name,
			url: isPdf ? "" : URL.createObjectURL(file),
			type: isPdf ? "pdf" : "image",
			isGenerating: isPdf,
		};

		if (isEdit) {
			editNewMedia.value.push(file);
			editNewMediaPreviews.value.push(previewObj);
			const idx = editNewMedia.value.length - 1;
			editNewMediaThumbs.value.push(null);

			if (isPdf) {
				generatePdfThumbnail(file)
					.then((blob: Blob) => {
						const thumbFile = new File([blob], `${file.name}.webp`, {
							type: "image/webp",
						});
						editNewMediaThumbs.value[idx] = thumbFile;
						editNewMediaPreviews.value[idx].url = URL.createObjectURL(blob);
						editNewMediaPreviews.value[idx].isGenerating = false;
					})
					.catch((err: unknown) => {
						console.error("PDF thumbnail generation failed:", err);
						editNewMediaPreviews.value[idx].isGenerating = false;
					});
			}
		} else {
			formNewMedia.value.push(file);
			formNewMediaPreviews.value.push(previewObj);
			const idx = formNewMedia.value.length - 1;
			formNewMediaThumbs.value.push(null);

			if (isPdf) {
				generatePdfThumbnail(file)
					.then((blob: Blob) => {
						const thumbFile = new File([blob], `${file.name}.webp`, {
							type: "image/webp",
						});
						formNewMediaThumbs.value[idx] = thumbFile;
						formNewMediaPreviews.value[idx].url = URL.createObjectURL(blob);
						formNewMediaPreviews.value[idx].isGenerating = false;
					})
					.catch((err: unknown) => {
						console.error("PDF thumbnail generation failed:", err);
						formNewMediaPreviews.value[idx].isGenerating = false;
					});
			}
		}
	};

	const removeNewFile = (index: number, isEdit: boolean) => {
		if (isEdit) {
			const preview = editNewMediaPreviews.value[index];
			if (preview?.url && !preview.url.startsWith("http")) {
				URL.revokeObjectURL(preview.url);
			}
			editNewMedia.value.splice(index, 1);
			editNewMediaThumbs.value.splice(index, 1);
			editNewMediaPreviews.value.splice(index, 1);
		} else {
			const preview = formNewMediaPreviews.value[index];
			if (preview?.url && !preview.url.startsWith("http")) {
				URL.revokeObjectURL(preview.url);
			}
			formNewMedia.value.splice(index, 1);
			formNewMediaThumbs.value.splice(index, 1);
			formNewMediaPreviews.value.splice(index, 1);
		}
	};

	const removeExistingFile = (index: number) => {
		editExistingMedia.value.splice(index, 1);
	};

	const openAddModal = () => {
		formTitle.value = "";
		formIssuer.value = "";
		formLogoUrl.value = "";
		showAddLogoUpload.value = false;
		formDate.value = "";
		formExpirationDate.value = "";
		formCredentialId.value = "";
		formCredentialUrl.value = "";
		formSkills.value = [];
		formNewMedia.value = [];
		formNewMediaThumbs.value = [];
		formNewMediaPreviews.value = [];
		showAddModal.value = true;
	};

	const closeAddModal = () => {
		showAddModal.value = false;
	};

	const handleAddCertificate = async () => {
		if (
			!formTitle.value.trim() ||
			!formIssuer.value.trim() ||
			!formDate.value.trim()
		) {
			emitAddToast("Please fill in all required fields.", "error");
			return;
		}

		isSubmittingAdd.value = true;
		isUploading.value = true;
		uploadProgress.value = 0;

		const formData = new FormData();
		formData.append("title", formTitle.value.trim());
		formData.append("issuer", formIssuer.value.trim());
		formData.append("date", formDate.value.trim());
		formData.append("expirationDate", formExpirationDate.value.trim());
		formData.append("credentialId", formCredentialId.value.trim());
		formData.append("credentialUrl", formCredentialUrl.value.trim());
		formData.append("skills", JSON.stringify(formSkills.value));

		formNewMedia.value.forEach((file, index) => {
			formData.append(`newMedia[${index}]`, file);
			if (formNewMediaThumbs.value[index]) {
				formData.append(
					`newMediaThumb[${index}]`,
					formNewMediaThumbs.value[index] as File,
				);
			}
		});

		try {
			const res = await trackUpload(
				(config) =>
					axios.post("/pagi/certificates", formData, {
						...config,
						headers: {
							...config.headers,
							"Content-Type": "multipart/form-data",
						},
					}),
				"Mengunggah Sertifikat",
			);
			if (res.data.success) {
				localCerts.value = res.data.certificates;
				emitUpdateCertificates(res.data.certificates);
				emitAddToast("Certificate uploaded successfully!", "success");
				isSubmittingAdd.value = false;
				closeAddModal();
			}
		} catch (err: any) {
			emitAddToast(
				err.response?.data?.message || "Failed to upload certificate.",
				"error",
			);
		} finally {
			isSubmittingAdd.value = false;
			isUploading.value = false;
		}
	};

	const openEditModal = (cert: any) => {
		editingCertId.value = cert.id;
		editTitle.value = cert.title || "";
		editIssuer.value = cert.issuer || "";
		editLogoUrl.value = cert.logo_url || "";
		showEditLogoUpload.value = false;
		if (!cert.logo_url && cert.issuer) {
			checkOrganizationLogo(cert.issuer, true);
		}
		editDate.value = cert.date || "";
		editExpirationDate.value = cert.expirationDate || "";
		editCredentialId.value = cert.credentialId || "";
		editCredentialUrl.value = cert.credentialUrl || "";
		editSkills.value = [...(cert.skills || [])];
		editExistingMedia.value = [...(cert.media || [])];
		editNewMedia.value = [];
		editNewMediaThumbs.value = [];
		editNewMediaPreviews.value = [];
		showEditModal.value = true;
	};

	const closeEditModal = () => {
		showEditModal.value = false;
	};

	const handleSaveEdit = async () => {
		if (!editingCertId.value) return;
		if (
			!editTitle.value.trim() ||
			!editIssuer.value.trim() ||
			!editDate.value.trim()
		) {
			emitAddToast("Please fill in all required fields.", "error");
			return;
		}

		isSavingEdit.value = true;
		isUploading.value = true;
		uploadProgress.value = 0;

		const formData = new FormData();
		formData.append("_method", "PUT");
		formData.append("title", editTitle.value.trim());
		formData.append("issuer", editIssuer.value.trim());
		formData.append("date", editDate.value.trim());
		formData.append("expirationDate", editExpirationDate.value.trim());
		formData.append("credentialId", editCredentialId.value.trim());
		formData.append("credentialUrl", editCredentialUrl.value.trim());
		formData.append("skills", JSON.stringify(editSkills.value));
		formData.append("existingMedia", JSON.stringify(editExistingMedia.value));

		editNewMedia.value.forEach((file, index) => {
			formData.append(`newMedia[${index}]`, file);
			if (editNewMediaThumbs.value[index]) {
				formData.append(
					`newMediaThumb[${index}]`,
					editNewMediaThumbs.value[index] as File,
				);
			}
		});

		try {
			const res = await trackUpload(
				(config) =>
					axios.post(`/pagi/certificates/${editingCertId.value}`, formData, {
						...config,
						headers: {
							...config.headers,
							"Content-Type": "multipart/form-data",
						},
					}),
				"Menyimpan Sertifikat",
			);
			if (res.data.success) {
				localCerts.value = res.data.certificates;
				emitUpdateCertificates(res.data.certificates);
				emitAddToast("Certificate updated successfully!", "success");
				isSavingEdit.value = false;
				closeEditModal();
			}
		} catch (err: any) {
			emitAddToast(
				err.response?.data?.message || "Failed to update certificate.",
				"error",
			);
		} finally {
			isSavingEdit.value = false;
			isUploading.value = false;
		}
	};

	const isDeletingId = ref<number | null>(null);
	const handleDeleteCertificate = async (id: number, title: string) => {
		if (!confirm(`Are you sure you want to delete "${title}"?`)) return;
		isDeletingId.value = id;
		try {
			const res = await axios.delete(`/pagi/certificates/${id}`);
			if (res.data.success) {
				localCerts.value = res.data.certificates;
				emitUpdateCertificates(res.data.certificates);
				emitAddToast("Certificate deleted successfully!", "success");
			}
		} catch (err: any) {
			emitAddToast(
				err.response?.data?.message || "Failed to delete certificate.",
				"error",
			);
		} finally {
			isDeletingId.value = null;
		}
	};

	return {
		localCerts,
		pdfThumbnails,
		generatingPdfPaths,
		failedLogos,

		// Add Form State
		showAddModal,
		formTitle,
		formIssuer,
		formLogoUrl,
		showAddLogoUpload,
		formDate,
		formExpirationDate,
		formCredentialId,
		formCredentialUrl,
		formSkills,
		formNewMedia,
		formNewMediaPreviews,
		isSubmittingAdd,

		// Edit Form State
		showEditModal,
		editingCertId,
		editTitle,
		editIssuer,
		editLogoUrl,
		showEditLogoUpload,
		editDate,
		editExpirationDate,
		editCredentialId,
		editCredentialUrl,
		editSkills,
		editExistingMedia,
		editNewMedia,
		editNewMediaPreviews,
		isSavingEdit,

		// Upload Progress
		isUploading,
		isCheckingAddLogo,
		isCheckingEditLogo,
		isUploadingAddLogo,
		isUploadingEditLogo,
		isDeletingId,

		// Methods
		openAddModal,
		closeAddModal,
		handleAddCertificate,
		openEditModal,
		closeEditModal,
		handleSaveEdit,
		handleDeleteCertificate,
		handleLogoUpload,
		onAddIssuerChange,
		onEditIssuerChange,
		validateAndAddFile,
		removeNewFile,
		removeExistingFile,
	};
}
