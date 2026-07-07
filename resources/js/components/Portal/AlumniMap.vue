<script setup lang="ts">
import { ref, computed, watch, nextTick } from "vue";
import L from "leaflet";
import AlumniStats from "@/components/Portal/AlumniStats.vue";
import { useLeafletMap } from "@/composables/useLeafletMap";
import "leaflet.markercluster";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";

const props = withDefaults(
    defineProps<{
        alumniData?: Array<any>;
        totalAlumni?: number;
        alumniStats?: any;
    }>(),
    {
        alumniData: () => [],
        totalAlumni: 0,
        alumniStats: () => ({}),
    },
);

const activeAlumniList = computed(() => {
    // Map alumni with a known current status, including home-location fallback for those not working yet.
    const list = props.alumniData || [];
    return list.filter((a) => {
        const s = a.status;
        return (
            s === "bekerja" ||
            s === "wirausaha" ||
            s === "lanjut_studi" ||
            s === "studi" ||
            s === "mencari_kerja" ||
            s === "belum"
        );
    });
});

const displayTotalAlumni = computed(() => {
    const total = props.alumniStats?.alumni ?? props.totalAlumni ?? 0;
    return total.toLocaleString("id-ID");
});

// ─── LEAFLET MAP INTEGRATION ──────────────────────────────
const { mapContainer, map, isReady } = useLeafletMap({
    center: [-2.5, 118],
    zoom: 4,
});

const colors = {
    bekerja: "#3b82f6",
    wirausaha: "#10b981",
    studi: "#8b5cf6",
    lanjut_studi: "#8b5cf6",
    belum: "#f59e0b",
};

const labels = {
    bekerja: "Bekerja",
    wirausaha: "Wirausaha",
    studi: "Lanjut Studi",
    lanjut_studi: "Lanjut Studi",
    belum: "Belum Bekerja",
};

const getStatusLabel = (status: string) => {
    if (status === "mencari_kerja") return labels.belum;
    return labels[status as keyof typeof labels] || "Belum Bekerja";
};

const getStatusColor = (status: string) => {
    if (status === "mencari_kerja") return colors.belum;
    return colors[status as keyof typeof colors] || colors.belum;
};

const createMarkerIcon = (status: string, size: number = 10) => {
    const color = getStatusColor(status);
    return L.divIcon({
        html: `<div style="width:${size}px;height:${size}px;background:${color};border:2px solid white;border-radius:50%;box-shadow:0 2px 6px ${color}66;"></div>`,
        className: "custom-marker-icon",
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
};

const createPopupContent = (a: any) => {
    const color = getStatusColor(a.status);
    const label = getStatusLabel(a.status);
    const location = [a.kota, a.provinsi].filter(Boolean).join(", ") || "-";

    return `
        <div style="min-width: 220px; font-family: system-ui, -apple-system, sans-serif; padding: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 8px; border-radius: 99px; background: ${color}15; color: ${color};">
                    ${label}
                </span>
                <span style="font-size: 10px; font-weight: 700; color: #64748b;">
                    Lulusan ${a.tahun_lulus}
                </span>
            </div>
            
            <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 6px; line-height: 1.3;">
                ${a.name}
            </div>
            
            ${
                a.detail_karir
                    ? `
                <div style="font-size: 11px; color: #475569; margin-bottom: 8px; display: flex; align-items: flex-start; gap: 4px; line-height: 1.4;">
                    <span style="flex-shrink: 0; margin-top: 1px;">💼</span>
                    <span>${a.detail_karir}</span>
                </div>
            `
                    : ""
            }
            
            <div style="border-top: 1px solid #f1f5f9; margin-top: 8px; padding-top: 8px; display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                <span style="font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">LOKASI</span>
                <span style="font-size: 10px; font-weight: 700; color: #475569; text-align: right; max-width: 70%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${location}">
                    📍 ${location}
                </span>
            </div>
        </div>
    `;
};

let markerCluster: any = null;

watch(
    [isReady, activeAlumniList],
    ([ready, list]) => {
        const m = map.value;
        if (!ready || !m) return;

        nextTick(() => {
            if (markerCluster) {
                try {
                    m.removeLayer(markerCluster);
                } catch (e) {
                    console.error("Error removing old layer:", e);
                }
            }

            let layerGroup: any;
            if (typeof (L as any).markerClusterGroup === "function") {
                try {
                    layerGroup = (L as any).markerClusterGroup({
                        showCoverageOnHover: false,
                        zoomToBoundsOnClick: true,
                        maxClusterRadius: 50,
                        iconCreateFunction: (cluster: any) => {
                            const count = cluster.getChildCount();
                            const size =
                                count > 100
                                    ? 44
                                    : count > 50
                                      ? 40
                                      : count > 10
                                        ? 36
                                        : 30;
                            return L.divIcon({
                                html: `<div class="cluster-icon" style="width:${size}px;height:${size}px;">${count}</div>`,
                                className: "custom-cluster",
                                iconSize: [size, size],
                            });
                        },
                    });
                } catch (e) {
                    console.error("Failed to create markerClusterGroup:", e);
                    layerGroup = L.featureGroup();
                }
            } else {
                console.warn(
                    "L.markerClusterGroup is not defined. Falling back to L.featureGroup.",
                );
                layerGroup = L.featureGroup();
            }

            markerCluster = layerGroup;
            const markers: L.Marker[] = [];

            list.forEach((alumni) => {
                const lat = parseFloat(alumni.latitude);
                const lng = parseFloat(alumni.longitude);
                if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return;

                const jitterLat = lat + (Math.random() - 0.5) * 0.02;
                const jitterLng = lng + (Math.random() - 0.5) * 0.02;

                try {
                    const marker = L.marker([jitterLat, jitterLng], {
                        icon: createMarkerIcon(alumni.status || "belum", 10),
                    });

                    marker.bindPopup(createPopupContent(alumni), {
                        maxWidth: 300,
                        className: "custom-popup",
                    });

                    markers.push(marker);
                } catch (err) {
                    console.error(
                        "Error creating marker for alumni:",
                        alumni.name,
                        err,
                    );
                }
            });

            if (markers.length > 0) {
                try {
                    if (typeof layerGroup.addLayers === "function") {
                        layerGroup.addLayers(markers);
                    } else {
                        markers.forEach((mk) => layerGroup.addLayer(mk));
                    }
                    m.addLayer(layerGroup);

                    // Adjust bounds to show all active markers
                    if (markers.length === 1) {
                        const latlng = markers[0].getLatLng();
                        m.setView(latlng, 8);
                    } else {
                        m.fitBounds(layerGroup.getBounds().pad(0.2));
                    }
                } catch (e) {
                    console.error(
                        "Error adding layer group to map or adjusting bounds:",
                        e,
                    );
                }
            } else {
                // No markers, fallback national view
                m.setView([-2.5, 118], 4);
            }
        });
    },
    { immediate: true },
);
</script>

<template>
    <!-- ALUMNI MAP TRACKING SECTION -->
    <section
        class="relative overflow-hidden bg-white py-24 sm:py-32 font-sans border-t border-gray-100"
    >
        <!-- Subtle Background Elements -->
        <div
            class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] bg-size-[16px_16px] opacity-30 pointer-events-none"
        ></div>

        <div class="relative z-10 mx-auto max-w-360 px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center mb-24"
            >
                <!-- Left Text Content -->
                <div
                    class="lg:col-span-5 flex flex-col justify-center text-center lg:text-left z-20 hide-animate slide-right"
                >
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-xs font-semibold w-fit mx-auto lg:mx-0 mb-8 shadow-sm"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex rounded-full h-2 w-2 bg-green-500"
                            ></span>
                        </span>
                        <span class="text-blue-500 font-bold">Terhubung</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-blue-500 font-bold">Tumbuh</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-blue-500 font-bold">Berdampak</span>
                    </div>

                    <h2
                        class="text-4xl sm:text-5xl lg:text-[3.5rem] font-extrabold tracking-tight text-slate-900 mb-6 leading-[1.1]"
                    >
                        Lacak Alumni,<br />Bentuk Masa Depan<br />
                        <span
                            class="text-transparent bg-clip-text bg-linear-to-r from-blue-600 to-blue-400"
                            >Bersama</span
                        >
                    </h2>

                    <p
                        class="text-slate-500 text-lg sm:text-xl mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0"
                    >
                        AlumniTrack membantu kampus terhubung dengan alumni,
                        memetakan jejak karier, dan membangun jaringan yang
                        lebih kuat di seluruh dunia.
                    </p>

                    <div
                        class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start"
                    >
                        <button
                            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center justify-center gap-2 group"
                            aria-label="Jelajahi Peta Alumni"
                        >
                            Jelajahi Peta Alumni
                            <svg
                                class="w-4 h-4 transition-transform group-hover:translate-x-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                />
                            </svg>
                        </button>
                        <button
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white text-slate-700 font-semibold border border-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-3"
                            aria-label="Lihat video cara kerja"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"
                            >
                                <svg
                                    class="w-4 h-4 ml-0.5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm">Lihat Video</div>
                                <div
                                    class="text-[10px] text-slate-400 font-normal -mt-0.5"
                                >
                                    Cara kerjanya
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Right Map Visualization (Leaflet Map) -->
                <div
                    class="lg:col-span-7 relative min-h-[550px] z-10 hide-animate slide-left flex items-center justify-center"
                >
                    <!-- Leaflet map container -->
                    <div
                        ref="mapContainer"
                        class="absolute inset-0 w-full h-full rounded-[2rem] overflow-hidden border border-slate-100 shadow-2xl z-10 bg-slate-50"
                    ></div>

                    <!-- Floating Total Alumni Card -->
                    <div
                        class="absolute top-4 left-4 bg-white/90 backdrop-blur-md border border-slate-100 rounded-2xl p-4 shadow-xl z-20 flex items-center gap-4 animate-[bounce_4s_infinite]"
                    >
                        <div
                            class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-[10px] text-slate-500 font-semibold mb-0.5"
                            >
                                Total Alumni
                            </p>
                            <div class="flex items-center gap-2">
                                <p
                                    class="text-lg font-black text-slate-900 leading-none"
                                >
                                    {{ displayTotalAlumni }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-10 hide-animate slide-up">
                <p
                    class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6"
                >
                    Dipercaya oleh berbagai institusi dan perusahaan
                </p>
                <div
                    class="flex flex-wrap justify-center gap-8 sm:gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500"
                >
                    <div
                        class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"
                    >
                        <span class="text-blue-600">GO</span>JEK
                    </div>
                    <div
                        class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"
                    >
                        tokopedia
                    </div>
                    <div
                        class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"
                    >
                        traveloka<span class="text-blue-400">.</span>
                    </div>
                    <div
                        class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"
                    >
                        <span class="text-orange-500">Shopee</span>
                    </div>
                    <div
                        class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"
                    >
                        Ruang<span class="text-blue-500">guru</span>
                    </div>
                </div>
            </div>

            <!-- 4 Stat Boxes -->
            <AlumniStats :stats="alumniStats" />
        </div>
    </section>
</template>

<style>
/* ─── CUSTOM MARKER ────────────────────────────── */
.custom-marker-icon {
    background: transparent !important;
    border: none !important;
}

/* ─── CLUSTER ICON ─────────────────────────────── */
.custom-cluster {
    background: transparent !important;
    border: none !important;
}
.cluster-icon {
    background: rgba(12, 68, 124, 0.85);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 900;
    box-shadow: 0 4px 12px rgba(12, 68, 124, 0.4);
}

/* ─── POPUP ────────────────────────────────────── */
.custom-popup .leaflet-popup-content-wrapper {
    border-radius: 1.25rem;
    padding: 0;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.06);
    overflow: hidden;
}
.custom-popup .leaflet-popup-content {
    margin: 0;
    padding: 16px;
}
.custom-popup .leaflet-popup-tip-container {
    display: none;
}
</style>
