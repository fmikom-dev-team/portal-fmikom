/**
 * useSanitize.ts — Sanitasi HTML untuk mencegah XSS
 *
 * Gunakan composable ini setiap kali me-render HTML dari database via v-html.
 *
 * Contoh:
 *   import { sanitize, sanitizeRich } from '@/composables/useSanitize'
 *   <div v-html="sanitize(content)" />
 */
import DOMPurify from "dompurify";

/**
 * Sanitasi standar — untuk teks biasa dengan formatting dasar.
 * Cocok untuk: paragraph, heading, list, quote, alert, warning.
 */
export function sanitize(html: string | null | undefined): string {
	if (!html) return "";
	return DOMPurify.sanitize(html, {
		ALLOWED_TAGS: [
			"p",
			"br",
			"strong",
			"b",
			"em",
			"i",
			"u",
			"s",
			"mark",
			"ul",
			"ol",
			"li",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"blockquote",
			"code",
			"pre",
			"kbd",
			"a",
			"span",
			"div",
			"table",
			"thead",
			"tbody",
			"tfoot",
			"tr",
			"td",
			"th",
			"img",
			"figure",
			"figcaption",
			"hr",
		],
		ALLOWED_ATTR: [
			"href",
			"target",
			"rel",
			"src",
			"alt",
			"width",
			"height",
			"loading",
			"class",
			"id",
			"colspan",
			"rowspan",
			"title",
		],
		FORBID_TAGS: [
			"script",
			"iframe",
			"object",
			"embed",
			"form",
			"input",
			"button",
			"meta",
			"link",
			"style",
		],
		FORBID_ATTR: [
			"onerror",
			"onload",
			"onclick",
			"onmouseover",
			"onfocus",
			"onblur",
			"onchange",
			"onsubmit",
			"style",
		],
		FORCE_BODY: false,
	});
}

/**
 * Sanitasi ketat — untuk teks inline satu baris.
 * Cocok untuk: caption, judul, label.
 * Tidak mengizinkan block-level elements.
 */
export function sanitizeInline(html: string | null | undefined): string {
	if (!html) return "";
	return DOMPurify.sanitize(html, {
		ALLOWED_TAGS: [
			"strong",
			"b",
			"em",
			"i",
			"u",
			"s",
			"mark",
			"code",
			"span",
			"a",
			"br",
		],
		ALLOWED_ATTR: ["href", "target", "rel", "class", "title"],
		FORBID_TAGS: ["script", "iframe", "object", "embed", "form", "input"],
		FORBID_ATTR: ["onerror", "onload", "onclick", "onmouseover", "style"],
	});
}

/**
 * Sanitasi kaya — untuk konten halaman publik yang lebih kompleks.
 * Cocok untuk: page.content, full article content.
 * Mengizinkan lebih banyak tag tapi tetap memblokir script.
 */
export function sanitizeRich(html: string | null | undefined): string {
	if (!html) return "";
	return DOMPurify.sanitize(html, {
		ALLOWED_TAGS: [
			"p",
			"br",
			"strong",
			"b",
			"em",
			"i",
			"u",
			"s",
			"mark",
			"ul",
			"ol",
			"li",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"blockquote",
			"code",
			"pre",
			"kbd",
			"a",
			"span",
			"div",
			"section",
			"article",
			"aside",
			"table",
			"thead",
			"tbody",
			"tfoot",
			"tr",
			"td",
			"th",
			"caption",
			"img",
			"figure",
			"figcaption",
			"picture",
			"source",
			"hr",
			"details",
			"summary",
			"dl",
			"dt",
			"dd",
			"sub",
			"sup",
		],
		ALLOWED_ATTR: [
			"href",
			"target",
			"rel",
			"src",
			"srcset",
			"alt",
			"width",
			"height",
			"loading",
			"class",
			"id",
			"colspan",
			"rowspan",
			"scope",
			"title",
			"lang",
			"open",
		],
		FORBID_TAGS: [
			"script",
			"iframe",
			"object",
			"embed",
			"form",
			"input",
			"button",
			"meta",
			"link",
			"base",
		],
		FORBID_ATTR: [
			"onerror",
			"onload",
			"onclick",
			"onmouseover",
			"onfocus",
			"onblur",
			"onchange",
			"onsubmit",
			"style",
			"javascript",
		],
		// Pastikan link rel="noopener noreferrer" untuk keamanan
		FORCE_BODY: false,
		ADD_ATTR: ["target"],
	});
}
