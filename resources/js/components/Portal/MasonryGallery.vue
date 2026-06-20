<script setup>
const items = [
	{ id: "1618005182384-a83a8bd57fbe", w: 600, h: 400, delay: 0 },
	{ id: "1523275335684-37898b6baf30", w: 600, h: 800, delay: 100 },
	{ id: "1460925895917-afdab827c52f", w: 600, h: 400, delay: 200 },
	{ id: "1557672172-298e090bd0f1", w: 600, h: 900, delay: 300 },
	{ id: "1483985988355-763728e1935b", w: 600, h: 700, delay: 150 },
	{ id: "1611162617474-5b21e879e113", w: 600, h: 500, delay: 250 },
	{ id: "1533473359331-0135ef1b58bf", w: 600, h: 1000, delay: 50 },
	{ id: "1441984904996-e0b6ba687e04", w: 600, h: 600, delay: 100 },
	{ id: "1551288049-bebda4e38f71", w: 600, h: 850, delay: 200 },
	{ id: "1504711434969-e33886168f5c", w: 600, h: 400, delay: 300 },
	{ id: "1550684848-fac1c5b4e853", w: 600, h: 700, delay: 150 },
	{ id: "1478147424044-caebd12467b7", w: 600, h: 500, delay: 250 },
	{ id: "1498050108023-c5249f4df085", w: 600, h: 900, delay: 50 },
	{ id: "1581291518633-83b4ebd1d83e", w: 600, h: 600, delay: 100 },
	{ id: "1555421689-491a97ff2040", w: 600, h: 500, delay: 200 }
];

const getUnsplashUrl = (id, w) => {
	return `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&q=80`;
};

const getUnsplashSrcset = (id) => {
	return `${getUnsplashUrl(id, 250)} 250w,
			${getUnsplashUrl(id, 500)} 500w,
			${getUnsplashUrl(id, 750)} 750w`;
};
</script>

<template>
    <!-- SHOWCASE GALLERY (TAILWIND STYLE MASONRY) -->
    <section class="hide-animate relative overflow-hidden bg-gray-50 py-24 sm:py-32 fade-in border-t border-gray-100" v-once>
        <div class="relative z-10 mx-auto max-w-360 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 lg:mb-20 hide-animate slide-up">
                <h2 class="text-4xl md:text-5xl lg:text-[4rem] font-extrabold tracking-tight text-slate-900 leading-tight mb-6">
                    Platform pilihan untuk skala besar.
                </h2>
                <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto font-medium">
                    Adaptif, super cepat, dan dipercaya oleh seluruh civitas akademika untuk menunjang produktivitas.
                </p>
            </div>

            <!-- Tailwind CSS Columns for Masonry -->
            <div class="columns-2 sm:columns-3 lg:columns-4 xl:columns-5 gap-4 lg:gap-6 space-y-4 lg:space-y-6 relative">
                
                <!-- Items dynamically rendered with v-for -->
                <div 
                    v-for="(item, idx) in items" 
                    :key="item.id" 
                    class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up"
                    :style="{ transitionDelay: `${item.delay}ms` }"
                >
                    <img 
                        :src="getUnsplashUrl(item.id, 500)" 
                        :srcset="getUnsplashSrcset(item.id)"
                        sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 250px"
                        class="w-full h-auto transform transition duration-700 group-hover:scale-105 group-hover:brightness-95" 
                        alt="Showcase image" 
                        :width="item.w" 
                        :height="item.h" 
                        loading="lazy" 
                        decoding="async"
                    />
                </div>

                <!-- Gradient Overlay at bottom for soft fade -->
                <div class="absolute inset-x-0 bottom-0 h-64 bg-linear-to-t from-gray-50 via-gray-50/80 to-transparent pointer-events-none z-10"></div>
            </div>

            <div class="relative z-20 mt-8 flex justify-center hide-animate slide-up">
                <button class="rounded-full bg-[#111827] px-8 py-3.5 text-sm font-semibold text-white shadow-xl hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black transition-all hover:-translate-y-1">
                    Jelajahi Lebih Banyak Modul
                </button>
            </div>
        </div>
    </section>
</template>
