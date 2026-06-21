import { copyFileSync, existsSync, mkdirSync } from "node:fs";
import { resolve } from "node:path";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig, type Plugin } from "vite";

/** Copies ffmpeg-core.{js,wasm} from node_modules to public/ so they can be
 * served at the same origin and loaded without COOP/COEP headers. */
function copyFFmpegCore(): Plugin {
	return {
		name: "copy-ffmpeg-core",
		buildStart() {
			const src = resolve("node_modules/@ffmpeg/core/dist/umd");
			const dest = resolve("public");
			if (
				!existsSync(`${src}/ffmpeg-core.js`) ||
				!existsSync(`${src}/ffmpeg-core.wasm`)
			) {
				return;
			}
			mkdirSync(dest, { recursive: true });
			copyFileSync(`${src}/ffmpeg-core.js`, `${dest}/ffmpeg-core.js`);
			copyFileSync(`${src}/ffmpeg-core.wasm`, `${dest}/ffmpeg-core.wasm`);
		},
	};
}

async function loadVisualizerPlugin(): Promise<Plugin | null> {
	try {
		const { visualizer } = await import("rollup-plugin-visualizer");

		return visualizer({
			filename: "stats.html",
			open: false,
			gzipSize: true,
			brotliSize: true,
		}) as Plugin;
	} catch {
		return null;
	}
}

export default defineConfig(async () => {
	const visualizerPlugin = await loadVisualizerPlugin();

	return {
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
			...(process.env.ENABLE_WAYFINDER === "1"
				? [wayfinder({ formVariants: true })]
				: []),
			copyFFmpegCore(),
			...(visualizerPlugin ? [visualizerPlugin] : []),
		],
		server: {
			host: "0.0.0.0",
		},
		build: {
			target: "es2022",
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
			chunkSizeWarningLimit: 1500,
			rollupOptions: {
				output: {
					manualChunks(id) {
						if (
							id.includes("node_modules/vue/") ||
							id.includes("node_modules/@vue/") ||
							id.includes("node_modules/@inertiajs/") ||
							id.includes("node_modules/axios/") ||
							id.includes("preload-helper")
						) {
							return "vue-runtime";
						}
						if (
							id.includes("node_modules/apexcharts") ||
							id.includes("node_modules/vue3-apexcharts")
						) {
							return "chart-vendor";
						}
						if (id.includes("node_modules/@editorjs/editorjs")) {
							return "editor-core";
						}
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
						if (id.includes("node_modules/@ffmpeg/")) {
							return "ffmpeg";
						}
					},
				},
			},
		},
	};
});
