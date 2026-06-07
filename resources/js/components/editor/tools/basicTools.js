import { safeImport } from "./loader";

/**
 * Basic block tools - ONLY @editorjs/* official packages (fully compatible with v2.31)
 */
export const loadBasicTools = async () => {
	const [Paragraph, Header, Quote, Delimiter, NestedList, Checklist, RawTool] =
		await Promise.all([
			safeImport(import("@editorjs/paragraph")),
			safeImport(import("@editorjs/header")),
			safeImport(import("@editorjs/quote")),
			safeImport(import("@editorjs/delimiter")),
			safeImport(import("@editorjs/nested-list")),
			safeImport(import("@editorjs/checklist")),
			safeImport(import("@editorjs/raw")),
		]);

	const result = {};

	if (Paragraph)
		result.paragraph = {
			class: Paragraph,
			inlineToolbar: true,
			config: { preserveBlank: true },
		};
	if (Header)
		result.header = {
			class: Header,
			inlineToolbar: true,
			config: { levels: [1, 2, 3, 4], defaultLevel: 2 },
			shortcut: "CMD+SHIFT+H",
		};
	if (Quote)
		result.quote = {
			class: Quote,
			inlineToolbar: true,
			config: {
				quotePlaceholder: "Tulis kutipan...",
				captionPlaceholder: "Sumber kutipan...",
			},
			shortcut: "CMD+SHIFT+O",
		};
	if (Delimiter)
		result.delimiter = { class: Delimiter, shortcut: "CMD+SHIFT+D" };
	if (NestedList)
		result.list = {
			class: NestedList,
			inlineToolbar: true,
			config: { defaultStyle: "unordered" },
			shortcut: "CMD+SHIFT+L",
		};
	if (Checklist)
		result.checklist = {
			class: Checklist,
			inlineToolbar: true,
			shortcut: "CMD+SHIFT+K",
		};
	if (RawTool) result.raw = { class: RawTool };

	return result;
};
