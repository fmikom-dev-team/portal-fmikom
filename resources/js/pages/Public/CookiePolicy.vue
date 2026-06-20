<script setup>
import {
	AlertTriangle,
	BookOpen,
	Building2,
	ChevronRight,
	Chrome,
	Cookie,
	ExternalLink,
	FileText,
	Globe,
	HelpCircle,
	Laptop,
	Mail,
	Server,
	Settings,
	ShieldCheck,
} from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";
import PublicLayout from "@/layouts/PublicLayout.vue";

// Active section for Scroll Spy
const activeSection = ref("definitions");

const sections = [
	{ id: "definitions", label: "Definisi & Istilah", icon: BookOpen },
	{ id: "use-cookies", label: "Penggunaan Cookies", icon: Cookie },
	{ id: "choices", label: "Pilihan & Pengaturan", icon: Settings },
	{ id: "changes", label: "Perubahan Kebijakan", icon: ShieldCheck },
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

const browsers = [
	{
		name: "Google Chrome",
		url: "https://support.google.com/accounts/answer/32050",
	},
	{
		name: "Microsoft Edge",
		url: "https://support.microsoft.com/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09",
	},
	{
		name: "Mozilla Firefox",
		url: "https://support.mozilla.org/en-US/kb/delete-cookies-remove-info-websites-stored",
	},
	{
		name: "Apple Safari",
		url: "https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/mac",
	},
];
</script>

<template>
    <PublicLayout
        title="Cookie Policy"
        description="Kebijakan cookie resmi untuk pengguna layanan Portal FMIKOM Universitas Nahdlatul Ulama Al Ghazali Cilacap."
        hero-title="Cookie Policy"
        hero-subtitle="Last updated: June 11, 2026"
        hero-class="bg-gradient-to-br from-indigo-950 via-slate-900 to-amber-950"
        :breadcrumbs="[{ label: 'Cookie Policy' }]"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Sticky Sidebar Navigation (Visible on Large Screens) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-24 space-y-4">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <div class="flex items-center gap-2 mb-6">
                        <Cookie class="w-5 h-5 text-amber-600" />
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Navigasi Dokumen</h3>
                    </div>
                    
                    <nav class="space-y-1">
                        <button 
                            v-for="sec in sections" 
                            :key="sec.id"
                            @click="scrollTo(sec.id)"
                            :class="[
                                activeSection === sec.id 
                                    ? 'bg-amber-50/80 text-amber-705 dark:bg-amber-950/40 dark:text-amber-400 font-bold' 
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
                        This Cookie Policy explains what Cookies are and how We use them. You should read this policy so You can understand what type of cookies We use, or the information We collect using Cookies and how that information is used. This Cookie Policy has been created with the help of the <a href="https://www.termsfeed.com/cookies-policy-generator/" target="_blank" class="text-amber-600 dark:text-amber-450 hover:underline font-bold inline-flex items-center gap-0.5">Cookies Policy Generator <ExternalLink class="w-3.5 h-3.5 inline" /></a>.
                    </p>
                    <p class="text-[14.5px] leading-relaxed text-slate-600 dark:text-slate-350">
                        Cookies do not typically contain any information that personally identifies a user, but personal information that We store about You may be linked to the information stored in and obtained from Cookies. For further information on how We use, store and keep your personal data secure, see our Privacy Policy, if and when We make it available within the Website or on our website.
                    </p>
                    <p class="text-[14.5px] leading-relaxed text-slate-600 dark:text-slate-350">
                        We do not store sensitive personal information, such as mailing addresses, account passwords, etc. in the Cookies We use.
                    </p>
                </section>

                <!-- 1. Interpretation and Definitions -->
                <section id="definitions" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center">
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
                        <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">For the purposes of this Cookie Policy:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Definition Cards -->
                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Building2 class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Company</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Refers to Faculty of Mathematics and Computer Science, Nahdlatul Ulama Al Ghazali University Cilacap.</p></div>
                            </div>

                            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-400"><Cookie class="w-5 h-5"/></div>
                                <div class="space-y-1"><h4 class="text-sm font-black text-slate-800 dark:text-white">Cookies</h4><p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Small files placed on Your device containing details of Your browsing history on that website.</p></div>
                            </div>
                        </div>

                        <!-- Full list of definitions link/detail accordion -->
                        <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-5 space-y-3.5">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2"><HelpCircle class="w-4 h-4 text-slate-400"/> Definisi Lainnya:</h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-xs text-slate-600 dark:text-slate-400 font-medium list-disc list-inside">
                                <li><strong>Website</strong>: PORTAL FMIKOM (<a href="https://portalfmikom.ac.id" target="_blank" class="hover:underline text-indigo-650 font-bold">https://portalfmikom.ac.id</a>).</li>
                                <li><strong>You</strong>: Individu yang mengakses atau menggunakan layanan.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 2. The use of the Cookies -->
                <section id="use-cookies" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center">
                            <Cookie class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">The use of the Cookies</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">Type of Cookies We Use</h3>
                            
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                Cookies can be "Persistent" or "Session" Cookies. Persistent Cookies remain on your personal computer or mobile device when You go offline, while Session Cookies are deleted as soon as You close your web browser.
                            </p>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                Where required by law, We will request your consent before using Cookies that are not strictly necessary. Strictly necessary Cookies are used to provide the Website and cannot be switched off in our systems.
                            </p>

                            <hr class="border-slate-100 dark:border-slate-800" />

                            <!-- Cookie Types -->
                            <div class="space-y-6">
                                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Necessary / Essential Cookies</h4>
                                        <span class="px-2 py-0.5 rounded-lg bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 text-[9px] font-black uppercase">Session Cookies</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        These Cookies are essential to provide You with services available through the Website and to enable You to use some of its features. They help to authenticate users and prevent fraudulent use of user accounts.
                                    </p>
                                </div>

                                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-sm font-black text-slate-800 dark:text-white">Functionality Cookies</h4>
                                        <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-400 text-[9px] font-black uppercase">Persistent Cookies</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        These Cookies allow Us to remember choices You make when You use the Website, such as remembering your login details or language preference. The purpose is to provide You with a more personal experience.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Your Choices Regarding Cookies -->
                <section id="choices" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center">
                            <Settings class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Your Choices Regarding Cookies</h2>
                    </div>

                    <div class="space-y-4 text-sm text-slate-500 dark:text-slate-450 leading-relaxed">
                        <p>
                            If You prefer to avoid the use of Cookies on the Website, first You must disable the use of Cookies in your browser and then delete the Cookies saved in your browser associated with the Website. You may use this option at any time.
                        </p>
                        <p class="text-slate-600 dark:text-slate-400 font-medium">
                            If You do not accept Our Cookies, You may experience some inconvenience in your use of the Website and some features may not function properly.
                        </p>
                        <p>
                            If You'd like to delete Cookies or instruct your web browser to delete or refuse Cookies, please visit the help pages of your web browser:
                        </p>

                        <!-- Browser Help Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <a 
                                v-for="browser in browsers" 
                                :key="browser.name" 
                                :href="browser.url" 
                                target="_blank" 
                                class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between hover:shadow-md transition-shadow group"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-450 flex items-center justify-center"><Chrome class="w-4 h-4" /></div>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ browser.name }}</span>
                                </div>
                                <ExternalLink class="w-4 h-4 text-slate-400 group-hover:text-slate-650 transition-colors" />
                            </a>
                        </div>
                    </div>
                </section>

                <!-- 4. Changes to this Cookie Policy -->
                <section id="changes" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center">
                            <ShieldCheck class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Changes to this Cookie Policy</h2>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            We may update this Cookie Policy from time to time. The "Last updated" date at the top indicates when it was last revised. We encourage you to review this policy periodically.
                        </p>
                    </div>
                </section>

                <!-- 5. Contact Us -->
                <section id="contact" class="scroll-mt-28 space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center">
                            <Mail class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white">Contact Us</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            If you have any questions about this Cookie Policy, You can contact us:
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <a href="mailto:fmikom@unugha.id" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-455 flex items-center justify-center group-hover:scale-105 transition-transform"><Mail class="w-5 h-5"/></div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 dark:text-white">By Email</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 hover:underline">fmikom@unugha.id</p>
                                </div>
                            </a>

                            <a href="https://portalfmikom.ac.id/contact" target="_blank" class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center gap-4 hover:shadow-md transition-shadow group">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-455 flex items-center justify-center group-hover:scale-105 transition-transform"><Globe class="w-5 h-5"/></div>
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
