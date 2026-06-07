<script setup lang="ts">
import {
	Award,
	Briefcase,
	Calendar,
	CheckCircle,
	ChevronLeft,
	ChevronRight,
	Cpu,
	Download,
	ExternalLink,
	FileText,
	Github,
	GraduationCap,
	Mail,
	MapPin,
	Sparkles,
	Terminal,
} from "lucide-vue-next";
import { computed, ref } from "vue";

const props = withDefaults(
	defineProps<{
		profileUser: any;
		projects: any[];
		activePalette: {
			bg: string;
			text: string;
			accent: string; // Can be a color or gradient
			cardBg?: string;
			border?: string;
			badge?: string;
		};
		customTitle?: string;
		customBio?: string;
		isMockup?: boolean;
		isInteractive?: boolean;
	}>(),
	{
		customTitle: "",
		customBio: "",
		isMockup: false,
		isInteractive: false,
	},
);

const emit = defineEmits<(e: "openProject", project: any) => void>();

const titleText = computed(() => props.customTitle || props.profileUser.name);
const bioText = computed(
	() =>
		props.customBio ||
		props.profileUser.bio ||
		`${props.profileUser.name} is a creative developer and researcher crafting award-winning SaaS interfaces and intelligent systems.`,
);

const userAvatar = computed(() => {
	if (props.profileUser.foto_path) {
		return props.profileUser.foto_path.startsWith("http")
			? props.profileUser.foto_path
			: `/storage/${props.profileUser.foto_path}`;
	}
	return (
		"https://api.dicebear.com/7.x/initials/svg?seed=" +
		encodeURIComponent(props.profileUser.name) +
		"&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true"
	);
});

const userInitials = computed(() => {
	const name = titleText.value;
	return name
		.split(" ")
		.map((n: string) => n[0])
		.join("")
		.substring(0, 2)
		.toUpperCase();
});

const handleProjectClick = (proj: any) => {
	if (props.isInteractive) {
		emit("openProject", proj);
	}
};

// Skill Categorization
const skillCategories = computed(() => {
	const skills = props.profileUser.skills || [
		"Figma",
		"UI/UX Design",
		"Vue.js",
		"TailwindCSS",
		"Laravel",
		"TypeScript",
	];
	return [
		{
			title: "Design & UX",
			skills: skills.filter((s: string) =>
				/design|figma|ui|ux|adobe|prototype/i.test(s),
			),
		},
		{
			title: "Development & Code",
			skills: skills.filter((s: string) =>
				/vue|laravel|js|typescript|react|tailwind|css|php|git|html/i.test(s),
			),
		},
		{
			title: "Other Competencies",
			skills: skills.filter(
				(s: string) =>
					!/design|figma|ui|ux|adobe|prototype|vue|laravel|js|typescript|react|tailwind|css|php|git|html/i.test(
						s,
					),
			),
		},
	].filter((cat) => cat.skills.length > 0);
});

// Testimonials Carousel
const currentTestimonial = ref(0);
const testimonials = [
	{
		name: "Dr. Rahmat Hidayat",
		role: "Dean of Computer Science, Universitas FMIKOM",
		quote:
			"An exceptionally talented student with a rare blend of engineering rigor and design polish. Their projects consistently push boundaries.",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=rahmat",
	},
	{
		name: "Sarah Amanda",
		role: "Lead UI/UX Designer, TechCorp Indonesia",
		quote:
			"Working with this student during their internship was an absolute pleasure. Their Figma organization is pristine and they code exactly what they design.",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=sarah",
	},
	{
		name: "Budi Santoso",
		role: "Tech Lead, Hackathon Partner",
		quote:
			"Super reliable developer. Handled the entire frontend and component styling under high pressure, helping us win 1st place in the university hackathon.",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=budi",
	},
];

const nextTestimonial = () => {
	currentTestimonial.value =
		(currentTestimonial.value + 1) % testimonials.length;
};

const prevTestimonial = () => {
	currentTestimonial.value =
		(currentTestimonial.value - 1 + testimonials.length) % testimonials.length;
};

// Timeline elements (mixed actual & mock data for high-fidelity presentation)
const timelineItems = [
	{
		type: "work",
		title: "Frontend Developer Intern",
		institution: "TechCorp Indonesia",
		period: "Jul 2025 - Dec 2025",
		desc: "Developed high-performance dashboard interfaces using Vue 3 and built robust client features under agile sprints.",
	},
	{
		type: "leadership",
		title: "Chief of Technology & Innovation",
		institution: "FMIKOM Student Association (HIMA)",
		period: "Mar 2024 - Present",
		desc: "Led a team of 8 student developers to design and launch the internal portal web applications, enhancing student campus integration.",
	},
	{
		type: "academic",
		title: "Head Developer (Academic Smart Campus Project)",
		institution: "Universitas FMIKOM Lab",
		period: "Sep 2024 - Jan 2025",
		desc: "Researched and built a full-stack Laravel API integrating calendar schedules and realtime chat modules for academic testing teams.",
	},
];
</script>

<template>
	<div 
		class="min-h-screen theme-section text-left font-sans relative overflow-x-hidden selection:bg-indigo-500 selection:text-white"
		:style="{ backgroundColor: props.activePalette.bg, color: props.activePalette.text }"
	>
		<!-- Background Glows -->
		<div class="absolute top-[-200px] left-[-200px] w-[600px] h-[600px] rounded-full filter blur-[150px] opacity-25 pointer-events-none" :style="{ background: props.activePalette.accent }"></div>
		<div class="absolute top-[400px] right-[-200px] w-[500px] h-[500px] rounded-full filter blur-[130px] opacity-20 pointer-events-none" style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%)"></div>
		<div class="absolute bottom-[200px] left-[10%] w-[400px] h-[400px] rounded-full filter blur-[150px] opacity-15 pointer-events-none" :style="{ background: props.activePalette.accent }"></div>

		<!-- 1. HERO SECTION -->
		<section class="relative max-w-6xl mx-auto px-6 pt-24 pb-20 md:pt-32 md:pb-28 z-10 flex flex-col md:flex-row items-center gap-12">
			<!-- Hero Left Info -->
			<div class="flex-1 space-y-6">
				<!-- Tagline Badge -->
				<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-white/5 border border-white/10 backdrop-blur-md">
					<Sparkles class="w-3.5 h-3.5" :style="{ color: props.activePalette.accent }" />
					<span>Available for freelance & contract</span>
				</div>
				
				<h1 class="text-4xl sm:text-7xl font-extrabold tracking-tight leading-[1.05] text-white">
					Hi, I'm <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-indigo-400 bg-clip-text text-transparent">{{ titleText }}</span>
				</h1>
				
				<p class="text-xs sm:text-sm uppercase tracking-widest font-black" :style="{ color: props.activePalette.accent }">
					{{ props.profileUser.pagi_role || props.profileUser.role_title || 'Software Engineer / UX Designer' }}
				</p>
				
				<p class="text-base sm:text-lg opacity-80 max-w-xl font-light leading-relaxed">
					{{ bioText }}
				</p>

				<div class="flex items-center gap-2 text-xs opacity-75">
					<MapPin class="w-4 h-4" :style="{ color: props.activePalette.accent }" />
					<span>{{ props.profileUser.location || 'Banyumas, Indonesia' }}</span>
				</div>

				<!-- CTA Actions -->
				<div class="flex flex-wrap gap-4 pt-4 no-print">
					<a 
						href="#projects" 
						class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs uppercase px-6 py-3.5 rounded-xl shadow-lg shadow-cyan-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer border-none"
					>
						Explore Projects
					</a>
					<a 
						href="#contact" 
						class="inline-flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold text-xs uppercase px-6 py-3.5 rounded-xl border border-white/10 backdrop-blur-sm transform hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer"
					>
						<Mail class="w-4 h-4" /> Get in Touch
					</a>
				</div>
			</div>

			<!-- Hero Right Image + Floating UI Elements -->
			<div class="w-72 sm:w-96 flex justify-center relative select-none">
				<!-- Main Frame -->
				<div class="relative w-64 h-64 sm:w-80 sm:h-80 rounded-3xl overflow-hidden border-2 border-white/10 shadow-2xl z-10 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center print-avatar">
					<img v-if="props.profileUser.foto_path || props.profileUser.avatar" :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
					<span v-else class="text-6xl font-black text-white bg-slate-800 w-full h-full flex items-center justify-center">{{ userInitials }}</span>
				</div>

				<!-- Floating Element 1 (Framer style info card) -->
				<div 
					class="absolute -top-6 -right-6 p-4 rounded-2xl border backdrop-blur-md shadow-lg z-20 hidden sm:block animate-bounce"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.1)', animationDuration: '6s' }"
				>
					<div class="flex items-center gap-3">
						<div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
						<div class="text-left">
							<span class="block text-[8px] font-black uppercase text-slate-400">Current Status</span>
							<span class="text-[10px] font-bold text-white leading-none">Coding & Designing</span>
						</div>
					</div>
				</div>

				<!-- Floating Element 2 (Linear style stats badge) -->
				<div 
					class="absolute -bottom-6 -left-6 p-4 rounded-2xl border backdrop-blur-md shadow-lg z-20 hidden sm:block animate-bounce"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.1)', animationDuration: '8s' }"
				>
					<div class="text-left">
						<span class="block text-[8px] font-black uppercase text-slate-400">Total Projects</span>
						<span class="text-lg font-black text-white">{{ isMockup ? '12+' : Math.max(props.projects.length, 3) }}+ Showcases</span>
					</div>
				</div>
			</div>
		</section>

		<!-- 2. ABOUT ME SECTION -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="grid grid-cols-1 md:grid-cols-12 gap-12">
				<!-- Left Profile & Stats -->
				<div class="md:col-span-5 space-y-8">
					<div>
						<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">About Me</h2>
						<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
					</div>
					
					<p class="text-sm sm:text-base opacity-80 leading-relaxed font-light">
						I'm currently pursuing my academic journey in computer science and technology. My core strength lies in translating complex functional requirements into beautiful, clean software applications. I focus on creating design systems, interactive components, and writing clean, scalable codebase.
					</p>

					<!-- Animated Stats Grid -->
					<div class="grid grid-cols-2 gap-4">
						<div 
							class="p-4 rounded-2xl border text-center"
							:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
						>
							<span class="block text-2xl font-black text-white">{{ isMockup ? '24' : props.profileUser.skills.length }}+</span>
							<span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Skills Mastered</span>
						</div>
						<div 
							class="p-4 rounded-2xl border text-center"
							:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
						>
							<span class="block text-2xl font-black text-white">{{ isMockup ? '10' : props.projects.length }}+</span>
							<span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Creative Works</span>
						</div>
					</div>
				</div>

				<!-- Right Education Timeline -->
				<div class="md:col-span-7 space-y-6">
					<h3 class="text-lg font-bold text-white flex items-center gap-2">
						<GraduationCap class="w-5 h-5" :style="{ color: props.activePalette.accent }" />
						Academic Milestones
					</h3>
					
					<div class="relative border-l border-white/10 pl-6 ml-2.5 space-y-8 text-left">
						<!-- Education Item 1 -->
						<div class="relative">
							<!-- Dot Indicator -->
							<div class="absolute -left-[31px] top-1.5 w-3 h-3 rounded-full border-2 border-slate-900 bg-cyan-400"></div>
							<div>
								<span class="text-[10px] uppercase font-bold text-slate-400">2023 - Present</span>
								<h4 class="text-sm font-bold text-white">Bachelor of Computer Science</h4>
								<p class="text-xs opacity-75 font-semibold">Universitas FMIKOM</p>
								<p class="text-[11px] opacity-65 mt-1">Focusing on Full-Stack Software Engineering, Interaction Design, and Database Management. Maintained a GPA of 3.85/4.0.</p>
							</div>
						</div>

						<!-- Education Item 2 -->
						<div class="relative">
							<!-- Dot Indicator -->
							<div class="absolute -left-[31px] top-1.5 w-3 h-3 rounded-full border-2 border-slate-900 bg-violet-400"></div>
							<div>
								<span class="text-[10px] uppercase font-bold text-slate-400">2020 - 2023</span>
								<h4 class="text-sm font-bold text-white">Software Engineering major</h4>
								<p class="text-xs opacity-75 font-semibold">Vocational High School</p>
								<p class="text-[11px] opacity-65 mt-1">Studied foundations of Web Design, Algorithms, and Object-Oriented Programming.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- 3. FEATURED PROJECTS SECTION -->
		<section id="projects" class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="flex justify-between items-end mb-12">
				<div>
					<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Selected Works</h2>
					<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
				</div>
				<span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 hidden sm:block">Framer Inspired Gallery</span>
			</div>

			<!-- Mockup Mode -->
			<div v-if="isMockup" class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-4">
				<div 
					v-for="i in 4" 
					:key="i"
					class="group rounded-3xl overflow-hidden border flex flex-col justify-between"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
				>
					<div class="aspect-video w-full bg-slate-800/40 relative">
						<div class="absolute inset-0 flex items-center justify-center text-[10px] font-mono opacity-25">PREVIEW PROJECT MOCKUP</div>
					</div>
					<div class="p-6 text-left">
						<div class="flex justify-between items-start">
							<h4 class="text-base font-bold text-white">Project Showcase Title {{ i }}</h4>
							<span class="text-[9px] px-2 py-0.5 rounded-md font-extrabold uppercase bg-white/5 text-slate-300">Laravel / Vue</span>
						</div>
						<p class="text-xs opacity-75 mt-2">High-fidelity engineering work project documenting system integration and interface layout design.</p>
					</div>
				</div>
			</div>

			<!-- Live Mode (Masonry Style / High-end SaaS layout) -->
			<div v-else>
				<div v-if="props.projects.length === 0" class="text-center py-16 opacity-75 italic text-xs">
					Belum ada karya yang diunggah.
				</div>

				<div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8 print-grid">
					<div 
						v-for="proj in props.projects" 
						:key="proj.id"
						@click="handleProjectClick(proj)"
						class="group rounded-3xl overflow-hidden cursor-pointer shadow-md hover:shadow-2xl hover:border-white/20 transition-all duration-300 border flex flex-col justify-between print-card"
						:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
					>
						<!-- Card Cover Image Container -->
						<div class="aspect-video w-full overflow-hidden bg-slate-950 relative print-card-image">
							<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-700" />
							<div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center no-print" v-if="isInteractive">
								<span class="px-5 py-2.5 rounded-xl bg-white text-black text-xs font-extrabold shadow-lg transform translate-y-3 group-hover:translate-y-0 transition-transform">
									View Case Study
								</span>
							</div>
						</div>

						<!-- Details -->
						<div class="p-6 flex-1 flex flex-col justify-between">
							<div class="space-y-3">
								<div class="flex justify-between items-start gap-4">
									<h4 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors leading-snug">
										{{ proj.title }}
									</h4>
									<span class="text-[9px] font-mono px-2 py-0.5 rounded border border-white/10 shrink-0" :class="props.activePalette.badge">
										{{ proj.category || 'Project' }}
									</span>
								</div>
								
								<p class="text-xs opacity-70 line-clamp-3 leading-relaxed">
									{{ proj.description }}
								</p>

								<!-- Role & Contribution info (SaaS design specification) -->
								<div class="text-[10px] border-l border-cyan-400/50 pl-3 space-y-0.5 opacity-80 pt-1">
									<span class="block text-slate-400">Contribution: Lead Developer / UI Architecture</span>
									<span class="block text-slate-400">Outcome: Completed in 3 weeks, optimized load performance.</span>
								</div>
							</div>

							<!-- Tags -->
							<div class="flex flex-wrap gap-1.5 mt-5 pt-3 border-t border-white/5">
								<span 
									v-for="t in proj.tags" 
									:key="t"
									class="px-2.5 py-0.5 rounded-md text-[9px] font-semibold bg-white/5 text-slate-300 border border-white/5"
								>
									{{ t }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- 4. EXPERIENCE TIMELINE SECTION -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="grid grid-cols-1 md:grid-cols-12 gap-12">
				<!-- Left Sidebar Layout (Linear Style) -->
				<div class="md:col-span-4 space-y-6">
					<div>
						<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Experience</h2>
						<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
					</div>
					<p class="text-xs opacity-75 leading-relaxed font-light">
						A timeline representing internships, campus organizations, leadership positions, and key research activities.
					</p>
					
					<!-- Interactive mini widget -->
					<div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-left hidden md:block">
						<div class="flex items-center gap-2 text-xs font-bold text-white mb-2">
							<Terminal class="w-4 h-4 text-cyan-400" /> System Integration
						</div>
						<span class="text-[10px] text-slate-400 block font-mono">
							git log --oneline
							<br/>* feat: complete timeline layout
							<br/>* refactor: modularize components
							<br/>* build: final compiled assets
						</span>
					</div>
				</div>

				<!-- Right Timeline Elements -->
				<div class="md:col-span-8 space-y-8">
					<div class="relative border-l border-white/10 pl-6 ml-2.5 space-y-10 text-left">
						<div 
							v-for="(item, idx) in timelineItems" 
							:key="idx" 
							class="relative group"
						>
							<!-- Circle icon badge -->
							<div class="absolute -left-[35px] top-1 w-5 h-5 rounded-full border border-white/20 bg-slate-950 flex items-center justify-center z-10">
								<Briefcase class="w-3 h-3 text-cyan-400" v-if="item.type === 'work'" />
								<Cpu class="w-3 h-3 text-violet-400" v-else-if="item.type === 'academic'" />
								<Award class="w-3 h-3 text-emerald-400" v-else />
							</div>

							<div class="space-y-1.5">
								<div class="flex flex-wrap items-center justify-between gap-2">
									<h4 class="text-sm font-bold text-white group-hover:text-cyan-300 transition-colors">{{ item.title }}</h4>
									<span class="text-[10px] font-semibold text-slate-400 flex items-center gap-1">
										<Calendar class="w-3 h-3" /> {{ item.period }}
									</span>
								</div>
								
								<p class="text-xs text-cyan-200/80 font-bold">{{ item.institution }}</p>
								<p class="text-xs opacity-75 font-light leading-relaxed">{{ item.desc }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- 5. SKILLS SECTION -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
				<div>
					<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Tech Stack</h2>
					<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
				</div>
				<p class="text-xs opacity-75 max-w-md font-light leading-relaxed">
					Core technology proficiency map. Visual indicators representing relative framework mastery.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<!-- Category Cards -->
				<div 
					v-for="cat in skillCategories" 
					:key="cat.title"
					class="p-6 rounded-2xl border text-left flex flex-col justify-between"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
				>
					<div class="space-y-5">
						<h4 class="text-sm font-black uppercase tracking-widest text-white border-b border-white/5 pb-2">{{ cat.title }}</h4>
						<div class="space-y-4">
							<div 
								v-for="skill in cat.skills" 
								:key="skill" 
								class="space-y-1.5"
							>
								<div class="flex justify-between text-xs font-bold">
									<span class="text-slate-300">{{ skill }}</span>
									<span class="text-slate-400 text-[10px]">Proficient</span>
								</div>
								<!-- Mini Visual Indicator -->
								<div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
									<div class="h-full bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full" style="width: 85%"></div>
								</div>
							</div>
						</div>
					</div>
					
					<span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold block pt-6">High Competency</span>
				</div>

				<!-- Fallback if user doesn't have custom categorized skills -->
				<div 
					v-if="skillCategories.length === 0"
					class="col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-4"
				>
					<div 
						v-for="s in ['Figma', 'Vue.js', 'TypeScript', 'Laravel', 'TailwindCSS', 'PHP', 'JavaScript', 'Git']"
						:key="s"
						class="p-4 rounded-xl border text-center flex items-center justify-center gap-2"
						:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
					>
						<CheckCircle class="w-4 h-4 text-cyan-400" />
						<span class="text-xs font-bold text-white">{{ s }}</span>
					</div>
				</div>
			</div>
		</section>

		<!-- 6. CERTIFICATES & CREDENTIALS SECTION -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section page-break-before-print">
			<div class="flex justify-between items-end mb-12">
				<div>
					<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Certificates</h2>
					<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
				</div>
				<span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 hidden sm:block">Verified Credentials</span>
			</div>

			<!-- Mockup Mode -->
			<div v-if="isMockup" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
				<div 
					v-for="i in 2" 
					:key="i"
					class="p-6 rounded-2xl border flex items-start gap-4"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
				>
					<div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
						<Award class="w-6 h-6 text-cyan-400" />
					</div>
					<div class="space-y-1 text-left">
						<h4 class="text-sm font-bold text-white">Google UX Design Professional Certificate</h4>
						<p class="text-xs opacity-75 font-semibold text-slate-300">Coursera • 2026</p>
						<p class="text-[9px] opacity-60 font-mono text-slate-450">ID: G-18A8B2C3</p>
					</div>
				</div>
			</div>

			<!-- Live Mode -->
			<div v-else>
				<div v-if="!props.profileUser.certificates || props.profileUser.certificates.length === 0" class="text-center py-12 opacity-75 italic text-xs">
					Belum ada sertifikat yang diunggah.
				</div>

				<div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="p-6 rounded-2xl border flex items-start gap-4 transition-transform hover:-translate-y-1 duration-200 print-card"
						:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
					>
						<div class="w-12 h-12 rounded-xl overflow-hidden bg-white/5 flex items-center justify-center shrink-0">
							<img v-if="cert.logo_url" :src="cert.logo_url" alt="Issuer logo" class="w-full h-full object-cover" />
							<Award v-else class="w-6 h-6 text-cyan-400" />
						</div>
						<div class="space-y-1 text-left">
							<h4 class="text-sm font-bold text-white leading-snug">{{ cert.title }}</h4>
							<p class="text-xs opacity-75 font-semibold text-slate-300">{{ cert.issuer }} • {{ cert.date }}</p>
							<p class="text-[9px] opacity-60 font-mono text-slate-450">ID: {{ cert.credentialId }}</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- 7. GALLERY SHOWCASE SECTION (Behance Style Layout) -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="flex justify-between items-end mb-12">
				<div>
					<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Visual Showcase</h2>
					<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
				</div>
				<span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 hidden sm:block">Behance Presentation</span>
			</div>

			<!-- Masonry style showcase -->
			<div class="grid grid-cols-2 md:grid-cols-3 gap-4">
				<div 
					v-for="(proj, idx) in props.projects.slice(0, 6)" 
					:key="proj.id"
					@click="handleProjectClick(proj)"
					class="relative rounded-2xl overflow-hidden group cursor-pointer border border-white/10 print-card"
					:class="idx % 3 === 0 ? 'row-span-2' : ''"
				>
					<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 aspect-square md:aspect-auto" />
					<div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4 text-left no-print">
						<h5 class="text-xs font-bold text-white">{{ proj.title }}</h5>
						<span class="text-[8px] text-cyan-300 font-black uppercase tracking-wider mt-1">View case</span>
					</div>
				</div>

				<!-- Fallback / Dummy Masonry visual cards if projects list is too small -->
				<div 
					v-if="props.projects.length < 3"
					v-for="i in [1, 2, 3]"
					:key="i"
					class="relative rounded-2xl overflow-hidden group border border-white/10"
					:class="i === 1 ? 'row-span-2' : ''"
				>
					<img :src="'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=640&auto=format&fit=crop&sig=' + i" alt="SaaS Mockup" class="w-full h-full object-cover aspect-square" />
					<div class="absolute inset-0 bg-gradient-to-t from-slate-950 to-transparent flex flex-col justify-end p-4 text-left">
						<h5 class="text-xs font-bold text-white">SaaS Conceptual Interface</h5>
					</div>
				</div>
			</div>
		</section>

		<!-- 8. TESTIMONIALS SECTION -->
		<section class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="flex justify-between items-end mb-12">
				<div>
					<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">References</h2>
					<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
				</div>
				<span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 hidden sm:block">Professional references</span>
			</div>

			<!-- Carousel slider review -->
			<div class="relative max-w-3xl mx-auto py-8">
				<div 
					class="p-8 md:p-12 rounded-3xl border text-center space-y-6 relative overflow-hidden backdrop-blur-md"
					:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
				>
					<p class="text-base sm:text-xl italic font-light leading-relaxed text-white">
						"{ testimonials[currentTestimonial].quote }"
					</p>

					<div class="flex flex-col items-center gap-3">
						<img :src="testimonials[currentTestimonial].avatar" alt="Avatar" class="w-12 h-12 rounded-full border border-white/10 bg-slate-800 shrink-0" />
						<div class="text-center">
							<h4 class="text-sm font-bold text-white">{{ testimonials[currentTestimonial].name }}</h4>
							<p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">{{ testimonials[currentTestimonial].role }}</p>
						</div>
					</div>

					<!-- Carousel Control buttons (hidden in print) -->
					<div class="flex justify-center gap-4 pt-4 no-print">
						<button 
							@click="prevTestimonial" 
							class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/5 active:scale-95 transition-all text-white cursor-pointer bg-transparent"
						>
							<ChevronLeft class="w-5 h-5" />
						</button>
						<button 
							@click="nextTestimonial" 
							class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/5 active:scale-95 transition-all text-white cursor-pointer bg-transparent"
						>
							<ChevronRight class="w-5 h-5" />
						</button>
					</div>
				</div>
			</div>
		</section>

		<!-- 9. CONTACT & RESUME FOOTER SECTION -->
		<section id="contact" class="max-w-6xl mx-auto px-6 py-20 border-t border-white/5 relative z-10 print-section">
			<div class="grid grid-cols-1 md:grid-cols-12 gap-12 text-left">
				<!-- Socials and Info -->
				<div class="md:col-span-5 space-y-6">
					<div>
						<h2 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-white">Let's Connect</h2>
						<div class="w-12 h-1.5 rounded-full mt-2" :style="{ background: 'linear-gradient(to right, ' + props.activePalette.accent + ', transparent)' }"></div>
					</div>
					<p class="text-xs opacity-75 leading-relaxed font-light">
						Interested in collaborating or discussing full-time roles? Drop a message or review credentials.
					</p>

					<div class="space-y-3 pt-2">
						<div class="flex items-center gap-3 text-xs">
							<Mail class="w-4 h-4 text-cyan-400" />
							<span class="font-bold text-white">{{ props.profileUser.email }}</span>
						</div>
					</div>

					<!-- Links -->
					<div class="flex gap-3 pt-4 no-print">
						<a 
							v-if="props.profileUser.github" 
							:href="props.profileUser.github" 
							target="_blank" 
							class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-colors"
						>
							<Github class="w-5 h-5" />
						</a>
						<a 
							v-if="props.profileUser.linkedin" 
							:href="props.profileUser.linkedin" 
							target="_blank" 
							class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-colors"
						>
							<Linkedin class="w-5 h-5" />
						</a>
						<a 
							v-if="props.profileUser.website" 
							:href="props.profileUser.website" 
							target="_blank" 
							class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-colors"
						>
							<ExternalLink class="w-5 h-5" />
						</a>
					</div>
				</div>

				<!-- Clean SaaS Form Container & Downloads -->
				<div class="md:col-span-7 space-y-6 text-left">
					<div 
						class="p-6 rounded-3xl border space-y-4 text-left"
						:style="{ backgroundColor: props.activePalette.cardBg, borderColor: props.activePalette.border || 'rgba(255,255,255,0.05)' }"
					>
						<h4 class="text-sm font-bold text-white flex items-center gap-2">
							<Sparkles class="w-4 h-4 text-cyan-400" /> Launch Conversation
						</h4>
						
						<div class="grid grid-cols-2 gap-4">
							<div class="space-y-1">
								<label class="text-[9px] font-black uppercase text-slate-400 block">Name</label>
								<input type="text" placeholder="John Doe" class="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-xs text-white outline-none focus:border-cyan-400" />
							</div>
							<div class="space-y-1">
								<label class="text-[9px] font-black uppercase text-slate-400 block">Email</label>
								<input type="email" placeholder="john@example.com" class="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-xs text-white outline-none focus:border-cyan-400" />
							</div>
						</div>
						
						<div class="space-y-1">
							<label class="text-[9px] font-black uppercase text-slate-400 block">Message</label>
							<textarea rows="3" placeholder="Tell me about your project opportunity..." class="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-xs text-white outline-none focus:border-cyan-400 resize-none"></textarea>
						</div>

						<div class="flex items-center justify-between pt-2">
							<!-- Direct email fallback link in case form is mock -->
							<a 
								:href="'mailto:' + props.profileUser.email"
								class="text-[10px] text-cyan-300 font-bold hover:underline"
							>
								Or send direct email →
							</a>
							<button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-black text-[10px] uppercase px-5 py-2.5 rounded-lg transform hover:-translate-y-0.5 transition-all border-none cursor-pointer">
								Send Message
							</button>
						</div>
					</div>

					<!-- Downloads widget (Resume / Print PDF) -->
					<div class="flex flex-wrap gap-4 pt-2 no-print">
						<a 
							:href="'mailto:' + props.profileUser.email + '?subject=Requesting Resume'"
							class="flex-1 flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold text-xs uppercase px-4 py-3 rounded-xl border border-white/10 transition-colors"
						>
							<Download class="w-4 h-4" /> Download Resume
						</a>
						
						<button 
							@click="() => window.print()"
							class="flex-1 flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold text-xs uppercase px-4 py-3 rounded-xl border border-white/10 transition-colors cursor-pointer bg-transparent"
						>
							<FileText class="w-4 h-4" /> Export Works PDF
						</button>
					</div>
				</div>
			</div>
		</section>
	</div>
</template>

<style scoped>
@keyframes bounce {
	0%, 100% { transform: translateY(0); }
	50% { transform: translateY(-8px); }
}

.animate-bounce {
	animation: bounce 6s ease-in-out infinite;
}
</style>
