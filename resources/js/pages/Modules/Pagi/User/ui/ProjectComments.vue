<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { computed, ref, watch } from "vue";
import { getInitialsAvatar } from "@/composables/useInitials";
import OptimizedImage from "./OptimizedImage.vue";

interface Props {
	portfolio?: any;
	checkUsername: () => boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<(e: "update-comments", comments: any[]) => void>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const siteSettings = computed<Record<string, any>>(() => (page.props as any).siteSettings || {});

// Feature & Audience Gates
const commentsEnabled = computed(() => {
	const val = siteSettings.value.pagi_enable_comments;
	if (val === false || val === "false") return false;
	return true;
});

const commentAudience = computed(() => {
	return siteSettings.value.pagi_comment_audience || "mahasiswa_mitra";
});

const userType = computed(() => {
	return (authUser.value?.user_type || "").toLowerCase();
});

const canUserComment = computed(() => {
	if (!authUser.value) return false;
	if (!commentsEnabled.value) return false;

	const audience = commentAudience.value;
	if (audience === "mahasiswa_only") {
		return userType.value === "mahasiswa";
	} else if (audience === "mahasiswa_mitra") {
		return ["mahasiswa", "mitra"].includes(userType.value);
	}
	return true;
});

const audienceRestrictionNotice = computed(() => {
	if (!commentsEnabled.value) {
		return "Fitur komentar pada karya sedang dinonaktifkan oleh administrator.";
	}
	if (!authUser.value) {
		return "Silakan login untuk memberikan komentar pada karya ini.";
	}
	if (commentAudience.value === "mahasiswa_only") {
		return "Hanya mahasiswa aktif yang diizinkan untuk memberikan komentar pada karya di modul PAGI.";
	}
	if (commentAudience.value === "mahasiswa_mitra") {
		return "Hanya mahasiswa dan mitra terverifikasi yang diizinkan untuk memberikan komentar.";
	}
	return "";
});

// Role Badge Helper
const getRoleBadge = (commentUserType?: string) => {
	const type = (commentUserType || "").toLowerCase();
	if (type === "mahasiswa") {
		return { label: "Mahasiswa", class: "bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 border-blue-100 dark:border-blue-900/30" };
	}
	if (type === "mitra") {
		return { label: "Mitra", class: "bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 border-purple-100 dark:border-purple-900/30" };
	}
	if (type === "dosen") {
		return { label: "Dosen", class: "bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30" };
	}
	if (["super-admin", "admin", "admin-universitas", "admin-akademik"].includes(type)) {
		return { label: "Admin", class: "bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-100 dark:border-amber-900/30" };
	}
	return null;
};

// Comments System State & Actions
const commentsList = ref<any[]>([]);
const commentText = ref("");
const isSubmittingComment = ref(false);
const commentErrorMessage = ref("");
const commentTextareaRef = ref<HTMLTextAreaElement | null>(null);

// Pagination
const showAllComments = ref(false);
const visibleComments = computed(() => {
	return showAllComments.value
		? commentsList.value
		: commentsList.value.slice(0, 10);
});
const hasMoreComments = computed(() => {
	return commentsList.value.length > 10 && !showAllComments.value;
});

// Per-comment reply state
const replyStates = ref<
	Record<
		string,
		{
			open: boolean;
			text: string;
			submitting: boolean;
			showReplies: boolean;
			replyToUserId?: number;
			error?: string;
		}
	>
>({});

const getReplyState = (commentId: string) => {
	if (!replyStates.value[commentId]) {
		replyStates.value[commentId] = {
			open: false,
			text: "",
			submitting: false,
			showReplies: true,
			replyToUserId: undefined,
			error: "",
		};
	}
	return replyStates.value[commentId];
};

const toggleReplyBox = (
	commentId: string,
	prefillName?: string,
	replyToUserId?: number,
) => {
	const state = getReplyState(commentId);
	state.error = "";
	if (prefillName) {
		state.open = true;
		state.text = `@${prefillName} `;
		state.replyToUserId = replyToUserId;
	} else {
		state.open = !state.open;
		if (!state.open) {
			state.replyToUserId = undefined;
		}
	}
};

const isCommentLiked = (c: any) => {
	const currentUserId = authUser.value?.id;
	if (!currentUserId || !Array.isArray(c.likes)) return false;
	return c.likes.map(Number).includes(Number(currentUserId));
};

const getCommentLikesCount = (c: any) => {
	return Array.isArray(c.likes) ? c.likes.length : 0;
};

const handleLikeComment = async (commentId: string) => {
	if (!authUser.value) {
		commentErrorMessage.value = "Silakan login terlebih dahulu untuk menyukai komentar.";
		return;
	}
	if (!props.checkUsername()) return;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment/${commentId}/like`,
		);
		commentsList.value = res.data.comments;
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
		emit("update-comments", res.data.comments);
	} catch (e: any) {
		console.error("Like comment error", e);
	}
};

const handleLikeReply = async (commentId: string, replyId: string) => {
	if (!authUser.value) {
		commentErrorMessage.value = "Silakan login terlebih dahulu untuk menyukai balasan.";
		return;
	}
	if (!props.checkUsername()) return;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment/${commentId}/reply/${replyId}/like`,
		);
		commentsList.value = res.data.comments;
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
		emit("update-comments", res.data.comments);
	} catch (e: any) {
		console.error("Like reply error", e);
	}
};

const handleReplySubmit = async (commentId: string) => {
	const state = getReplyState(commentId);
	state.error = "";

	if (!authUser.value) {
		state.error = "Silakan login terlebih dahulu untuk membalas.";
		return;
	}
	if (!canUserComment.value) {
		state.error = audienceRestrictionNotice.value;
		return;
	}
	if (!props.checkUsername()) return;
	if (!state.text.trim()) return;

	if (!props.portfolio?.id) {
		const comment = commentsList.value.find((c) => c.id === commentId);
		if (comment) {
			if (!comment.replies) comment.replies = [];
			comment.replies.push({
				id: Date.now().toString(),
				name: authUser.value.name,
				user_type: authUser.value.user_type,
				avatar: authUser.value.avatar || getInitialsAvatar(authUser.value.name),
				body: state.text,
				time: "baru saja",
				likes: [],
			});
		}
		state.text = "";
		state.open = false;
		state.replyToUserId = undefined;
		emit("update-comments", commentsList.value);
		return;
	}

	state.submitting = true;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment/${commentId}/reply`,
			{
				body: state.text,
				reply_to_user_id: state.replyToUserId,
			},
		);
		commentsList.value = res.data.comments;
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
		state.text = "";
		state.open = false;
		state.replyToUserId = undefined;
		state.showReplies = true;
		emit("update-comments", res.data.comments);
	} catch (e: any) {
		console.error("Reply error", e);
		state.error = e.response?.data?.message || "Gagal mempublikasikan balasan.";
	} finally {
		state.submitting = false;
	}
};

watch(
	() => props.portfolio?.comments,
	(newComments) => {
		if (Array.isArray(newComments)) {
			commentsList.value = [...newComments];
		} else {
			commentsList.value = [];
		}
	},
	{ immediate: true, deep: true },
);

const handleCommentSubmit = async () => {
	commentErrorMessage.value = "";

	if (!authUser.value) {
		commentErrorMessage.value = "Silakan login terlebih dahulu untuk memberikan komentar.";
		return;
	}
	if (!canUserComment.value) {
		commentErrorMessage.value = audienceRestrictionNotice.value;
		return;
	}
	if (!props.checkUsername()) return;
	if (!commentText.value.trim()) return;

	if (!props.portfolio?.id) {
		const newComment = {
			id: Date.now().toString(),
			name: authUser.value.name,
			user_type: authUser.value.user_type,
			avatar: authUser.value.avatar || getInitialsAvatar(authUser.value.name),
			body: commentText.value,
			time: "baru saja",
			likes: [],
			replies: [],
		};
		commentsList.value.push(newComment);
		commentText.value = "";
		emit("update-comments", commentsList.value);
		return;
	}

	isSubmittingComment.value = true;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment`,
			{
				body: commentText.value,
			},
		);
		commentsList.value = res.data.comments;
		commentText.value = "";
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
		emit("update-comments", res.data.comments);
	} catch (e: any) {
		console.error("Comment error", e);
		commentErrorMessage.value = e.response?.data?.message || "Gagal mengirimkan komentar.";
	} finally {
		isSubmittingComment.value = false;
	}
};
</script>

<template>
	<div class="lg:col-span-2 flex flex-col">
		
		<!-- Comment Input Box / Restriction Banner -->
		<div v-if="canUserComment" class="flex gap-4 items-start w-full">
			<div class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-zinc-200 dark:border-zinc-800">
				<OptimizedImage :src="page.props.auth?.user?.avatar || getInitialsAvatar(page.props.auth?.user?.name || 'User')" className="w-full h-full object-cover" alt="User Avatar" />
			</div>
			
			<div class="flex-grow flex flex-col gap-2">
				<div v-if="commentErrorMessage" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 text-xs font-semibold text-rose-600 dark:text-rose-400">
					{{ commentErrorMessage }}
				</div>
				<textarea ref="commentTextareaRef" v-model="commentText" maxlength="1000" rows="3" class="w-full border border-zinc-200 dark:border-zinc-800 rounded-md p-3 text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 bg-white dark:bg-slate-900 placeholder-zinc-400 text-zinc-800 dark:text-zinc-100 resize-none font-medium" placeholder="Tuliskan apresiasi atau tanggapan Anda tentang karya ini..."></textarea>
				<div class="flex items-center justify-between">
					<span class="text-[11px] font-medium text-zinc-400">
						{{ commentText.length }}/1000 karakter
					</span>
					<button type="button" @click="handleCommentSubmit" :disabled="isSubmittingComment || !commentText.trim()" class="px-5 py-2 rounded-full bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-xs font-bold shadow-xs transition-colors disabled:opacity-30 disabled:cursor-not-allowed border-none cursor-pointer">
						{{ isSubmittingComment ? 'Mengirim...' : 'Kirim Komentar' }}
					</button>
				</div>
			</div>
		</div>

		<!-- Restriction Banner (when user cannot comment) -->
		<div v-else class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between gap-3">
			<div class="flex items-center gap-3">
				<div class="h-9 w-9 rounded-xl bg-amber-100 dark:bg-amber-950/40 flex items-center justify-center shrink-0 text-amber-600 dark:text-amber-400 font-bold text-base">
					💬
				</div>
				<div>
					<p class="text-xs font-bold text-slate-800 dark:text-zinc-200">Komentar Dibatasi</p>
					<p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">{{ audienceRestrictionNotice }}</p>
				</div>
			</div>
			<a v-if="!authUser" href="/login" class="shrink-0 px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors no-underline">
				Login Sekarang
			</a>
		</div>

		<!-- Divider Line -->
		<div class="w-full h-px bg-zinc-200 dark:bg-zinc-800 my-6"></div>

		<!-- Dynamic Comments List -->
		<div v-if="commentsList.length > 0" class="flex flex-col gap-0">
			<div v-for="c in visibleComments" :key="c.id" class="w-full py-5 border-b border-zinc-100 dark:border-zinc-800/60 last:border-none">
				<!-- Main comment row -->
				<div class="flex items-start gap-3 w-full">
					<!-- Clickable avatar -->
					<a :href="c.pagi_username ? `/pagi/${c.pagi_username}` : (c.user_id ? `/pagi/profile/${c.user_id}` : '#')" class="cursor-pointer block shrink-0 w-9 h-9 rounded-full overflow-hidden border border-zinc-200 dark:border-zinc-700">
						<OptimizedImage :src="c.avatar || getInitialsAvatar(c.name)" className="w-full h-full object-cover" alt="Comment author avatar" />
					</a>
					<div class="flex-grow min-w-0">
						<!-- Name + Role Badge + time -->
						<div class="flex items-center flex-wrap gap-x-2 gap-y-1 mb-1">
							<a :href="c.pagi_username ? `/pagi/${c.pagi_username}` : (c.user_id ? `/pagi/profile/${c.user_id}` : '#')" class="text-sm font-black text-zinc-900 dark:text-zinc-100 hover:underline cursor-pointer leading-none">{{ c.pagi_username ? c.pagi_username : c.name }}</a>
							
							<!-- Role badge -->
							<span v-if="getRoleBadge(c.user_type)" :class="['inline-flex items-center rounded-full px-1.5 py-0.2 text-[9px] font-bold border', getRoleBadge(c.user_type)?.class]">
								{{ getRoleBadge(c.user_type)?.label }}
							</span>

							<span class="text-zinc-300 dark:border-zinc-600 text-xs">·</span>
							<span class="text-xs text-zinc-400">{{ c.time }}</span>
						</div>
						<p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed mb-2">{{ c.body }}</p>
						<!-- Action row: Reply + show replies count -->
						<div class="flex items-center gap-3">
							<button v-if="canUserComment" type="button" @click="toggleReplyBox(c.id, c.pagi_username || c.name)" class="text-xs font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors cursor-pointer bg-transparent border-none p-0">
								{{ getReplyState(c.id).open ? 'Batal' : 'Balas' }}
							</button>
							<button v-if="c.replies && c.replies.length > 0" type="button" @click="getReplyState(c.id).showReplies = !getReplyState(c.id).showReplies" class="text-xs font-bold text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer bg-transparent border-none p-0 flex items-center gap-1">
								<svg class="w-3 h-3 transition-transform" :class="getReplyState(c.id).showReplies ? 'rotate-90' : 'rotate-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
								{{ c.replies.length }} balasan
							</button>
						</div>
					</div>
					<!-- Comment Like -->
					<div class="flex flex-col items-center justify-center gap-0.5 shrink-0 pt-0.5 ml-1">
						<button type="button" @click="handleLikeComment(c.id)" class="transition-colors p-1 bg-transparent border-none cursor-pointer flex items-center justify-center" :class="isCommentLiked(c) ? 'text-rose-500' : 'text-zinc-350 hover:text-rose-500'">
							<svg v-if="isCommentLiked(c)" class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
							<svg v-else class="w-3.5 h-3.5 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
						</button>
						<span v-if="getCommentLikesCount(c) > 0" class="text-[10px] font-bold" :class="isCommentLiked(c) ? 'text-rose-500' : 'text-zinc-400'">{{ getCommentLikesCount(c) }}</span>
					</div>
				</div>

				<!-- Inline Reply Box -->
				<div v-if="getReplyState(c.id).open" class="mt-3 ml-12 flex items-start gap-2.5">
					<div class="w-7 h-7 rounded-full overflow-hidden shrink-0 border border-zinc-200 dark:border-zinc-700">
						<OptimizedImage :src="page.props.auth?.user?.avatar || getInitialsAvatar(page.props.auth?.user?.name || 'User')" className="w-full h-full object-cover" alt="You" />
					</div>
					<div class="flex-grow">
						<div v-if="getReplyState(c.id).error" class="mb-2 p-2 rounded-lg bg-rose-50 dark:bg-rose-950/30 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
							{{ getReplyState(c.id).error }}
						</div>
						<textarea
							v-model="getReplyState(c.id).text"
							maxlength="1000"
							rows="2"
							class="w-full border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white dark:bg-slate-900 placeholder-zinc-400 text-zinc-800 dark:text-zinc-100 resize-none font-medium"
							:placeholder="`Balas @${c.pagi_username || c.name}…`"
							@keydown.enter.exact.prevent="handleReplySubmit(c.id)"
						></textarea>
						<div class="flex items-center justify-between mt-1.5">
							<span class="text-[10px] text-zinc-400">{{ getReplyState(c.id).text.length }}/1000</span>
							<div class="flex items-center gap-2">
								<button type="button" @click="getReplyState(c.id).open = false" class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 font-semibold cursor-pointer bg-transparent border-none p-0">Batal</button>
								<button type="button" @click="handleReplySubmit(c.id)" :disabled="getReplyState(c.id).submitting || !getReplyState(c.id).text.trim()" class="px-4 py-1.5 rounded-full bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold transition-colors disabled:opacity-30 disabled:cursor-not-allowed border-none cursor-pointer hover:bg-zinc-700 dark:hover:bg-zinc-100">
									{{ getReplyState(c.id).submitting ? 'Mengirim…' : 'Kirim' }}
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Nested Replies -->
				<div v-if="c.replies && c.replies.length > 0 && getReplyState(c.id).showReplies" class="mt-3 ml-12 flex flex-col gap-4">
					<div v-for="r in c.replies" :key="r.id" class="flex items-start gap-2.5">
						<a :href="r.pagi_username ? `/pagi/${r.pagi_username}` : (r.user_id ? `/pagi/profile/${r.user_id}` : '#')" class="block shrink-0 w-7 h-7 rounded-full overflow-hidden border border-zinc-200 dark:border-zinc-700">
							<OptimizedImage :src="r.avatar || getInitialsAvatar(r.name)" className="w-full h-full object-cover" alt="Reply author avatar" />
						</a>
						<div class="flex-grow min-w-0">
							<div class="flex items-center flex-wrap gap-x-2 gap-y-1 mb-0.5">
								<a :href="r.pagi_username ? `/pagi/${r.pagi_username}` : (r.user_id ? `/pagi/profile/${r.user_id}` : '#')" class="text-xs font-black text-zinc-900 dark:text-zinc-100 hover:underline cursor-pointer leading-none">{{ r.pagi_username ? r.pagi_username : r.name }}</a>
								
								<!-- Role badge -->
								<span v-if="getRoleBadge(r.user_type)" :class="['inline-flex items-center rounded-full px-1.5 py-0.2 text-[8.5px] font-bold border', getRoleBadge(r.user_type)?.class]">
									{{ getRoleBadge(r.user_type)?.label }}
								</span>

								<span class="text-zinc-300 dark:text-zinc-600 text-[10px]">·</span>
								<span class="text-[10px] text-zinc-400">{{ r.time }}</span>
							</div>
							<p class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed mb-1">{{ r.body }}</p>
							<div class="flex items-center gap-3">
								<button v-if="canUserComment" type="button" @click="toggleReplyBox(c.id, r.pagi_username || r.name, r.user_id)" class="text-[10px] font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors cursor-pointer bg-transparent border-none p-0">
									Balas
								</button>
							</div>
						</div>
						<!-- Reply Like -->
						<div class="flex flex-col items-center gap-0.5 shrink-0 pt-0.5">
							<button type="button" @click="handleLikeReply(c.id, r.id)" class="p-1 bg-transparent border-none cursor-pointer transition-colors" :class="(Array.isArray(r.likes) && r.likes.map(Number).includes(Number(authUser?.id))) ? 'text-rose-500' : 'text-zinc-350 hover:text-rose-500'">
								<svg v-if="Array.isArray(r.likes) && r.likes.map(Number).includes(Number(authUser?.id))" class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
								<svg v-else class="w-3 h-3 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
							</button>
							<span v-if="Array.isArray(r.likes) && r.likes.length > 0" class="text-[9px] font-bold" :class="r.likes.map(Number).includes(Number(authUser?.id)) ? 'text-rose-500' : 'text-zinc-400'">{{ r.likes.length }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-else class="text-center py-6">
			<p class="text-sm text-zinc-400 font-medium">Belum ada komentar. Jadilah yang pertama!</p>
		</div>

		<!-- Show More Comments -->
		<button v-if="hasMoreComments" @click="showAllComments = true" type="button" class="w-full py-2.5 mt-4 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-950 dark:text-zinc-50 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-xs font-bold transition-all cursor-pointer bg-white dark:bg-slate-900">
			Tampilkan lebih banyak komentar
		</button>
	</div>
</template>
