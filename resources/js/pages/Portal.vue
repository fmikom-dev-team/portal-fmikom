<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useDark, useDateFormat, useNow } from "@vueuse/core";
import {
	Bell,
	Box,
	Briefcase,
	Calendar,
	CalendarDays,
	Check,
	CheckSquare,
	ChevronLeft,
	ChevronRight,
	Clock,
	Download,
	Edit2,
	Edit3,
	Gift,
	Layers,
	LayoutGrid,
	MessageSquare,
	Moon,
	MoreHorizontal,
	MousePointer2,
	Paperclip,
	PenTool,
	PieChart,
	Plus,
	Search,
	Settings,
	Settings2,
	Share2,
	Sun,
	Trash2,
	UserCircle,
	Users,
} from "lucide-vue-next";
import { computed, ref } from "vue";

// Define our standard user
const page = usePage();
const user = computed(() => page.props.auth.user);
const firstName = computed(() => user.value?.name?.split(" ")[0] || "James");

// Ensure matching styling for primary and tertiary as requested
// Primary: #2563EB
// Tertiary: #B6FF00

const time = useNow();
const formattedTime = useDateFormat(time, "HH:mm:ss");
const isDark = useDark({
	selector: "html",
	attribute: "class",
	valueDark: "dark",
	valueLight: "",
});

const notifications = computed<any[]>(
	() => (user.value?.unreadNotifications as any[]) || [],
);

const searchQuery = ref("");
const handleSearch = () => {
	router.get("/search", { query: searchQuery.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Dashboard - Portal FMIKOM" />

    <div class="min-h-screen bg-[#eaecf4] dark:bg-slate-900 transition-colors duration-300 flex items-center justify-center p-4 lg:p-8 font-sans selection:bg-[#B6FF00] selection:text-black">
        <!-- Main Application Container -->
        <div class="w-full max-w-[1536px] bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-[0_20px_60px_rgba(37,99,235,0.05)] flex overflow-hidden min-h-[92vh] border border-white relative">

            <!-- RIGHT CONTENT AREA -->
            <div class="flex-1 flex flex-col p-5 md:p-8 xl:p-10 overflow-y-auto relative z-10 bg-white dark:bg-slate-900 dark:text-slate-100 transition-colors duration-300">
                
                <!-- HEADER BAR -->
                <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <!-- Logo & Main Nav -->
                        <div class="flex items-center gap-4 lg:gap-8 text-[#0f172a] dark:text-white font-extrabold text-[15px]">
                            <div class="flex items-center gap-2">
                                <LayoutGrid class="w-[18px] h-[18px] text-[#2563EB] md:text-slate-800 dark:text-slate-100"/> Dashboard
                            </div>
                            <div class="items-center gap-2 text-slate-400 hover:text-slate-800 dark:text-slate-100 transition-colors cursor-pointer hidden sm:flex">
                                <Layers class="w-[18px] h-[18px]"/> Workflows
                            </div>
                            <div class="items-center gap-2 text-slate-400 hover:text-slate-800 dark:text-slate-100 transition-colors cursor-pointer hidden sm:flex">
                                <Box class="w-[18px] h-[18px]"/> Integrations
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex-1 w-full lg:max-w-md lg:mx-6">
                        <div class="relative flex items-center w-full h-11 rounded-2xl bg-white dark:bg-slate-800 border outline-none border-slate-200 dark:border-slate-700 shadow-sm focus-within:ring-2 focus-within:ring-[#2563EB] focus-within:border-transparent transition-all">
                            <Search class="w-[18px] h-[18px] text-slate-400 absolute left-4 pointer-events-none"/>
                            <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" placeholder="Search Scout..." class="w-full h-full bg-transparent pl-12 pr-4 text-[13px] font-bold text-slate-700 dark:text-slate-100 placeholder-slate-400 border-none outline-none focus:ring-0 rounded-2xl" />
                        </div>
                    </div>

                    <!-- Top Menu Actions -->
                    <div class="flex items-center gap-2 sm:gap-4 shrink-0 overflow-x-auto pb-1 lg:pb-0">
                        <!-- Theme Toggle -->
                        <div class="flex items-center bg-[#f4f6fa] dark:bg-slate-800 p-1 rounded-2xl shrink-0">
                            <button @click="isDark = false" :class="[!isDark ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-300', 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-black transition-colors']">
                                <Sun class="w-3.5 h-3.5"/> Light
                            </button>
                            <button @click="isDark = true" :class="[isDark ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-300', 'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-black transition-colors']">
                                <Moon class="w-3.5 h-3.5"/> Dark
                            </button>
                        </div>

                        <!-- Notification & Settings -->
                        <button class="text-slate-400 hover:text-slate-800 dark:text-slate-100 transition-colors shrink-0"><Bell class="w-5 h-5"/></button>
                        <Link href="/settings" class="text-slate-400 hover:text-slate-800 dark:hover:text-slate-300 transition-colors shrink-0 hidden sm:block"><Settings class="w-5 h-5"/></Link>
                        
                        <!-- Actions -->
                        <div class="items-center justify-center text-slate-600 dark:text-slate-300 font-extrabold text-[12px] shrink-0 hidden sm:flex tabular-nums w-[80px]">
                            <span v-text="formattedTime" class="tracking-widest"></span> WIB
                        </div>

                        <!-- Call to action button with dark background `#1e1b4b` -> hover `#B6FF00` to satisfy tertiary requirement in a cool way -->
                        <Link href="/logout" method="post" as="button" class="bg-[#1e1b4b] hover:bg-[#B6FF00] hover:text-[#1e1b4b] text-white font-black text-[12px] px-4 sm:px-5 py-2.5 rounded-[14px] shadow-sm transition-colors duration-300 shrink-0">
                            Logout
                        </Link>
                    </div>
                </header>

                <!-- BENTO GRID LAYOUT -->
                <!-- The entire dashboard is inside a 12-column grid. We perfectly match the image layout -->
                <div class="grid grid-cols-12 gap-5 lg:gap-6 auto-rows-min">
                    
                    <!-- ================= ROW 1 ================= -->

                    <!-- Greeting Area (Span 4) -->
                    <div class="col-span-12 xl:col-span-4 flex flex-col justify-center pr-2 py-4 dark:text-white">
                        <h1 class="text-[34px] xl:text-[42px] font-extrabold text-[#0f172a] dark:text-white leading-[1.05] tracking-tight mb-4 flex flex-wrap items-center">
                            Hi, {{ firstName }}! 
                            <span class="inline-flex -space-x-2 ml-3">
                                <img src="https://api.dicebear.com/7.x/initials/svg?seed=James&backgroundColor=2563eb&textColor=ffffff" class="w-9 h-9 rounded-full border-[3px] border-white relative z-10 shadow-sm" />
                                <div class="w-9 h-9 rounded-full border-[3px] border-white bg-[#f8f9fc] dark:bg-slate-700/50 flex items-center justify-center relative z-0 text-[#2563EB]">
                                    <PieChart class="w-3.5 h-3.5"/>
                                </div>
                            </span>
                            <br><span class="mt-1 block">What are your plans for today?</span>
                        </h1>
                        <p class="text-slate-500 font-semibold text-[14px] leading-relaxed w-[90%]">
                            <span class="dark:text-slate-300">This platform is designed to revolutionize the way you organize and access your notes</span>
                        </p>
                    </div>

                    <!-- Add Empty Box (Span 2) -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-2 h-[170px] bg-[#f8f9fc] dark:bg-slate-700/50 rounded-[2rem] flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-600">
                        <!-- Add Button -->
                        <button class="w-12 h-12 rounded-[14px] bg-[#2563EB] hover:bg-blue-700 text-white flex items-center justify-center shadow-[0_8px_20px_rgba(37,99,235,0.25)] transition-transform hover:scale-105">
                            <Plus class="w-6 h-6"/>
                        </button>
                    </div>

                    <!-- Card 1: Stay organized (Span 2) -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-2 h-[170px] bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.06)] flex flex-col justify-end p-6 relative group overflow-hidden border border-slate-50 dark:border-slate-700">
                        <div class="absolute top-5 left-1/2 -translate-x-1/2 flex items-center justify-center">
                            <!-- Recreated Icon Doodle for Stay Organized -->
                            <div class="relative bg-white border-2 border-slate-800 rounded-xl w-[52px] h-[60px] flex flex-col items-center justify-start pt-2">
                                <div class="flex gap-1.5 mb-1"><div class="w-1 h-3 border-2 border-slate-800 rounded-full bg-white -mt-4"></div><div class="w-1 h-3 border-2 border-slate-800 rounded-full bg-white -mt-4"></div><div class="w-1 h-3 border-2 border-slate-800 rounded-full bg-white -mt-4"></div></div>
                                <div class="w-6 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full mt-1"></div>
                                <div class="w-8 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full mt-2"></div>
                                <!-- Note: Tertiary color used tastefully as an accent in illustrations -->
                                <div class="absolute -right-3 -bottom-2 bg-[#2563EB] text-white p-1.5 rounded-lg -rotate-12 shadow-md">
                                    <MousePointer2 class="w-3.5 h-3.5 fill-current"/>
                                </div>
                            </div>
                        </div>
                        <h3 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 text-center mb-1 leading-snug">Stay organized</h3>
                        <p class="text-[9px] font-extrabold text-slate-400 text-center uppercase tracking-wide">A clear structure for your notes</p>
                    </div>

                    <!-- Card 2: Sync your notes (Span 2) -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-2 h-[170px] bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.06)] flex flex-col justify-end p-6 relative group overflow-hidden border border-slate-50 dark:border-slate-700">
                        <div class="absolute top-5 left-1/2 -translate-x-1/2 flex items-center justify-center">
                            <!-- Recreated Icon Doodle for Sync your notes -->
                            <div class="relative flex">
                                <div class="absolute top-1 -left-2 bg-slate-50 dark:bg-slate-800 border-2 border-slate-800 rounded-xl w-10 h-14 -rotate-12"></div>
                                <div class="relative bg-white border-2 border-slate-800 rounded-xl w-10 h-14 flex flex-col items-center justify-center gap-1.5 px-2 z-10">
                                    <div class="w-full h-1 bg-slate-200 rounded-full"></div>
                                    <div class="w-full h-1 bg-slate-200 rounded-full"></div>
                                    <div class="w-[70%] h-1 bg-[#B6FF00] rounded-full self-start"></div> <!-- Tertiary Color -->
                                </div>
                                <div class="absolute -top-1 -right-4 text-slate-400 scale-[0.6] rotate-12 bg-white rounded-full"><Clock class="w-6 h-6 text-slate-800 dark:text-slate-100"/></div>
                            </div>
                        </div>
                        <h3 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 text-center mb-1 leading-snug">Sync your notes</h3>
                        <p class="text-[9px] font-extrabold text-slate-400 text-center uppercase tracking-wide">Ensure that notes are synced</p>
                    </div>

                    <!-- Card 3: Collaborate and share (Span 2) -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-2 h-[170px] bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.06)] flex flex-col justify-end p-6 relative group overflow-hidden border border-slate-50 dark:border-slate-700">
                        <div class="absolute top-6 left-1/2 -translate-x-1/2 flex items-center justify-center">
                            <!-- Recreated Icon Doodle for Collaborate -->
                            <div class="relative bg-white border-2 border-slate-800 rounded-xl w-[56px] h-[40px] flex items-center justify-center overflow-visible">
                                <div class="absolute -top-2 left-[15%] w-[40%] h-3 border-t-2 border-x-2 border-slate-800 rounded-t-md bg-white"></div>
                                <div class="w-8 h-[6px] bg-slate-200 rounded-full overflow-hidden flex"><div class="w-[50%] h-full bg-[#B6FF00]"></div></div>
                                <div class="absolute -right-3 -top-3 bg-white border-[1.5px] border-slate-100 dark:border-slate-700 rounded-full p-1 text-[#2563EB] shadow-sm transform rotate-[15deg]">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M21 3l-7 7"/><path d="M9 21H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h5"/><polyline points="16 21 21 21 21 16"/><line x1="21" y1="21" x2="14" y2="14"/></svg>
                                </div>
                            </div>
                        </div>
                        <h3 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 text-center mb-1 leading-snug">Collaborate and share</h3>
                        <p class="text-[9px] font-extrabold text-slate-400 text-center uppercase tracking-wide">Share notes with colleagues</p>
                    </div>

                    
                    <!-- ================= ROW 2 ================= -->
                    
                    <!-- Notifications (Span 4) -->
                    <div class="col-span-12 xl:col-span-4 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] p-7 shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 flex flex-col relative h-[310px]">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="font-extrabold text-[16px] text-slate-800 dark:text-slate-100">Notifications</h2>
                            <button class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 hover:text-slate-600 dark:text-slate-300 transition-colors">
                                <Trash2 class="w-3.5 h-3.5"/> Clear
                            </button>
                        </div>
                        
                        <div class="flex-1 flex flex-col gap-4">
                            <div v-for="notif in notifications" :key="notif.id" class="relative w-[calc(100%+0.5rem)] -ml-2 bg-white dark:bg-slate-800 rounded-3xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.08)] cursor-pointer group hover:scale-[1.01] transition-transform z-10 border border-slate-50 dark:border-slate-700 mb-4">
                                <!-- Green active line -->
                                <div class="absolute left-0 top-3 bottom-3 w-1.5 bg-[#B6FF00] rounded-r-full"></div>
                                <div class="ml-2">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-extrabold text-[14px] text-slate-800 dark:text-white flex items-center gap-2">{{ notif.data?.title || 'Notification' }} 
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#B6FF00] shadow-[0_0_8px_#B6FF00]"></span>
                                        </h4>
                                        <button class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"><MoreHorizontal class="w-4 h-4"/></button>
                                    </div>
                                    <p class="text-[11px] font-bold text-slate-400 mb-3 flex items-center gap-2">
                                        {{ notif.data?.message || 'You have a new alert.' }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <span class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-extrabold text-[10px] px-2.5 py-1.5 rounded-[10px] flex items-center gap-1.5">
                                            <Clock class="w-3.5 h-3.5 text-[#2563EB]"/> {{ (new Date(notif.created_at)).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="notifications.length === 0" class="text-[11px] font-bold text-center text-slate-400 bg-slate-50 dark:bg-slate-800 rounded-3xl p-5 border border-slate-100 dark:border-slate-700">
                                You don't have any unread notifications yet.
                            </div>
                        </div>
                    </div>


                    <!-- Assignments (Span 4) -->
                    <div class="col-span-12 xl:col-span-4 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] p-7 shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 flex flex-col h-[310px] relative">
                        <div class="flex justify-between items-center mb-[1.75rem]">
                            <h2 class="font-extrabold text-[16px] text-slate-800 dark:text-slate-100">Assignments</h2>
                            <button class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 hover:text-[#2563EB] transition-colors"><Edit2 class="w-3 h-3"/> Edit</button>
                        </div>
                        
                        <div class="flex-1 bg-[#fcfdff] rounded-[1.5rem] p-5 border border-slate-100 dark:border-slate-700 relative group flex flex-col">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex gap-2.5 text-[11px] font-extrabold">
                                    <span class="text-[#2563EB]">Motion design</span>
                                    <span class="text-slate-800 dark:text-slate-100">Logo</span>
                                </div>
                                <button class="text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"><MoreHorizontal class="w-4 h-4"/></button>
                            </div>
                            
                            <div class="flex justify-between items-start gap-4 mb-auto">
                                <h3 class="font-extrabold text-[15px] xl:text-[16px] text-slate-800 dark:text-slate-100 leading-[1.3] pt-1">Design a packaging concept <br>for a new product</h3>
                                <div class="text-[9px] font-black text-rose-500 bg-rose-50 px-2.5 py-1 rounded-md shrink-0 uppercase tracking-widest mt-1">High</div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg">Package design</span>
                                <div class="flex items-center gap-2.5">
                                    <span class="text-[10px] font-bold text-slate-400">Rachel Lee</span>
                                    <img src="https://api.dicebear.com/7.x/initials/svg?seed=Rachel+Lee&backgroundColor=2563eb&textColor=ffffff" class="w-[22px] h-[22px] rounded-full" />
                                </div>
                            </div>
                        </div>
                        
                        <button class="w-full mt-4 bg-[#f8f9fc] dark:bg-slate-700/50 hover:bg-[#2563EB] hover:text-white text-[#2563EB] font-extrabold text-[12px] py-4 rounded-2xl flex items-center justify-center gap-2 transition-colors duration-300 group">
                            <div class="w-5 h-5 rounded-[6px] bg-[#2563EB] group-hover:bg-[#B6FF00] group-hover:text-black text-white flex items-center justify-center text-sm font-black transition-colors duration-300">
                                <Plus class="w-3.5 h-3.5"/>
                            </div>
                            Add new assignment
                        </button>
                    </div>


                    <!-- Calendar (Span 4) -->
                    <div class="col-span-12 xl:col-span-4 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] p-7 shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 flex flex-col h-[310px]">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="font-extrabold text-[16px] text-slate-800 dark:text-slate-100">May 2021</h2>
                            <div class="flex gap-1.5">
                                <button class="w-6 h-6 rounded-full bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:bg-slate-700 text-slate-400 font-bold flex items-center justify-center transition-colors"><ChevronLeft class="w-3.5 h-3.5"/></button>
                                <button class="w-6 h-6 rounded-full bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:bg-slate-700 text-slate-400 font-bold flex items-center justify-center transition-colors"><ChevronRight class="w-3.5 h-3.5"/></button>
                            </div>
                        </div>
                        
                        <!-- Date selection exactly like image -->
                        <div class="flex justify-between items-center mb-7 px-1">
                            <div v-for="(day, i) in [{d:'Mon',n:14}, {d:'Tue',n:15}, {d:'Wed',n:16}, {d:'Thu',n:17}, {d:'Fri',n:18,active:true}, {d:'Sat',n:19}, {d:'Sun',n:20}]" :key="i" class="flex flex-col items-center gap-2">
                                <span class="text-[10px] font-extrabold text-slate-400">{{ day.d }}</span>
                                <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-black cursor-pointer transition-transform duration-300 transform', day.active ? 'bg-[#2563EB] text-white shadow-[0_4px_12px_rgba(37,99,235,0.4)] scale-110' : 'text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:bg-slate-700']">
                                    {{ day.n }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timeline events -->
                        <div class="flex-1 space-y-5 overflow-hidden relative pb-2">
                            <!-- Line decoration on the left -->
                            <div class="absolute left-5 top-8 bottom-4 w-[1px] border-l border-dashed border-slate-200 dark:border-slate-600 z-0"></div>

                            <!-- Event 1 -->
                            <div class="relative z-10">
                                <div class="text-[10px] font-black text-slate-800 dark:text-slate-100 mb-2 flex items-center justify-between w-full pr-1">
                                    04:30-05:00 PM <div class="w-[60%] h-[1px] border-t border-dashed border-slate-200 dark:border-slate-600 ml-3"></div>
                                </div>
                                <div class="flex items-start gap-4 mt-2">
                                    <div class="w-10 h-10 rounded-xl bg-[#f0f4ff] flex items-center justify-center text-slate-500 shrink-0 shadow-sm border border-slate-50 dark:border-slate-700"><Layers class="w-4 h-4"/></div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 leading-snug">Team meeting</h4>
                                            <button class="text-slate-300 hover:text-slate-500 mt-1"><MoreVertical class="w-3.5 h-3.5"/></button>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 mt-1">12:00 - 12:30 <span class="mx-1">•</span> <span class="text-indigo-400">UX/UI design</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Event 2 -->
                            <div class="relative z-10">
                                <div class="text-[10px] font-black text-slate-800 dark:text-slate-100 mb-2 flex items-center justify-between w-full pr-1">
                                    11:30-12:30 PM <div class="w-[60%] h-[1px] border-t border-dashed border-slate-200 dark:border-slate-600 ml-3"></div>
                                </div>
                                <div class="flex items-start gap-4 mt-2">
                                    <div class="w-10 h-10 rounded-xl bg-[#fff0f5] flex items-center justify-center text-slate-500 shrink-0 shadow-sm border border-slate-50 dark:border-slate-700"><Briefcase class="w-4 h-4"/></div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 leading-snug">Meeting with new client</h4>
                                            <button class="text-slate-300 hover:text-slate-500 mt-1"><MoreVertical class="w-3.5 h-3.5"/></button>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 mt-1">12:30 - 01:30 PM <span class="mx-1">•</span> <span class="text-slate-400">Job interview</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ================= ROW 3 & 4 ================= -->
                    <!-- Tasks (Span 5 | Row-Span 2) -->
                    <div class="col-span-12 xl:col-span-5 row-span-2 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] p-7 shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 flex flex-col h-full min-h-[300px]">
                        <div class="flex justify-between items-center mb-7">
                            <div class="flex items-center gap-3">
                                <h2 class="font-extrabold text-[16px] text-slate-800 dark:text-slate-100">Today tasks</h2>
                                <div class="hidden md:flex -space-x-2">
                                    <img src="https://api.dicebear.com/7.x/initials/svg?seed=User1&backgroundColor=2563eb&textColor=ffffff" class="w-6 h-6 rounded-full border-2 border-white relative z-20" />
                                    <img src="https://api.dicebear.com/7.x/initials/svg?seed=User2&backgroundColor=2563eb&textColor=ffffff" class="w-6 h-6 rounded-full border-2 border-white relative z-10" />
                                    <div class="w-6 h-6 rounded-full border-2 border-white bg-[#B6FF00] text-black text-[8px] font-black flex items-center justify-center relative z-0">+</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1.5 bg-[#f0f4ff] font-extrabold text-[#2563EB] px-2.5 py-1 rounded-[8px] text-[10px]">
                                    <LayoutGrid class="w-3 h-3"/> 88
                                </div>
                                <span class="text-[11px] font-extrabold text-slate-400">08</span>
                                <button class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 hover:text-slate-800 dark:text-slate-100"><Edit2 class="w-3 h-3"/> Edit</button>
                                <button class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 hover:text-slate-800 dark:text-slate-100"><Share2 class="w-3 h-3"/> Share</button>
                            </div>
                        </div>

                        <!-- Progress tasks map perfectly to image -->
                        <div class="flex-1 flex flex-col gap-6 justify-center">
                            <!-- Task 1 -->
                            <div class="flex items-center gap-2">
                                <div class="w-32 lg:w-40 shrink-0">
                                    <h4 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 mb-1 leading-snug">Conduct research</h4>
                                    <p class="text-[9px] font-bold text-slate-400">4 May, 09:20 AM</p>
                                </div>
                                <div class="w-16 shrink-0 hidden md:block">
                                    <p class="text-[9px] font-bold text-slate-400 mb-0.5">Duration</p>
                                    <p class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100">02 h 45 m</p>
                                </div>
                                <div class="flex-1 flex items-center gap-3 pr-2">
                                    <span class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100 w-7">90%</span>
                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="w-[90%] h-full bg-[#2563EB] rounded-full"></div>
                                    </div>
                                </div>
                                <div class="w-16 shrink-0 flex items-center justify-end gap-1.5 text-[9px] font-extrabold text-slate-400">
                                    <MessageSquare class="w-3 h-3"/> 4 <span class="mx-0.5">•</span> 16
                                </div>
                            </div>
                            
                            <div class="w-full h-px bg-slate-50 dark:bg-slate-800"></div>

                            <!-- Task 2 -->
                            <div class="flex items-center gap-2">
                                <div class="w-32 lg:w-40 shrink-0">
                                    <h4 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 mb-1 leading-snug">Schedule a meeting</h4>
                                    <p class="text-[9px] font-bold text-slate-400">14 May, 12:45 AM</p>
                                </div>
                                <div class="w-16 shrink-0 hidden md:block">
                                    <p class="text-[9px] font-bold text-slate-400 mb-0.5">Duration</p>
                                    <p class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100">06 h 55 m</p>
                                </div>
                                <div class="flex-1 flex items-center gap-3 pr-2">
                                    <span class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100 w-7">50%</span>
                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="w-[50%] h-full bg-[#B6FF00] rounded-full"></div> <!-- Tertiary Color Accent! -->
                                    </div>
                                </div>
                                <div class="w-16 shrink-0 flex items-center justify-end text-[9px] font-extrabold text-slate-400">
                                    <Paperclip class="w-3 h-3 mr-1"/> 4 <span class="mx-1">•</span> <span class="bg-indigo-50 text-indigo-500 px-1 py-0.5 rounded">3 June</span>
                                </div>
                            </div>
                            
                            <div class="w-full h-px bg-slate-50 dark:bg-slate-800"></div>

                            <!-- Task 3 -->
                            <div class="flex items-center gap-2">
                                <div class="w-32 lg:w-40 shrink-0">
                                    <h4 class="font-extrabold text-[13px] text-slate-800 dark:text-slate-100 mb-1 leading-snug">Send out reminders</h4>
                                    <p class="text-[9px] font-bold text-slate-400">21 May, 10:30 AM</p>
                                </div>
                                <div class="w-16 shrink-0 hidden md:block">
                                    <p class="text-[9px] font-bold text-slate-400 mb-0.5">Duration</p>
                                    <p class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100">01 h 30 m</p>
                                </div>
                                <div class="flex-1 flex items-center gap-3 pr-2">
                                    <span class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100 w-7">10%</span>
                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="w-[10%] h-full bg-[#2563EB] rounded-full"></div>
                                    </div>
                                </div>
                                <div class="w-16 shrink-0 flex items-center justify-end text-[9px] font-extrabold text-slate-400">
                                    <Paperclip class="w-3 h-3 mr-1"/> 16 <span class="mx-1">•</span> <span class="bg-indigo-50 text-indigo-500 px-1 py-0.5 rounded">3 June</span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Promo / Go Premium (Span 3 | Row-Span 2) -->
                    <!-- Made with Primary Color #2563EB and Tertiary accents as requested -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-3 row-span-2 bg-[#2563EB] rounded-[2rem] p-8 shadow-[0_20px_40px_rgba(37,99,235,0.3)] flex flex-col items-center justify-center text-center relative overflow-hidden group">
                        
                        <!-- Decorative background shapes matching screenshot style -->
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-[#B6FF00]/10 rounded-full blur-xl"></div>
                        
                        <div class="w-[72px] h-[72px] bg-white/10 rounded-[1.25rem] backdrop-blur-sm flex items-center justify-center mb-6 shadow-inner border border-white/10 group-hover:scale-110 transition-transform duration-300">
                            <!-- Gift doodle with Tertiary color touch -->
                            <Gift class="w-9 h-9 text-white stroke-[1.5]" />
                            <div class="absolute -top-1.5 -right-1.5 w-3 h-3 rounded-full bg-[#B6FF00] shadow-[0_0_10px_#B6FF00]"></div>
                        </div>
                        
                        <h2 class="text-[22px] font-black text-white mb-3 tracking-tight">Go premium!</h2>
                        <p class="text-[12px] font-medium text-white/80 leading-relaxed mb-8 max-w-[170px]">
                            Gain access to a range of benefits designed to enhance your user experience
                        </p>
                        
                        <button class="w-[85%] bg-[#1e1b4b] hover:bg-[#B6FF00] hover:text-[#1e1b4b] text-white text-[12px] font-black py-3.5 rounded-[1rem] shadow-[0_10px_20px_rgba(30,27,75,0.4)] transition-all duration-300">
                            Find out more
                        </button>
                    </div>


                    <!-- Stats (Span 4 | Row 1) -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 flex items-center p-6 lg:p-7 relative min-h-[160px]">
                        <!-- Two items split perfectly in the middle -->
                        <div class="w-1/2 border-r border-slate-100 dark:border-slate-700 pr-4 flex flex-col justify-center h-full">
                            <div class="flex items-center gap-3 mb-4">
                                <!-- Progress Circle 90% -->
                                <div class="relative w-[46px] h-[46px] flex items-center justify-center">
                                    <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 36 36">
                                        <path class="text-slate-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="4"></path>
                                        <path class="text-emerald-400" stroke-dasharray="90, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                                    </svg>
                                    <span class="absolute text-[10px] font-black text-slate-800 dark:text-slate-100">90%</span>
                                </div>
                                <div>
                                    <div class="text-[8px] font-black uppercase text-emerald-400 tracking-wider mb-0.5">DATA RESEARCH</div>
                                    <div class="text-[13px] font-black text-slate-800 dark:text-slate-100">Marketing</div>
                                </div>
                            </div>
                            <div class="text-[9px] font-bold text-slate-400 leading-relaxed">
                                <span class="text-emerald-500">You marked 5/5</span><br>
                                All assignments are done!
                            </div>
                        </div>

                        <div class="w-1/2 pl-5 flex flex-col justify-center h-full">
                            <div class="flex items-center gap-3 mb-4">
                                <!-- Progress Circle 65% -->
                                <div class="relative w-[46px] h-[46px] flex items-center justify-center">
                                    <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 36 36">
                                        <path class="text-slate-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="4"></path>
                                        <path class="text-rose-500" stroke-dasharray="65, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                                    </svg>
                                    <span class="absolute text-[10px] font-black text-slate-800 dark:text-slate-100">65%</span>
                                </div>
                                <div>
                                    <div class="text-[8px] font-black uppercase text-rose-500 tracking-wider mb-0.5">UX/UI DESIGN</div>
                                    <div class="text-[13px] font-black text-slate-800 dark:text-slate-100">Typography</div>
                                </div>
                            </div>
                            <div class="flex justify-between items-end">
                                <div class="text-[9px] font-bold text-slate-400 leading-relaxed">
                                    <span class="text-rose-500">You marked 3/5</span><br>
                                    2 assignments left
                                </div>
                                <button class="bg-[#2563EB] hover:bg-blue-700 text-white text-[10px] font-black px-3.5 py-1.5 rounded-lg shadow-sm transition-colors">
                                    Check
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Board Meeting (Span 4 | Row 2) -->
                    <!-- Positioned perfectly below Stats -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 bg-white dark:bg-slate-800 dark:border-slate-700 rounded-[2rem] shadow-[0_12px_40px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-700 p-6 lg:p-7 flex flex-col justify-between min-h-[160px]">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="font-extrabold text-[15px] xl:text-[16px] text-slate-800 dark:text-slate-100 mb-1.5">Board meeting</h2>
                                <p class="text-[10px] font-bold text-slate-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> 
                                    March 24 at 4:00 PM
                                </p>
                            </div>
                            <button class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 hover:text-slate-800 dark:text-slate-100 transition-colors">
                                <Edit3 class="w-3.5 h-3.5"/> Edit
                            </button>
                        </div>
                        
                        <div class="flex justify-between items-end mt-4">
                            <p class="text-[10px] font-bold text-slate-500 leading-relaxed max-w-[130px]">
                                Meeting with John Smith, 4th floor, room 159
                            </p>
                            <div class="flex gap-2.5">
                                <button class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-[10px] sm:text-[11px] font-black px-3.5 py-2.5 rounded-xl transition-colors">
                                    Reschedule
                                </button>
                                <button class="bg-indigo-500 hover:bg-indigo-600 text-white text-[10px] sm:text-[11px] font-black px-3.5 py-2.5 rounded-xl shadow-[0_6px_15px_rgba(99,102,241,0.3)] transition-colors">
                                    Accept invite
                                </button>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Hide scrollbar in the main content container to match clean UX */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

/* Ensure font rendering is perfectly crisp */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
</style>
