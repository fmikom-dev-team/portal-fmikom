import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { copyFileSync, mkdirSync } from "node:fs";
import { resolve } from "node:path";
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
	],
	server: {
		host: "0.0.0.0",
	},
	build: {
		// Prevent browser warnings about CSS preloaded but not used immediately.
		// Inertia lazy-loads page components, so their CSS is not needed on initial render.
		modulePreload: false,
		cssCodeSplit: true,
		chunkSizeWarningLimit: 1000,
		rollupOptions: {
			output: {
				manualChunks: {
					"vue-vendor": [
						"vue",
						"@inertiajs/vue3",
						"@inertiajs/core",
						"axios",
						"lucide-vue-next",
					],
					"chart-vendor": ["apexcharts", "vue3-apexcharts"],
					"editor-core": ["@editorjs/editorjs"],
					"editor-media": [
						"@editorjs/image",
						"@editorjs/attaches",
						"@editorjs/embed",
						"@editorjs/link",
					],
					"editor-code": ["@editorjs/code", "@editorjs/table"],
					"editor-basic": [
						"@editorjs/paragraph",
						"@editorjs/header",
						"@editorjs/nested-list",
						"@editorjs/quote",
						"@editorjs/checklist",
						"@editorjs/delimiter",
						"@editorjs/raw",
					],
					"editor-inline": [
						"@editorjs/inline-code",
						"@editorjs/marker",
						"@editorjs/underline",
						"@editorjs/link-autocomplete",
						"@sotaproject/strikethrough",
					],
					"ffmpeg": ["@ffmpeg/ffmpeg", "@ffmpeg/util"],
				},
			},
		},
	},
});
