<script setup>
import {
	AlertTriangle,
	BookOpen,
	Building2,
	CheckCircle2,
	ChevronRight,
	ExternalLink,
	FileText,
	Gavel,
	Globe,
	HelpCircle,
	Laptop,
	Mail,
	Scale,
	Server,
	ShieldAlert,
	UserCheck,
	XCircle,
} from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";
import PublicLayout from "@/layouts/PublicLayout.vue";

// Active section for Scroll Spy
const activeSection = ref("definitions");

const sections = [
	{ id: "definitions", label: "Definisi & Istilah", icon: BookOpen },
	{ id: "acknowledgment", label: "Pengakuan & Kelayakan", icon: UserCheck },
	{ id: "acceptable-use", label: "Larangan Penggunaan", icon: ShieldAlert },
	{ id: "admin-rights", label: "Hak & Otoritas Admin", icon: Gavel },
	{ id: "external-links", label: "Tautan Pihak Ketiga", icon: ExternalLink },
	{ id: "termination", label: "Penghentian Layanan", icon: XCircle },
	{ id: "liability", label: "Batasan Tanggung Jawab", icon: ShieldAlert },
	{
		id: "disclaimer",
		label: "Pernyataan Penyangkalan (AS IS)",
		icon: AlertTriangle,
	},
	{ id: "governing-law", label: "Hukum yang Mengatur", icon: Gavel },
	{ id: "disputes", label: "Penyelesaian Sengketa", icon: Scale },
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
        title="Terms of Service"
        description="Syarat dan ketentuan penggunaan resmi layanan Portal FMIKOM Universitas Nahdlatul Ulama Al Ghazali Cilacap."
        hero-title="Terms of Service"
        hero-subtitle="Last updated: June 11, 2026"
        hero-class="bg-gradient-to-br from-slate-900 via-indigo-950 to-emerald-950"
        :breadcrumbs="[{ label: 'Terms of Service' }]"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Sticky Sidebar Navigation (Visible on Large Screens) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-24 space-y-4">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <div class="flex items-center gap-2 mb-6">
                        <Scale class="w-5 h-5 text-emerald-650" />
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Navigasi Dokumen</h3>
                    </div>
                    
                    <nav class="space-y-1">
                        <button 
                            v-for="sec in sections" 
                            :key="sec.id"
                            @click="scrollTo(sec.id)"
                            :class="[
                                activeSection === sec.id 
                                    ? 'bg-emerald-50/80 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 font-bold' 
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
                <section class="bg-white dark:bg-slate-900 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <p class="text-[14.5px] leading-relaxed text-slate-600 dark:text-slate-350">
                        Please read these Terms of Service carefully before using Our Service. These Terms of Service govern Your access to and use of the Service and form the entire agreement between You and the Company.
                    </p>
                </section>

                <!-- 1. Interpretation and Definitions -->
                <section id="definitions" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
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
                        <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">For the purposes of these Terms of Service:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Definition Cards -->
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Building2 class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Company</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Refers to Faculty of Mathematics and Computer Science, Nahdlatul Ulama Al Ghazali University Cilacap.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Laptop class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Device</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Any device that can access the Service such as a computer, a cell phone or a digital tablet.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><FileText class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Terms of Service</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">These Terms, including any documents expressly incorporated by reference, governing Your access to and use of the Service.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Globe class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Third-Party Social Media</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Any services or content (including data, information, or products) provided by a third party.</p></div>
                            </div>
                        </div>

                        <!-- Full list of definitions link/detail accordion -->
                        <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-5 space-y-3.5">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2"><HelpCircle class="w-4 h-4 text-slate-400"/> Definisi Lainnya:</h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-xs text-slate-600 dark:text-slate-400 font-medium list-disc list-inside">
                                <li><strong>Affiliate</strong>: Entitas pengendali atau yang dikendalikan bersama (kepemilikan saham >= 50%).</li>
                                <li><strong>Country</strong>: Indonesia.</li>
                                <li><strong>Service</strong>: Merujuk pada Website.</li>
                                <li><strong>Website</strong>: PORTAL FMIKOM (<a href="https://portalfmikom.ac.id" target="_blank" class="hover:underline text-indigo-650 font-bold">https://portalfmikom.ac.id</a>).</li>
                                <li><strong>You</strong>: Individu yang mengakses atau menggunakan layanan.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 2. Acknowledgment & Eligibility -->
                <section id="acknowledgment" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <UserCheck class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Acknowledgment & User Eligibility</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-5">
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-350">
                            These are the Terms of Service governing the use of this Service and the agreement between You and the Company. These Terms set out the rights and obligations of all users regarding the use of the Service.
                        </p>
                        
                        <div class="p-5 rounded-2xl bg-emerald-50/20 dark:bg-emerald-950/10 border border-emerald-500/10 space-y-3.5 text-xs text-slate-600 dark:text-slate-400 font-medium">
                            <h4 class="text-sm font-black text-emerald-750 dark:text-emerald-300 flex items-center gap-2">
                                <CheckCircle2 class="w-4 h-4" /> Persyaratan Pengguna & Persetujuan:
                            </h4>
                            <ul class="space-y-2.5 pl-6 list-disc">
                                <li><strong>Access Condition</strong>: Akses dan penggunaan Layanan ini bergantung pada penerimaan dan kepatuhan Anda terhadap Syarat dan Ketentuan Layanan ini.</li>
                                <li><strong>Agreement</strong>: Dengan mengakses atau menggunakan Layanan, Anda setuju untuk terikat oleh Syarat dan Ketentuan Layanan ini. Jika Anda tidak setuju dengan bagian mana pun, Anda tidak diperkenankan mengakses Layanan.</li>
                                <li><strong>Age Limit</strong>: Anda menyatakan bahwa Anda berusia di atas 18 tahun. Perusahaan tidak mengizinkan mereka yang berusia di bawah 18 tahun untuk menggunakan Layanan secara mandiri.</li>
                                <li><strong>Privacy Policy</strong>: Akses Anda juga tunduk pada Kebijakan Privasi kami. Harap membaca Kebijakan Privasi kami dengan saksama sebelum menggunakan Layanan.</li>
                            </ul>

                            <hr class="border-emerald-500/10 my-3" />

                            <h4 class="text-sm font-black text-emerald-750 dark:text-emerald-300 flex items-center gap-2">
                                <UserCheck class="w-4 h-4" /> Kelayakan Pengguna (Who Can Use the Portal):
                            </h4>
                            <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 mt-1">
                                Portal FMIKOM secara resmi ditujukan khusus untuk digunakan oleh pihak-pihak internal berikut:
                            </p>
                            <ul class="space-y-2.5 pl-6 list-disc font-medium text-slate-600 dark:text-slate-400">
                                <li><strong>Mahasiswa</strong>: Mahasiswa aktif dari program studi di bawah Fakultas Matematika dan Ilmu Komputer.</li>
                                <li><strong>Dosen</strong>: Tenaga pengajar dan staf akademik resmi di lingkungan fakultas.</li>
                                <li><strong>Admin</strong>: Tim pengelola teknologi informasi dan administrasi yang berwenang.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 3. Acceptable Use and Prohibitions -->
                <section id="acceptable-use" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <ShieldAlert class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Acceptable Use & Prohibitions</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-350">
                            Sebagai pengguna Portal FMIKOM, Anda berkewajiban untuk menggunakan layanan ini secara bertanggung jawab. Anda dilarang keras melakukan tindakan-tindakan berikut (Prohibitions):
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="p-4 rounded-2xl bg-red-50/20 dark:bg-red-950/10 border border-red-500/10 space-y-1">
                                <h4 class="text-xs font-bold text-red-750 dark:text-red-400">Spamming</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Mengirim pesan massal, iklan tidak sah, pesan berulang, atau bentuk komunikasi sampah lainnya di dalam portal.</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-red-50/20 dark:bg-red-950/10 border border-red-500/10 space-y-1">
                                <h4 class="text-xs font-bold text-red-750 dark:text-red-400">Upload Malware / Virus</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Mengunggah berkas yang mengandung kode berbahaya, malware, ransomware, worm, atau program berbahaya lainnya.</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-red-50/20 dark:bg-red-950/10 border border-red-500/10 space-y-1">
                                <h4 class="text-xs font-bold text-red-750 dark:text-red-400">Penyalahgunaan Akun</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Menggunakan akun milik orang lain, memberikan akses akun kepada pihak tidak berwenang, atau memalsukan identitas.</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-red-50/20 dark:bg-red-950/10 border border-red-500/10 space-y-1">
                                <h4 class="text-xs font-bold text-red-750 dark:text-red-400">Konten Ilegal</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Mengunggah, membagikan, atau mempublikasikan materi yang melanggar hukum, sara, ujaran kebencian, atau pornografi.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Administrator Rights -->
                <section id="admin-rights" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <Gavel class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Administrator Rights & Authority</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-350">
                            Pengelola dan Administrator Portal FMIKOM memiliki hak penuh dan otoritas mutlak untuk menjaga ketertiban, keamanan, dan fungsionalitas layanan portal. Hak-hak tersebut meliputi:
                        </p>
                        
                        <div class="space-y-3 pt-2">
                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between gap-4">
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-bold text-slate-850 dark:text-white">Suspend Akun (Account Suspension)</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Menonaktifkan sementara atau memblokir permanen akses akun pengguna yang melanggar ketentuan.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-650 dark:text-amber-400 text-[10px] font-black uppercase shrink-0">Suspend</span>
                            </div>
                            
                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between gap-4">
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-bold text-slate-850 dark:text-white">Hapus Konten (Content Moderation)</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Menghapus, menyunting, atau menyembunyikan konten/materi yang dianggap melanggar aturan atau tidak pantas.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 text-[10px] font-black uppercase shrink-0">Hapus</span>
                            </div>

                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between gap-4">
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-bold text-slate-850 dark:text-white">Menonaktifkan Layanan (Service Deactivation)</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">Menghentikan atau menangguhkan sebagian atau seluruh layanan portal untuk pemeliharaan atau alasan keamanan.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase shrink-0">Nonaktif</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Links to Other Websites -->
                <section id="external-links" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <ExternalLink class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Links to Other Websites</h2>
                    </div>

                    <div class="space-y-4 text-sm text-slate-500 dark:text-slate-450 leading-relaxed">
                        <p>
                            Our Service may contain links to third-party websites or services that are not owned or controlled by the Company.
                        </p>
                        <p>
                            The Company has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third-party websites or services. You further acknowledge and agree that the Company shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with the use of or reliance on any such content, goods or services available on or through any such websites or services.
                        </p>
                        
                        <div class="rounded-2xl border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-5 space-y-2 mt-4">
                            <h4 class="font-bold text-slate-800 dark:text-white">Links from a Third-Party Social Media Service</h4>
                            <p class="text-xs">
                                The Service may display, include, make available, or link to content or services provided by a Third-Party Social Media Service. Your use of any Third-Party Social Media Service is governed by that Third-Party Social Media Service's terms and privacy policies.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 6. Termination -->
                <section id="termination" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <XCircle class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Termination</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            We may terminate or suspend Your access immediately, without prior notice or liability, for any reason whatsoever, including without limitation if You breach these Terms of Service.
                        </p>
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-bold text-red-600 dark:text-red-400">
                            Upon termination, Your right to use the Service will cease immediately.
                        </p>
                    </div>
                </section>

                <!-- 7. Limitation of Liability -->
                <section id="liability" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <ShieldAlert class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Limitation of Liability</h2>
                    </div>

                    <div class="space-y-4 text-sm text-slate-500 dark:text-slate-450 leading-relaxed">
                        <p>
                            Notwithstanding any damages that You might incur, the entire liability of the Company and any of its suppliers under any provision of these Terms and Your exclusive remedy for all of the foregoing shall be limited to the amount actually paid by You through the Service or 100 USD if You haven't purchased anything through the Service.
                        </p>
                        <p>
                            To the maximum extent permitted by applicable law, in no event shall the Company or its suppliers be liable for any special, incidental, indirect, or consequential damages whatsoever (including, but not limited to, damages for loss of profits, loss of data or other information, for business interruption, for personal injury, loss of privacy arising out of or in any way related to the use of or inability to use the Service).
                        </p>
                    </div>
                </section>

                <!-- 8. Disclaimer of Warranties -->
                <section id="disclaimer" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <AlertTriangle class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">"AS IS" and "AS AVAILABLE" Disclaimer</h2>
                    </div>

                    <div class="bg-amber-50/20 dark:bg-amber-950/10 rounded-3xl p-6 border border-amber-500/10 space-y-4">
                        <h4 class="text-sm font-black text-amber-700 dark:text-amber-400 flex items-center gap-2">Penyangkalan Hak (Disclaimer)</h4>
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-350">
                            The Service is provided to You "AS IS" and "AS AVAILABLE" and with all faults and defects without warranty of any kind. To the maximum extent permitted under applicable law, the Company, on its own behalf and on behalf of its Affiliates and its and their respective licensors and service providers, expressly disclaims all warranties, whether express, implied, statutory or otherwise.
                        </p>
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-350">
                            Without limiting the foregoing, the Company provides no warranty or undertaking, and makes no representation of any kind that the Service will meet Your requirements, achieve any intended results, be compatible or work with any other software, applications, systems or services, operate without interruption, meet any performance or reliability standards or be error free or that any errors or defects can or will be corrected.
                        </p>
                    </div>
                </section>

                <!-- 9. Governing Law -->
                <section id="governing-law" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <Gavel class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Governing Law</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            The laws of the Country (Indonesia), excluding its conflicts of law rules, shall govern these Terms and Your use of the Service. Your use of the Application may also be subject to other local, state, national, or international laws.
                        </p>
                    </div>
                </section>

                <!-- 10. Disputes Resolution & Other Provisions -->
                <section id="disputes" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <Scale class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Disputes Resolution & Legal Compliance</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2">
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">Disputes Resolution</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">If You have any concern or dispute about the Service, You agree to first try to resolve the dispute informally by contacting the Company.</p>
                            </div>
                            
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2">
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">United States Compliance</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">You represent that (i) You are not located in an embargoed or terrorist-supporting country, and (ii) You are not on any prohibited party list.</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-5 space-y-3 mt-4 text-xs text-slate-600 dark:text-slate-400">
                            <h4 class="font-bold text-slate-800 dark:text-white">Severability and Waiver:</h4>
                            <p><strong>Severability</strong>: If any provision is held unenforceable, it will be modified to accomplish its objectives under law while remaining provisions continue in force.</p>
                            <p><strong>Waiver</strong>: Failure to exercise a right or require performance does not affect ability to do so later, nor does waiver of a breach waive future breaches.</p>
                        </div>
                    </div>
                </section>

                <!-- 11. Contact Us -->
                <section id="contact" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-650 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center">
                            <Mail class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Contact Us</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            If you have any questions about these Terms of Service, You can contact us:
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <a href="mailto:fmikom@unugha.id" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform"><Mail class="w-5 h-5"/></div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 dark:text-white">By Email</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 hover:underline">fmikom@unugha.id</p>
                                </div>
                            </a>

                            <a href="https://portalfmikom.ac.id/contact" target="_blank" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform"><Globe class="w-5 h-5"/></div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 dark:text-white">By Website</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 hover:underline">portalfmikom.ac.id/contact</p>
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
