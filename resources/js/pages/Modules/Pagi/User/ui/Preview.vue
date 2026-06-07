<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	LayoutGrid,
	Link2,
	Linkedin,
	Paperclip,
	PlaySquare,
	Plus,
	Share2,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { sanitize } from "@/composables/useSanitize";
import Modal from "./Modal.vue";
import OptimizedImage from "./OptimizedImage.vue";
import ShareWorkModal from "./ShareWorkModal.vue";
import VideoLazy from "./VideoLazy.vue";

const props = withDefaults(
	defineProps<{
		title: string;
		content: Array<any>;
		coverImage?: string | null;
		portfolio?: any;
		canvasBgColor?: string;
		canvasTextColor?: string;
		canvasBorderColor?: string;
		globalSpacing?: number;
		description?: string;
		category?: string;
		toolsUsed?: string;
		tags?: string | string[];
	}>(),
	{
		canvasBgColor: "#ffffff",
		canvasTextColor: "#111827",
		canvasBorderColor: "#e2e8f0",
		globalSpacing: 50,
		description: "",
		category: "",
		toolsUsed: "",
		tags: "",
	},
);

const emit = defineEmits<{
	(e: "close"): void;
	(e: "select-portfolio", project: any): void;
}>();

const getCoverFit = () => {
	const content = props.content || props.portfolio?.content;
	if (!content || !Array.isArray(content)) return "cover";
	const settings = content.find((b: any) => b && b.type === "settings");
	if (settings?.coverFit) return settings.coverFit;
	const details = content.find((b: any) => b && b.type === "featured_details");
	if (details?.cover_fit) return details.cover_fit;
	return "cover";
};

const showFloatingPill = ref(true);

const page = usePage();
const authorName = computed(() => {
	return (
		props.portfolio?.user?.name ||
		page.props.auth?.user?.name ||
		"Sangars Beraksi"
	);
});
const authorUsername = computed(() => {
	return (
		props.portfolio?.user?.pagi_username || page.props.auth?.user?.pagi_username
	);
});
const authorAvatar = computed(() => {
	return (
		props.portfolio?.user?.avatar ||
		page.props.auth?.user?.avatar ||
		`https://ui-avatars.com/api/?name=${encodeURIComponent(authorName.value)}&background=random`
	);
});
const authorLocation = computed(() => {
	return (
		props.portfolio?.user?.location ||
		page.props.auth?.user?.location ||
		"Banyumas, Indonesia"
	);
});

const formattedPublishedDate = computed(() => {
	const dateStr = props.portfolio?.created_at;
	if (dateStr) {
		const d = new Date(dateStr);
		if (!Number.isNaN(d.getTime())) {
			return `Published: ${d.toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })}`;
		}
	}
	const today = new Date();
	return `Published: ${today.toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })}`;
});

const computedProject = computed(() => {
	if (!props.portfolio) return null;
	return {
		id: props.portfolio.id,
		title: props.title,
		image: props.coverImage,
		cover_fit: getCoverFit(),
	};
});

const computedUser = computed(() => {
	return {
		name: authorName.value,
		foto_path: authorAvatar.value,
		pagi_username: authorUsername.value,
	};
});

const spacingInPx = computed(() => {
	return (props.globalSpacing / 100) * 80;
});

const projectDescription = computed(() => {
	return props.description || props.portfolio?.description || "";
});

const projectCategoryList = computed(() => {
	const cat = props.category || props.portfolio?.category || "";
	if (!cat) return [];
	return cat
		.split(",")
		.map((c: string) => c.trim())
		.filter(Boolean);
});

const projectToolsList = computed(() => {
	const tools = props.toolsUsed || props.portfolio?.tools_used || "";
	if (!tools) return [];
	return tools
		.split(",")
		.map((t: string) => t.trim())
		.filter(Boolean);
});

const projectTagsList = computed(() => {
	const rawTags = props.tags || props.portfolio?.tags || [];
	if (Array.isArray(rawTags)) {
		return rawTags
			.map((t: any) => (typeof t === "object" ? t.name || "" : t))
			.filter(Boolean);
	}
	if (typeof rawTags === "string") {
		return rawTags
			.split(",")
			.map((t: string) => t.trim())
			.filter(Boolean);
	}
	return [];
});

const acceptedCollaborators = computed(() => {
	const collabs = props.portfolio?.resolved_collaborators || [];
	return collabs.filter((c: any) => c.status === "accepted");
});

// Safe media type check helpers
const isVideoBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith("video");
	if (block.file?.type) return block.file.type.startsWith("video");
	if (block.name) {
		const ext = block.name.split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
	}
	if (block.preview) {
		const ext = block.preview.split(".").pop()?.split("?")[0].toLowerCase();
		return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
	}
	return false;
};

const isAudioBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith("audio");
	if (block.file?.type) return block.file.type.startsWith("audio");
	if (block.name) {
		const ext = block.name.split(".").pop()?.toLowerCase();
		return ["mp3", "wav", "ogg", "aac", "flac", "m4a"].includes(ext || "");
	}
	if (block.preview) {
		const ext = block.preview.split(".").pop()?.split("?")[0].toLowerCase();
		return ["mp3", "wav", "ogg", "aac", "flac", "m4a"].includes(ext || "");
	}
	return false;
};

// Aspect Ratios Layout Engine
const aspectRatios = ref<Record<string, number>>({});
const windowWidth = ref(
	typeof window !== "undefined" ? window.innerWidth : 1200,
);

const handleResize = () => {
	windowWidth.value = window.innerWidth;
};

const normalizeSrc = (src: string) => {
	if (src.startsWith("http") || src.startsWith("blob:")) return src;
	return `/storage/${src}`;
};

const handleImageLoad = (src: string, event: Event) => {
	const img = event.target as HTMLImageElement;
	if (img.naturalWidth && img.naturalHeight) {
		aspectRatios.value = {
			...aspectRatios.value,
			[normalizeSrc(src)]: img.naturalWidth / img.naturalHeight,
		};
	}
};

const getAspectRatio = (src: string) => {
	return aspectRatios.value[normalizeSrc(src)] || 1.5;
};

const loadImageAspectRatios = (previews: string[]) => {
	previews.forEach((src) => {
		const normalized = normalizeSrc(src);
		if (aspectRatios.value[normalized]) return;
		const img = new Image();
		img.onload = () => {
			if (img.naturalWidth && img.naturalHeight) {
				aspectRatios.value = {
					...aspectRatios.value,
					[normalized]: img.naturalWidth / img.naturalHeight,
				};
			}
		};
		img.src = normalized;
	});
};

const getJustifiedLayout = (
	previews: string[],
	containerWidth: number,
	targetHeight: number,
	gap: number,
) => {
	const items = previews || [];
	if (items.length === 0) return [];

	const rows = [];
	let i = 0;
	const n = items.length;

	while (i < n) {
		let currentRow = [items[i]];
		let currentSum = getAspectRatio(items[i]);
		let j = i + 1;

		while (j < n) {
			const nextItem = items[j];
			const nextAr = getAspectRatio(nextItem);

			const totalGapsWidth = currentRow.length * gap;
			const availableWidth = containerWidth - totalGapsWidth;
			const newHeight = availableWidth / (currentSum + nextAr);

			const prevGapsWidth = (currentRow.length - 1) * gap;
			const prevHeight = (containerWidth - prevGapsWidth) / currentSum;

			if (prevHeight > targetHeight) {
				currentRow.push(nextItem);
				currentSum += nextAr;
				j++;
			} else {
				if (
					Math.abs(newHeight - targetHeight) <
					Math.abs(prevHeight - targetHeight)
				) {
					currentRow.push(nextItem);
					currentSum += nextAr;
					j++;
				} else {
					break;
				}
			}
		}

		const isLastRow = j === n;
		const totalGapsWidth = (currentRow.length - 1) * gap;
		const availableWidth = containerWidth - totalGapsWidth;
		const calculatedHeight = availableWidth / currentSum;

		if (isLastRow) {
			const canStretch = currentRow.length >= 2 && calculatedHeight <= 900;
			const finalHeight = canStretch
				? calculatedHeight
				: Math.min(calculatedHeight, targetHeight);
			rows.push({
				items: [...currentRow],
				height: finalHeight,
				isLast: !canStretch,
			});
		} else {
			rows.push({
				items: [...currentRow],
				height: calculatedHeight,
				isLast: false,
			});
		}

		i = j;
	}

	return rows;
};

const canvasContainer = ref<HTMLElement | null>(null);
const containerWidth = ref(1200);

const updateContainerWidth = () => {
	if (canvasContainer.value) {
		containerWidth.value = canvasContainer.value.clientWidth;
	}
};

const getContainerWidth = (isFullWidth: boolean) => {
	if (isFullWidth) return containerWidth.value;
	return Math.min(containerWidth.value, 896) - 48;
};

let resizeObserver: ResizeObserver | null = null;

watch(
	() => props.content,
	(newContent) => {
		if (!newContent) return;
		newContent.forEach((block) => {
			if (block.type === "photo_grid" && block.previews) {
				loadImageAspectRatios(block.previews);
			}
		});
	},
	{ deep: true, immediate: true },
);

onMounted(() => {
	if (typeof window !== "undefined") {
		window.addEventListener("resize", handleResize);
	}
	updateContainerWidth();
	if (typeof ResizeObserver !== "undefined" && canvasContainer.value) {
		resizeObserver = new ResizeObserver(() => {
			updateContainerWidth();
		});
		resizeObserver.observe(canvasContainer.value);
	}
	checkUsernameMandatory();
});

onUnmounted(() => {
	if (typeof window !== "undefined") {
		window.removeEventListener("resize", handleResize);
	}
	if (resizeObserver) {
		resizeObserver.disconnect();
	}
});

// Authentication & Creator identification
const authUser = computed(() => page.props.auth?.user);
const showUsernameWarningModal = ref(false);

const hasAccess = computed(() => {
	if (
		authUser.value &&
		authUser.value.user_type === "mahasiswa" &&
		!authUser.value.pagi_username
	) {
		return false;
	}
	return true;
});

const checkUsernameMandatory = (): boolean => {
	if (!hasAccess.value) {
		showUsernameWarningModal.value = true;
		return false;
	}
	return true;
};

const closeWarningModal = () => {
	showUsernameWarningModal.value = false;
	if (!hasAccess.value) {
		emit("close");
	}
};
const ownerId = computed(
	() => props.portfolio?.user_id || props.portfolio?.user?.id,
);

const isOwnProject = computed(() => {
	const currentUserId = authUser.value?.id;
	const projectOwnerId = ownerId.value || currentUserId;
	return currentUserId === projectOwnerId;
});

const showShareToast = ref(false);
const showShareModal = ref(false);

const shareToken = ref("");

const generateShareToken = () => {
	const chars =
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	let result = "";
	for (let i = 0; i < 12; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return btoa(result).replace(/=/g, "").substring(0, 14);
};

watch(
	() => props.portfolio?.id,
	() => {
		shareToken.value = generateShareToken();
	},
	{ immediate: true },
);

const getShareUrl = () => {
	const username = authorUsername.value;
	const pOwnerId = ownerId.value;
	const baseUrl = username
		? `${window.location.origin}/pagi/${username}`
		: `${window.location.origin}/pagi/profile/${pOwnerId}`;

	const params: string[] = [];
	if (props.portfolio?.id) {
		params.push(`project=${props.portfolio.id}`);
	}
	if (shareToken.value) {
		params.push(`pagi_share=${shareToken.value}`);
	}
	return params.length > 0 ? `${baseUrl}?${params.join("&")}` : baseUrl;
};

const handleShare = () => {
	showShareModal.value = true;
};

const copyShareLink = async () => {
	const shareUrl = getShareUrl();
	try {
		await navigator.clipboard.writeText(shareUrl);
		showShareToast.value = true;
		setTimeout(() => {
			showShareToast.value = false;
		}, 3000);
	} catch (err) {
		console.error("Failed to copy link: ", err);
	}
	showShareModal.value = false;
};

const openChat = () => {
	if (!page.props.auth?.user) {
		alert("Silakan login terlebih dahulu untuk mengirim pesan.");
		return;
	}
	const pOwnerId = ownerId.value;
	if (isOwnProject.value) {
		router.visit("/pagi/messages");
	} else if (pOwnerId) {
		router.visit(`/pagi/messages?chat=${pOwnerId}`);
	} else {
		router.visit("/pagi/messages");
	}
};

// Follow System State & Actions
const isFollowingState = ref(false);
const followingIds = computed(
	() => authUser.value?.following ?? authUser.value?.metadata?.following ?? [],
);

watch(
	() => [followingIds.value, ownerId.value],
	() => {
		if (ownerId.value) {
			const targetId = Number(ownerId.value);
			isFollowingState.value = followingIds.value
				.map(Number)
				.includes(targetId);
		}
	},
	{ immediate: true },
);

const isFollowing = computed(() => isFollowingState.value);

const handleFollow = async () => {
	if (!authUser.value) {
		alert("Please login to follow this user.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	if (isOwnProject.value || !ownerId.value) return;
	try {
		const res = await axios.post(`/pagi/users/${ownerId.value}/follow`);
		isFollowingState.value = res.data.following;

		// Update page metadata to sync globally
		if (page.props.auth?.user) {
			if (!page.props.auth.user.metadata) {
				page.props.auth.user.metadata = {};
			}
			let list = page.props.auth.user.metadata.following || [];
			if (res.data.following) {
				list.push(ownerId.value);
			} else {
				list = list.filter((id: number) => id !== ownerId.value);
			}
			page.props.auth.user.metadata.following = list;
			page.props.auth.user.following = list;
		}
	} catch (e) {
		console.error("Follow error", e);
	}
};

// Appreciate / Like System State & Actions
const likedState = ref(false);
const likesCount = ref(0);

watch(
	() => props.portfolio,
	(newPortfolio) => {
		if (newPortfolio) {
			likedState.value =
				newPortfolio.liked ||
				(authUser.value
					? newPortfolio.likes?.includes(authUser.value.id) || false
					: false);
			likesCount.value =
				typeof newPortfolio.likes === "number"
					? newPortfolio.likes
					: Array.isArray(newPortfolio.likes)
						? newPortfolio.likes.length
						: 0;
		}
	},
	{ immediate: true, deep: true },
);

const handleLike = async () => {
	if (!authUser.value) {
		alert("Please login to appreciate this project.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	if (!props.portfolio?.id) {
		// Mock local behavior inside Editor preview mode
		likedState.value = !likedState.value;
		likesCount.value += likedState.value ? 1 : -1;
		return;
	}
	try {
		const res = await axios.post(`/pagi/preview/${props.portfolio.id}/like`);
		likedState.value = res.data.liked;
		likesCount.value = res.data.likes;
		if (props.portfolio) {
			props.portfolio.liked = res.data.liked;
			props.portfolio.likes = res.data.likes;
		}
	} catch (e) {
		console.error("Like error", e);
	}
};

// Comments System State & Actions
const commentsList = ref<any[]>([]);
const commentText = ref("");
const isSubmittingComment = ref(false);
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

// Per-comment reply state: { [commentId]: { open: boolean, text: string, submitting: boolean, showReplies: boolean, replyToUserId?: number } }
const replyStates = ref<
	Record<
		string,
		{
			open: boolean;
			text: string;
			submitting: boolean;
			showReplies: boolean;
			replyToUserId?: number;
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
	if (!currentUserId || !c.likes) return false;
	return c.likes.map(Number).includes(Number(currentUserId));
};

const getCommentLikesCount = (c: any) => {
	return Array.isArray(c.likes) ? c.likes.length : 0;
};

const handleLikeComment = async (commentId: string) => {
	if (!authUser.value) {
		alert("Please login to like comments.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment/${commentId}/like`,
		);
		commentsList.value = res.data.comments;
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
	} catch (e) {
		console.error("Like comment error", e);
	}
};

const handleLikeReply = async (commentId: string, replyId: string) => {
	if (!authUser.value) {
		alert("Please login to like replies.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	try {
		const res = await axios.post(
			`/pagi/preview/${props.portfolio.id}/comment/${commentId}/reply/${replyId}/like`,
		);
		commentsList.value = res.data.comments;
		if (props.portfolio) {
			props.portfolio.comments = res.data.comments;
		}
	} catch (e) {
		console.error("Like reply error", e);
	}
};

const handleReplySubmit = async (commentId: string) => {
	const state = getReplyState(commentId);
	if (!authUser.value) {
		alert("Please login to reply.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	if (!state.text.trim()) return;

	if (!props.portfolio?.id) {
		// Mock in editor preview mode
		const comment = commentsList.value.find((c) => c.id === commentId);
		if (comment) {
			if (!comment.replies) comment.replies = [];
			comment.replies.push({
				id: Date.now().toString(),
				name: authUser.value.name,
				avatar:
					authUser.value.avatar ||
					`https://ui-avatars.com/api/?name=${encodeURIComponent(authUser.value.name)}&background=random`,
				body: state.text,
				time: "baru saja",
				likes: [],
			});
		}
		state.text = "";
		state.open = false;
		state.replyToUserId = undefined;
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
	} catch (e) {
		console.error("Reply error", e);
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
	if (!authUser.value) {
		alert("Please login to comment.");
		return;
	}
	if (!checkUsernameMandatory()) return;
	if (!commentText.value.trim()) return;

	if (!props.portfolio?.id) {
		// Mock local behavior inside Editor preview mode
		const newComment = {
			id: Date.now().toString(),
			name: authUser.value.name,
			avatar:
				authUser.value.avatar ||
				`https://ui-avatars.com/api/?name=${encodeURIComponent(authUser.value.name)}&background=random`,
			body: commentText.value,
			time: "baru saja",
			likes: [],
			replies: [],
		};
		commentsList.value.push(newComment);
		commentText.value = "";
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
	} catch (e) {
		console.error("Comment error", e);
	} finally {
		isSubmittingComment.value = false;
	}
};

const scrollWrapper = ref<HTMLElement | null>(null);
const authorOtherProjects = ref<any[]>([]);
const isLoadingOtherProjects = ref(false);

const fetchOtherProjects = async () => {
	if (!ownerId.value) return;
	isLoadingOtherProjects.value = true;
	try {
		const response = await axios.get(`/pagi/users/${ownerId.value}/works`);
		authorOtherProjects.value = (response.data.works || [])
			.filter((p: any) => p.id !== props.portfolio?.id)
			.slice(0, 3);
	} catch (error) {
		console.error("Failed to fetch other projects:", error);
	} finally {
		isLoadingOtherProjects.value = false;
	}
};

watch(
	() => props.portfolio?.id,
	() => {
		fetchOtherProjects();
		if (scrollWrapper.value) {
			scrollWrapper.value.scrollTop = 0;
		}
	},
	{ immediate: true },
);

const handleSelectPortfolio = (p: any) => {
	emit("select-portfolio", p);
};

const isVideoUrl = (url: string) => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
};

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

	return name
		.replace(/\.js/g, "dotjs")
		.replace(/\.net/g, "dotnet")
		.replace(/[^a-z0-9]+/g, "-");
};
</script>

<template>
	<!-- Fixed Sticky Close Button -->
	<button @click="emit('close')" class="fixed top-6 right-6 md:top-8 md:right-8 z-[10005] text-zinc-400 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-full cursor-pointer flex items-center justify-center border-none bg-transparent" title="Close Preview" aria-label="Close Preview">
		<X class="w-6 h-6" />
	</button>
 
	<!-- Right-side Floating Sidebar -->
	<div v-if="hasAccess" class="hidden md:flex fixed right-6 top-1/2 -translate-y-1/2 z-[10001] flex-col items-center gap-6 select-none">
		<!-- Follow Circle -->
		<div v-if="!isOwnProject && !isFollowing" class="flex flex-col items-center gap-1 group">
			<button type="button" @click="handleFollow" class="relative w-12 h-12 rounded-full border border-zinc-800 bg-[#151515] flex items-center justify-center cursor-pointer hover:bg-zinc-850 transition-colors shadow-lg p-0 overflow-hidden" aria-label="Follow creator">
				<OptimizedImage :src="authorAvatar" alt="Author avatar" className="w-full h-full object-cover" />
				<!-- small blue plus button -->
				<div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-blue-500 border border-[#151515] flex items-center justify-center text-white text-xs font-black">
					+
				</div>
			</button>
			<span class="text-[10px] font-bold text-zinc-400 group-hover:text-white transition-colors">Follow</span>
		</div>
 
		<!-- Message Circle -->
		<div v-if="!isOwnProject" class="flex flex-col items-center gap-1 group">
			<button type="button" @click="openChat" class="w-12 h-12 rounded-full border border-zinc-850 bg-[#151515] text-zinc-400 hover:text-white flex items-center justify-center cursor-pointer hover:bg-zinc-850 transition-colors shadow-lg" title="Kirim Pesan" aria-label="Kirim Pesan">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
				</svg>
			</button>
			<span class="text-[10px] font-bold text-zinc-400 group-hover:text-white transition-colors">Message</span>
		</div>
		<!-- Appreciate Circle -->
		<div class="flex flex-col items-center gap-1 group">
			<button type="button" @click="handleLike" :class="['w-12 h-12 rounded-full flex items-center justify-center cursor-pointer transition-colors shadow-lg border-none', likedState ? 'bg-rose-600 text-white hover:bg-rose-500' : 'bg-blue-600 hover:bg-blue-500 text-white']" aria-label="Appreciate project">
				<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
					<path d="M2 10h3v10H2zm19-1c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L12.17 2 6.58 7.59C6.22 7.95 6 8.45 6 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01L21 9z"/>
				</svg>
			</button>
			<span class="text-[10px] font-bold text-zinc-400 group-hover:text-white transition-colors">Appreciate</span>
		</div>
 
		<!-- Share Circle -->
		<div class="flex flex-col items-center gap-1 group">
			<button type="button" @click="handleShare" class="w-12 h-12 rounded-full border border-zinc-850 bg-[#151515] text-zinc-400 hover:text-white flex items-center justify-center cursor-pointer hover:bg-zinc-850 transition-colors shadow-lg" aria-label="Share project">
				<Share2 class="w-5 h-5" />
			</button>
			<span class="text-[10px] font-bold text-zinc-400 group-hover:text-white transition-colors">Share</span>
		</div>
	</div>
 
	<!-- Floating Bottom Center Pill -->
	<div v-if="showFloatingPill && hasAccess" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[10002] bg-[#1e1e1e]/95 border border-zinc-800 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-full shadow-2xl flex items-center gap-3 sm:gap-4 backdrop-blur-xs select-none w-[calc(100%-2rem)] max-w-md sm:w-auto sm:min-w-[340px] justify-between">
		<div class="flex items-center gap-2 min-w-0">
			<div class="w-7 h-7 sm:w-8 sm:h-8 shrink-0 rounded-md overflow-hidden border border-zinc-700">
				<VideoLazy v-if="coverImage && isVideoUrl(coverImage)" :src="coverImage" className="w-full h-full object-cover" />
				<OptimizedImage v-else :src="coverImage || authorAvatar" className="w-full h-full object-cover" alt="Cover" />
			</div>
			<div class="flex flex-col min-w-0">
				<span class="text-[10px] sm:text-xs font-bold text-white truncate max-w-[85px] sm:max-w-[120px]">{{ title || 'Untitled Project' }}</span>
				<div class="flex flex-col leading-none">
					<span class="text-[9px] sm:text-[10px] text-zinc-450 truncate max-w-[85px] sm:max-w-[120px]">{{ authorName }}</span>
				</div>
			</div>
		</div>
 
		<div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
			<button v-if="!isOwnProject && !isFollowing" type="button" @click="handleFollow" class="px-2.5 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white hover:bg-zinc-100 text-[9px] sm:text-[10px] font-black text-zinc-950 flex items-center gap-1 cursor-pointer border-none shadow-xs">
				<Plus class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-zinc-950" /> <span class="hidden xs:inline">Follow</span>
			</button>
			<button type="button" @click="handleLike" :class="['px-2.5 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[9px] sm:text-[10px] font-black text-white transition-colors flex items-center gap-1 sm:gap-1.5 cursor-pointer border-none', likedState ? 'bg-rose-600 hover:bg-rose-500' : 'bg-blue-600 hover:bg-blue-500']">
				<svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-current" viewBox="0 0 24 24">
					<path d="M2 10h3v10H2zm19-1c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L12.17 2 6.58 7.59C6.22 7.95 6 8.45 6 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01L21 9z"/>
				</svg>
				<span>Appreciate{{ likedState ? 'd' : '' }}</span>
			</button>
			<button type="button" @click="handleShare" class="px-2.5 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-zinc-800 hover:bg-zinc-700 text-[9px] sm:text-[10px] font-black text-white flex items-center gap-1 sm:gap-1.5 cursor-pointer border-none shadow-xs">
				<Share2 class="w-3.5 h-3.5 text-white" />
				<span>Share</span>
			</button>
			<div class="w-px h-4 bg-zinc-800 ml-0.5"></div>
			<button @click="showFloatingPill = false" class="text-zinc-550 hover:text-white transition-colors p-1 cursor-pointer flex items-center justify-center border-none bg-transparent">
				<X class="w-3.5 h-3.5" />
			</button>
		</div>
	</div>

	<!-- Fullscreen scrollable canvas wrapper -->
	<div ref="scrollWrapper" class="fixed inset-0 bg-[#0a0a0a]/70 z-[10000] overflow-y-auto pt-8 pb-24" @click.stop>
		<template v-if="hasAccess">
		
		<!-- Floating Header block above the card, in the scrollable flow -->
		<div class="w-full max-w-7xl mx-auto px-4 xl:px-0 mb-6 flex items-center justify-between text-white select-none">
			<!-- Left Info -->
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 shrink-0 rounded-full overflow-hidden border-2 border-zinc-700/60 shadow-md">
					<OptimizedImage :src="authorAvatar" alt="Avatar" className="w-full h-full object-cover" />
				</div>
				<div class="flex flex-col justify-center">
					<span class="text-sm font-bold text-white leading-tight truncate max-w-[240px] sm:max-w-md hover:underline cursor-pointer">{{ title || 'Untitled Project' }}</span>
					<div class="flex flex-col text-xs text-zinc-400">
						<div class="flex items-center gap-1.5 font-semibold">
							<span class="text-zinc-350 hover:text-white transition-colors cursor-pointer leading-normal">{{ authorName }}</span>
							<span v-if="!isOwnProject && !isFollowing" class="text-zinc-600">•</span>
							<button v-if="!isOwnProject && !isFollowing" type="button" @click="handleFollow" class="text-blue-500 hover:text-blue-400 font-bold transition-colors cursor-pointer bg-transparent border-none p-0">Follow</button>
						</div>
						<span v-if="authorUsername" class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 leading-none mt-0.5">@{{ authorUsername }}</span>
					</div>
				</div>
			</div>

			<!-- Right Actions placeholder to preserve space since X is now fixed -->
			<div class="w-10 h-10"></div>
		</div>

		<div ref="canvasContainer" class="w-full mx-auto flex flex-col shrink-0 overflow-hidden transition-colors duration-300 max-w-7xl min-h-screen shadow-2xl border-none rounded-none pt-0" :style="{ backgroundColor: canvasBgColor }">
			
			<template v-for="(block, index) in content" :key="index">
				<div v-if="block.type !== 'settings'" class="w-full relative group transition-all rounded-none" :style="{ marginBottom: spacingInPx + 'px' }">
					
					<!-- If type is Text -->
					<div v-if="block.type === 'text'" class="editor-content w-full max-w-4xl mx-auto px-6 text-slate-800 dark:text-slate-100 leading-relaxed font-sans" :style="{ color: canvasTextColor, textAlign: block.align || 'left' }">
						<div v-html="sanitize(block.value)"></div>
					</div>

					<!-- If type is Image -->
					<div v-else-if="block.type === 'image'" :class="['relative group/media transition-all duration-300 w-full', block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full']">
						<div v-if="!block.preview" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
							<p class="text-xs text-slate-450 mb-4">No preview available for this image.</p>
						</div>
						<OptimizedImage v-else :src="block.preview" alt="Portfolio Image" className="h-auto w-full object-cover select-none border-none pointer-events-none rounded-none shadow-none" />
					</div>

					<!-- If type is Photo Grid -->
					<div v-else-if="block.type === 'photo_grid'" :class="['relative group/media transition-all duration-300 w-full', block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full']">
						<div v-if="!block.previews || block.previews.length === 0" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
							<p class="text-xs text-slate-450 mb-4">This grid is empty.</p>
						</div>
						<div v-else class="w-full flex flex-col" :style="{ gap: '8px' }">
							<div v-for="(row, rIdx) in getJustifiedLayout(block.previews, getContainerWidth(block.isFullWidth), 380, 8)" :key="rIdx" class="w-full flex justify-start" :style="{ gap: '8px' }">
								<div v-for="p in row.items" :key="p" class="relative overflow-hidden cursor-pointer transition-transform duration-300 hover:scale-[1.01]" :style="{
									width: (row.height * getAspectRatio(p)) + 'px',
									flexGrow: row.isLast ? 0 : getAspectRatio(p),
									flexShrink: row.isLast ? 0 : 1,
									flexBasis: 'auto',
									height: row.height + 'px'
								}">
									<OptimizedImage :src="p" @load="handleImageLoad(p, $event)" className="w-full h-full object-cover border-none shadow-none" loading="lazy" alt="Grid Image" />
								</div>
							</div>
						</div>
					</div>

					<!-- If type is Video Audio -->
					<div v-else-if="block.type === 'video_audio'" :class="['relative group/media transition-all duration-300 w-full', block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full']">
						<div v-if="!block.preview" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
							<p class="text-xs text-slate-450 mb-4">No preview available for this media.</p>
						</div>
						<div v-else :class="['w-full overflow-hidden flex items-center justify-center p-0 border-none transition-all duration-300', block.isFullWidth === false ? 'rounded-none shadow-lg bg-slate-950 border border-slate-200/50 dark:border-slate-800/50' : 'rounded-none shadow-none bg-slate-950']">
							<VideoLazy v-if="isVideoBlock(block)" :src="block.preview" :controls="true" className="max-h-[85vh] w-full object-cover rounded-none" />
							<audio v-else-if="isAudioBlock(block)" :src="block.preview" controls class="w-full p-4"></audio>
						</div>
					</div>

					<!-- If type is Asset -->
					<div v-else-if="block.type === 'asset'" class="w-full max-w-4xl mx-auto px-6">
						<a v-if="block.link" :href="block.link" target="_blank" rel="noopener noreferrer" class="block w-full p-4 border border-slate-200 dark:border-slate-800 rounded-none flex items-center justify-between bg-white dark:bg-slate-950 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors shadow-none group/asset">
							<div class="flex items-center gap-3">
								<Paperclip class="h-5 w-5 text-slate-500 group-hover/asset:text-blue-500 transition-colors" />
								<div class="flex flex-col text-left">
									<span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover/asset:text-blue-600 dark:group-hover/asset:text-blue-400 transition-colors">{{ block.name }}</span>
									<span class="text-xs text-zinc-450 dark:text-slate-550 break-all">{{ block.link }}</span>
								</div>
							</div>
							<Link2 class="h-4 w-4 text-slate-400 group-hover/asset:text-blue-500 transition-colors shrink-0" />
						</a>
						<div v-else class="w-full p-4 border border-slate-200 dark:border-slate-800 rounded-none flex items-center gap-3 bg-white dark:bg-slate-950 shadow-none">
							<Paperclip class="h-5 w-5 text-slate-500" />
							<span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ block.name }}</span>
						</div>
					</div>

					<!-- If type is Gallery Item -->
					<div v-else-if="block.type === 'gallery_item'" class="w-full max-w-4xl mx-auto px-6 flex flex-col items-center">
						<div class="relative overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 shadow-md">
							<VideoLazy v-if="isVideoUrl(coverImage)" :src="coverImage" :controls="true" className="max-h-[75vh] w-auto object-contain rounded-2xl" />
							<OptimizedImage v-else :src="coverImage" alt="Gallery Image" className="max-h-[75vh] w-auto object-contain select-none" />
						</div>
						<div v-if="block.description" class="mt-8 text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-medium text-center max-w-2xl px-4">
							{{ block.description }}
						</div>
					</div>

				</div>
			</template>

			<!-- Mobile Metadata/Widgets (Visible only on mobile/tablet, hidden on desktop) -->
			<div class="lg:hidden w-full bg-[#f9f9f9] dark:bg-slate-950 px-4 py-8 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col gap-6 text-left">
				<div class="max-w-4xl mx-auto w-full flex flex-col gap-6">
					
					<!-- Card 1: Owner Profile Card -->
					<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-4">
						<div class="flex items-center justify-between">
							<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Owner</span>
							<span v-if="acceptedCollaborators.length > 0" class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[8px] font-black tracking-wider uppercase border border-indigo-100/10">Kolaborasi</span>
						</div>

						<div class="flex items-center gap-3">
							<div class="relative flex items-center shrink-0">
								<!-- Main Owner Avatar -->
								<div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 dark:border-slate-800 z-10">
									<OptimizedImage :src="authorAvatar" alt="Owner Avatar" className="w-full h-full object-cover" />
								</div>
								<!-- Collaborator Avatars (stacked behind the main one) -->
								<div v-if="acceptedCollaborators.length > 0" class="flex -space-x-2.5 ml-1.5 z-0">
									<Link 
										v-for="(collab, idx) in acceptedCollaborators.slice(0, 3)" 
										:key="collab.id"
										:href="collab.pagi_username ? `/pagi/${collab.pagi_username}` : `/pagi/profile/${collab.id}`"
										class="inline-block relative hover:z-10 group"
										:style="{ zIndex: 9 - idx }"
									>
										<img
											class="rounded-full ring-2 ring-white dark:ring-slate-900 w-8 h-8 object-cover shadow-xs"
											:src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`"
											:alt="collab.name"
											:title="collab.name"
										/>
									</Link>
									<div v-if="acceptedCollaborators.length > 3" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[10px] font-black text-slate-600 dark:text-slate-400 shrink-0 shadow-sm z-0">
										+{{ acceptedCollaborators.length - 3 }}
									</div>
								</div>
							</div>
							<div class="flex flex-col min-w-0">
								<span class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight truncate">
									{{ authorName }}
									<span v-if="acceptedCollaborators.length > 0" class="font-normal text-slate-400 dark:text-slate-500 text-xs">
										& {{ acceptedCollaborators.length }} lainnya
									</span>
								</span>
								<span v-if="authorUsername" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 leading-normal">@{{ authorUsername }}</span>
								<span class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ authorLocation }}</span>
							</div>
						</div>

						<div v-if="!isOwnProject" class="grid grid-cols-2 gap-2.5 mt-2">
							<button v-if="!isOwnProject && !isFollowing" type="button" @click="handleFollow" class="py-2.5 px-4 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer border-none">
								<Plus class="w-4 h-4" /> Follow
							</button>
							<button type="button" @click="openChat" :class="[!isOwnProject && !isFollowing ? 'col-span-1' : 'col-span-2', 'py-2.5 px-4 rounded-full border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-none transition-all flex items-center justify-center gap-1.5 cursor-pointer bg-white dark:bg-slate-900']">
								<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
								</svg>
								Message
							</button>
						</div>
					</div>

					<!-- Card 2: Small Project Widget -->
					<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2">
						<span class="text-sm font-black text-slate-800 dark:text-slate-100">{{ title || 'Untitled Project' }}</span>
						<div class="flex items-center gap-4 text-xs text-slate-400 font-bold justify-start mt-1">
							<span class="flex items-center gap-1.5 cursor-pointer hover:text-white transition-colors" @click="handleLike">
								<svg class="w-3.5 h-3.5 fill-current" :class="likedState ? 'text-red-500' : 'text-slate-400'" viewBox="0 0 24 24">
									<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
								</svg>
								{{ likesCount }}
							</span>
							<span class="flex items-center gap-1.5">
								<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
									<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
								</svg>
								{{ portfolio?.views_count || 0 }}
							</span>
							<span class="flex items-center gap-1.5">
								<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
								</svg>
								{{ commentsList.length }}
							</span>
							<button type="button" @click="handleShare" class="flex items-center gap-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition-colors bg-transparent border-none p-0 cursor-pointer font-bold">
								<Share2 class="w-3.5 h-3.5" />
								<span>Share</span>
							</button>
						</div>
						<div class="w-full h-px bg-slate-100 dark:bg-slate-800 my-2"></div>
						<span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">{{ formattedPublishedDate }}</span>
					</div>

					<!-- Card 3: About Project (Description) -->
					<div v-if="projectDescription" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2">
						<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">About Project</span>
						<p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap mt-1">
							{{ projectDescription }}
						</p>
					</div>

					<!-- Card 4: Creative Fields (Category) -->
					<div v-if="projectCategoryList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2.5">
						<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Creative Fields</span>
						<div class="flex flex-wrap gap-1.5 mt-1">
							<span v-for="cat in projectCategoryList" :key="cat" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold">
								{{ cat }}
							</span>
						</div>
					</div>

					<!-- Card 5: Tools Used -->
					<div v-if="projectToolsList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2.5">
						<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Tools Used</span>
						<div class="flex flex-wrap gap-1.5 mt-1">
							<span v-for="tool in projectToolsList" :key="tool" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center gap-1.5 border border-slate-200/20 dark:border-slate-800/20">
								<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tool)}/default.svg`" 
									 class="w-3.5 h-3.5 object-contain" 
									 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
								<span>{{ tool }}</span>
							</span>
						</div>
					</div>

					<!-- Card: Collaborators -->
					<div v-if="acceptedCollaborators.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2.5">
						<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Collaborators</span>
						<div class="flex items-center rounded-full border border-slate-205 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 p-1.5 shadow-xs w-fit">
							<div class="flex -space-x-1.5">
								<Link 
									v-for="collab in acceptedCollaborators" 
									:key="collab.id"
									:href="collab.pagi_username ? `/pagi/${collab.pagi_username}` : `/pagi/profile/${collab.id}`"
									class="inline-block relative hover:z-10 group"
								>
									<img
										class="rounded-full ring-2 ring-white dark:ring-slate-900 w-6 h-6 object-cover"
										:src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`"
										:alt="collab.name"
										:title="collab.name"
									/>
								</Link>
							</div>
							<p class="px-2.5 text-[10px] text-slate-500 dark:text-slate-400 font-bold truncate max-w-[200px] sm:max-w-xs">
								With <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ acceptedCollaborators.map(c => c.name).join(', ') }}</span>
							</p>
						</div>
					</div>

					<!-- Card 6: Tags -->
					<div v-if="projectTagsList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col gap-2.5">
						<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Tags</span>
						<div class="flex flex-wrap gap-1.5 mt-1">
							<span v-for="tag in projectTagsList" :key="tag" class="px-2.5 py-1 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-slate-650 dark:text-slate-400 text-[10px] font-medium transition-colors">
								#{{ tag }}
							</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Behance-Style Project Footer -->
			<div class="w-full flex flex-col bg-[#0d0d0d] border-t border-zinc-900 select-none mt-16 rounded-b-lg overflow-hidden">
				<!-- Stats/Header Band (Black background) -->
				<div class="w-full bg-[#111111] py-12 px-6 flex flex-col items-center justify-center border-b border-zinc-900 text-center">
					<!-- Blue Thumbs up circle -->
					<button type="button" @click="handleLike" :class="['w-16 h-16 rounded-full flex items-center justify-center text-white mb-4 shadow-lg border-none cursor-pointer transition-colors', likedState ? 'bg-rose-600 hover:bg-rose-500' : 'bg-blue-600 hover:bg-blue-500']">
						<svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
							<path d="M2 10h3v10H2zm19-1c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L12.17 2 6.58 7.59C6.22 7.95 6 8.45 6 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01L21 9z"/>
						</svg>
					</button>

					<h2 class="text-2xl font-black text-white mb-2 text-center">{{ title || 'Untitled Project' }}</h2>

					<!-- Stats counters -->
					<div class="flex items-center gap-5 text-sm text-zinc-400 font-bold mb-4 justify-center">
						<span class="flex items-center gap-1.5 cursor-pointer hover:text-white transition-colors" @click="handleLike">
							<svg class="w-4 h-4 fill-current" :class="likedState ? 'text-red-500' : 'text-zinc-450'" viewBox="0 0 24 24">
								<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
							</svg>
							{{ likesCount }}
						</span>
						<span class="flex items-center gap-1.5">
							<svg class="w-4 h-4 text-zinc-450" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
								<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
							</svg>
							{{ portfolio?.views_count || 0 }}
						</span>
						<span class="flex items-center gap-1.5">
							<svg class="w-4 h-4 text-zinc-450" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
							</svg>
							{{ commentsList.length }}
						</span>
						<button type="button" @click="handleShare" class="flex items-center gap-1.5 text-zinc-450 hover:text-white transition-colors bg-transparent border-none p-0 cursor-pointer font-bold">
							<Share2 class="w-4 h-4" />
							<span>Share</span>
						</button>
					</div>

					<span class="text-xs text-zinc-500 font-medium">{{ formattedPublishedDate }}</span>
				</div>

				<!-- More by Author Section (rounded modern thin ui layout) -->
				<div v-if="isLoadingOtherProjects || authorOtherProjects.length > 0" class="w-full bg-[#111111]/40 border-b border-zinc-900/50 py-12 px-6 md:px-12 text-left backdrop-blur-xs">
					<div class="max-w-6xl mx-auto">
						<!-- Section Header -->
						<div class="flex items-center justify-between mb-8">
							<div class="flex items-center gap-3">
								<div v-if="isLoadingOtherProjects" class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-zinc-800 bg-zinc-800 animate-shimmer"></div>
								<div v-else class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-zinc-800">
									<OptimizedImage :src="authorAvatar" alt="Author avatar" className="w-full h-full object-cover" />
								</div>
								
								<div v-if="isLoadingOtherProjects" class="space-y-1.5">
									<div class="h-4 w-40 bg-zinc-800 rounded animate-shimmer"></div>
									<div class="h-3 w-20 bg-zinc-800 rounded animate-shimmer"></div>
								</div>
								<div v-else>
									<h3 class="text-sm font-black text-white leading-tight">Postingan Lainnya oleh {{ authorName }}</h3>
									<span v-if="authorUsername" class="text-[10px] text-zinc-500 dark:text-zinc-400 font-bold">@{{ authorUsername }}</span>
								</div>
							</div>
							
							<div v-if="isLoadingOtherProjects" class="h-8 w-24 bg-zinc-800 rounded-full animate-shimmer"></div>
							<a v-else :href="authorUsername ? `/pagi/${authorUsername}` : (ownerId ? `/pagi/profile/${ownerId}` : '#')" class="px-4 py-1.5 rounded-full border border-zinc-800 hover:bg-zinc-900 text-zinc-300 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
								Lihat Profil
							</a>
						</div>

						<!-- Skeleton Loader List -->
						<div v-if="isLoadingOtherProjects" class="flex sm:grid overflow-x-auto sm:overflow-visible grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 snap-x snap-mandatory pb-4 sm:pb-0" style="scrollbar-width: none;">
							<div v-for="i in 3" :key="i" class="snap-start shrink-0 w-[280px] xs:w-[320px] sm:w-auto flex flex-col gap-2.5">
								<div class="aspect-16/10 rounded-xl overflow-hidden bg-zinc-800 border border-zinc-800/80 animate-shimmer"></div>
								<div class="space-y-1.5 px-0.5">
									<div class="h-3 w-32 bg-zinc-800 rounded animate-shimmer"></div>
									<div class="h-2.5 w-16 bg-zinc-800 rounded animate-shimmer"></div>
								</div>
							</div>
						</div>

						<!-- Real Project Cards (Horizontal slider on mobile, Grid on desktop) -->
						<div v-else class="flex sm:grid overflow-x-auto sm:overflow-visible grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 snap-x snap-mandatory pb-4 sm:pb-0" style="scrollbar-width: none;">
							<div v-for="p in authorOtherProjects" :key="p.id" 
								 @click="handleSelectPortfolio(p)"
								 class="snap-start shrink-0 w-[280px] xs:w-[320px] sm:w-auto group cursor-pointer flex flex-col gap-2.5">
								<!-- Cover Image Container -->
								<div class="aspect-16/10 rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800/80 group-hover:border-zinc-700 transition-all duration-300 relative shadow-2xs group-hover:scale-[1.02]">
									<VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" className="w-full h-full object-cover select-none pointer-events-none" />
									<OptimizedImage v-else :src="p.image" :alt="p.title" className="w-full h-full object-cover select-none pointer-events-none" />
									
									<!-- Quick stats on hover overlay -->
									<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 text-xs font-bold text-white">
										<span class="flex items-center gap-1">
											<svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24">
												<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
											</svg>
											{{ p.likes }}
										</span>
										<span class="flex items-center gap-1">
											<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
												<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
											</svg>
											{{ p.views }}
										</span>
									</div>
								</div>
								
								<!-- Project Info -->
								<div class="flex flex-col gap-0.5 px-0.5">
									<h4 class="text-xs font-bold text-zinc-200 group-hover:text-white transition-colors truncate max-w-full">
										{{ p.title }}
									</h4>
									<div class="flex items-center gap-1.5 text-[10px] text-zinc-500 font-medium">
										<span>{{ p.category ? p.category.split(',')[0].trim() : 'Project' }}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Details Area: Columns for Comments and Owner Profile Card (White background) -->
				<div class="w-full bg-[#f9f9f9] dark:bg-slate-950 py-12 px-6 md:px-12 text-left">
					<div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
						
						<!-- Left side (2/3 width) - Comments area -->
						<div class="lg:col-span-2 flex flex-col">
							<!-- Comment Input (Flat, matching Behance style) -->
							<div class="flex gap-4 items-start w-full">
								<div class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-zinc-200 dark:border-zinc-800">
									<OptimizedImage :src="page.props.auth?.user?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(page.props.auth?.user?.name || 'User')}&background=random`" className="w-full h-full object-cover" alt="User Avatar" />
								</div>
								
								<div class="flex-grow flex flex-col gap-3">
									<textarea ref="commentTextareaRef" v-model="commentText" rows="3" class="w-full border border-zinc-200 dark:border-zinc-800 rounded-md p-3 text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 bg-white dark:bg-slate-900 placeholder-zinc-400 text-zinc-800 dark:text-zinc-100 resize-none font-medium" placeholder="What are your thoughts on this project?"></textarea>
									<div class="flex justify-end">
										<button type="button" @click="handleCommentSubmit" :disabled="isSubmittingComment || !commentText.trim()" class="px-5 py-2 rounded-full bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-xs font-bold shadow-xs transition-colors disabled:opacity-30 disabled:cursor-not-allowed border-none cursor-pointer">
											{{ isSubmittingComment ? 'Posting...' : 'Post a Comment' }}
										</button>
									</div>
								</div>
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
											<OptimizedImage :src="c.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.name)}&background=random`" className="w-full h-full object-cover" alt="Comment author avatar" />
										</a>
										<div class="flex-grow min-w-0">
											<!-- Name + time + reply -->
											<div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 mb-1">
												<a :href="c.pagi_username ? `/pagi/${c.pagi_username}` : (c.user_id ? `/pagi/profile/${c.user_id}` : '#')" class="text-sm font-black text-zinc-900 dark:text-zinc-100 hover:underline cursor-pointer leading-none">{{ c.pagi_username ? c.pagi_username : c.name }}</a>
												<span class="text-zinc-300 dark:border-zinc-600 text-xs">·</span>
												<span class="text-xs text-zinc-400">{{ c.time }}</span>
											</div>
											<p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed mb-2">{{ c.body }}</p>
											<!-- Action row: Reply + show replies count -->
											<div class="flex items-center gap-3">
												<button type="button" @click="toggleReplyBox(c.id, c.pagi_username || c.name)" class="text-xs font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors cursor-pointer bg-transparent border-none p-0">
													{{ getReplyState(c.id).open ? 'Batal' : 'Balas' }}
												</button>
												<button v-if="c.replies && c.replies.length > 0" type="button" @click="getReplyState(c.id).showReplies = !getReplyState(c.id).showReplies" class="text-xs font-bold text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer bg-transparent border-none p-0 flex items-center gap-1">
													<svg class="w-3 h-3 transition-transform" :class="getReplyState(c.id).showReplies ? 'rotate-90' : 'rotate-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
													{{ c.replies.length }} {{ c.replies.length === 1 ? 'balasan' : 'balasan' }}
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
											<OptimizedImage :src="page.props.auth?.user?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(page.props.auth?.user?.name || 'User')}&background=random`" className="w-full h-full object-cover" alt="You" />
										</div>
										<div class="flex-grow">
											<textarea
												v-model="getReplyState(c.id).text"
												rows="2"
												class="w-full border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white dark:bg-slate-900 placeholder-zinc-400 text-zinc-800 dark:text-zinc-100 resize-none font-medium"
												:placeholder="`Balas @${c.pagi_username || c.name}…`"
												@keydown.enter.exact.prevent="handleReplySubmit(c.id)"
											></textarea>
											<div class="flex items-center justify-end gap-2 mt-1.5">
												<button type="button" @click="getReplyState(c.id).open = false" class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 font-semibold cursor-pointer bg-transparent border-none p-0">Batal</button>
												<button type="button" @click="handleReplySubmit(c.id)" :disabled="getReplyState(c.id).submitting || !getReplyState(c.id).text.trim()" class="px-4 py-1.5 rounded-full bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold transition-colors disabled:opacity-30 disabled:cursor-not-allowed border-none cursor-pointer hover:bg-zinc-700 dark:hover:bg-zinc-100">
													{{ getReplyState(c.id).submitting ? 'Mengirim…' : 'Kirim' }}
												</button>
											</div>
										</div>
									</div>
 
									<!-- Nested Replies -->
									<div v-if="c.replies && c.replies.length > 0 && getReplyState(c.id).showReplies" class="mt-3 ml-12 flex flex-col gap-4">
										<div v-for="r in c.replies" :key="r.id" class="flex items-start gap-2.5">
											<a :href="r.pagi_username ? `/pagi/${r.pagi_username}` : (r.user_id ? `/pagi/profile/${r.user_id}` : '#')" class="block shrink-0 w-7 h-7 rounded-full overflow-hidden border border-zinc-200 dark:border-zinc-700">
												<OptimizedImage :src="r.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(r.name)}&background=random`" className="w-full h-full object-cover" alt="Reply author avatar" />
											</a>
											<div class="flex-grow min-w-0">
												<div class="flex items-center flex-wrap gap-x-2 mb-0.5">
													<a :href="r.pagi_username ? `/pagi/${r.pagi_username}` : (r.user_id ? `/pagi/profile/${r.user_id}` : '#')" class="text-xs font-black text-zinc-900 dark:text-zinc-100 hover:underline cursor-pointer leading-none">{{ r.pagi_username ? r.pagi_username : r.name }}</a>
													<span class="text-zinc-300 dark:text-zinc-600 text-[10px]">·</span>
													<span class="text-[10px] text-zinc-400">{{ r.time }}</span>
												</div>
												<p class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed mb-1">{{ r.body }}</p>
												<div class="flex items-center gap-3">
													<button type="button" @click="toggleReplyBox(c.id, r.pagi_username || r.name, r.user_id)" class="text-[10px] font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors cursor-pointer bg-transparent border-none p-0">
														Balas
													</button>
												</div>
											</div>
											<!-- Reply Like -->
											<div class="flex flex-col items-center gap-0.5 shrink-0 pt-0.5">
												<button type="button" @click="handleLikeReply(c.id, r.id)" class="p-1 bg-transparent border-none cursor-pointer transition-colors" :class="(r.likes && r.likes.map(Number).includes(Number(authUser?.id))) ? 'text-rose-500' : 'text-zinc-350 hover:text-rose-500'">
													<svg v-if="r.likes && r.likes.map(Number).includes(Number(authUser?.id))" class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
													<svg v-else class="w-3 h-3 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
												</button>
												<span v-if="r.likes && r.likes.length > 0" class="text-[9px] font-bold" :class="r.likes.map(Number).includes(Number(authUser?.id)) ? 'text-rose-500' : 'text-zinc-400'">{{ r.likes.length }}</span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div v-else class="text-center py-6">
								<p class="text-sm text-zinc-455 dark:text-zinc-550 font-medium">Belum ada komentar. Jadilah yang pertama!</p>
							</div>

							<!-- Show More Comments -->
							<button v-if="hasMoreComments" @click="showAllComments = true" type="button" class="w-full py-2.5 mt-4 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-950 dark:text-zinc-50 hover:bg-zinc-50 dark:hover:bg-zinc-855 text-xs font-bold transition-all cursor-pointer bg-white dark:bg-slate-900">
								Tampilkan lebih banyak komentar
							</button>
						</div>

						<!-- Right side (1/3 width) - Owner Profile Card -->
						<div class="hidden lg:flex flex-col gap-6">
							<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-4">
								<div class="flex items-center justify-between">
									<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Owner</span>
									<span v-if="acceptedCollaborators.length > 0" class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[8px] font-black tracking-wider uppercase border border-indigo-100/10">Kolaborasi</span>
								</div>

								<div class="flex items-center gap-3">
									<div class="relative flex items-center shrink-0">
										<!-- Main Owner Avatar -->
										<div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 dark:border-slate-800 z-10">
											<OptimizedImage :src="authorAvatar" alt="Owner Avatar" className="w-full h-full object-cover" />
										</div>
										<!-- Collaborator Avatars (stacked behind the main one) -->
										<div v-if="acceptedCollaborators.length > 0" class="flex -space-x-2.5 ml-1.5 z-0">
											<Link 
												v-for="(collab, idx) in acceptedCollaborators.slice(0, 3)" 
												:key="collab.id"
												:href="collab.pagi_username ? `/pagi/${collab.pagi_username}` : `/pagi/profile/${collab.id}`"
												class="inline-block relative hover:z-10 group"
												:style="{ zIndex: 9 - idx }"
											>
												<img
													class="rounded-full ring-2 ring-white dark:ring-slate-900 w-8 h-8 object-cover shadow-xs"
													:src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`"
													:alt="collab.name"
													:title="collab.name"
												/>
											</Link>
											<div v-if="acceptedCollaborators.length > 3" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[10px] font-black text-slate-600 dark:text-slate-400 shrink-0 shadow-sm z-0">
												+{{ acceptedCollaborators.length - 3 }}
											</div>
										</div>
									</div>
									<div class="flex flex-col min-w-0">
										<span class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight truncate">
											{{ authorName }}
											<span v-if="acceptedCollaborators.length > 0" class="font-normal text-slate-400 dark:text-slate-500 text-xs">
												& {{ acceptedCollaborators.length }} lainnya
											</span>
										</span>
										<span v-if="authorUsername" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 leading-normal">@{{ authorUsername }}</span>
										<span class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ authorLocation }}</span>
									</div>
								</div>

								<div v-if="!isOwnProject" class="flex flex-col gap-2 mt-2">
									<button v-if="!isOwnProject && !isFollowing" type="button" @click="handleFollow" class="w-full py-2.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer border-none">
										<Plus class="w-4 h-4" /> Follow
									</button>
									<button type="button" @click="openChat" class="w-full py-2.5 rounded-full border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-none transition-all flex items-center justify-center gap-1.5 cursor-pointer bg-white dark:bg-slate-900">
										<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
										</svg>
										Message
									</button>
								</div>
							</div>

							<!-- Card 2: Small Project Widget -->
							<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2">
								<span class="text-sm font-black text-slate-800 dark:text-slate-100">{{ title || 'Untitled Project' }}</span>
								<div class="flex items-center gap-4 text-xs text-slate-400 font-bold justify-start mt-1">
									<span class="flex items-center gap-1.5 cursor-pointer hover:text-white transition-colors" @click="handleLike">
										<svg class="w-3.5 h-3.5 fill-current" :class="likedState ? 'text-red-500' : 'text-slate-400'" viewBox="0 0 24 24">
											<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
										</svg>
										{{ likesCount }}
									</span>
									<span class="flex items-center gap-1.5">
										<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
											<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
										</svg>
										{{ portfolio?.views_count || 0 }}
									</span>
									<span class="flex items-center gap-1.5">
										<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
										</svg>
										{{ commentsList.length }}
									</span>
								</div>
								<div class="w-full h-px bg-slate-100 dark:bg-slate-800 my-2"></div>
								<span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">{{ formattedPublishedDate }}</span>
							</div>

							<!-- Card 3: About Project (Description) -->
							<div v-if="projectDescription" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2">
								<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">About Project</span>
								<p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap mt-1">
									{{ projectDescription }}
								</p>
							</div>

							<!-- Card 4: Creative Fields (Category) -->
							<div v-if="projectCategoryList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2.5">
								<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Creative Fields</span>
								<div class="flex flex-wrap gap-1.5 mt-1">
									<span v-for="cat in projectCategoryList" :key="cat" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold">
										{{ cat }}
									</span>
								</div>
							</div>

							<!-- Card 5: Tools Used -->
							<div v-if="projectToolsList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2.5">
								<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Tools Used</span>
								<div class="flex flex-wrap gap-1.5 mt-1">
									<span v-for="tool in projectToolsList" :key="tool" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center gap-1.5 border border-slate-200/20 dark:border-slate-800/20">
										<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tool)}/default.svg`" 
											 class="w-3.5 h-3.5 object-contain" 
											 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
										<span>{{ tool }}</span>
									</span>
								</div>
							</div>

							<!-- Card: Collaborators -->
							<div v-if="acceptedCollaborators.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2.5">
								<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Collaborators</span>
								<div class="flex items-center rounded-full border border-slate-205 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 p-1.5 shadow-xs w-fit">
									<div class="flex -space-x-1.5">
										<Link 
											v-for="collab in acceptedCollaborators" 
											:key="collab.id"
											:href="collab.pagi_username ? `/pagi/${collab.pagi_username}` : `/pagi/profile/${collab.id}`"
											class="inline-block relative hover:z-10 group"
										>
											<img
												class="rounded-full ring-2 ring-white dark:ring-slate-900 w-6 h-6 object-cover"
												:src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`"
												:alt="collab.name"
												:title="collab.name"
											/>
										</Link>
									</div>
									<p class="px-2.5 text-[10px] text-slate-500 dark:text-slate-400 font-bold truncate max-w-[200px] sm:max-w-xs">
										With <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ acceptedCollaborators.map(c => c.name).join(', ') }}</span>
									</p>
								</div>
							</div>

							<!-- Card 6: Tags -->
							<div v-if="projectTagsList.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col gap-2.5">
								<span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Tags</span>
								<div class="flex flex-wrap gap-1.5 mt-1">
									<span v-for="tag in projectTagsList" :key="tag" class="px-2.5 py-1 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-slate-650 dark:text-slate-400 text-[10px] font-medium transition-colors">
										#{{ tag }}
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</template>
	</div>

	<!-- Username Warning Modal -->
	<Modal :show="showUsernameWarningModal" title="Lengkapi Profil Anda" maxWidth="md" @close="closeWarningModal">
		<div class="p-6 text-center space-y-4">
			<div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center justify-center text-amber-600 dark:text-amber-400 mx-auto">
				<!-- Warning icon -->
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
				</svg>
			</div>
			<div class="space-y-2">
				<h3 class="text-base font-extrabold text-slate-900 dark:text-white">Username PAGI Wajib Diisi</h3>
				<p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">
					Mahasiswa wajib mengisi <strong>pagi_username</strong> untuk mengakses fitur interaktif di PAGI (menyukai, mengomentari, membalas, atau mengikuti creator). Silakan lengkapi profil Anda terlebih dahulu.
				</p>
			</div>
			<div class="flex items-center justify-center gap-3 pt-3">
				<button type="button" @click="closeWarningModal" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer bg-white dark:bg-transparent">
					Batal
				</button>
				<button type="button" @click="router.visit('/pagi/profile?edit=username')" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer border-none">
					Atur Username Sekarang
				</button>
			</div>
		</div>
	</Modal>

	<!-- Share Modal -->
	<ShareWorkModal
		:show="showShareModal"
		:project="computedProject"
		:user="computedUser"
		:shareUrl="getShareUrl()"
		@close="showShareModal = false"
	/>

	<!-- Share Toast Notification -->
	<Transition
		enter-active-class="transition ease-out duration-300"
		enter-from-class="opacity-0 translate-y-2"
		enter-to-class="opacity-100 translate-y-0"
		leave-active-class="transition ease-in duration-200"
		leave-from-class="opacity-100 translate-y-0"
		leave-to-class="opacity-0 translate-y-2"
	>
		<div v-show="showShareToast" class="fixed bottom-20 left-1/2 -translate-x-1/2 z-[10010] bg-slate-900/90 border border-slate-800 text-white dark:bg-white dark:border-slate-200 dark:text-slate-900 px-5 py-3 rounded-xl shadow-2xl flex items-center gap-2 backdrop-blur-md text-xs font-black select-none pointer-events-none">
			<span>Link postingan berhasil disalin ke clipboard!</span>
		</div>
	</Transition>
</template>

<style scoped>
.editor-content { outline: none; }
.editor-content :deep(h1) { font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin: 1.5rem 0 1rem 0; }
.editor-content :deep(h2) { font-size: 1.5rem; font-weight: 700; line-height: 1.3; margin: 1.25rem 0 0.875rem 0; }
.editor-content :deep(h3) { font-size: 1.25rem; font-weight: 600; line-height: 1.4; margin: 1rem 0 0.75rem 0; }
.editor-content :deep(p) { margin: 0.875rem 0; font-size: 1.125rem; line-height: 1.8; }
.editor-content :deep(blockquote) { border-left: 4px solid #e2e8f0; padding-left: 1rem; color: #64748b; font-style: italic; margin: 1rem 0; }
.editor-content :deep(a) { color: inherit; text-decoration: underline; text-decoration-color: #64748b; }
.editor-content :deep(ul) { list-style-type: disc; padding-left: 1.5rem; margin: 0.875rem 0; }
.editor-content :deep(ol) { list-style-type: decimal; padding-left: 1.5rem; margin: 0.875rem 0; }
.editor-content :deep(li) { margin: 0.375rem 0; }

@keyframes shimmer {
	0% {
		background-position: -200% 0;
	}
	100% {
		background-position: 200% 0;
	}
}
.animate-shimmer {
	background-image: linear-gradient(90deg, rgba(226, 232, 240, 0.08) 0%, rgba(226, 232, 240, 0.18) 50%, rgba(226, 232, 240, 0.08) 100%);
	background-size: 200% 100%;
	animation: shimmer 1.5s infinite linear;
}
.dark .animate-shimmer {
	background-image: linear-gradient(90deg, rgba(39, 39, 42, 0.5) 0%, rgba(63, 63, 70, 0.7) 50%, rgba(39, 39, 42, 0.5) 100%);
}
</style>
