<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Loader2 } from "lucide-vue-next";
import axios from "axios";
import Modal from "../../ui/Modal.vue";
import OptimizedImage from "../../ui/OptimizedImage.vue";

const props = defineProps<{
	show: boolean;
	type: "followers" | "following";
	userId: number;
	isOwnProfile: boolean;
}>();

const emit = defineEmits(["close", "toast", "following-count-changed"]);

const page = usePage();

const followersList = ref<any[]>([]);
const followingList = ref<any[]>([]);
const isLoadingRelations = ref(false);
const FollbackInProgress = ref<Record<number, boolean>>({});
const relationsSearchQuery = ref("");

const filteredRelations = computed(() => {
	const list = props.type === "followers" ? followersList.value : followingList.value;
	if (!relationsSearchQuery.value.trim()) return list;
	const q = relationsSearchQuery.value.toLowerCase().trim();
	return list.filter((item: any) => item.name?.toLowerCase().includes(q));
});

const isFollowingBack = (senderId: number) => {
	const following = (page.props.auth?.user as any)?.metadata?.following ?? [];
	return following.includes(senderId);
};

const fetchRelations = async () => {
	isLoadingRelations.value = true;
	try {
		const res = await axios.get(`/pagi/users/${props.userId}/relations`);
		followersList.value = res.data.followers || [];
		followingList.value = res.data.following || [];
	} catch (e) {
		console.error("Failed to load relations:", e);
		emit("toast", "Gagal memuat data relasi.", "error");
	} finally {
		isLoadingRelations.value = false;
	}
};

watch(() => props.show, (newVal) => {
	if (newVal) {
		relationsSearchQuery.value = "";
		fetchRelations();
	}
});

const toggleFollowRelation = async (rel: any) => {
	const senderId = rel.id;
	if (!senderId) return;

	FollbackInProgress.value[senderId] = true;
	try {
		const res = await axios.post(`/pagi/users/${senderId}/follow`);
		const authUser = page.props.auth?.user as any;
		if (!authUser) return;

		const following = authUser.metadata?.following ?? [];
		if (res.data.following) {
			if (!following.includes(senderId)) following.push(senderId);
			emit("toast", `Mulai mengikuti ${rel.name}!`, "success");
		} else {
			const idx = following.indexOf(senderId);
			if (idx > -1) following.splice(idx, 1);
			emit("toast", `Berhenti mengikuti ${rel.name}.`, "info");
		}
		if (!authUser.metadata) {
			authUser.metadata = {};
		}
		authUser.metadata.following = following;

		// Reactively update following count in parent if own profile
		if (props.isOwnProfile) {
			emit("following-count-changed", res.data.following);
		}
	} catch (e) {
		console.error("Relation follow toggle failed:", e);
		emit("toast", "Gagal memperbarui status ikuti.", "error");
	} finally {
		FollbackInProgress.value[senderId] = false;
	}
};
</script>

<template>
	<Modal :show="show" :title="type === 'followers' ? 'Pengikut' : 'Mengikuti'" maxWidth="md" @close="emit('close')">
		<div class="space-y-4 my-2">
			<!-- Search box to filter users dynamically -->
			<div class="relative">
				<input 
					v-model="relationsSearchQuery" 
					type="text" 
					:placeholder="type === 'followers' ? 'Cari pengikut...' : 'Cari akun yang diikuti...'"
					class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
				/>
				<svg class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
				</svg>
			</div>

			<!-- Loader -->
			<div v-if="isLoadingRelations" class="flex flex-col items-center justify-center py-12 gap-3">
				<Loader2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400 animate-spin" />
				<span class="text-xs font-bold text-slate-400 dark:text-slate-505 uppercase tracking-wider">Memuat data...</span>
			</div>

			<!-- Content List -->
			<div v-else class="max-h-[380px] overflow-y-auto pr-1 space-y-3 scrollbar-thin scrollbar-thumb-slate-200">
				<div v-if="filteredRelations.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-slate-400 dark:text-slate-500 gap-3">
					<svg class="w-12 h-12 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
					</svg>
					<p class="text-xs font-bold uppercase tracking-wider">
						{{ type === 'followers' ? 'Tidak ada pengikut yang cocok' : 'Tidak ada akun diikuti yang cocok' }}
					</p>
				</div>

				<div 
					v-else 
					v-for="rel in filteredRelations" 
					:key="rel.id" 
					class="flex items-center justify-between gap-3 p-2.5 rounded-2xl border border-slate-105 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-all duration-200"
				>
					<!-- User profile block -->
					<div class="flex items-center gap-3 min-w-0 flex-1">
						<Link 
							:href="rel.pagi_username ? '/pagi/' + rel.pagi_username : '/pagi/profile/' + rel.id" 
							@click="emit('close')"
							class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 overflow-hidden flex items-center justify-center shrink-0 shadow-3xs cursor-pointer hover:scale-102 transition-transform"
						>
							<OptimizedImage v-if="rel.foto_path" :src="rel.foto_path.startsWith('http') ? rel.foto_path : '/storage/' + rel.foto_path" alt="Avatar" className="w-full h-full object-cover" />
							<div v-else class="w-full h-full bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center">
								<span class="text-xs font-bold text-indigo-500 dark:text-indigo-400">{{ rel.name.charAt(0).toUpperCase() }}</span>
							</div>
						</Link>

						<div class="min-w-0 flex-1">
							<div class="flex items-center gap-1.5">
								<Link 
									:href="rel.pagi_username ? '/pagi/' + rel.pagi_username : '/pagi/profile/' + rel.id" 
									@click="emit('close')"
									class="text-xs font-black text-slate-800 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
								>
									{{ rel.name }}
								</Link>
								<img src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Premium Account" alt="Verified Badge" />
							</div>
							<p class="text-[10px] font-semibold text-slate-400 dark:text-slate-505 truncate mt-0.5">
								{{ rel.role_title || 'Top Creator' }}
							</p>
						</div>
					</div>

					<!-- Action Button (Follow / Follback) - only show if NOT the logged in user themselves -->
					<div v-if="page.props.auth?.user && (page.props.auth.user as any).id !== rel.id" class="shrink-0">
						<button 
							@click="toggleFollowRelation(rel)" 
							:disabled="FollbackInProgress[rel.id]"
							class="h-8 px-4 rounded-full font-black text-[10px] uppercase tracking-wider cursor-pointer shadow-3xs active:scale-95 transition-all duration-200 flex items-center justify-center gap-1 disabled:opacity-50"
							:class="isFollowingBack(rel.id)
								? 'bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300/40 dark:border-slate-700/40' 
								: 'bg-indigo-600 hover:bg-indigo-700 text-white'"
						>
							<Loader2 v-if="FollbackInProgress[rel.id]" class="w-3 h-3 animate-spin mr-1" />
							<span>{{ isFollowingBack(rel.id) ? 'Mengikuti' : 'Ikuti' }}</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</Modal>
</template>
