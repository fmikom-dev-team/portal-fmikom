import {
	copyFileSync,
	existsSync,
	mkdirSync,
	readFileSync,
	writeFileSync,
} from "node:fs";
import { resolve } from "node:path";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig, type Plugin } from "vite";

/**
 * Plugin: Otomatis perbarui CACHE_VERSION di public/sw-pwa.js setiap build.
 * Ini WAJIB agar browser user mendeteksi Service Worker baru dan menampilkan
 * banner update. Tanpa ini, sw-pwa.js tidak pernah berubah = banner tidak muncul.
 */
function updateSwCacheVersion(): Plugin {
	return {
		name: "update-sw-cache-version",
		// Jalankan di buildStart (saat build dimulai) dan juga di configureServer (saat dev)
		buildStart() {
			const swPath = resolve("public/sw-pwa.js");
			if (!existsSync(swPath)) return;

			const newVersion = `v${Date.now()}`;
			const content = readFileSync(swPath, "utf-8");
			const updated = content.replace(
				/const CACHE_VERSION = '[^']*';/,
				`const CACHE_VERSION = '${newVersion}';`,
			);

			if (updated !== content) {
				writeFileSync(swPath, updated, "utf-8");
				console.log(`[PWA] sw-pwa.js CACHE_VERSION updated to ${newVersion}`);
			}
		},
	};
}

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
	if (process.env.ANALYZE !== "1") return null;

	try {
		const { visualizer } = await import("rollup-plugin-visualizer");

		return visualizer({
			filename: "stats.html",
			open: false,
			gzipSize: true,
			brotliSize: false,
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
			updateSwCacheVersion(),
			...(visualizerPlugin ? [visualizerPlugin] : []),
		],
		server: {
			host: "localhost",
			cors: true,
		},
		build: {
			target: "es2022",
			modulePreload: {
				polyfill: true,
				resolveDependencies(_filename: string, deps: string[]) {
					return deps.filter(
						(dep: string) =>
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
					manualChunks(id: string) {
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
						if (
							id.includes("@editorjs/code") ||
							id.includes("@editorjs/table")
						) {
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
		// Inject build-time constants yang dapat dibaca di semua komponen
		// __APP_BUILD_TIME__ digunakan oleh AppUpdateBanner untuk menampilkan versi realtime
		define: {
			__APP_BUILD_TIME__: JSON.stringify(
				new Date().toLocaleDateString("id-ID", {
					day: "2-digit",
					month: "short",
					year: "numeric",
				}),
			),
		},
	};
});
