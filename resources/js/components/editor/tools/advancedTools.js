import { safeImport } from "./loader";

/**
 * Advanced block tools - all official @editorjs/* packages
 *
 * REMOVED (incompatible with EditorJS v2.31 sanitizeConfig):
 *   - editorjs-button             ← undefined sanitizeConfig
 *   - @editorjs/personality       ← requires server + broken validate
 *   - editorjs-text-alignment-blocktune  ← MAIN CAUSE of Tunes sanitizeConfig crash
 *   - editorjs-anchor             ← unmaintained
 *   - editorjs-indent-tune        ← incompatible
 *   - @editorjs/text-variant-tune ← sanitizeConfig undefined in v2.31
 */
export const loadAdvancedTools = async () => {
	const [Table, CodeTool] = await Promise.all([
		safeImport(import("@editorjs/table")),
		safeImport(import("@editorjs/code")),
	]);

	const result = {};

	if (Table) {
		result.table = {
			class: Table,
			inlineToolbar: true,
			config: { rows: 2, cols: 3, withHeadings: true },
		};
	}

	if (CodeTool) {
		result.code = {
			class: CodeTool,
			shortcut: "CMD+SHIFT+C",
			config: { placeholder: "Ketik kode di sini..." },
		};
	}

	return result;
};
