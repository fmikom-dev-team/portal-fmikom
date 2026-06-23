import { type Ref } from "vue";
import L from "leaflet";
import axios from "axios";
import "leaflet.markercluster";
import "leaflet.heat";
import {
    type MarkerEntry,
    type ViewMode,
    type TematicField,
    CATEGORY_CONFIG,
    TEMATIC_PALETTES,
    createMarkerIcon,
} from "./useMapData";

export function useMapLayers() {
    let markerCluster: any = null;
    let heatLayer: any = null;
    let choroplethLayer: any = null;
    let geoJsonCache: any = null;

    const clearAllLayers = (rawMap: () => L.Map | null) => {
        if (!rawMap()) return;
        if (markerCluster) {
            try {
                rawMap()!.removeLayer(markerCluster);
            } catch (e) {}
        }
        if (heatLayer) {
            try {
                rawMap()!.removeLayer(heatLayer);
            } catch (e) {}
            heatLayer = null;
        }
        if (choroplethLayer) {
            try {
                rawMap()!.removeLayer(choroplethLayer);
            } catch (e) {}
            choroplethLayer = null;
        }
    };

    const initCluster = () => {
        markerCluster = (L as any).markerClusterGroup({
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 50,
            iconCreateFunction: (cluster: any) => {
                const count = cluster.getChildCount();
                const size =
                    count > 100 ? 44 : count > 50 ? 40 : count > 10 ? 36 : 30;
                return L.divIcon({
                    html: `<div class="cluster-icon" style="width:${size}px;height:${size}px;">${count}</div>`,
                    className: "custom-cluster",
                    iconSize: [size, size],
                });
            },
        });
        return markerCluster;
    };

    const applyLayers = async (
        rawMap: () => L.Map | null,
        allMarkers: MarkerEntry[],
        layerVisibility: Ref<Record<string, boolean>>,
        viewMode: Ref<ViewMode>,
        showMarkers: Ref<boolean>,
        tematicField: Ref<TematicField>,
        heatPoints?: [number, number, number][],
    ) => {
        if (!rawMap()) return;
        clearAllLayers(rawMap);

        if (!markerCluster) initCluster();

        const visibleMarkers = allMarkers.filter(
            (m) => layerVisibility.value[m.category],
        );

        if (viewMode.value === "cluster") {
            markerCluster.clearLayers();
            markerCluster.addLayers(visibleMarkers.map((m) => m.marker));
            rawMap()!.addLayer(markerCluster);
        } else if (viewMode.value === "heat") {
            const hp =
                heatPoints ||
                visibleMarkers.map(
                    (m) =>
                        [m.latlng.lat, m.latlng.lng, 1] as [
                            number,
                            number,
                            number,
                        ],
                );
            try {
                heatLayer = (L as any)
                    .heatLayer(hp, {
                        radius: 25,
                        blur: 15,
                        maxZoom: 12,
                        gradient: {
                            0.2: "#3b82f6",
                            0.4: "#22c55e",
                            0.6: "#eab308",
                            0.8: "#f97316",
                            1.0: "#ef4444",
                        },
                    })
                    .addTo(rawMap()!);
            } catch (e) {}
            if (showMarkers.value) {
                markerCluster.clearLayers();
                visibleMarkers.forEach((m) =>
                    m.marker.setIcon(
                        createMarkerIcon(CATEGORY_CONFIG[m.category].color, 6),
                    ),
                );
                markerCluster.addLayers(visibleMarkers.map((m) => m.marker));
                rawMap()!.addLayer(markerCluster);
            }
        } else if (viewMode.value === "choropleth") {
            await loadChoropleth(rawMap, visibleMarkers);
            if (showMarkers.value) {
                markerCluster.clearLayers();
                visibleMarkers.forEach((m) =>
                    m.marker.setIcon(
                        createMarkerIcon(CATEGORY_CONFIG[m.category].color, 6),
                    ),
                );
                markerCluster.addLayers(visibleMarkers.map((m) => m.marker));
                rawMap()!.addLayer(markerCluster);
            }
        } else if (viewMode.value === "tematic") {
            const palette = TEMATIC_PALETTES[tematicField.value];
            const valueColorMap = new Map<string, string>();
            let colorIdx = 0;

            visibleMarkers.forEach((m) => {
                let key = "";
                if (tematicField.value === "status")
                    key = m.data.tipe_lokasi || "Lainnya";
                else if (tematicField.value === "sektor")
                    key = m.data.sektor_industri || "Tidak Diketahui";
                else if (tematicField.value === "angkatan")
                    key = m.data.angkatan || "?";
                else if (tematicField.value === "prodi")
                    key = m.data.program_studi || "Tidak Diketahui";

                if (!valueColorMap.has(key)) {
                    valueColorMap.set(key, palette[colorIdx % palette.length]);
                    colorIdx++;
                }
                m.marker.setIcon(createMarkerIcon(valueColorMap.get(key)!, 12));
            });

            markerCluster.clearLayers();
            markerCluster.addLayers(visibleMarkers.map((m) => m.marker));
            rawMap()!.addLayer(markerCluster);
        }
    };

    // ─── CHOROPLETH ─────────────────────────────────────────────
    const loadChoropleth = async (
        rawMap: () => L.Map | null,
        markers: MarkerEntry[],
    ) => {
        if (!rawMap()) return;

        if (!geoJsonCache) {
            try {
                const resp = await axios.get(
                    "/geojson/indonesia-provinces.json",
                );
                geoJsonCache = resp.data;
            } catch (e) {
                return;
            }
        }

        const provCountMap = new Map<string, number>();
        const provKabMap = new Map<string, Map<string, number>>();

        const getBBox = (coords: any): [number, number, number, number] => {
            let minLat = 90,
                maxLat = -90,
                minLng = 180,
                maxLng = -180;
            const flatten = (c: any) => {
                if (typeof c[0] === "number") {
                    minLng = Math.min(minLng, c[0]);
                    maxLng = Math.max(maxLng, c[0]);
                    minLat = Math.min(minLat, c[1]);
                    maxLat = Math.max(maxLat, c[1]);
                } else {
                    c.forEach(flatten);
                }
            };
            flatten(coords);
            return [minLat, maxLat, minLng, maxLng];
        };

        const features = geoJsonCache.features.map((f: any) => ({
            name:
                f.properties.PROVINSI ||
                f.properties.Propinsi ||
                f.properties.name ||
                "Unknown",
            bbox: getBBox(f.geometry.coordinates),
        }));

        markers.forEach((m) => {
            const lat = m.latlng.lat;
            const lng = m.latlng.lng;
            for (const fb of features) {
                const [minLat, maxLat, minLng, maxLng] = fb.bbox;
                if (
                    lat >= minLat &&
                    lat <= maxLat &&
                    lng >= minLng &&
                    lng <= maxLng
                ) {
                    provCountMap.set(
                        fb.name,
                        (provCountMap.get(fb.name) || 0) + 1,
                    );
                    // Track kabupaten
                    const kota = m.data.nama_kota || "Tidak Diketahui";
                    if (!provKabMap.has(fb.name))
                        provKabMap.set(fb.name, new Map());
                    const kabMap = provKabMap.get(fb.name)!;
                    kabMap.set(kota, (kabMap.get(kota) || 0) + 1);
                    break;
                }
            }
        });

        const maxCount = Math.max(...Array.from(provCountMap.values()), 1);
        const getColor = (count: number) => {
            if (count === 0) return "rgba(12,68,124,0.03)";
            const r = count / maxCount;
            if (r > 0.7) return "#0C447C";
            if (r > 0.5) return "#1a6bb5";
            if (r > 0.3) return "#3a8fd4";
            if (r > 0.1) return "#85B7EB";
            return "#c5dcf5";
        };

        const buildTooltip = (provName: string, total: number) => {
            const kabMap = provKabMap.get(provName);
            let kabHtml = "";
            if (kabMap && kabMap.size > 0) {
                const sorted = Array.from(kabMap.entries()).sort(
                    (a, b) => b[1] - a[1],
                );
                const top5 = sorted.slice(0, 5);
                kabHtml =
                    '<div style="border-top:1px solid #e2e8f0;margin:6px 0 4px;"></div>';
                kabHtml += top5
                    .map(
                        ([name, count]) =>
                            `<div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:2px;">
                        <span style="font-size:10px;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;">${name}</span>
                        <span style="font-size:10px;font-weight:900;color:#0C447C;">${count}</span>
                    </div>`,
                    )
                    .join("");
                if (sorted.length > 5) {
                    kabHtml += `<div style="font-size:9px;color:#94a3b8;text-align:center;margin-top:2px;">+${sorted.length - 5} kota/kab lainnya</div>`;
                }
            }
            return `<div style="min-width:180px;">
                <div style="font-size:12px;font-weight:900;color:#0C447C;">${provName}</div>
                <div style="font-size:11px;font-weight:700;color:#64748b;">${total} Alumni</div>
                ${kabHtml}
            </div>`;
        };

        choroplethLayer = L.geoJSON(geoJsonCache, {
            style: (feature: any) => {
                const name =
                    feature?.properties?.PROVINSI ||
                    feature?.properties?.Propinsi ||
                    "";
                const count = provCountMap.get(name) || 0;
                return {
                    fillColor: getColor(count),
                    weight: 1.5,
                    opacity: 0.8,
                    color: "#0C447C",
                    fillOpacity: count > 0 ? 0.6 : 0.05,
                };
            },
            onEachFeature: (feature: any, layer: any) => {
                const name =
                    feature?.properties?.PROVINSI ||
                    feature?.properties?.Propinsi ||
                    "Unknown";
                const count = provCountMap.get(name) || 0;
                layer.bindTooltip(buildTooltip(name, count), {
                    sticky: true,
                    className: "province-tooltip",
                });
                layer.on({
                    mouseover: (e: any) => {
                        e.target.setStyle({
                            weight: 3,
                            fillOpacity: 0.8,
                            color: "#EF9F27",
                        });
                    },
                    mouseout: (e: any) => {
                        choroplethLayer?.resetStyle(e.target);
                    },
                });
            },
        }).addTo(rawMap()!);
    };

    return {
        initCluster,
        clearAllLayers,
        applyLayers,
        loadChoropleth,
    };
}
