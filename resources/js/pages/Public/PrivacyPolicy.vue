<script setup>
import {
	BookOpen,
	Building2,
	ChevronRight,
	Clock,
	Cookie,
	Database,
	ExternalLink,
	Eye,
	FileText,
	Globe,
	HelpCircle,
	Info,
	Laptop,
	Lock,
	Mail,
	MapPin,
	RefreshCw,
	Server,
	Shield,
	Trash2,
	UserX,
} from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";
import PublicLayout from "@/layouts/PublicLayout.vue";

// Active section for Scroll Spy
const activeSection = ref("definitions");

const sections = [
	{ id: "definitions", label: "Definisi & Istilah", icon: BookOpen },
	{ id: "collecting-data", label: "Pengumpulan Data Pribadi", icon: Database },
	{ id: "use-data", label: "Penggunaan Data Pribadi", icon: Eye },
	{ id: "retention", label: "Retensi Data", icon: Clock },
	{ id: "transfer", label: "Transfer Data", icon: Globe },
	{ id: "delete-data", label: "Penghapusan Data", icon: Trash2 },
	{ id: "disclosure", label: "Pengungkapan Data", icon: Info },
	{ id: "security", label: "Keamanan Data", icon: Lock },
	{ id: "children", label: "Privasi Anak-Anak", icon: UserX },
	{ id: "external-links", label: "Tautan Pihak Ketiga", icon: ExternalLink },
	{ id: "changes", label: "Perubahan Kebijakan", icon: RefreshCw },
	{ id: "contact", label: "Hubungi Kami", icon: Mail },
];

let observer;

onMounted(() => {
	// Scroll Spy Observer
	const options = {
		root: null,
		rootMargin: "-20% 0px -60% 0px",
		threshold: 0,
	};

	observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				activeSection.value = entry.target.id;
			}
		});
	}, options);

	sections.forEach((sec) => {
		const el = document.getElementById(sec.id);
		if (el) observer.observe(el);
	});
});

onUnmounted(() => {
	if (observer) observer.disconnect();
});

const scrollTo = (id) => {
	const el = document.getElementById(id);
	if (el) {
		const yOffset = -90; // offset for sticky navbar
		const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
		window.scrollTo({ top: y, behavior: "smooth" });
		activeSection.value = id;
	}
};
</script>

<template>
    <PublicLayout
        title="Privacy Policy"
        description="Kebijakan privasi resmi untuk pengguna layanan Portal FMIKOM Universitas Nahdlatul Ulama Al Ghazali Cilacap."
        hero-title="Privacy Policy"
        hero-subtitle="Last updated: June 11, 2026"
        hero-class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900"
        :breadcrumbs="[{ label: 'Privacy Policy' }]"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Sticky Sidebar Navigation (Visible on Large Screens) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-24 space-y-4">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <div class="flex items-center gap-2 mb-6">
                        <Shield class="w-5 h-5 text-indigo-650" />
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Navigasi Dokumen</h3>
                    </div>
                    
                    <nav class="space-y-1">
                        <button 
                            v-for="sec in sections" 
                            :key="sec.id"
                            @click="scrollTo(sec.id)"
                            :class="[
                                activeSection === sec.id 
                                    ? 'bg-indigo-50/80 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 font-bold' 
                                    : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/50',
                                'w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] text-left transition-all'
                            ]"
                        >
                            <component :is="sec.icon" class="w-4 h-4 shrink-0" />
                            <span class="truncate">{{ sec.label }}</span>
                        </button>
                    </nav>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="col-span-1 lg:col-span-9 space-y-12">
                
                <!-- Introduction Card -->
                <section class="bg-white dark:bg-slate-900 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)] space-y-4">
                    <p class="text-[14.5px] leading-relaxed text-slate-600 dark:text-slate-350">
                        This Privacy Policy describes Our policies and procedures on the collection, use and disclosure of Your information when You use the Service and tells You about Your privacy rights and how the law protects You.
                    </p>
                    <p class="text-[14.5px] leading-relaxed text-slate-600 dark:text-slate-350">
                        We use Your Personal Data to provide and improve the Service. By using the Service, You agree to the collection and use of information in accordance with this Privacy Policy. This Privacy Policy has been created with the help of the <a href="https://www.termsfeed.com/privacy-policy-generator/" target="_blank" class="text-indigo-600 dark:text-indigo-450 hover:underline font-bold inline-flex items-center gap-0.5">Privacy Policy Generator <ExternalLink class="w-3.5 h-3.5 inline" /></a>.
                    </p>
                </section>

                <!-- 1. Interpretation and Definitions -->
                <section id="definitions" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <BookOpen class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Interpretation and Definitions</h2>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base font-black text-slate-800 dark:text-white">Interpretation</h3>
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            The words whose initial letters are capitalized have meanings defined under the following conditions. The following definitions shall have the same meaning regardless of whether they appear in singular or in plural.
                        </p>
                    </div>

                    <div class="space-y-6 mt-6">
                        <h3 class="text-base font-black text-slate-800 dark:text-white">Definitions</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">For the purposes of this Privacy Policy:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Definition Cards -->
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><FileText class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Account</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">A unique account created for You to access our Service or parts of our Service.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Building2 class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Company</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Refers to Faculty of Mathematics and Computer Science, Nahdlatul Ulama Al Ghazali University Cilacap.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Cookie class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Cookies</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Small files placed on Your device containing details of Your browsing history on that website.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Laptop class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Device</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Any device that can access the Service such as a computer, a cell phone or a digital tablet.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Database class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Personal Data</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Any information that relates to an identified or identifiable individual.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Server class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Service Provider</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Any natural or legal person who processes the data on behalf of the Company.</p></div>
                            </div>
                        </div>

                        <!-- Full list of definitions link/detail accordion -->
                        <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-5 space-y-3.5">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2"><HelpCircle class="w-4 h-4 text-slate-400"/> Definisi Lainnya:</h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-xs text-slate-600 dark:text-slate-400 font-medium list-disc list-inside">
                                <li><strong>Affiliate</strong>: Entitas pengendali atau yang dikendalikan bersama.</li>
                                <li><strong>Country</strong>: Indonesia.</li>
                                <li><strong>Service</strong>: Merujuk pada Website.</li>
                                <li><strong>Usage Data</strong>: Data yang dikumpulkan secara otomatis.</li>
                                <li><strong>Website</strong>: PORTAL FMIKOM (<a href="https://portalfmikom.ac.id" target="_blank" class="hover:underline text-indigo-600 font-bold">https://portalfmikom.ac.id</a>).</li>
                                <li><strong>You</strong>: Individu yang mengakses atau menggunakan layanan.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 2. Collecting and Using Your Personal Data -->
                <section id="collecting-data" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Database class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Collecting and Using Personal Data</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">Types of Data Collected</h3>

                            <!-- Personal Data sub-section -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5"><ChevronRight class="w-4 h-4"/> Personal Data</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                    While using Our Service, We may ask You to provide Us with certain personally identifiable information that can be used to contact or identify You. Personally identifiable information collected includes:
                                </p>
                                <div class="flex flex-wrap gap-2 pt-1">
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">Nama (Full Name)</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">NIM (Student ID)</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">Foto Profil (Profile Picture)</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">Pesan (Messages)</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">IP Address</span>
                                    <span class="bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300">Activity Logs</span>
                                </div>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-800" />

                            <!-- Usage Data sub-section -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5"><ChevronRight class="w-4 h-4"/> Usage Data</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Usage Data is collected automatically when using the Service.
                                </p>
                                <p class="text-xs text-slate-450 dark:text-slate-555 leading-relaxed">
                                    Usage Data may include information such as Your Device's Internet Protocol address (e.g. IP address), browser type, browser version, the pages of our Service that You visit, the time and date of Your visit, the time spent on those pages, unique device identifiers and other diagnostic data.
                                </p>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-800" />

                            <!-- Cookies sub-section -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5"><ChevronRight class="w-4 h-4"/> Tracking Technologies and Cookies</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                    We use Cookies and similar tracking technologies to track the activity on Our Service and store certain information.
                                </p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-100/50 dark:border-slate-800 space-y-1">
                                        <h5 class="text-xs font-bold text-slate-800 dark:text-white">Cookies or Browser Cookies</h5>
                                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">A cookie is a small file placed on Your Device. You can instruct Your browser to refuse all Cookies.</p>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-100/50 dark:border-slate-800 space-y-1">
                                        <h5 class="text-xs font-bold text-slate-800 dark:text-white">Web Beacons</h5>
                                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">Electronic files permitting the Company, for example, to count users who have visited those pages.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Use of Your Personal Data -->
                <section id="use-data" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Eye class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Use of Your Personal Data</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-555 leading-relaxed">
                            The Company may use Personal Data for the following purposes:
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Purpose Grid -->
                            <div class="p-5 rounded-2xl border border-slate-100/80 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2 hover:shadow-md transition-shadow">
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-emerald-650 dark:text-emerald-400 text-[9px] font-black uppercase">Autentikasi</span>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white">Autentikasi (Authentication)</h4>
                                <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">Mengidentifikasi pengguna dan memverifikasi akses masuk resmi ke dalam portal.</p>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100/80 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2 hover:shadow-md transition-shadow">
                                <span class="px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/20 text-blue-650 dark:text-blue-400 text-[9px] font-black uppercase">Keamanan</span>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white">Keamanan (Security)</h4>
                                <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">Menjaga keamanan data portal, mendeteksi aktivitas mencurigakan, dan mencegah penyalahgunaan.</p>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100/80 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2 hover:shadow-md transition-shadow">
                                <span class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/20 text-purple-650 dark:text-purple-400 text-[9px] font-black uppercase">Komunikasi</span>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white">Komunikasi (Communication)</h4>
                                <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">Menghubungi pengguna via email atau notifikasi terkait layanan akademik dan informasi portal.</p>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100/80 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2 hover:shadow-md transition-shadow">
                                <span class="px-2 py-0.5 rounded-lg bg-orange-50 dark:bg-orange-950/20 text-orange-650 dark:text-orange-400 text-[9px] font-black uppercase">Operasional</span>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white">Operasional Portal (Portal Operations)</h4>
                                <p class="text-[11.5px] text-slate-500 dark:text-slate-400 leading-relaxed">Menjalankan fungsi inti portal, memantau kinerja sistem, serta meningkatkan kualitas layanan.</p>
                            </div>
                        </div>

                        <!-- Share situations section -->
                        <div class="bg-indigo-50/20 dark:bg-indigo-950/10 rounded-3xl p-6 border border-indigo-500/10 space-y-4 mt-6">
                            <h4 class="text-sm font-black text-indigo-750 dark:text-indigo-300">Situations where we may share your Personal Data:</h4>
                            <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-450 font-medium">
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0 mt-1.5"></div><span><strong>With Service Providers</strong>: To monitor and analyze usage and contact You.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0 mt-1.5"></div><span><strong>For business transfers</strong>: During negotiations or transfers of assets.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0 mt-1.5"></div><span><strong>With Affiliates</strong>: In which case we will require them to honor this Privacy Policy.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0 mt-1.5"></div><span><strong>With business partners</strong>: To offer You certain products, services or promotions.</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 4. Retention of Your Personal Data -->
                <section id="retention" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Clock class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Retention of Your Personal Data</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            The Company will retain Your Personal Data only for as long as is necessary for the purposes set out in this Privacy Policy. We apply different retention periods to different categories of Personal Data based on the purpose of processing:
                        </p>

                        <!-- Timeline/Visual Retention periods -->
                        <div class="space-y-3">
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Account Information</h4>
                                    <p class="text-xs text-slate-450 dark:text-slate-500">User accounts data</p>
                                </div>
                                <span class="bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 px-3 py-1 rounded-xl text-xs font-black">Up to 24 months after closure</span>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Customer Support Data</h4>
                                    <p class="text-xs text-slate-450 dark:text-slate-500">Tickets, correspondence & chat transcripts</p>
                                </div>
                                <span class="bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 px-3 py-1 rounded-xl text-xs font-black">Up to 24 months</span>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Usage Data & Server Logs</h4>
                                    <p class="text-xs text-slate-450 dark:text-slate-500">Website analytics cookies & server access records</p>
                                </div>
                                <span class="bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 px-3 py-1 rounded-xl text-xs font-black">Up to 24 months</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Transfer of Your Personal Data -->
                <section id="transfer" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Globe class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Transfer of Your Personal Data</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            Your information, including Personal Data, is processed at the Company's operating offices and in any other places where the parties involved in the processing are located. It means that this information may be transferred to — and maintained on — computers located outside of Your state, province, country or other governmental jurisdiction where the data protection laws may differ from those from Your jurisdiction.
                        </p>
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            The Company will take all steps reasonably necessary to ensure that Your data is treated securely and in accordance with this Privacy Policy and no transfer of Your Personal Data will take place to an organization or a country unless there are adequate controls in place including the security of Your data and other personal information.
                        </p>
                    </div>
                </section>

                <!-- 6. Delete Your Personal Data -->
                <section id="delete-data" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Trash2 class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Delete Your Personal Data</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            You have the right to delete or request that We assist in deleting the Personal Data that We have collected about You.
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            You may update, amend, or delete Your information at any time by signing in to Your Account, if you have one, and visiting the account settings section that allows you to manage Your personal information. You may also contact Us to request access to, correct, or delete any Personal Data that You have provided to Us.
                        </p>
                    </div>
                </section>

                <!-- 7. Disclosure of Your Personal Data -->
                <section id="disclosure" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Info class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Disclosure of Your Personal Data</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2">
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">Business Transactions</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">If the Company is involved in a merger, acquisition or asset sale, Your Personal Data may be transferred.</p>
                            </div>
                            
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2">
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">Law Enforcement</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">We may disclose Your data if required to do so by law or in response to valid requests by public authorities.</p>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2">
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">Legal Requirements</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">To comply with legal obligations, protect rights or property, investigate wrongdoing, and protect personal safety.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 8. Security of Your Personal Data -->
                <section id="security" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Lock class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Security of Your Personal Data</h2>
                    </div>

                    <div class="bg-indigo-50/20 dark:bg-indigo-950/10 rounded-3xl p-6 border border-indigo-500/10 space-y-4">
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-350">
                            The security of Your Personal Data is important to Us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While We strive to use commercially reasonable means to protect Your Personal Data, We cannot guarantee its absolute security.
                        </p>
                    </div>
                </section>

                <!-- 9. Children's Privacy -->
                <section id="children" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <UserX class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Children's Privacy</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            Our Service does not address anyone under the age of 16. We do not knowingly collect personally identifiable information from anyone under the age of 16. If You are a parent or guardian and You are aware that Your child has provided Us with Personal Data, please contact Us. If We become aware that We have collected Personal Data from anyone under the age of 16 without verification of parental consent, We take steps to remove that information from Our servers.
                        </p>
                    </div>
                </section>

                <!-- 10. Links to Other Websites -->
                <section id="external-links" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <ExternalLink class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Links to Other Websites</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            Our Service may contain links to other websites that are not operated by Us. If You click on a third party link, You will be directed to that third party's site. We strongly advise You to review the Privacy Policy of every site You visit.
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            We have no control over and assume no responsibility for the content, privacy policies or practices of any third party sites or services.
                        </p>
                    </div>
                </section>

                <!-- 11. Changes to this Privacy Policy -->
                <section id="changes" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <RefreshCw class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Changes to this Privacy Policy</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            We may update Our Privacy Policy from time to time. We will notify You of any changes by posting the new Privacy Policy on this page.
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            We will let You know via email and/or a prominent notice on Our Service, prior to the change becoming effective and update the "Last updated" date at the top of this Privacy Policy.
                        </p>
                    </div>
                </section>

                <!-- 12. Contact Us -->
                <section id="contact" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                            <Mail class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Contact Us</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            If you have any questions about this Privacy Policy, You can contact us:
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <a href="mailto:fmikom@unugha.id" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition-transform"><Mail class="w-5 h-5"/></div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 dark:text-white">By Email</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 hover:underline">fmikom@unugha.id</p>
                                </div>
                            </a>

                            <a href="https://portalfmikom.ac.id" target="_blank" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition-transform"><Globe class="w-5 h-5"/></div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 dark:text-white">By Website</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 hover:underline">portalfmikom.ac.id</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.scroll-mt-28 {
    scroll-margin-top: 7rem;
}
</style>
