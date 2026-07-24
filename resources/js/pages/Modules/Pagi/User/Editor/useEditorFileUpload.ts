import { computed, ref } from "vue";
import { usePagiProgress } from "../../shared/composables/usePagiProgress";
import { idbPut } from "./useEditorDraft";

const getFileAspectRatio = (file: File): Promise<number> => {
	return new Promise((resolve) => {
		const img = new Image();
		const timeout = setTimeout(() => {
			resolve(1.5);
		}, 3000);

		img.onload = () => {
			clearTimeout(timeout);
			const ar =
				img.naturalWidth && img.naturalHeight
					? img.naturalWidth / img.naturalHeight
					: 1.5;
			resolve(ar);
			URL.revokeObjectURL(img.src);
		};
		img.onerror = () => {
			clearTimeout(timeout);
			resolve(1.5);
		};
		img.src = URL.createObjectURL(file);
	});
};

const compressImageToWebP = (
	file: File,
	quality = 0.8,
	maxDimension = 1920,
): Promise<File> => {
	return new Promise((resolve) => {
		if (!file.type.startsWith("image/")) {
			resolve(file);
			return;
		}

		const img = new Image();
		const objectUrl = URL.createObjectURL(file);

		const timeout = setTimeout(() => {
			URL.revokeObjectURL(objectUrl);
			console.warn("Compression timed out, using original file");
			resolve(file);
		}, 6000);

		img.onload = () => {
			clearTimeout(timeout);
			URL.revokeObjectURL(objectUrl);
			const canvas = document.createElement("canvas");
			let width = img.naturalWidth;
			let height = img.naturalHeight;

			if (width > maxDimension || height > maxDimension) {
				if (width > height) {
					height = Math.round((height * maxDimension) / width);
					width = maxDimension;
				} else {
					width = Math.round((width * maxDimension) / height);
					height = maxDimension;
				}
			}

			canvas.width = width;
			canvas.height = height;
			const ctx = canvas.getContext("2d");
			if (ctx) {
				ctx.drawImage(img, 0, 0, width, height);
				canvas.toBlob(
					(blob) => {
						if (blob) {
							let baseName = file.name;
							const lastDotIndex = file.name.lastIndexOf(".");
							if (lastDotIndex !== -1) {
								baseName = file.name.substring(0, lastDotIndex);
							}
							const newFileName = `${baseName}.webp`;
							const compressedFile = new File([blob], newFileName, {
								type: "image/webp",
								lastModified: Date.now(),
							});
							resolve(compressedFile);
						} else {
							resolve(file);
						}
					},
					"image/webp",
					quality,
				);
			} else {
				resolve(file);
			}
		};
		img.onerror = () => {
			clearTimeout(timeout);
			URL.revokeObjectURL(objectUrl);
			resolve(file);
		};
		img.src = objectUrl;
	});
};

export function useEditorFileUpload(
	form: any,
	addToast: (message: string, type?: string) => void,
) {
	const { startProgress, updateProgress, finishProgress } = usePagiProgress();
	const coverPreview = ref<string | null>(null);

	const isCoverVideo = computed(() => {
		if (!form.cover_image) return false;
		if (form.cover_image instanceof File) {
			return form.cover_image.type.startsWith("video/");
		}
		if (typeof form.cover_image === "string") {
			const ext = form.cover_image.split(".").pop()?.toLowerCase();
			return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
		}
		return false;
	});

	const getUploadStatusMessage = (progress: number) => {
		if (progress < 5) return "Memulai pengunggahan...";
		if (progress < 25) return "Mengompresi dan mengoptimalkan gambar/video...";
		if (progress < 50) return "Menyiapkan data blok konten...";
		if (progress < 75) return "Mengunggah aset karya...";
		if (progress < 95) return "Menyimpan ke basis data...";
		return "Selesai!";
	};

	const triggerFileInput = (type: string) => {
		const input = document.createElement("input");
		input.type = "file";

		if (type === "image") input.accept = "image/*";
		else if (type === "photo_grid") {
			input.accept = "image/*";
			input.multiple = true;
		} else if (type === "video_audio") input.accept = "video/*";
		else if (type === "cover") input.accept = "image/*,video/*";

		input.onchange = async (e: Event) => {
			const target = e.target as HTMLInputElement;
			if (!target.files?.length) return;
			const files = Array.from(target.files);

			// Blocks Size Guard (100MB limit for block uploads)
			const blockLimit = 100 * 1024 * 1024; // 100MB
			for (const f of files) {
				if (f.size > blockLimit) {
					addToast(
						`Berkas "${f.name}" terlalu besar! Batas maksimal adalah 100MB.`,
						"error",
					);
					return;
				}
			}

			// Size & Duration Guard for Cover
			if (type === "cover") {
				let file = files[0];
				const isVideo = file.type.startsWith("video/");
				if (!isVideo) {
					startProgress("upload", "Mengompresi Sampul");
					updateProgress(20, "Mengompresi gambar sampul...");
					file = await compressImageToWebP(file);
					updateProgress(100, "Selesai!");
					finishProgress();
				}
				const maxCoverBytes = isVideo ? 20 * 1024 * 1024 : 5 * 1024 * 1024; // 20MB video, 5MB image
				if (file.size > maxCoverBytes) {
					addToast(
						`Ukuran berkas sampul terlalu besar! Batas maksimal adalah ${isVideo ? "20MB" : "5MB"}.`,
						"error",
					);
					return;
				}
				if (isVideo) {
					const video = document.createElement("video");
					video.preload = "metadata";

					let resolved = false;
					const applyVideo = () => {
						if (resolved) return;
						resolved = true;
						form.cover_image = file;
						coverPreview.value = URL.createObjectURL(file);
					};

					const timeoutId = setTimeout(() => {
						globalThis.URL.revokeObjectURL(video.src);
						applyVideo();
					}, 4000);

					video.onloadedmetadata = () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						if (video.duration > 60.5) {
							addToast(
								"Durasi video sampul maksimal adalah 1 menit (60 detik).",
								"error",
							);
						} else {
							applyVideo();
						}
					};
					video.onerror = () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						applyVideo();
					};
					video.src = URL.createObjectURL(file);
				} else {
					form.cover_image = file;
					coverPreview.value = URL.createObjectURL(file);
				}
				return;
			}

			if (type === "photo_grid") {
				startProgress("upload", "Mengompresi Gambar Grid");
				let completed = 0;
				updateProgress(0, `Mengompresi... (0/${files.length})`);
				const compressedFiles = await Promise.all(
					files.map(async (f) => {
						const res = await compressImageToWebP(f);
						completed++;
						const pct = Math.round((completed * 100) / files.length);
						updateProgress(
							pct,
							`Mengompresi... (${completed}/${files.length})`,
						);
						return res;
					}),
				);
				finishProgress();
				const previews = compressedFiles.map((f) => URL.createObjectURL(f));
				const fileKeys: string[] = [];
				for (const f of compressedFiles) {
					const key = `pagi_${Date.now()}_${f.name}`;
					await idbPut(key, f);
					fileKeys.push(key);
				}
				const aspectRatios = await Promise.all(
					compressedFiles.map(getFileAspectRatio),
				);
				form.content.push({
					type: "photo_grid",
					files: compressedFiles,
					previews,
					fileKeys,
					aspectRatios,
					isFullWidth: true,
				});
			} else {
				let file = files[0];
				if (file.type.startsWith("image/")) {
					startProgress("upload", "Mengompresi Gambar");
					updateProgress(20, "Mengompresi...");
					file = await compressImageToWebP(file);
					updateProgress(100, "Selesai!");
					finishProgress();
				}

				// Strict security extension and mime checks
				const fileName = file.name.toLowerCase();
				const extension = fileName.split(".").pop() || "";
				const forbiddenExtensions = [
					"php",
					"js",
					"sh",
					"html",
					"exe",
					"bat",
					"cmd",
					"py",
					"pl",
					"jsp",
					"asp",
					"aspx",
					"phar",
					"phtml",
				];

				if (forbiddenExtensions.includes(extension)) {
					addToast("Format file ini dilarang demi keamanan sistem.", "error");
					return;
				}

				const allowedMimes = [
					"image/png",
					"image/jpeg",
					"image/jpg",
					"image/gif",
					"image/webp",
					"video/mp4",
					"video/webm",
					"video/ogg",
					"video/quicktime",
					"application/pdf",
					"application/zip",
					"application/x-zip-compressed",
					"application/x-rar-compressed",
					"application/x-7z-compressed",
				];
				if (
					!allowedMimes.includes(file.type.toLowerCase()) &&
					file.type !== ""
				) {
					addToast("Format file tidak diperbolehkan.", "error");
					return;
				}

				// Max 1 minute video duration and 20MB size limit
				if (file.type.toLowerCase().startsWith("video/")) {
					if (file.size > 20 * 1024 * 1024) {
						addToast(
							"Ukuran video terlalu besar! Batas maksimal adalah 20MB.",
							"error",
						);
						return;
					}
					const video = document.createElement("video");
					video.preload = "metadata";

					let resolved = false;
					const pushVideo = async () => {
						if (resolved) return;
						resolved = true;
						const preview = URL.createObjectURL(file);
						const fileKey = `pagi_${Date.now()}_${file.name}`;
						await idbPut(fileKey, file);
						form.content.push({
							type,
							file,
							preview,
							name: file.name,
							mimeType: file.type,
							fileKey,
							isFullWidth: true,
						});
					};

					const timeoutId = setTimeout(() => {
						globalThis.URL.revokeObjectURL(video.src);
						pushVideo();
					}, 4000);

					video.onloadedmetadata = async () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						if (video.duration > 60.5) {
							addToast(
								"Durasi video maksimal adalah 1 menit (60 detik).",
								"error",
							);
						} else {
							await pushVideo();
						}
					};

					video.onerror = async () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						await pushVideo();
					};

					video.src = URL.createObjectURL(file);
					return;
				}

				const preview =
					file.type.startsWith("image") || file.type.startsWith("video")
						? URL.createObjectURL(file)
						: null;
				const fileKey = `pagi_${Date.now()}_${file.name}`;
				await idbPut(fileKey, file);
				form.content.push({
					type,
					file,
					preview,
					name: file.name,
					mimeType: file.type,
					fileKey,
					isFullWidth: true,
				});
			}
		};
		input.click();
	};

	const triggerFileInputForBlock = (index: number) => {
		const block = form.content[index];
		const input = document.createElement("input");
		input.type = "file";

		if (block.type === "image") input.accept = "image/*";
		else if (block.type === "photo_grid") {
			input.accept = "image/*";
			input.multiple = true;
		} else if (block.type === "video_audio") input.accept = "video/*";

		input.onchange = async (e: Event) => {
			const target = e.target as HTMLInputElement;
			if (!target.files?.length) return;
			const files = Array.from(target.files);

			// Blocks Size Guard (100MB limit)
			const blockLimit = 100 * 1024 * 1024; // 100MB
			for (const f of files) {
				if (f.size > blockLimit) {
					addToast(
						`Berkas "${f.name}" terlalu besar! Batas maksimal adalah 100MB.`,
						"error",
					);
					return;
				}
			}

			if (block.type === "photo_grid") {
				startProgress("upload", "Mengompresi Gambar Grid");
				let completed = 0;
				updateProgress(0, `Mengompresi... (0/${files.length})`);
				const compressedFiles = await Promise.all(
					files.map(async (f) => {
						const res = await compressImageToWebP(f);
						completed++;
						const pct = Math.round((completed * 100) / files.length);
						updateProgress(
							pct,
							`Mengompresi... (${completed}/${files.length})`,
						);
						return res;
					}),
				);
				finishProgress();
				const newPreviews = compressedFiles.map((f) => URL.createObjectURL(f));
				const newKeys: string[] = [];
				for (const f of compressedFiles) {
					const key = `pagi_${Date.now()}_${f.name}`;
					await idbPut(key, f);
					newKeys.push(key);
				}
				const newAspectRatios = await Promise.all(
					compressedFiles.map(getFileAspectRatio),
				);
				block.files = [...(block.files || []), ...compressedFiles];
				block.previews = [...(block.previews || []), ...newPreviews];
				block.fileKeys = [...(block.fileKeys || []), ...newKeys];
				block.aspectRatios = [
					...(block.aspectRatios || []),
					...newAspectRatios,
				];
			} else {
				let file = files[0];
				if (file.type.startsWith("image/")) {
					startProgress("upload", "Mengompresi Gambar");
					updateProgress(20, "Mengompresi...");
					file = await compressImageToWebP(file);
					updateProgress(100, "Selesai!");
					finishProgress();
				}

				// Strict security extension and mime checks
				const fileName = file.name.toLowerCase();
				const extension = fileName.split(".").pop() || "";
				const forbiddenExtensions = [
					"php",
					"js",
					"sh",
					"html",
					"exe",
					"bat",
					"cmd",
					"py",
					"pl",
					"jsp",
					"asp",
					"aspx",
					"phar",
					"phtml",
				];

				if (forbiddenExtensions.includes(extension)) {
					addToast("Format file ini dilarang demi keamanan sistem.", "error");
					return;
				}

				const allowedMimes = [
					"image/png",
					"image/jpeg",
					"image/jpg",
					"image/gif",
					"image/webp",
					"video/mp4",
					"video/webm",
					"video/ogg",
					"video/quicktime",
					"application/pdf",
					"application/zip",
					"application/x-zip-compressed",
					"application/x-rar-compressed",
					"application/x-7z-compressed",
				];
				if (
					!allowedMimes.includes(file.type.toLowerCase()) &&
					file.type !== ""
				) {
					addToast("Format file tidak diperbolehkan.", "error");
					return;
				}

				// Max 1 minute video duration and 20MB size limit
				if (file.type.toLowerCase().startsWith("video/")) {
					if (file.size > 20 * 1024 * 1024) {
						addToast(
							"Ukuran video terlalu besar! Batas maksimal adalah 20MB.",
							"error",
						);
						return;
					}
					const video = document.createElement("video");
					video.preload = "metadata";

					let resolved = false;
					const applyBlockVideo = async () => {
						if (resolved) return;
						resolved = true;
						const fileKey = `pagi_${Date.now()}_${file.name}`;
						await idbPut(fileKey, file);
						block.file = file;
						block.preview = URL.createObjectURL(file);
						block.name = file.name;
						block.mimeType = file.type;
						block.fileKey = fileKey;
					};

					const timeoutId = setTimeout(() => {
						globalThis.URL.revokeObjectURL(video.src);
						applyBlockVideo();
					}, 4000);

					video.onloadedmetadata = async () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						if (video.duration > 60.5) {
							addToast(
								"Durasi video maksimal adalah 1 menit (60 detik).",
								"error",
							);
						} else {
							await applyBlockVideo();
						}
					};

					video.onerror = async () => {
						clearTimeout(timeoutId);
						globalThis.URL.revokeObjectURL(video.src);
						await applyBlockVideo();
					};

					video.src = URL.createObjectURL(file);
					return;
				}

				const fileKey = `pagi_${Date.now()}_${file.name}`;
				await idbPut(fileKey, file);
				block.file = file;
				block.preview = URL.createObjectURL(file);
				block.name = file.name;
				block.mimeType = file.type;
				block.fileKey = fileKey;
			}
		};
		input.click();
	};

	return {
		coverPreview,
		isCoverVideo,
		getUploadStatusMessage,
		triggerFileInput,
		triggerFileInputForBlock,
	};
}
