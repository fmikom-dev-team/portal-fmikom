import axios from "axios";
import L from "leaflet";
import { type Ref, ref } from "vue";

// ─── TYPES ─────────────────────────────────────────────────
export interface AlumniData {
	profil_alumni_id: number;
	nama_lengkap: string;
	nim: string;
	angkatan: string;
	program_studi: string;
	instansi: string;
	detail: string;
	sektor_industri: string;
	nama_kota: string;
	latitude: string;
	longitude: string;
	tipe_lokasi: string;
}

export interface MarkerEntry {
	marker: L.Marker;
	category: "bekerja" | "wirausaha" | "studi" | "belum";
	data: AlumniData;
	latlng: L.LatLng;
}

export type ViewMode = "cluster" | "heat" | "choropleth" | "tematic";
export type TematicField = "status" | "sektor" | "angkatan" | "prodi";

// ─── CONSTANTS ─────────────────────────────────────────────
export const CATEGORY_CONFIG: Record<
	string,
	{ color: string; bg: string; label: string; icon: string }
> = {
	bekerja: {
		color: "#3b82f6",
		bg: "bg-blue-500",
		label: "Bekerja",
		icon: "💼",
	},
	wirausaha: {
		color: "#10b981",
		bg: "bg-emerald-500",
		label: "Wirausaha",
		icon: "🏢",
	},
	studi: {
		color: "#8b5cf6",
		bg: "bg-violet-500",
		label: "Lanjut Studi",
		icon: "🎓",
	},
	belum: {
		color: "#f59e0b",
		bg: "bg-amber-500",
		label: "Belum Bekerja",
		icon: "🔍",
	},
};

export const TEMATIC_PALETTES: Record<string, string[]> = {
	status: [
		"#3b82f6",
		"#10b981",
		"#8b5cf6",
		"#f59e0b",
		"#ef4444",
		"#06b6d4",
		"#f97316",
		"#ec4899",
	],
	sektor: [
		"#0C447C",
		"#1a6bb5",
		"#3a8fd4",
		"#85B7EB",
		"#EF9F27",
		"#10b981",
		"#ef4444",
		"#8b5cf6",
		"#f97316",
		"#06b6d4",
	],
	angkatan: [
		"#0C447C",
		"#1a6bb5",
		"#3a8fd4",
		"#85B7EB",
		"#c5dcf5",
		"#EF9F27",
		"#f59e0b",
		"#f97316",
	],
	prodi: [
		"#3b82f6",
		"#10b981",
		"#8b5cf6",
		"#f59e0b",
		"#ef4444",
		"#06b6d4",
		"#ec4899",
		"#84cc16",
	],
};

// ─── HELPERS ───────────────────────────────────────────────
export const getCategory = (
	tipe: string,
): "bekerja" | "wirausaha" | "studi" | "belum" => {
	if (tipe === "Bekerja") return "bekerja";
	if (tipe === "Wirausaha") return "wirausaha";
	if (tipe === "Lanjut Studi") return "studi";
	return "belum";
};

export const createMarkerIcon = (color: string, size: number = 10) => {
	return L.divIcon({
		html: `<div style="width:${size}px;height:${size}px;background:${color};border:2px solid white;border-radius:50%;box-shadow:0 2px 6px ${color}66;"></div>`,
		className: "custom-marker-icon",
		iconSize: [size, size],
		iconAnchor: [size / 2, size / 2],
	});
};

export const createPopupContent = (a: AlumniData) => {
	const cat = getCategory(a.tipe_lokasi);
	const cfg = CATEGORY_CONFIG[cat];
	return `
        <div style="min-width:240px;font-family:system-ui,-apple-system,sans-serif;">
            <div style="display:inline-block;font-size:9px;font-weight:900;letter-spacing:0.08em;text-transform:uppercase;padding:3px 8px;border-radius:999px;margin-bottom:8px;background:${cfg.color}22;color:${cfg.color};">
                ${cfg.label}
            </div>
            <div style="font-size:14px;font-weight:900;color:#1e293b;margin-bottom:10px;line-height:1.3;">${a.nama_lengkap}</div>
            <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">PRODI</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;">${a.program_studi || "-"}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">ANGKATAN</span>
                <span style="font-size:11px;font-weight:700;color:#334155;">${a.angkatan || "-"}</span>
            </div>
            ${
							a.instansi
								? `<div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">${cat === "studi" ? "UNIVERSITAS" : cat === "belum" ? "ALAMAT DOMISILI" : "PERUSAHAAN"}</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;max-width:60%;">${a.instansi}</span>
            </div>`
								: ""
						}
            ${
							a.detail
								? `<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">STATUS</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;max-width:60%;">${a.detail}</span>
            </div>`
								: ""
						}
            ${
							a.nama_kota
								? `<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">KOTA</span>
                <span style="font-size:11px;font-weight:700;color:#334155;">${a.nama_kota}</span>
            </div>`
								: ""
						}
            <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <a href="/trace/admin/alumni/${a.profil_alumni_id}" target="_blank"
               style="display:block;text-align:center;background:${cfg.color};color:white;padding:8px 16px;border-radius:12px;font-size:11px;font-weight:800;text-decoration:none;">
                📋 Lihat Profil Lengkap
            </a>
        </div>`;
};

export const formatTime = (d: Date) =>
	d.toLocaleDateString("id-ID", {
		day: "2-digit",
		month: "long",
		year: "numeric",
	}) +
	" " +
	d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });

// ─── COMPOSABLE ────────────────────────────────────────────
export function useMapData() {
	const allAlumni = ref<AlumniData[]>([]);
	const allMarkers: MarkerEntry[] = [];
	const filterOptions = ref({
		angkatan: [] as string[],
		prodi: [] as string[],
		sektor: [] as string[],
	});
	const completionMeta = ref({
		total_alumni: 0,
		mapped_count: 0,
		completion_rate: 0,
		is_filter_active: false,
		filtered: { total: 0, mapped: 0, rate: 0 },
	});
	const isLoading = ref(false);
	const lastUpdated = ref("");

	const fetchMapData = async (
		rawMap: () => L.Map | null,
		filters: Ref<{ angkatan: string; program_studi: string; sektor: string }>,
		layerVisibility: Ref<Record<string, boolean>>,
		callbacks: {
			clearAllLayers: () => void;
			applyLayers: (heatPoints?: [number, number, number][]) => Promise<void>;
			setupViewportTracking: () => void;
			updateViewportStats: () => void;
		},
	) => {
		const m = rawMap();
		if (!m) return;
		isLoading.value = true;

		try {
			const params: any = {};
			if (filters.value.angkatan) params.angkatan = filters.value.angkatan;
			if (filters.value.program_studi)
				params.program_studi = filters.value.program_studi;
			if (filters.value.sektor) params.sektor = filters.value.sektor;

			const { data: response } = await axios.get("/trace/admin/map/data", {
				params,
			});
			const alumniData: AlumniData[] = response.data || [];

			completionMeta.value = response.meta || completionMeta.value;
			allAlumni.value = alumniData;

			// Extract filter options
			const angkatanSet = new Set<string>();
			const prodiSet = new Set<string>();
			const sektorSet = new Set<string>();
			alumniData.forEach((a) => {
				if (a.angkatan) angkatanSet.add(a.angkatan);
				if (a.program_studi) prodiSet.add(a.program_studi);
				if (a.sektor_industri) sektorSet.add(a.sektor_industri);
			});
			filterOptions.value = {
				angkatan: Array.from(angkatanSet).sort(),
				prodi: Array.from(prodiSet).sort(),
				sektor: Array.from(sektorSet).sort(),
			};

			// Clear existing layers
			callbacks.clearAllLayers();

			// Build markers
			allMarkers.length = 0;
			const heatPoints: [number, number, number][] = [];

			alumniData.forEach((a) => {
				if (
					a.latitude == null ||
					a.longitude == null ||
					a.latitude === "" ||
					a.longitude === ""
				)
					return;
				const lat = parseFloat(String(a.latitude));
				const lng = parseFloat(String(a.longitude));
				if (Number.isNaN(lat) || Number.isNaN(lng) || lat === 0 || lng === 0)
					return;

				// Jitter ±0.01 (~1km)
				const jLat = lat + (Math.random() - 0.5) * 0.02;
				const jLng = lng + (Math.random() - 0.5) * 0.02;
				const latlng = L.latLng(jLat, jLng);

				const category = getCategory(a.tipe_lokasi);
				const cfg = CATEGORY_CONFIG[category];

				const marker = L.marker(latlng, {
					icon: createMarkerIcon(cfg.color),
				});
				marker.bindPopup(createPopupContent(a), {
					maxWidth: 300,
					className: "custom-popup",
				});

				allMarkers.push({ marker, category, data: a, latlng });
				heatPoints.push([jLat, jLng, 1]);
			});

			// Apply layers based on mode
			await callbacks.applyLayers(heatPoints);

			// Fit bounds
			if (allMarkers.length > 0 && rawMap()) {
				const visibleMarkers = allMarkers.filter(
					(m) => layerVisibility.value[m.category],
				);
				if (visibleMarkers.length > 0) {
					const group = L.featureGroup(visibleMarkers.map((m) => m.marker));
					try {
						rawMap()!.fitBounds(group.getBounds().pad(0.2));
					} catch (e) {}
				}
			}

			// Setup viewport tracking
			callbacks.setupViewportTracking();
			callbacks.updateViewportStats();

			lastUpdated.value = formatTime(new Date());
		} catch (error) {
			const { toast } = await import("vue-sonner");
			toast.error("Gagal memuat data peta.");
		} finally {
			isLoading.value = false;
		}
	};

	return {
		allAlumni,
		allMarkers,
		filterOptions,
		completionMeta,
		isLoading,
		lastUpdated,
		fetchMapData,
	};
}
