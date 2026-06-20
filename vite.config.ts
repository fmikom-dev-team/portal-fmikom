import { copyFileSync, mkdirSync } from "node:fs";
import { resolve } from "node:path";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { visualizer } from "rollup-plugin-visualizer";
import { defineConfig, type Plugin } from "vite";

/** Copies ffmpeg-core.{js,wasm} from node_modules to public/ so they can be
 *  served at the same origin and loaded without COOP/COEP headers. */
function copyFFmpegCore(): Plugin {
	return {
		name: "copy-ffmpeg-core",
		buildStart() {
			const src = resolve("node_modules/@ffmpeg/core/dist/umd");
			const dest = resolve("public");
			mkdirSync(dest, { recursive: true });
			copyFileSync(`${src}/ffmpeg-core.js`, `${dest}/ffmpeg-core.js`);
			copyFileSync(`${src}/ffmpeg-core.wasm`, `${dest}/ffmpeg-core.wasm`);
		},
	};
}

export default defineConfig({
	plugins: [
		laravel({
			input: ["resources/js/app.ts"],
			ssr: "resources/js/ssr.ts",
			refresh: true,
		}),
		tailwindcss(),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
		wayfinder({
			formVariants: true,
		}),
		copyFFmpegCore(),
		visualizer({
			filename: "stats.html",
			open: true,
			gzipSize: true,
			brotliSize: true,
		}) as Plugin,
	],
	server: {
		host: "0.0.0.0",
	},
	build: {
		target: "es2022",
		// Polyfill ensures Safari <17 also benefits from modulepreload.
		// Using the object form (vs bare `true`) also enables preloading of
		// dynamically-imported Inertia page chunks, not just the entry point.
		modulePreload: {
			polyfill: true,
			resolveDependencies(filename, deps) {
				return deps.filter(
					(dep) =>
						!dep.includes("chart-vendor") &&
						!dep.includes("editor-") &&
						!dep.includes("ffmpeg"),
				);
			},
		},
		cssCodeSplit: true,
		chunkSizeWarningLimit: 1000,
		rollupOptions: {
			output: {
				/**
				 * Granular manual chunk splitting:
				 * - Keeps each vendor group small so the browser can cache them
				 *   independently and parse only what's needed per page.
				 * - Using a function (vs object) gives Vite full control over
				 *   modules NOT in our list (they go into auto-named chunks).
				 */
				manualChunks(id) {
					// Vue & Inertia core — loaded on every page, grouped to prevent circular dependency TDZ errors
					if (
						id.includes("node_modules/vue/") ||
						id.includes("node_modules/@vue/") ||
						id.includes("node_modules/@inertiajs/") ||
						id.includes("node_modules/axios/") ||
						id.includes("preload-helper")
					) {
						return "vue-runtime";
					}
					// Charts — only needed on analytics/dashboard pages
					if (
						id.includes("node_modules/apexcharts") ||
						id.includes("node_modules/vue3-apexcharts")
					) {
						return "chart-vendor";
					}
					// Editor.js core — large, lazy-loaded only in editor views
					if (id.includes("node_modules/@editorjs/editorjs")) {
						return "editor-core";
					}
					// Editor.js plugins — split further so non-media editors don't pull media
					if (
						id.includes("@editorjs/image") ||
						id.includes("@editorjs/attaches") ||
						id.includes("@editorjs/embed") ||
						id.includes("@editorjs/link")
					) {
						return "editor-media";
					}
					if (id.includes("@editorjs/code") || id.includes("@editorjs/table")) {
						return "editor-code";
					}
					if (
						id.includes("@editorjs/paragraph") ||
						id.includes("@editorjs/header") ||
						id.includes("@editorjs/nested-list") ||
						id.includes("@editorjs/quote") ||
						id.includes("@editorjs/checklist") ||
						id.includes("@editorjs/delimiter") ||
						id.includes("@editorjs/raw")
					) {
						return "editor-basic";
					}
					if (
						id.includes("@editorjs/inline-code") ||
						id.includes("@editorjs/marker") ||
						id.includes("@editorjs/underline") ||
						id.includes("@editorjs/link-autocomplete") ||
						id.includes("@sotaproject/strikethrough")
					) {
						return "editor-inline";
					}
					// FFmpeg — very large, lazy-loaded only when video upload is triggered
					if (id.includes("node_modules/@ffmpeg/")) {
						return "ffmpeg";
					}
				},
			},
		},
	},
});
