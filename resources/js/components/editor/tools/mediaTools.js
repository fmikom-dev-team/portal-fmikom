import AttachesTool from "./AttachesTool";
import { safeImport } from "./loader";

/**
 * Media tools - official @editorjs/* packages + custom modern AttachesTool
 */
export const loadMediaTools = async (uploadByFile, uploadByUrl, uploadFile) => {
	const [Image, Embed, LinkTool] = await Promise.all([
		safeImport(import("@editorjs/image")),
		safeImport(import("@editorjs/embed")),
		safeImport(import("@editorjs/link")),
	]);

	const result = {};

	if (Image) {
		result.image = {
			class: Image,
			config: {
				uploader: { uploadByFile, uploadByUrl },
				captionPlaceholder: "Keterangan gambar...",
			},
		};
	}

	result.attaches = {
		class: AttachesTool,
		config: {
			uploader: { uploadByFile: uploadFile },
			buttonText: "Pilih / Drop File Lampiran (Maks 100 MB)",
			errorMessage: "Gagal mengunggah file.",
		},
	};

	if (Embed) {
		result.embed = {
			class: Embed,
			config: {
				services: {
					youtube: true,
					twitter: true,
					instagram: true,
					codepen: true,
					imgur: true,
					vimeo: true,
				},
			},
		};
	}

	if (LinkTool) {
		result.linkTool = {
			class: LinkTool,
			config: { endpoint: "/portal-admin/fetchUrl" },
		};
	}

	return result;
};
