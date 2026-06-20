<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
	AlertCircle,
	Check,
	Copy,
	Download,
	Edit2,
	ExternalLink,
	FileText,
	Link2,
	MoreVertical,
	Plus,
	Share2,
	Trash2,
	X,
} from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";
import { Skeleton } from "@/components/ui/skeleton";
import Footer from "../ui/Footer.vue";
import Navbar from "../ui/Navbar.vue";
import UmumNavbar from "../ui/UmumNavbar.vue";

const isLoading = ref(false);

const page = usePage();
const flash = computed(() => (page.props.flash || {}) as Record<string, any>);
const isMahasiswa = computed(() => {
	const role = (page.props as any).context?.active_role || "mahasiswa";
	return role.toLowerCase() === "mahasiswa";
});

const props = defineProps<{
	cvs: Array<{
		id: number;
		title: string;
		template_id: string;
		status: string;
		updated_at: string;
	}>;
}>();

const showDeleteModal = ref(false);
const cvToDelete = ref<number | null>(null);
const activeDropdown = ref<number | null>(null);

const toggleDropdown = (id: number) => {
	if (activeDropdown.value === id) {
		activeDropdown.value = null;
	} else {
		activeDropdown.value = id;
	}
};

const closeDropdowns = () => {
	activeDropdown.value = null;
};

// Global click handler to close dropdowns when clicking outside
if (typeof globalThis.window !== "undefined") {
	globalThis.window.addEventListener("click", (e) => {
		const target = e.target as HTMLElement;
		if (!target.closest(".dropdown-trigger")) {
			closeDropdowns();
		}
	});
}

const formatDate = (dateString: string) => {
	const date = new Date(dateString);
	return date.toLocaleDateString("id-ID", {
		day: "numeric",
		month: "short",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};

const getTemplateName = (templateId: string) => {
	const names: Record<string, string> = {
		"ats-professional": "ATS Professional",
		"modern-sidebar": "Modern Sidebar",
		executive: "Executive Classic",
		"creative-minimal": "Creative Minimalist",
		"student-resume": "Student Resume",
		custom: "Custom Bebas",
	};
	return names[templateId] || templateId;
};

const getTemplateLabelClass = (templateId: string) => {
	const classes: Record<string, string> = {
		"ats-professional":
			"bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200/50 dark:border-blue-800/30",
		"modern-sidebar":
			"bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400 border-sky-200/50 dark:border-sky-800/30",
		executive:
			"bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-300 border-slate-200/60 dark:border-zinc-700",
		"creative-minimal":
			"bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200/50 dark:border-purple-800/30",
		"student-resume":
			"bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50 dark:border-emerald-800/30",
		custom:
			"bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200/50 dark:border-indigo-800/30",
	};
	return classes[templateId] || "bg-slate-50 text-slate-700 border-slate-200";
};

const confirmDelete = (id: number) => {
	cvToDelete.value = id;
	showDeleteModal.value = true;
	activeDropdown.value = null;
};

const executeDelete = () => {
	if (cvToDelete.value !== null) {
		router.delete(`/pagi/cv/${cvToDelete.value}`, {
			onSuccess: () => {
				showDeleteModal.value = false;
				cvToDelete.value = null;
			},
		});
	}
};

const showShareModal = ref(false);
const cvToShare = ref<any>(null);
const copied = ref(false);
const shareLink = ref("");

const openShareModal = (cv: any) => {
	cvToShare.value = cv;
	if (typeof globalThis.window !== "undefined") {
		shareLink.value = `${globalThis.window.location.origin}/pagi/cv/${cv.id}/shared`;
	}
	showShareModal.value = true;
	activeDropdown.value = null;
	copied.value = false;
};

const copyShareLink = () => {
	if (!shareLink.value) return;
	navigator.clipboard.writeText(shareLink.value).then(() => {
		copied.value = true;
		setTimeout(() => {
			copied.value = false;
		}, 2000);
	});
};

const downloadCv = (id: number) => {
	// Use native navigation to bypass Inertia interception of binary responses
	const link = document.createElement("a");
	link.href = `/pagi/cv/${id}/download`;
	link.setAttribute("rel", "external");
	document.body.appendChild(link);
	link.click();
	link.remove();
};
</script>

<template>
    <Head title="CV Builder Dashboard — PAGI">
        <title>CV Builder Dashboard — PAGI</title>
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 font-sans text-slate-900 dark:text-zinc-100">
        <Navbar v-if="isMahasiswa" />
        <UmumNavbar v-else />

        <!-- Header Hero Section -->
        <div class="relative overflow-hidden bg-linear-to-br from-slate-900 via-zinc-950 to-indigo-950 border-b border-slate-800/80 dark:border-zinc-900 py-12 px-6">
            <!-- Glowing dots background -->
            <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] bg-size-[20px_20px]"></div>
            
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">
                        CV Builder Dashboard
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-xl">
                        Buat CV profesional yang dioptimalkan untuk sistem ATS (Applicant Tracking System) atau pilih desain kreatif untuk melamar magang dan pekerjaan.
                    </p>
                </div>
                
                <Link
                    v-if="cvs.length < 3"
                    href="/pagi/cv/templates"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-3 text-xs font-bold shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/35 transition-all transform hover:-translate-y-0.5"
                >
                    <Plus class="w-4 h-4" />
                    Buat CV Baru
                </Link>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="max-w-6xl mx-auto px-6 py-10">
            <!-- Flash Message Alerts -->
            <div v-if="flash.success" class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-3.5 rounded-lg text-xs font-semibold flex items-center gap-2">
                <Check class="w-4 h-4 shrink-0" />
                <span>{{ flash.success }}</span>
            </div>
            <div v-if="flash.error" class="mb-5 bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 p-3.5 rounded-lg text-xs font-semibold flex items-center gap-2">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ flash.error }}</span>
            </div>

            <!-- Loading Skeleton Grid -->
            <div v-if="isLoading" class="space-y-4 select-none">
                <Skeleton class="h-4 w-32 rounded mb-4" />
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="n in 3" :key="n" class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-900 rounded-xl p-4 flex flex-col justify-between h-[180px]">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <Skeleton class="h-5 w-40 rounded" />
                                <Skeleton class="h-7 w-7 rounded-lg" />
                            </div>
                            <Skeleton class="h-5 w-16 rounded" />
                            <div class="space-y-2 mt-4">
                                <div class="flex justify-between flex-wrap gap-1">
                                    <Skeleton class="h-3 w-16" />
                                    <Skeleton class="h-3.5 w-24" />
                                </div>
                                <div class="flex justify-between flex-wrap gap-1">
                                    <Skeleton class="h-3 w-20" />
                                    <Skeleton class="h-3.5 w-32" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CV List Empty State -->
            <div v-else-if="cvs.length === 0" class="text-center py-16 bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-900 rounded-xl shadow-xs px-6">
                <div class="w-14 h-14 rounded-xl bg-slate-50 dark:bg-zinc-800/50 flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-zinc-550">
                    <FileText class="w-7 h-7" />
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-zinc-200 mb-1">Belum ada CV yang dibuat</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mx-auto leading-relaxed mb-6">
                    Pilih template profesional kami, isi data diri Anda, dan unduh CV dalam format PDF instan dalam hitungan menit.
                </p>
                <Link
                    href="/pagi/cv/templates"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 text-xs font-bold transition-all shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/20 cursor-pointer"
                >
                    Pilih Template & Mulai
                </Link>
            </div>

            <!-- CV List Cards Grid -->
            <div v-else class="space-y-4">
                <h2 class="text-xs font-extrabold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-2">CV Terbaru Anda</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div 
                        v-for="cv in cvs" 
                        :key="cv.id" 
                        class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-900 hover:border-slate-300 dark:hover:border-zinc-800 rounded-xl shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden group"
                    >
                        <!-- Top header card layout -->
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <!-- Title and status -->
                                <div class="space-y-1 min-w-0">
                                    <h3 class="font-bold text-xs text-slate-800 dark:text-zinc-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ cv.title }}
                                    </h3>
                                    <span class="inline-flex items-center text-[9px] font-extrabold px-2 py-0.5 rounded-md border"
                                        :class="cv.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-200/50 dark:border-emerald-800/30' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border-amber-200/50 dark:border-amber-800/30'">
                                        {{ cv.status === 'published' ? 'Siap Guna' : 'Draf' }}
                                    </span>
                                </div>

                                <!-- Action Menu Dropdown -->
                                <div class="relative dropdown-trigger">
                                    <button 
                                        @click.stop="toggleDropdown(cv.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:text-zinc-550 dark:hover:text-zinc-350 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-all border-none bg-transparent"
                                        aria-label="Opsi CV"
                                    >
                                        <MoreVertical class="w-4 h-4" />
                                    </button>

                                    <!-- Dropdown list -->
                                    <div 
                                        v-show="activeDropdown === cv.id"
                                        class="absolute right-0 top-full mt-1 w-44 rounded-xl border border-slate-150 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl p-1 z-30"
                                    >
                                        <Link 
                                            :href="`/pagi/cv/${cv.id}/edit`"
                                            class="w-full inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded-lg transition-all"
                                        >
                                            <Edit2 class="w-3.5 h-3.5" />
                                            Edit / Buka
                                        </Link>
                                        <button 
                                            @click="openShareModal(cv)"
                                            class="w-full inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded-lg transition-all border-none bg-transparent text-left cursor-pointer"
                                        >
                                            <Share2 class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
                                            Bagikan
                                        </button>
                                        <button 
                                            v-if="cv.status === 'published'"
                                            @click="downloadCv(cv.id)"
                                            class="w-full inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded-lg transition-all border-none bg-transparent text-left cursor-pointer"
                                        >
                                            <Download class="w-3.5 h-3.5" />
                                            Unduh PDF
                                        </button>
                                        <span 
                                            v-else
                                            class="w-full inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-400 dark:text-zinc-600 rounded-lg cursor-not-allowed"
                                            title="Lengkapi CV terlebih dahulu untuk mengunduh"
                                        >
                                            <Download class="w-3.5 h-3.5" />
                                            Unduh PDF
                                        </span>
                                        <div class="h-px bg-slate-100 dark:bg-zinc-800 my-1"></div>
                                        <button 
                                            @click="confirmDelete(cv.id)"
                                            class="w-full inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-all border-none bg-transparent text-left cursor-pointer"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta details (layout selected) -->
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-500 dark:text-zinc-400">
                                    <span>Template</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border" :class="getTemplateLabelClass(cv.template_id)">
                                        {{ getTemplateName(cv.template_id) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-slate-500 dark:text-zinc-400">
                                    <span>Pembaruan</span>
                                    <span class="font-medium text-slate-700 dark:text-zinc-350">
                                        {{ formatDate(cv.updated_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer actions -->
                        <div class="border-t border-slate-100 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-zinc-900/40 p-3 flex items-center justify-end gap-2">
                            <button 
                                v-if="cv.status === 'published'"
                                @click="downloadCv(cv.id)"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-bold transition-all border border-slate-200/60 dark:border-zinc-800 cursor-pointer bg-transparent"
                            >
                                <Download class="w-3.5 h-3.5" />
                                PDF
                            </button>
                            <span
                                v-else
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 dark:text-zinc-600 text-xs font-bold border border-slate-200/60 dark:border-zinc-800 cursor-not-allowed select-none"
                                title="Lengkapi CV terlebih dahulu"
                            >
                                <Download class="w-3.5 h-3.5" />
                                PDF
                            </span>
                            <Link 
                                :href="`/pagi/cv/${cv.id}/edit`"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all shadow-sm cursor-pointer"
                            >
                                <Edit2 class="w-3.5 h-3.5" />
                                Edit CV
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- DELETE CONFIRMATION MODAL -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-sm w-full border border-slate-200 dark:border-zinc-800 p-6 shadow-2xl space-y-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400">
                    <AlertCircle class="w-6 h-6" />
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-base font-bold text-slate-800 dark:text-zinc-100">Hapus CV ini?</h3>
                    <p class="text-xs text-slate-550 dark:text-zinc-400 leading-relaxed">
                        Tindakan ini tidak dapat dibatalkan. Seluruh data CV yang telah diisi akan dihapus secara permanen.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button 
                        @click="showDeleteModal = false; cvToDelete = null"
                        class="rounded-xl border border-slate-200 dark:border-zinc-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-850 transition-all border-none bg-transparent cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        @click="executeDelete"
                        class="rounded-xl bg-red-600 hover:bg-red-500 text-white px-4 py-2 text-xs font-bold transition-all shadow-sm shadow-red-600/10 cursor-pointer"
                    >
                        Hapus Permanen
                    </button>
                </div>
            </div>
        </div>

        <!-- SHARE MODAL -->
        <div v-if="showShareModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4" @click.self="showShareModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-zinc-800 p-6 shadow-2xl relative overflow-hidden transition-all duration-300">
                <!-- Close Button -->
                <button 
                    @click="showShareModal = false"
                    class="absolute top-4 right-4 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors border-none bg-transparent cursor-pointer"
                    aria-label="Tutup"
                >
                    <X class="w-4 h-4" />
                </button>

                <!-- Icon and Title -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <Share2 class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-zinc-100">Bagikan CV Anda</h3>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400">Siap dibagikan ke rekruter atau publik</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="text-xs text-slate-555 dark:text-zinc-400 leading-relaxed">
                        Salin tautan di bawah untuk membagikan CV versi PDF siap cetak langsung. Siapa saja yang memiliki tautan ini dapat melihat tanpa perlu login.
                    </p>

                    <!-- CV Title & Status Display inside Modal -->
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-zinc-850/50 border border-slate-100 dark:border-zinc-800 text-xs">
                        <span class="font-bold text-slate-700 dark:text-zinc-300 truncate max-w-[200px]">
                            {{ cvToShare?.title }}
                        </span>
                        <span 
                            class="inline-flex items-center text-[9px] font-extrabold px-2 py-0.5 rounded-md border"
                            :class="cvToShare?.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-200/50 dark:border-emerald-800/30' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border-amber-200/50 dark:border-amber-800/30'"
                        >
                            {{ cvToShare?.status === 'published' ? 'Siap Guna' : 'Draf' }}
                        </span>
                    </div>

                    <!-- Link Box & Copy Button -->
                    <div class="flex items-center gap-2">
                        <div class="flex-1 flex items-center gap-2 bg-slate-50 dark:bg-zinc-850/50 border border-slate-200 dark:border-zinc-800 rounded-xl px-3 py-2.5 overflow-hidden">
                            <Link2 class="w-4 h-4 text-slate-400 shrink-0" />
                            <input 
                                type="text" 
                                readonly 
                                :value="shareLink"
                                class="w-full bg-transparent border-none outline-none text-xs text-slate-600 dark:text-zinc-300 font-semibold select-all"
                            />
                        </div>
                        <button 
                            @click="copyShareLink"
                            class="rounded-xl px-4 py-2.5 text-xs font-bold transition-all shrink-0 cursor-pointer flex items-center gap-1.5 border-none shadow-sm"
                            :class="copied ? 'bg-emerald-600 text-white shadow-emerald-600/10' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-600/10'"
                        >
                            <Check v-if="copied" class="w-3.5 h-3.5" />
                            <Copy v-else class="w-3.5 h-3.5" />
                            <span>{{ copied ? 'Tersalin!' : 'Salin' }}</span>
                        </button>
                    </div>

                    <!-- Warning for Draft -->
                    <div v-if="cvToShare?.status === 'draft'" class="bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 p-3 rounded-xl text-[11px] font-semibold flex items-start gap-2 leading-relaxed">
                        <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
                        <span>CV Anda saat ini berstatus <strong>Draf</strong>. Beberapa bagian mungkin belum lengkap. Anda disarankan untuk menyelesaikan CV agar siap kerja.</span>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800/80">
                        <button 
                            @click="showShareModal = false"
                            class="rounded-xl px-4 py-2 text-xs font-bold text-slate-605 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-all border-none bg-transparent cursor-pointer"
                        >
                            Tutup
                        </button>
                        <a 
                            :href="shareLink"
                            target="_blank"
                            class="rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white px-4 py-2 text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer decoration-none"
                        >
                            <ExternalLink class="w-3.5 h-3.5" />
                            Buka Tautan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
