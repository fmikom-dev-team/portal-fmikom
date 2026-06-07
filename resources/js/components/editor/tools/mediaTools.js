import { safeImport } from "./loader";

/**
 * Media tools - all official @editorjs/* packages (fully compatible with v2.31)
 */
export const loadMediaTools = async (uploadByFile, uploadByUrl, uploadFile) => {
	const [Image, Attaches, Embed, LinkTool] = await Promise.all([
		safeImport(import("@editorjs/image")),
		safeImport(import("@editorjs/attaches")),
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

	if (Attaches) {
		result.attaches = {
			class: Attaches,
			config: {
				uploader: { uploadByFile: uploadFile },
				buttonText: "📎 Pilih File",
				errorMessage: "Gagal mengunggah file.",
			},
		};
	}

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
