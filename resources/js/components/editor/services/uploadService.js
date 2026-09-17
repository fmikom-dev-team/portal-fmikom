import axios from "axios";

export const createUploadService = (
	uploadUrl,
	uploadFileUrl,
	uploadChunkUrl = "/portal-admin/posts/upload-chunk",
) => {
	const uploadByFile = async (file) => {
		const fd = new FormData();
		fd.append("image", file);
		try {
			const res = await axios.post(uploadUrl, fd, {
				headers: { "Content-Type": "multipart/form-data" },
			});
			// Editor.js expects: { success: 1, file: { url: ... } }
			return {
				success: res.data.success ?? 1,
				file: res.data.file ?? { url: res.data.url },
			};
		} catch (e) {
			return {
				success: 0,
				error: {
					message: `Upload gagal: ${e.response?.status || "Unknown error"}`,
				},
			};
		}
	};

	const uploadByUrl = async (url) => ({ success: 1, file: { url } });

	const CHUNK_SIZE = 1024 * 1024; // 1 MB slice

	/**
	 * Chunked file upload for Editor.js attaches.
	 * Slices file into 1MB chunks and sends them sequentially to prevent proxy/socket timeouts.
	 */
	const uploadFile = async (file, onProgress, signal) => {
		if (file && file.size > 100 * 1024 * 1024) {
			return {
				success: 0,
				error: {
					message: "Ukuran file melebihi batas maksimal 100MB.",
				},
			};
		}

		const totalSize = file.size;
		const totalChunks = Math.max(1, Math.ceil(totalSize / CHUNK_SIZE));
		const fileId = `f_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
		const chunkEndpoint = uploadChunkUrl || uploadFileUrl.replace(/upload-file$/, "upload-chunk");

		let finalResponse = null;

		for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
			if (signal?.aborted) {
				return {
					success: 0,
					canceled: true,
					error: { message: "Proses unggah dibatalkan oleh pengguna." },
				};
			}

			const start = chunkIndex * CHUNK_SIZE;
			const end = Math.min(start + CHUNK_SIZE, totalSize);
			const chunkBlob = file.slice(start, end);

			const fd = new FormData();
			fd.append("chunk", chunkBlob, file.name);
			fd.append("file_id", fileId);
			fd.append("chunk_index", chunkIndex);
			fd.append("total_chunks", totalChunks);
			fd.append("original_name", file.name);
			fd.append("total_size", totalSize);

			// Retry up to 3 times per chunk for network resilience
			let retries = 3;
			let chunkSuccess = false;
			let lastError = null;

			while (retries > 0 && !chunkSuccess) {
				if (signal?.aborted) {
					return {
						success: 0,
						canceled: true,
						error: { message: "Proses unggah dibatalkan oleh pengguna." },
					};
				}

				try {
					const res = await axios.post(chunkEndpoint, fd, {
						headers: { "Content-Type": "multipart/form-data" },
						timeout: 60000, // 60s per 1MB chunk
						signal,
						onUploadProgress: (progressEvent) => {
							if (onProgress) {
								const chunkLoaded = progressEvent.loaded || 0;
								const totalLoaded = Math.min(start + chunkLoaded, totalSize);
								const percent = Math.round((totalLoaded * 100) / totalSize);
								onProgress({
									percent,
									loaded: totalLoaded,
									total: totalSize,
									rate: progressEvent.rate,
									estimated: progressEvent.estimated,
								});
							}
						},
					});

					if (res.data && res.data.success === 1) {
						chunkSuccess = true;
						finalResponse = res.data;
					} else {
						lastError = new Error(
							res.data?.message || `Gagal mengunggah potongan file #${chunkIndex + 1}`,
						);
						break; // Stop retry on server business validation failure
					}
				} catch (err) {
					if (axios.isCancel(err) || err.name === "CanceledError" || signal?.aborted) {
						return {
							success: 0,
							canceled: true,
							error: { message: "Proses unggah dibatalkan oleh pengguna." },
						};
					}
					lastError = err;
					retries--;
					if (retries > 0) {
						await new Promise((resolve) => setTimeout(resolve, 800));
					}
				}
			}

			if (!chunkSuccess) {
				const errMsg =
					lastError?.response?.data?.message ||
					lastError?.response?.data?.errors?.file?.[0] ||
					lastError?.response?.data?.errors?.chunk?.[0] ||
					lastError?.message ||
					`Gagal mengunggah berkas pada bagian ${chunkIndex + 1} dari ${totalChunks}.`;

				return {
					success: 0,
					error: {
						message: errMsg,
					},
				};
			}
		}

		if (finalResponse && finalResponse.file) {
			return {
				success: 1,
				file: finalResponse.file,
			};
		}

		return {
			success: 0,
			error: {
				message: "Gagal menyelesaikan penggabungan file di server.",
			},
		};
	};

	return { uploadByFile, uploadByUrl, uploadFile };
};

