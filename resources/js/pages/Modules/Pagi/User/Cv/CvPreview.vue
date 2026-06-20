<script setup lang="ts">
import {
	Github,
	Globe,
	Instagram,
	Linkedin,
	Mail,
	MapPin,
	Phone,
} from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps<{
	cv: any;
	zoom: number; // e.g. 0.8, 1.0
}>();

const personalInfo = computed(() => props.cv.personal_info || {});
const education = computed(() => props.cv.education || []);
const experience = computed(() => props.cv.experience || []);
const organizations = computed(() => props.cv.organizations || []);
const skills = computed(() => props.cv.skills || []);
const certifications = computed(() => props.cv.certifications || []);
const trainings = computed(() => props.cv.trainings || []);
const achievements = computed(() => props.cv.achievements || []);
const languages = computed(() => props.cv.languages || []);
const references = computed(() => props.cv.references || []);
const customization = computed(() => props.cv.customization || {});

const primaryColor = computed(
	() => customization.value.primary_color || "#1e3a8a",
);
const fontSizeClass = computed(() => {
	const size = customization.value.font_size || "11pt";
	if (size === "9pt") return "text-[11px] leading-relaxed";
	if (size === "10pt") return "text-[12px] leading-relaxed";
	if (size === "12pt") return "text-[15px] leading-relaxed";
	return "text-[13px] leading-relaxed"; // Default 11pt
});

const spacingClass = computed(() => {
	const sp = customization.value.spacing || "normal";
	if (sp === "compact") return "space-y-2.5";
	if (sp === "loose") return "space-y-6";
	return "space-y-4"; // normal
});

const entrySpacingClass = computed(() => {
	const sp = customization.value.spacing || "normal";
	if (sp === "compact") return "space-y-1";
	if (sp === "loose") return "space-y-4";
	return "space-y-2"; // normal
});

const sectionOrder = computed(() => {
	return (
		customization.value.section_order || [
			"summary",
			"experience",
			"education",
			"organizations",
			"skills",
			"certifications",
			"trainings",
			"achievements",
			"languages",
			"references",
		]
	);
});

const sectionsVisibility = computed(() => {
	return customization.value.sections_visibility || {};
});

const isVisible = (sectionKey: string) => {
	return sectionsVisibility.value[sectionKey] !== false;
};

const getTemplateName = () => {
	return props.cv.template_id || "ats-professional";
};

const getPhotoUrl = (path: string) => {
	if (!path) return "";
	if (path.startsWith("http") || path.startsWith("data:")) return path;
	if (path.startsWith("/storage")) return path;
	return `/storage/${path}`;
};

const KNOWN_TOOLS = new Set([
	"figma",
	"photoshop",
	"illustrator",
	"premiere",
	"visual-studio-code",
	"visual-studio",
	"vue",
	"react",
	"tailwind-css",
	"laravel",
	"php",
	"javascript",
	"html5",
	"css",
	"git",
	"github",
	"docker",
	"postman",
	"canva",
	"trello",
	"jira",
	"sass",
	"nodedotjs",
	"typescript",
	"python",
	"mysql",
	"postgresql",
	"mongodb",
	"firebase",
	"flutter",
	"kotlin",
	"swift",
	"adobe-xd",
	"adobe-indesign",
	"adobe-after-effects",
	"bootstrap",
	"wordpress",
	"jquery",
	"npm",
	"yarn",
	"vite",
	"webpack",
	"aws",
	"kubernetes",
	"redis",
	"google-cloud",
	"azure",
	"linux",
	"ubuntu",
	"android",
	"ios",
	"java",
	"c",
	"cpp",
	"csharp",
	"go",
]);

const getToolSlug = (toolName: string): string => {
	const name = toolName.toLowerCase().trim();
	if (name === "figma") return "figma";
	if (name === "photoshop" || name === "adobe photoshop" || name === "ps")
		return "photoshop";
	if (name === "illustrator" || name === "adobe illustrator" || name === "ai")
		return "illustrator";
	if (
		name === "premiere" ||
		name === "premiere pro" ||
		name === "pr" ||
		name === "premierepro"
	)
		return "premiere";
	if (
		name === "vs code" ||
		name === "vscode" ||
		name === "visual studio code" ||
		name === "visual-studio-code"
	)
		return "visual-studio-code";
	if (name === "visual studio" || name === "vs") return "visual-studio";
	if (
		name === "vue" ||
		name === "vue.js" ||
		name === "vuejs" ||
		name === "vuedotjs"
	)
		return "vue";
	if (name === "react" || name === "reactjs" || name === "react.js")
		return "react";
	if (
		name === "tailwind" ||
		name === "tailwindcss" ||
		name === "tailwind css" ||
		name === "tailwind-css"
	)
		return "tailwind-css";
	if (name === "laravel") return "laravel";
	if (name === "php") return "php";
	if (name === "javascript" || name === "js") return "javascript";
	if (name === "html" || name === "html5") return "html5";
	if (name === "css" || name === "css3") return "css";
	if (name === "git") return "git";
	if (name === "github") return "github";
	if (name === "docker") return "docker";
	if (name === "postman") return "postman";
	if (name === "canva") return "canva";
	if (name === "trello") return "trello";
	if (name === "jira") return "jira";
	if (name === "sass" || name === "scss") return "sass";
	if (name === "nodejs" || name === "node" || name === "node.js")
		return "nodedotjs";
	if (name === "typescript" || name === "ts") return "typescript";
	if (name === "python") return "python";
	if (name === "mysql") return "mysql";
	if (name === "postgresql" || name === "postgres") return "postgresql";
	if (name === "mongodb" || name === "mongo") return "mongodb";
	if (name === "firebase") return "firebase";
	if (name === "flutter") return "flutter";
	if (name === "kotlin") return "kotlin";
	if (name === "swift") return "swift";
	if (name === "xd" || name === "adobe xd") return "adobe-xd";
	if (name === "indesign" || name === "adobe indesign") return "adobe-indesign";
	if (
		name === "after effects" ||
		name === "ae" ||
		name === "adobe after effects"
	)
		return "adobe-after-effects";
	if (name === "bootstrap") return "bootstrap";
	if (name === "wordpress") return "wordpress";
	if (name === "jquery") return "jquery";
	if (name === "npm") return "npm";
	if (name === "yarn") return "yarn";
	if (name === "vite") return "vite";
	if (name === "webpack") return "webpack";
	if (name === "aws") return "aws";
	if (name === "kubernetes") return "kubernetes";
	if (name === "redis") return "redis";
	if (name === "google cloud" || name === "google-cloud" || name === "gcp")
		return "google-cloud";
	if (name === "azure") return "azure";
	if (name === "linux") return "linux";
	if (name === "ubuntu") return "ubuntu";
	if (name === "android") return "android";
	if (name === "ios") return "ios";
	if (name === "java") return "java";
	if (name === "c") return "c";
	if (name === "c++" || name === "cpp") return "cplusplus";
	if (name === "c#" || name === "csharp") return "csharp";
	if (name === "go" || name === "golang") return "go";

	return name
		.replace(/\.js/g, "dotjs")
		.replace(/\.net/g, "dotnet")
		.replace(/[^a-z0-9]+/g, "-");
};

const hasLogo = (toolName: string): boolean => {
	if (!toolName) return false;
	const slug = getToolSlug(toolName);
	return KNOWN_TOOLS.has(slug);
};

const getLogoUrl = (toolName: string): string => {
	const slug = getToolSlug(toolName);
	return `https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${slug}/default.svg`;
};
</script>

<template>
    <div 
        class="cv-preview-page shadow-2xl bg-white text-zinc-800 transition-all origin-top mx-auto select-none shrink-0"
        :style="{
            transform: `scale(${zoom})`,
            width: '210mm',
            minWidth: '210mm',
            minHeight: '297mm',
            padding: '20mm 20mm'
        }"
    >
        <div :class="[fontSizeClass, 'h-full flex flex-col justify-between']">
            <!-- 1. ATS PROFESSIONAL TEMPLATE -->
            <div v-if="getTemplateName() === 'ats-professional'" class="space-y-5">
                <!-- Header -->
                <div class="border-b-2 pb-3" :style="{ borderColor: primaryColor }">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-black tracking-tight" :style="{ color: primaryColor }">
                            {{ personalInfo.name || 'Nama Lengkap Anda' }}
                        </h1>
                        <p class="text-sm font-semibold text-zinc-500 mt-0.5">
                            {{ personalInfo.job_title || 'Gelar / Posisi Profesional' }}
                        </p>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-zinc-500 mt-2 font-medium">
                            <span v-if="personalInfo.email" class="flex items-center gap-1"><Mail class="w-3 h-3"/> {{ personalInfo.email }}</span>
                            <span v-if="personalInfo.phone" class="flex items-center gap-1"><Phone class="w-3 h-3"/> {{ personalInfo.phone }}</span>
                            <span v-if="personalInfo.location" class="flex items-center gap-1"><MapPin class="w-3 h-3"/> {{ personalInfo.location }}</span>
                            <span v-if="personalInfo.website" class="flex items-center gap-1"><Globe class="w-3 h-3"/> {{ personalInfo.website }}</span>
                            <span v-if="personalInfo.linkedin" class="flex items-center gap-1"><Linkedin class="w-3 h-3"/> {{ personalInfo.linkedin }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sections Grid/List dynamic ordering -->
                <div :class="spacingClass">
                    <template v-for="sec in sectionOrder" :key="sec">
                        <div v-if="isVisible(sec)">
                            
                            <!-- Summary -->
                            <div v-if="sec === 'summary' && personalInfo.summary" class="space-y-1">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Ringkasan Profesional</h2>
                                <p class="text-[11px] text-zinc-650 text-justify whitespace-pre-line">{{ personalInfo.summary }}</p>
                            </div>

                            <!-- Experience -->
                            <div v-if="sec === 'experience' && experience.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Pengalaman Kerja</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="exp in experience" :key="exp.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ exp.position || 'Jabatan' }} <span class="font-normal text-zinc-500">di {{ exp.company || 'Perusahaan' }}</span></span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ exp.start_date || 'Mulai' }} - {{ exp.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="exp.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ exp.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Education -->
                            <div v-if="sec === 'education' && education.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Pendidikan</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="edu in education" :key="edu.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ edu.degree || 'Gelar' }} {{ edu.field ? ' - ' + edu.field : '' }}</span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ edu.start_date || 'Mulai' }} - {{ edu.end_date || 'Selesai' }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-600 font-semibold italic">{{ edu.school || 'Sekolah/Universitas' }}</div>
                                        <p v-if="edu.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ edu.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Organizations -->
                            <div v-if="sec === 'organizations' && organizations.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Organisasi</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="org in organizations" :key="org.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ org.role || 'Peran' }} <span class="font-normal text-zinc-500">di {{ org.name || 'Nama Organisasi' }}</span></span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ org.start_date || 'Mulai' }} - {{ org.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="org.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ org.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div v-if="sec === 'skills' && skills.length > 0" class="space-y-1.5">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Keahlian</h2>
                                
                                <!-- Customizable Grid Layout (Checklist, Logo, or Percentage active) -->
                                <div v-if="customization.skills_show_checkbox || customization.skills_show_logo || customization.skills_show_percentage" class="grid grid-cols-2 gap-x-6 gap-y-3 pt-1">
                                    <div v-for="sk in skills" :key="sk.id" class="space-y-1 text-[11px]">
                                        <div class="flex items-center gap-1.5 font-bold text-zinc-750">
                                            <!-- Checkmark -->
                                            <span v-if="customization.skills_show_checkbox" class="font-black shrink-0" :style="{ color: primaryColor }">✓</span>
                                            
                                            <!-- App Logo -->
                                            <img v-if="customization.skills_show_logo && hasLogo(sk.name)" :src="getLogoUrl(sk.name)" :alt="sk.name" class="w-4 h-4 shrink-0 object-contain" />
                                            
                                            <!-- Skill Name -->
                                            <span>{{ sk.name }}</span>
                                            
                                            <!-- Percentage info next to name if bar is shown -->
                                            <span v-if="customization.skills_show_percentage" class="text-zinc-400 font-normal ml-auto text-[10px]">{{ sk.level || 50 }}%</span>
                                        </div>
                                        
                                        <!-- Percentage Bar -->
                                        <div v-if="customization.skills_show_percentage" class="h-1.5 bg-zinc-150 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all" :style="{ width: `${sk.level || 50}%`, backgroundColor: primaryColor }"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default Tags / Badge Layout (with option to show Logo) -->
                                <div v-else class="flex flex-wrap gap-2 text-[10px] pt-1">
                                    <span v-for="sk in skills" :key="sk.id" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-zinc-100 text-zinc-700 font-bold border border-zinc-200/50">
                                        <img v-if="customization.skills_show_logo && hasLogo(sk.name)" :src="getLogoUrl(sk.name)" :alt="sk.name" class="w-3.5 h-3.5 shrink-0 object-contain" />
                                        <span>{{ sk.name }}</span>
                                        <span class="text-zinc-400 font-normal">({{ sk.level || 50 }}%)</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Certifications -->
                            <div v-if="sec === 'certifications' && certifications.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Sertifikasi</h2>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div v-for="cert in certifications" :key="cert.id" class="border border-zinc-100 p-2 rounded-lg bg-zinc-50/50">
                                        <div class="font-bold text-zinc-800">{{ cert.name || 'Nama Sertifikasi' }}</div>
                                        <div class="text-[10px] text-zinc-500 font-semibold">{{ cert.issuer || 'Penerbit' }} ({{ cert.date || 'Tanggal' }})</div>
                                        <div v-if="cert.credential_id" class="text-[9px] text-zinc-400 font-mono mt-0.5">ID: {{ cert.credential_id }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trainings -->
                            <div v-if="sec === 'trainings' && trainings.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Pelatihan</h2>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div v-for="trn in trainings" :key="trn.id" class="border border-zinc-100 p-2 rounded-lg bg-zinc-50/50">
                                        <div class="font-bold text-zinc-800">{{ trn.name || 'Nama Pelatihan' }}</div>
                                        <div class="text-[10px] text-zinc-500 font-semibold">{{ trn.provider || 'Penyelenggara' }} - {{ trn.date || 'Tanggal' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Achievements -->
                            <div v-if="sec === 'achievements' && achievements.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Prestasi</h2>
                                <div class="space-y-2 text-[11px]">
                                    <div v-for="ach in achievements" :key="ach.id">
                                        <div class="flex justify-between font-bold text-zinc-800">
                                            <span>{{ ach.title || 'Judul Penghargaan' }}</span>
                                            <span class="text-zinc-500 font-medium shrink-0">{{ ach.date || 'Tanggal' }}</span>
                                        </div>
                                        <p v-if="ach.description" class="text-[10px] text-zinc-500 pl-2">{{ ach.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Languages -->
                            <div v-if="sec === 'languages' && languages.length > 0" class="space-y-1.5">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Bahasa</h2>
                                <div class="flex flex-wrap gap-4 text-[11px] pt-1">
                                    <div v-for="lang in languages" :key="lang.id" class="flex items-center gap-1.5 font-semibold">
                                        <span class="text-zinc-800">{{ lang.name }}</span>
                                        <span class="text-zinc-400 font-normal">({{ lang.proficiency || 'Tingkat' }})</span>
                                    </div>
                                </div>
                            </div>

                            <!-- References -->
                            <div v-if="sec === 'references' && references.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-extrabold uppercase tracking-wider border-b border-zinc-200 pb-0.5" :style="{ color: primaryColor }">Referensi</h2>
                                <div class="grid grid-cols-2 gap-4 text-[11px]">
                                    <div v-for="ref in references" :key="ref.id" class="space-y-0.5">
                                        <div class="font-bold text-zinc-800">{{ ref.name || 'Nama Referensi' }}</div>
                                        <div class="text-zinc-500 font-semibold text-[10px]">{{ ref.position || 'Posisi' }} - {{ ref.company || 'Perusahaan' }}</div>
                                        <div class="text-zinc-400 text-[10px] font-mono">{{ ref.contact || 'Kontak' }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </div>

            <!-- 2. MODERN SIDEBAR TEMPLATE -->
            <div v-else-if="getTemplateName() === 'modern-sidebar'" class="flex gap-6 h-full">
                <!-- Left Sidebar Col -->
                <div class="w-1/3 border-r border-zinc-100 pr-5 space-y-6 text-zinc-700 bg-zinc-50/50 p-4 rounded-2xl">
                    <div class="text-center space-y-2">
                        <div v-if="personalInfo.foto_path" class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-3 border border-zinc-200">
                            <img :src="getPhotoUrl(personalInfo.foto_path)" alt="Foto Profil" class="w-full h-full object-cover" />
                        </div>
                        <h1 class="text-xl font-black tracking-tight text-zinc-800 leading-none">
                            {{ personalInfo.name || 'Nama Lengkap' }}
                        </h1>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider" :style="{ color: primaryColor }">
                            {{ personalInfo.job_title || 'Pekerjaan/Gelar' }}
                        </p>
                    </div>

                    <!-- Contact Details -->
                    <div class="space-y-3">
                        <h3 class="text-[10px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Kontak</h3>
                        <div class="space-y-2 text-[10px] font-medium text-zinc-600">
                            <div v-if="personalInfo.email" class="flex gap-1.5"><Mail class="w-3.5 h-3.5 text-zinc-400 shrink-0"/> <span class="break-all">{{ personalInfo.email }}</span></div>
                            <div v-if="personalInfo.phone" class="flex gap-1.5"><Phone class="w-3.5 h-3.5 text-zinc-400 shrink-0"/> <span>{{ personalInfo.phone }}</span></div>
                            <div v-if="personalInfo.location" class="flex gap-1.5"><MapPin class="w-3.5 h-3.5 text-zinc-400 shrink-0"/> <span>{{ personalInfo.location }}</span></div>
                            <div v-if="personalInfo.website" class="flex gap-1.5"><Globe class="w-3.5 h-3.5 text-zinc-400 shrink-0"/> <span class="break-all">{{ personalInfo.website }}</span></div>
                            <div v-if="personalInfo.linkedin" class="flex gap-1.5"><Linkedin class="w-3.5 h-3.5 text-zinc-400 shrink-0"/> <span class="break-all">{{ personalInfo.linkedin }}</span></div>
                        </div>
                    </div>

                    <!-- Skills in Sidebar -->
                    <div v-if="isVisible('skills') && skills.length > 0" class="space-y-3">
                        <h3 class="text-[10px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Keahlian</h3>
                        <div class="space-y-2 text-[10px]">
                            <div v-for="sk in skills" :key="sk.id" class="space-y-1">
                                <div class="flex justify-between font-bold">
                                    <span>{{ sk.name }}</span>
                                    <span class="text-zinc-400 font-normal">{{ sk.level || 50 }}%</span>
                                </div>
                                <div class="h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all" :style="{ width: `${sk.level || 50}%`, backgroundColor: primaryColor }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Languages in Sidebar -->
                    <div v-if="isVisible('languages') && languages.length > 0" class="space-y-3">
                        <h3 class="text-[10px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Bahasa</h3>
                        <div class="space-y-2 text-[10px] text-zinc-650">
                            <div v-for="lang in languages" :key="lang.id" class="space-y-0.5">
                                <div class="font-bold text-zinc-800">{{ lang.name }}</div>
                                <div class="text-[9px] text-zinc-400 font-semibold">{{ lang.proficiency || 'Tingkat' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Main Content Col -->
                <div class="flex-1 space-y-6">
                    <template v-for="sec in sectionOrder" :key="sec">
                        <div v-if="isVisible(sec)">
                            
                            <!-- Summary -->
                            <div v-if="sec === 'summary' && personalInfo.summary" class="space-y-1.5">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Tentang Saya</h2>
                                <p class="text-[11px] text-zinc-650 text-justify whitespace-pre-line">{{ personalInfo.summary }}</p>
                            </div>

                            <!-- Experience -->
                            <div v-if="sec === 'experience' && experience.length > 0" class="space-y-3">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Pengalaman Kerja</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="exp in experience" :key="exp.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ exp.position || 'Jabatan' }} <span class="font-normal text-zinc-500">di {{ exp.company || 'Perusahaan' }}</span></span>
                                            <span class="text-[10px] text-zinc-400 shrink-0 font-medium">{{ exp.start_date || 'Mulai' }} - {{ exp.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="exp.description" class="text-[10px] text-zinc-500 whitespace-pre-line">{{ exp.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Education -->
                            <div v-if="sec === 'education' && education.length > 0" class="space-y-3">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Pendidikan</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="edu in education" :key="edu.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ edu.degree || 'Gelar' }} {{ edu.field ? ' - ' + edu.field : '' }}</span>
                                            <span class="text-[10px] text-zinc-400 shrink-0 font-medium">{{ edu.start_date || 'Mulai' }} - {{ edu.end_date || 'Selesai' }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-650 font-bold italic">{{ edu.school || 'Sekolah/Universitas' }}</div>
                                        <p v-if="edu.description" class="text-[10px] text-zinc-500 whitespace-pre-line">{{ edu.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Organizations -->
                            <div v-if="sec === 'organizations' && organizations.length > 0" class="space-y-3">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Organisasi</h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="org in organizations" :key="org.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ org.role || 'Peran' }} <span class="font-normal text-zinc-500">di {{ org.name || 'Organisasi' }}</span></span>
                                            <span class="text-[10px] text-zinc-400 shrink-0 font-medium">{{ org.start_date || 'Mulai' }} - {{ org.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="org.description" class="text-[10px] text-zinc-500 whitespace-pre-line">{{ org.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Certifications -->
                            <div v-if="sec === 'certifications' && certifications.length > 0" class="space-y-2.5">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Sertifikasi</h2>
                                <div class="grid grid-cols-2 gap-3 text-[10px]">
                                    <div v-for="cert in certifications" :key="cert.id" class="border border-zinc-150/70 p-2 rounded-xl">
                                        <div class="font-bold text-zinc-800 leading-tight">{{ cert.name }}</div>
                                        <div class="text-zinc-400 mt-0.5">{{ cert.issuer }} - {{ cert.date }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trainings -->
                            <div v-if="sec === 'trainings' && trainings.length > 0" class="space-y-2.5">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Pelatihan</h2>
                                <div class="grid grid-cols-2 gap-3 text-[10px]">
                                    <div v-for="trn in trainings" :key="trn.id" class="border border-zinc-150/70 p-2 rounded-xl">
                                        <div class="font-bold text-zinc-800 leading-tight">{{ trn.name }}</div>
                                        <div class="text-zinc-400 mt-0.5">{{ trn.provider }} - {{ trn.date }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Achievements -->
                            <div v-if="sec === 'achievements' && achievements.length > 0" class="space-y-2">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Prestasi</h2>
                                <div class="space-y-1 text-[11px]">
                                    <div v-for="ach in achievements" :key="ach.id" class="flex justify-between items-center">
                                        <span class="font-bold text-zinc-850">{{ ach.title }}</span>
                                        <span class="text-[10px] text-zinc-400 shrink-0 font-medium">{{ ach.date }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- References -->
                            <div v-if="sec === 'references' && references.length > 0" class="space-y-2">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-wider border-b pb-1" :style="{ borderColor: primaryColor, color: primaryColor }">Referensi</h2>
                                <div class="grid grid-cols-2 gap-3 text-[10px]">
                                    <div v-for="ref in references" :key="ref.id" class="space-y-0.5">
                                        <div class="font-bold text-zinc-800">{{ ref.name }}</div>
                                        <div class="text-zinc-400">{{ ref.position }} di {{ ref.company }}</div>
                                        <div class="text-zinc-400 text-[9px] font-mono">{{ ref.contact }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. EXECUTIVE / CREATIVE / STUDENT TEMPLATES -->
            <div v-else :class="[getTemplateName() === 'executive' ? 'executive-style' : (getTemplateName() === 'creative-minimal' ? 'creative-style' : 'student-style'), 'space-y-5']">
                <!-- Header centered/left depending on template -->
                <div :class="[getTemplateName() === 'executive' ? 'text-center border-b pb-4' : 'text-left border-l-4 pl-4 py-1 border-emerald-500 bg-zinc-50/50 p-4 rounded-r-2xl', 'space-y-2 flex items-center gap-4']">
                    <div v-if="personalInfo.foto_path && getTemplateName() === 'student-resume'" class="w-20 h-20 rounded-xl overflow-hidden shrink-0 border border-zinc-200">
                        <img :src="getPhotoUrl(personalInfo.foto_path)" alt="Foto Profil" class="w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 space-y-2">
                        <h1 :class="[getTemplateName() === 'executive' ? 'text-4xl font-serif text-zinc-900' : 'text-3xl font-extrabold text-zinc-800', 'leading-none']">
                            {{ personalInfo.name || 'Nama Lengkap Anda' }}
                        </h1>
                        <p class="text-xs font-semibold text-zinc-500">
                            {{ personalInfo.job_title || 'Gelar / Bidang' }}
                        </p>
                        <div :class="[getTemplateName() === 'executive' ? 'justify-center border-t border-b py-1.5 my-2' : 'justify-start', 'flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-zinc-500 mt-2 font-medium']">
                            <span v-if="personalInfo.email" class="flex items-center gap-1"><Mail class="w-3 h-3"/> {{ personalInfo.email }}</span>
                            <span v-if="personalInfo.phone" class="flex items-center gap-1"><Phone class="w-3 h-3"/> {{ personalInfo.phone }}</span>
                            <span v-if="personalInfo.location" class="flex items-center gap-1"><MapPin class="w-3 h-3"/> {{ personalInfo.location }}</span>
                            <span v-if="personalInfo.website" class="flex items-center gap-1"><Globe class="w-3 h-3"/> {{ personalInfo.website }}</span>
                            <span v-if="personalInfo.linkedin" class="flex items-center gap-1"><Linkedin class="w-3 h-3"/> {{ personalInfo.linkedin }}</span>
                        </div>
                    </div>
                </div>

                <!-- Content Grid ordered dynamically -->
                <div :class="spacingClass">
                    <template v-for="sec in sectionOrder" :key="sec">
                        <div v-if="isVisible(sec)">
                            
                            <!-- Summary -->
                            <div v-if="sec === 'summary' && personalInfo.summary" class="space-y-1">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Tentang Saya</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <p class="text-[11px] text-zinc-650 text-justify whitespace-pre-line">{{ personalInfo.summary }}</p>
                            </div>

                            <!-- Experience -->
                            <div v-if="sec === 'experience' && experience.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Pengalaman Kerja</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="exp in experience" :key="exp.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ exp.position || 'Jabatan' }} <span class="font-normal text-zinc-500">di {{ exp.company || 'Perusahaan' }}</span></span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ exp.start_date || 'Mulai' }} - {{ exp.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="exp.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ exp.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Education -->
                            <div v-if="sec === 'education' && education.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Pendidikan</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="edu in education" :key="edu.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ edu.degree || 'Gelar' }} {{ edu.field ? ' - ' + edu.field : '' }}</span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ edu.start_date || 'Mulai' }} - {{ edu.end_date || 'Selesai' }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-600 font-semibold italic">{{ edu.school || 'Sekolah/Universitas' }}</div>
                                        <p v-if="edu.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ edu.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Organizations -->
                            <div v-if="sec === 'organizations' && organizations.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Organisasi & Kegiatan</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div :class="entrySpacingClass">
                                    <div v-for="org in organizations" :key="org.id" class="space-y-1">
                                        <div class="flex justify-between items-start text-[11px]">
                                            <span class="font-bold text-zinc-800">{{ org.role || 'Peran' }} <span class="font-normal text-zinc-500">di {{ org.name || 'Organisasi' }}</span></span>
                                            <span class="text-zinc-500 shrink-0 font-medium">{{ org.start_date || 'Mulai' }} - {{ org.end_date || 'Selesai' }}</span>
                                        </div>
                                        <p v-if="org.description" class="text-[11px] text-zinc-650 pl-2 border-l border-zinc-200/80 whitespace-pre-line">{{ org.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div v-if="sec === 'skills' && skills.length > 0" class="space-y-1.5">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Keahlian</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                
                                <!-- Customizable Grid Layout (Checklist, Logo, or Percentage active) -->
                                <div v-if="customization.skills_show_checkbox || customization.skills_show_logo || customization.skills_show_percentage" class="grid grid-cols-2 gap-x-6 gap-y-3 pt-1">
                                    <div v-for="sk in skills" :key="sk.id" class="space-y-1 text-[11px]">
                                        <div class="flex items-center gap-1.5 font-bold text-zinc-750">
                                            <!-- Checkmark -->
                                            <span v-if="customization.skills_show_checkbox" class="font-black shrink-0" :style="{ color: primaryColor }">✓</span>
                                            
                                            <!-- App Logo -->
                                            <img v-if="customization.skills_show_logo && hasLogo(sk.name)" :src="getLogoUrl(sk.name)" :alt="sk.name" class="w-4 h-4 shrink-0 object-contain" />
                                            
                                            <!-- Skill Name -->
                                            <span>{{ sk.name }}</span>
                                            
                                            <!-- Percentage info next to name if bar is shown -->
                                            <span v-if="customization.skills_show_percentage" class="text-zinc-400 font-normal ml-auto text-[10px]">{{ sk.level || 50 }}%</span>
                                        </div>
                                        
                                        <!-- Percentage Bar -->
                                        <div v-if="customization.skills_show_percentage" class="h-1.5 bg-zinc-150 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all" :style="{ width: `${sk.level || 50}%`, backgroundColor: primaryColor }"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default Tags / Badge Layout (with option to show Logo) -->
                                <div v-else class="flex flex-wrap gap-2 text-[10px] pt-1">
                                    <span v-for="sk in skills" :key="sk.id" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-50 border border-zinc-200 text-zinc-700 font-bold">
                                        <img v-if="customization.skills_show_logo && hasLogo(sk.name)" :src="getLogoUrl(sk.name)" :alt="sk.name" class="w-3.5 h-3.5 shrink-0 object-contain" />
                                        <span>{{ sk.name }}</span>
                                        <span class="text-zinc-400 font-normal">({{ sk.level || 50 }}%)</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Certifications -->
                            <div v-if="sec === 'certifications' && certifications.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Sertifikasi</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div v-for="cert in certifications" :key="cert.id" class="border border-zinc-100 p-2 rounded bg-zinc-50/20">
                                        <div class="font-bold text-zinc-800">{{ cert.name }}</div>
                                        <div class="text-[10px] text-zinc-400 font-semibold">{{ cert.issuer }} - {{ cert.date }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trainings -->
                            <div v-if="sec === 'trainings' && trainings.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Pelatihan</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div class="grid grid-cols-2 gap-3 text-[11px]">
                                    <div v-for="trn in trainings" :key="trn.id" class="border border-zinc-100 p-2 rounded bg-zinc-50/20">
                                        <div class="font-bold text-zinc-800">{{ trn.name }}</div>
                                        <div class="text-[10px] text-zinc-400 font-semibold">{{ trn.provider }} - {{ trn.date }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Achievements -->
                            <div v-if="sec === 'achievements' && achievements.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Prestasi</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div class="space-y-1 text-[11px]">
                                    <div v-for="ach in achievements" :key="ach.id" class="flex justify-between font-bold">
                                        <span>{{ ach.title }}</span>
                                        <span class="text-zinc-500 font-medium shrink-0">{{ ach.date }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- References -->
                            <div v-if="sec === 'references' && references.length > 0" class="space-y-2">
                                <h2 class="text-[12px] font-bold border-b pb-0.5 flex flex-col items-start gap-1" :style="{ color: primaryColor, borderColor: primaryColor }">
                                    <span>Referensi</span>
                                    <span v-if="getTemplateName() === 'creative-minimal'" class="w-6 h-0.5 bg-current"></span>
                                </h2>
                                <div class="grid grid-cols-2 gap-4 text-[11px]">
                                    <div v-for="ref in references" :key="ref.id" class="space-y-0.5">
                                        <div class="font-bold text-zinc-800">{{ ref.name }}</div>
                                        <div class="text-zinc-400 font-semibold text-[10px]">{{ ref.position }} - {{ ref.company }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure CV preview exactly mirrors print dimensions visually */
.cv-preview-page {
    position: relative;
    box-sizing: border-box;
}

/* Custom Serif layout override */
.executive-style h1, .executive-style h2 {
    font-family: Georgia, 'Times New Roman', Times, serif;
}
.creative-style h1, .creative-style h2 {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}
</style>
