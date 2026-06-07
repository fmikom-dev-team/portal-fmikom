<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";

const { title, description } = defineProps<{
	title?: string;
	description?: string;
}>();

const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-[#F4F6FB] flex flex-col p-4 sm:p-8 font-sans overflow-y-auto">
        <div class="w-full max-w-[1000px] md:h-[720px] bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:p-2 overflow-hidden relative block m-auto shrink-0">
            
            <!-- Sliding Presentation Banner -->
            <div 
                class="hidden md:flex flex-col justify-between w-[calc(45%-8px)] bg-[#2563eb] p-8 rounded-2xl text-white absolute top-2 bottom-2 z-20 transition-all duration-700 ease-in-out overflow-hidden"
                :style="{ left: (page.component === 'auth/Register' || page.component === 'auth/OAuthRegister') ? 'calc(55% + 4px)' : '8px' }"
            >
                <div class="relative z-10 flex flex-col h-full">
                    
                    <!-- Text Header -->
                    <div class="mt-2 mb-3 shrink-0">
                        <h1 class="text-[26px] sm:text-[28px] leading-[1.2] font-bold mb-2 tracking-tight">
                            Sistem Administrasi Himpunan Terpadu
                        </h1>
                        <p class="text-xs sm:text-sm text-white/80 font-medium leading-relaxed pr-2">
                            Portal informasi dan manajemen aktivitas organisasi modern. Masukkan kredensial Anda untuk mengakses dashboard fungsionaris.
                        </p>
                    </div>

                    <!-- Mini Dashboard UI Mockup -->
                    <div class="relative flex-1 flex items-center justify-center min-h-0 mt-4 mb-6 mx-2">
                        <!-- Main Dashboard Frame -->
                        <div class="w-full h-full max-h-[200px] bg-[#F8FAFC] rounded-xl overflow-hidden shadow-2xl flex flex-col relative border border-white/20">
                            <!-- Mac Window Controls -->
                            <div class="h-6 bg-white border-b border-slate-200 flex items-center px-4 gap-1.5 shrink-0">
                                <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                <div class="ml-auto w-10 h-1.5 bg-slate-100 rounded-full"></div>
                            </div>
                            
                            <!-- Application Content -->
                            <div class="flex-1 p-3 bg-white relative overflow-hidden flex flex-col">
                                <div class="flex justify-between items-end mb-3 shrink-0">
                                    <div class="space-y-1.5">
                                        <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
                                        <div class="w-8 h-4 bg-slate-800 rounded-full"></div>
                                    </div>
                                    <!-- Bar Chart Mock -->
                                    <div class="w-24 h-12 bg-indigo-50/50 rounded-lg flex items-end p-1.5 gap-1 justify-center border border-indigo-50">
                                        <div class="w-full bg-indigo-200 h-[30%] rounded-sm"></div>
                                        <div class="w-full bg-indigo-400 h-[80%] rounded-sm"></div>
                                        <div class="w-full bg-indigo-600 h-full rounded-sm"></div>
                                        <div class="w-full bg-indigo-300 h-[50%] rounded-sm"></div>
                                        <div class="w-full bg-indigo-500 h-[70%] rounded-sm"></div>
                                    </div>
                                </div>
                                
                                <div class="space-y-2.5 flex-1 min-h-0 overflow-hidden">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-orange-100 shrink-0"></div>
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full"></div>
                                        <div class="w-10 h-1.5 bg-slate-200 rounded-full shrink-0"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-emerald-100 shrink-0"></div>
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full"></div>
                                        <div class="w-8 h-1.5 bg-slate-200 rounded-full shrink-0"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-blue-100 shrink-0"></div>
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full"></div>
                                        <div class="w-12 h-1.5 bg-slate-200 rounded-full shrink-0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Members Modal -->
                        <div class="absolute -right-2 bottom-1 w-44 bg-white rounded-xl shadow-[0_15px_30px_rgba(0,0,0,0.12)] border border-slate-100 p-3 z-10">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-[11px] font-bold text-slate-800">Reviewers</div>
                                <div class="text-[8px] font-semibold px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700">All access</div>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-teal-100 border border-teal-200 text-center text-teal-800 flex items-center justify-center text-[9px] font-bold shrink-0">A</div>
                                    <div class="space-y-1 flex-1">
                                        <div class="w-full h-1 bg-slate-200 rounded-full"></div>
                                        <div class="w-1/2 h-1 bg-slate-100 rounded-full"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-rose-100 border border-rose-200 text-center text-rose-800 flex items-center justify-center text-[9px] font-bold shrink-0">R</div>
                                    <div class="space-y-1 flex-1">
                                        <div class="w-3/4 h-1 bg-slate-200 rounded-full"></div>
                                        <div class="w-1/3 h-1 bg-slate-100 rounded-full"></div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Bottom Logos Layer -->
                    <div class="mt-auto pb-2 overflow-hidden mask-edges max-w-[400px] mx-auto w-full relative shrink-0">
                        <div class="flex animate-marquee hover:pause whitespace-nowrap pt-2">
                            <!-- First Set -->
                            <div class="flex gap-10 items-center px-6 shrink-0 opacity-75 text-white/90">
                                <!-- Google -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Google</span>
                                </div>
                                <!-- Spotify -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Spotify</span>
                                </div>
                                <!-- Stripe -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Stripe</span>
                                </div>
                                <!-- YouTube -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">YouTube</span>
                                </div>
                                <!-- GitHub -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">GitHub</span>
                                </div>
                            </div>

                            <!-- Second Set (Duplicate for seamless loop) -->
                            <div class="flex gap-10 items-center px-6 shrink-0 opacity-75 text-white/90">
                                <!-- Google -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Google</span>
                                </div>
                                <!-- Spotify -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Spotify</span>
                                </div>
                                <!-- Stripe -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">Stripe</span>
                                </div>
                                <!-- YouTube -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">YouTube</span>
                                </div>
                                <!-- GitHub -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                    <span class="font-semibold text-sm tracking-tight">GitHub</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div 
                class="w-full md:w-[55%] min-h-[650px] md:h-full p-6 sm:px-10 sm:py-10 md:pt-[60px] flex flex-col transition-all duration-700 ease-in-out z-10 md:overflow-y-auto custom-scrollbar"
                :class="(page.component === 'auth/Register' || page.component === 'auth/OAuthRegister') ? 'md:ml-0' : 'md:ml-[45%]'"
            >
                <div class="mb-5">
                    <svg class="w-6 h-6 text-[#2563eb] mb-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L13.1 8.5L19 7L16 12L19 17L13.1 15.5L12 22L10.9 15.5L5 17L8 12L5 7L10.9 8.5L12 2Z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        {{ title || (page.component === 'auth/Register' ? 'Create an account' : (page.component === 'auth/OAuthRegister' ? 'Selesaikan Pendaftaran' : 'Welcome back')) }}
                    </h2>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        {{ description || (page.component === 'auth/OAuthRegister' ? 'Lengkapi detail profil Anda untuk menautkan akun Google dan mengakses dasbor fungsionaris.' : 'Access your tasks, notes, and projects anytime, anywhere - and keep everything flowing in one place.') }}
                    </p>
                </div>
                
                <div class="flex-1 w-full max-w-sm mx-auto">
                    <slot />
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.animate-marquee {
  animation: marquee 25s linear infinite;
  width: max-content;
}
.pause:hover {
  animation-play-state: paused;
}
.mask-edges {
  mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>
