import { type ComputedRef, computed, ref, watch } from "vue";

interface FollowProps {
	isFollowing?: boolean;
	profileUser?: any;
}

export function useProfileFollow(
	props: FollowProps,
	user: ComputedRef<any>,
	isOwnProfile: ComputedRef<boolean>,
	page: any,
	addToast: (message: string, type?: string) => void,
) {
	const followingState = ref(false);
	const isFollowLoading = ref(false);
	const showUnfollowModal = ref(false);

	const realFollowersCount = ref<number>(
		props.profileUser?.followers_count ??
			user.value?.metadata?.followers?.length ??
			0,
	);

	const dynamicFollowersCount = computed(() => realFollowersCount.value);

	watch(
		() => [props.isFollowing, user.value?.id],
		([newIsFollowing, newUserId]) => {
			if (newIsFollowing !== undefined) {
				followingState.value = !!newIsFollowing;
			} else if (newUserId) {
				followingState.value =
					localStorage.getItem(`follow_${newUserId}`) === "true";
			}
		},
		{ immediate: true },
	);

	const toggleFollow = async () => {
		if (!page.props.auth?.user) {
			addToast(
				"Silakan login terlebih dahulu untuk mengikuti creator.",
				"info",
			);
			return;
		}
		if (isOwnProfile.value) return;
		if (isFollowLoading.value) return;

		isFollowLoading.value = true;
		const prevState = followingState.value;
		followingState.value = !followingState.value;

		try {
			const csrfToken = (
				document.querySelector("meta[name=csrf-token]") as HTMLMetaElement
			)?.content;
			const res = await fetch(`/pagi/users/${user.value.id}/follow`, {
				method: "POST",
				headers: {
					"X-CSRF-TOKEN": csrfToken || "",
					Accept: "application/json",
					"Content-Type": "application/json",
				},
			});
			const data = await res.json();
			if (!res.ok) throw new Error(data.error || "Failed");
			followingState.value = data.following;
			realFollowersCount.value = data.followers_count;
			localStorage.setItem(`follow_${user.value.id}`, String(data.following));

			// Update auth user following state globally
			if (page.props.auth?.user) {
				if (!page.props.auth.user.metadata) {
					page.props.auth.user.metadata = {};
				}
				let list =
					page.props.auth.user.following ??
					page.props.auth.user.metadata?.following ??
					[];
				list = [...list];
				const targetId = Number(user.value.id);
				if (data.following) {
					if (!list.some((id: any) => Number(id) === targetId)) {
						list.push(targetId);
					}
				} else {
					list = list.filter((id: any) => Number(id) !== targetId);
				}
				page.props.auth.user.metadata.following = list;
				page.props.auth.user.following = list;
			}

			if (data.following) {
				addToast(`Kamu sekarang mengikuti ${user.value.name}!`, "success");
			} else {
				addToast(`Kamu berhenti mengikuti ${user.value.name}.`, "info");
			}
		} catch (e) {
			console.error(e);
			followingState.value = prevState;
			addToast("Gagal memperbarui status follow. Coba lagi.", "error");
		} finally {
			isFollowLoading.value = false;
		}
	};

	const requestUnfollow = () => {
		showUnfollowModal.value = true;
	};

	const confirmUnfollow = () => {
		showUnfollowModal.value = false;
		toggleFollow();
	};

	const cancelUnfollow = () => {
		showUnfollowModal.value = false;
	};

	return {
		followingState,
		isFollowLoading,
		showUnfollowModal,
		realFollowersCount,
		dynamicFollowersCount,
		toggleFollow,
		requestUnfollow,
		confirmUnfollow,
		cancelUnfollow,
	};
}
