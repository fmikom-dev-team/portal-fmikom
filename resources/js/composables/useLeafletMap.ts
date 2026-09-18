import L from "leaflet";
import { nextTick, onUnmounted, ref, shallowRef, watch } from "vue";
import "leaflet/dist/leaflet.css";

const TILE_URL = "https://tile.openstreetmap.org/{z}/{x}/{y}.png";

const TILE_OPTIONS: L.TileLayerOptions = {
	maxZoom: 19,
	attribution:
		'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	className: "custom-osm-tiles",
};

/**
 * Inject CSS untuk dark mode Leaflet.
 *
 * Tidak perlu menaruh CSS ini di app.css.
 */
const injectMapStyles = () => {
	if (typeof document === "undefined") return;

	const STYLE_ID = "leaflet-osm-dark-mode";

	// Jangan inject dua kali
	if (document.getElementById(STYLE_ID)) return;

	const style = document.createElement("style");
	style.id = STYLE_ID;

	style.textContent = `
		.custom-osm-tiles {
			transition:
				filter 200ms ease,
				opacity 200ms ease;
		}

		.custom-osm-tiles.dark-map {
			filter:
				invert(90%)
				hue-rotate(180deg)
				brightness(85%)
				contrast(90%);
		}
	`;

	document.head.appendChild(style);
};

export function useLeafletMap(options?: {
	center?: [number, number];
	zoom?: number;
	scrollWheelZoom?: boolean;
}) {
	const mapContainer = ref<HTMLElement | null>(null);
	const map = shallowRef<L.Map | null>(null);

	const isReady = ref(false);
	const isMapLoading = ref(true);
	const isDarkMap = ref(false);
	const currentZoom = ref(options?.zoom ?? 5);

	let tileLayer: L.TileLayer | null = null;
	let resizeObserver: ResizeObserver | null = null;

	const invalidateTimers: ReturnType<typeof setTimeout>[] = [];

	/**
	 * Detect Tailwind dark mode.
	 */
	const detectDarkMode = () => {
		if (typeof document === "undefined") {
			return false;
		}

		return document.documentElement.classList.contains("dark");
	};

	/**
	 * Apply dark mode class ke tile layer.
	 */
	const applyTileTheme = () => {
		if (!tileLayer) return;

		const container = tileLayer.getContainer();

		if (!container) return;

		container.classList.toggle("dark-map", isDarkMap.value);
	};

	/**
	 * Create OSM tile layer.
	 */
	const createTileLayer = (leafletMap: L.Map) => {
		tileLayer = L.tileLayer(TILE_URL, TILE_OPTIONS).addTo(leafletMap);

		applyTileTheme();

		return tileLayer;
	};

	/**
	 * Create Leaflet map.
	 */
	const createMap = (el: HTMLElement) => {
		if (map.value) return;

		// Inject CSS sebelum membuat tile layer
		injectMapStyles();

		/**
		 * HMR safety.
		 */
		if ((el as any)._leaflet_id) {
			try {
				const oldMap = (el as any)._leaflet;

				if (oldMap && typeof oldMap.remove === "function") {
					oldMap.remove();
				}
			} catch {
				// Ignore HMR cleanup error
			}

			delete (el as any)._leaflet_id;
			el.replaceChildren();
		}

		/**
		 * Initial dark mode.
		 */
		isDarkMap.value = detectDarkMode();

		const leafletMap = L.map(el, {
			center: options?.center ?? [-2.5, 118],
			zoom: options?.zoom ?? 5,
			zoomControl: false,
			preferCanvas: true,
			scrollWheelZoom: options?.scrollWheelZoom ?? false,
		});

		/**
		 * Add OSM tiles.
		 */
		createTileLayer(leafletMap);

		/**
		 * Zoom control.
		 */
		L.control
			.zoom({
				position: "bottomright",
			})
			.addTo(leafletMap);

		/**
		 * Track zoom.
		 */
		leafletMap.on("zoomend", () => {
			currentZoom.value = leafletMap.getZoom();
		});

		map.value = leafletMap;

		/**
		 * Map ready.
		 */
		leafletMap.whenReady(() => {
			leafletMap.invalidateSize();

			isReady.value = true;
			isMapLoading.value = false;
		});

		/**
		 * Fix map size after CSS/layout loads.
		 */
		[50, 150, 400, 800, 1500, 3000].forEach((ms) => {
			const timer = setTimeout(() => {
				try {
					leafletMap.invalidateSize();

					if (ms >= 1500 && !isReady.value) {
						isReady.value = true;
						isMapLoading.value = false;
					}
				} catch {
					// Ignore invalidation error
				}
			}, ms);

			invalidateTimers.push(timer);
		});

		/**
		 * Watch container resize.
		 */
		if (typeof ResizeObserver !== "undefined") {
			resizeObserver = new ResizeObserver(() => {
				try {
					leafletMap.invalidateSize();
				} catch {
					// Ignore resize error
				}
			});

			resizeObserver.observe(el);
		}
	};

	/**
	 * Initialize map when template ref is available.
	 */
	watch(
		mapContainer,
		(el) => {
			if (!el || map.value) return;

			nextTick(() => {
				requestAnimationFrame(() => {
					createMap(el);
				});
			});

			// Fallback
			setTimeout(() => {
				if (el && !map.value) {
					createMap(el);
				}
			}, 200);
		},
		{
			immediate: true,
		},
	);

	/**
	 * Toggle dark/light map.
	 */
	const toggleDarkMode = () => {
		if (!map.value || !tileLayer) return;

		isDarkMap.value = !isDarkMap.value;

		applyTileTheme();
	};

	/**
	 * Sync dengan Tailwind:
	 *
	 * <html class="dark">
	 */
	const syncDarkMode = () => {
		if (!map.value || !tileLayer) return;

		const dark = detectDarkMode();

		if (isDarkMap.value === dark) return;

		isDarkMap.value = dark;

		applyTileTheme();
	};

	/**
	 * Destroy map.
	 */
	const destroy = () => {
		invalidateTimers.forEach(clearTimeout);
		invalidateTimers.length = 0;

		resizeObserver?.disconnect();
		resizeObserver = null;

		if (map.value) {
			try {
				map.value.remove();
			} catch {
				// Ignore map removal error
			}

			map.value = null;
		}

		tileLayer = null;

		isReady.value = false;
		isMapLoading.value = true;
	};

	onUnmounted(destroy);

	return {
		mapContainer,
		map,
		isReady,
		isMapLoading,
		isDarkMap,
		currentZoom,
		toggleDarkMode,
		syncDarkMode,
	};
}
