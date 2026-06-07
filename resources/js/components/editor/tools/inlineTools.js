import { safeImport } from "./loader";

/**
 * Inline tools - ONLY @editorjs/* official packages (fully compatible with v2.31)
 *
 * REMOVED (incompatible with EditorJS v2.31 sanitizeConfig):
 *   - editorjs-text-color-plugin  ← MAIN CAUSE of sanitizeConfig crash
 *   - editorjs-tooltip            ← uses old inline tool API
 *   - editorjs-superscript        ← undefined sanitize
 *   - editorjs-subscript          ← undefined sanitize
 *   - @skchawala/editorjs-text-style ← not maintained
 */
export const loadInlineTools = async () => {
	const [InlineCode, Marker, Strikethrough, Underline, LinkAutocomplete] =
		await Promise.all([
			safeImport(import("@editorjs/inline-code")),
			safeImport(import("@editorjs/marker")),
			safeImport(import("@sotaproject/strikethrough")),
			safeImport(import("@editorjs/underline")),
			safeImport(import("@editorjs/link-autocomplete")),
		]);

	const result = {};

	if (InlineCode) result.inlineCode = { class: InlineCode };
	if (Marker) result.marker = { class: Marker, shortcut: "CMD+SHIFT+M" };
	if (Strikethrough) result.strikethrough = { class: Strikethrough };
	if (Underline) result.underline = { class: Underline, shortcut: "CMD+U" };
	if (LinkAutocomplete) result.linkAutocomplete = { class: LinkAutocomplete };

	return result;
};
