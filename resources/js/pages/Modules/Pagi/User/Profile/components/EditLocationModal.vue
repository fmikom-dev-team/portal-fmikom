<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue";
import { MapPin, X, Loader2 } from "lucide-vue-next";
import Modal from "../../ui/Modal.vue";

const props = defineProps<{
	show: boolean;
	form: any;
	userLocation?: string;
}>();

const emit = defineEmits(["close", "submit"]);

const searchQuery = ref("");
const showDropdown = ref(false);

const cities = [
	"Banyumas, Jawa Tengah",
	"Purwokerto, Jawa Tengah",
	"Cilacap, Jawa Tengah",
	"Purbalingga, Jawa Tengah",
	"Banjarnegara, Jawa Tengah",
	"Semarang, Jawa Tengah",
	"Surakarta (Solo), Jawa Tengah",
	"Yogyakarta, DIY",
	"Sleman, DIY",
	"Bantul, DIY",
	"Jakarta Pusat, DKI Jakarta",
	"Jakarta Selatan, DKI Jakarta",
	"Jakarta Barat, DKI Jakarta",
	"Jakarta Timur, DKI Jakarta",
	"Jakarta Utara, DKI Jakarta",
	"Bandung, Jawa Barat",
	"Bogor, Jawa Barat",
	"Depok, Jawa Barat",
	"Tangerang, Banten",
	"Tangerang Selatan, Banten",
	"Bekasi, Jawa Barat",
	"Surabaya, Jawa Timur",
	"Malang, Jawa Timur",
	"Sidoarjo, Jawa Timur",
	"Gresik, Jawa Timur",
	"Medan, Sumatera Utara",
	"Palembang, Sumatera Selatan",
	"Pekanbaru, Riau",
	"Padang, Sumatera Barat",
	"Bandar Lampung, Lampung",
	"Banda Aceh, Aceh",
	"Jambi, Jambi",
	"Bengkulu, Bengkulu",
	"Pontianak, Kalimantan Barat",
	"Banjarmasin, Kalimantan Selatan",
	"Balikpapan, Kalimantan Timur",
	"Samarinda, Kalimantan Timur",
	"Makassar, Sulawesi Selatan",
	"Manado, Sulawesi Utara",
	"Denpasar, Bali",
	"Mataram, Nusa Tenggara Barat",
	"Kupang, Nusa Tenggara Timur",
	"Ambon, Maluku",
	"Jayapura, Papua"
];

const filteredCities = computed(() => {
	if (!searchQuery.value) return cities;
	const query = searchQuery.value.toLowerCase();
	return cities.filter(city => city.toLowerCase().includes(query));
});

const selectCity = (city: string) => {
	props.form.location = city;
	searchQuery.value = city;
	showDropdown.value = false;
};

const handleSubmit = () => {
	if (searchQuery.value && !props.form.location) {
		props.form.location = searchQuery.value;
	}
	emit("submit");
};

watch(() => props.show, (newVal) => {
	if (newVal) {
		searchQuery.value = props.form.location || props.userLocation || "";
	}
});

onMounted(() => {
	searchQuery.value = props.form.location || props.userLocation || "";
});
</script>

<template>
	<Modal :show="show" title="Update Location" maxWidth="md" @close="emit('close')">
		<form @submit.prevent="handleSubmit" class="space-y-6">
			<div class="relative text-left" id="location_search_container">
				<label for="location_search" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Search City (Indonesia)</label>
				<div class="relative flex items-center">
					<span class="absolute left-4 text-slate-400"><MapPin class="w-3.5 h-3.5" /></span>
					<input 
						id="location_search" 
						v-model="searchQuery" 
						type="text" 
						placeholder="Type to search (e.g. Purwokerto, Jakarta...)"
						@focus="showDropdown = true"
						class="w-full h-11 pl-10 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-450 focus:outline-hidden focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800 dark:focus:border-slate-200 transition-all shadow-2xs"
					/>
					<button 
						v-if="searchQuery" 
						type="button" 
						@click="searchQuery = ''; form.location = ''; showDropdown = true" 
						class="absolute right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
					>
						<X class="w-3.5 h-3.5" />
					</button>
				</div>

				<!-- Searchable Dropdown for Cities -->
				<div 
					v-if="showDropdown && filteredCities.length > 0" 
					class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg max-h-60 overflow-y-auto"
				>
					<ul class="py-1 m-0 list-none p-0">
						<li 
							v-for="city in filteredCities" 
							:key="city" 
							@mousedown="selectCity(city)"
							class="px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer flex items-center gap-2"
						>
							<MapPin class="w-3.5 h-3.5 text-slate-400" />
							<span>{{ city }}</span>
						</li>
					</ul>
				</div>
			</div>

			<!-- Actions -->
			<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
				<button 
					type="button"
					@click="emit('close')"
					class="h-11 px-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-xs cursor-pointer"
				>
					Cancel
				</button>
				<button 
					type="submit"
					:disabled="form.processing"
					class="h-11 px-6 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-50 transition-all shadow-xs flex items-center gap-2 cursor-pointer"
				>
					<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
					Save changes
				</button>
			</div>
		</form>
	</Modal>
</template>
