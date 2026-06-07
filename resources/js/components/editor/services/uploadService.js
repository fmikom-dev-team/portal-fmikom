import axios from "axios";

export const createUploadService = (uploadUrl, uploadFileUrl) => {
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

	const uploadFile = async (file) => {
		const fd = new FormData();
		fd.append("file", file);
		try {
			const res = await axios.post(uploadFileUrl, fd, {
				headers: { "Content-Type": "multipart/form-data" },
			});
			return res.data.success === 1
				? res.data
				: { success: 0, error: { message: "Upload file gagal." } };
		} catch (e) {
			return {
				success: 0,
				error: {
					message: `Upload file gagal: ${e.response?.status || "Unknown error"}`,
				},
			};
		}
	};

	return { uploadByFile, uploadByUrl, uploadFile };
};
