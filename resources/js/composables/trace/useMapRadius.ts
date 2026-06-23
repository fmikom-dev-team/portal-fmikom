import L from "leaflet";
import { ref } from "vue";
import type { MarkerEntry } from "./useMapData";

export function useMapRadius() {
	const radiusMode = ref(false);
	const radiusKm = ref(10);
	const radiusCenter = ref<L.LatLng | null>(null);
	const radiusAlumniCount = ref(0);
	let radiusCircle: L.Circle | null = null;
	let radiusMarker: L.Marker | null = null;

	const clearRadius = (rawMap: () => L.Map | null) => {
		const m = rawMap();
		if (!m) return;
		if (radiusCircle) {
			m.removeLayer(radiusCircle);
			radiusCircle = null;
		}
		if (radiusMarker) {
			m.removeLayer(radiusMarker);
			radiusMarker = null;
		}
		radiusCenter.value = null;
		radiusAlumniCount.value = 0;
		m.off("click", onRadiusClick);
	};

	const toggleRadiusMode = (rawMap: () => L.Map | null) => {
		radiusMode.value = !radiusMode.value;
		if (!radiusMode.value) clearRadius(rawMap);
	};

	let _rawMap: (() => L.Map | null) | null = null;
	let _allMarkers: MarkerEntry[] = [];
	let _layerVisibility: Record<string, boolean> = {};

	const onRadiusClick = (e: L.LeafletMouseEvent) => {
		if (!radiusMode.value || !_rawMap) return;
		placeRadius(_rawMap, e.latlng, _allMarkers, _layerVisibility);
	};

	const activateRadiusListener = (
		rawMap: () => L.Map | null,
		allMarkers: MarkerEntry[],
		layerVisibility: Record<string, boolean>,
	) => {
		const m = rawMap();
		if (!m) return;
		_rawMap = rawMap;
		_allMarkers = allMarkers;
		_layerVisibility = layerVisibility;
		m.off("click", onRadiusClick);
		m.on("click", onRadiusClick);
	};

	const placeRadius = (
		rawMap: () => L.Map | null,
		center: L.LatLng,
		allMarkers: MarkerEntry[],
		layerVisibility: Record<string, boolean>,
	) => {
		const m = rawMap();
		if (!m) return;

		// Clear previous
		if (radiusCircle) m.removeLayer(radiusCircle);
		if (radiusMarker) m.removeLayer(radiusMarker);

		radiusCenter.value = center;
		const radiusMeters = radiusKm.value * 1000;

		radiusCircle = L.circle(center, {
			radius: radiusMeters,
			color: "#0C447C",
			weight: 2,
			fillColor: "#0C447C",
			fillOpacity: 0.08,
			dashArray: "6 4",
		}).addTo(m);

		radiusMarker = L.marker(center, {
			icon: L.divIcon({
				html: '<div style="width:12px;height:12px;background:#0C447C;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(12,68,124,0.5);"></div>',
				className: "radius-center-icon",
				iconSize: [12, 12],
				iconAnchor: [6, 6],
			}),
		}).addTo(m);

		// Count alumni in radius
		const count = allMarkers.filter(
			(mk) =>
				layerVisibility[mk.category] &&
				center.distanceTo(mk.latlng) <= radiusMeters,
		).length;
		radiusAlumniCount.value = count;

		radiusMarker
			.bindPopup(
				`<div style="text-align:center;font-family:system-ui;padding:4px;">
                <div style="font-size:24px;font-weight:900;color:#0C447C;">${count}</div>
                <div style="font-size:10px;font-weight:700;color:#64748b;">alumni dalam radius ${radiusKm.value} km</div>
            </div>`,
				{ className: "custom-popup" },
			)
			.openPopup();
	};

	const updateRadiusSize = (
		rawMap: () => L.Map | null,
		km: number,
		allMarkers: MarkerEntry[],
		layerVisibility: Record<string, boolean>,
	) => {
		radiusKm.value = km;
		if (radiusCenter.value)
			placeRadius(rawMap, radiusCenter.value, allMarkers, layerVisibility);
	};

	return {
		radiusMode,
		radiusKm,
		radiusCenter,
		radiusAlumniCount,
		toggleRadiusMode,
		clearRadius,
		activateRadiusListener,
		placeRadius,
		updateRadiusSize,
	};
}
