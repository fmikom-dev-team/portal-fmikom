import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

export function useProfileForm(user: any, props: any, addToast: (msg: string, type?: string) => void, triggerWarning: (title: string, msg: string) => void) {
	const form = useForm({
		name: "",
		role_title: "",
		bio: "",
		location: "",
		website: "",
		twitter: "",
		linkedin: "",
		github: "",
		instagram: "",
		tanggal_lahir: "",
		banner: null as File | null,
		foto: null as File | null,
		avatar_url: "",
		remove_foto: false,
		skills: [] as string[],
		timezone: "" as string | null,
		timezone_extended: "" as string | null,
		languages: [] as Array<{ language: string, proficiency: string }>,
		pagi_username: "" as string,
	});

	// Direct-edit modal states
	const showLocationModal = ref(false);
	const showLocationOnlyModal = ref(false);
	const showSocialLinksModal = ref(false);
	const showBioModal = ref(false);
	const showAvatarModal = ref(false);
	const showBannerModal = ref(false);
	const showUsernameModal = ref(false);

	// Cropping references
	const isCropperOpen = ref(false);
	const cropperImageSrc = ref("");
	const cropperAspectRatio = ref<number | string>(3200 / 410);
	const originalFileName = ref("banner.jpg");
	const originalFileType = ref("image/jpeg");

	const usernameChangesCount = computed(() => user.value?.metadata?.username_changes_count || 0);
	const lastUsernameChangedAt = computed(() => user.value?.metadata?.last_username_changed_at || null);

	const parseSkills = (skillsArray: any[]): Array<{ name: string, percentage: number }> => {
		if (!Array.isArray(skillsArray)) return [];
		return skillsArray.map(item => {
			if (typeof item === 'string') {
				const parts = item.split(':');
				if (parts.length === 2 && !isNaN(Number(parts[1]))) {
					return { name: parts[0], percentage: Number(parts[1]) };
				}
				return { name: item, percentage: 80 };
			}
			if (item && typeof item === 'object' && item.name) {
				return { name: item.name, percentage: Number(item.percentage) || 80 };
			}
			return { name: String(item), percentage: 80 };
		});
	};

	const initFormValues = () => {
		if (!user.value) return;
		form.name = user.value.name || "";
		form.role_title = user.value.role_title || "";
		form.bio = user.value.bio || "";
		form.location = user.value.location || "";
		form.website = user.value.website || "";
		form.twitter = user.value.twitter || "";
		form.linkedin = user.value.linkedin || "";
		form.github = user.value.github || "";
		form.instagram = user.value.instagram || "";
		form.tanggal_lahir = user.value.tanggal_lahir || "";
		form.pagi_username = user.value.pagi_username || "";
		
		const s = user.value.skills || user.value.metadata?.skills || props.profileUser?.skills;
		form.skills = Array.isArray(s) ? parseSkills(s) : parseSkills(['Figma', 'UI/UX Design', 'Vue.js']);
		
		form.timezone = user.value.timezone || user.value.metadata?.timezone || props.profileUser?.timezone || "";
		form.timezone_extended = user.value.timezone_extended || user.value.timezoneExtended || user.value.metadata?.timezone_extended || user.value.metadata?.timezoneExtended || props.profileUser?.timezone_extended || props.profileUser?.timezoneExtended || "No extended hours";
		
		const l = user.value.languages || user.value.metadata?.languages || props.profileUser?.languages;
		form.languages = Array.isArray(l) ? l : [];
	};

	// Form submission methods
	const submitLocation = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				showLocationModal.value = false;
				addToast("Location updated successfully!", "success");
			},
		});
	};

	const submitLocationOnly = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				showLocationOnlyModal.value = false;
				addToast("Location updated successfully!", "success");
			},
		});
	};

	const submitSocialLinks = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				showSocialLinksModal.value = false;
				addToast("Social links updated successfully!", "success");
			},
		});
	};

	const submitBio = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				showBioModal.value = false;
				addToast("Biography updated successfully!", "success");
			},
		});
	};

	const submitAvatar = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			forceFormData: true,
			onSuccess: () => {
				initFormValues();
				showAvatarModal.value = false;
				form.foto = null;
				form.remove_foto = false;
				addToast("Profile avatar updated successfully!", "success");
			},
		});
	};

	const submitBanner = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			forceFormData: true,
			onSuccess: () => {
				initFormValues();
				showBannerModal.value = false;
				form.banner = null;
				addToast("Featured banner updated successfully!", "success");
			},
		});
	};

	const submitUsername = () => {
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				showUsernameModal.value = false;
				addToast("Username updated successfully!", "success");
			},
		});
	};

	const updateSkills = (newSkills: string[]) => {
		form.skills = newSkills;
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Skills updated successfully!", "success");
			}
		});
	};

	const updateBio = (newBio: string) => {
		form.bio = newBio;
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Biography updated successfully!", "success");
			}
		});
	};

	const updateLocation = (newLocation: string) => {
		form.location = newLocation;
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Location updated successfully!", "success");
			}
		});
	};

	const updateTimezone = (data: { timezone: string, extended: string }) => {
		form.timezone = data.timezone;
		form.timezone_extended = data.extended;
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Time zone updated successfully!", "success");
			}
		});
	};

	const updateLanguages = (newLanguages: Array<{ language: string, proficiency: string }>) => {
		form.languages = newLanguages;
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Languages updated successfully!", "success");
			}
		});
	};

	const updateSocials = (socials: { website?: string, linkedin?: string, github?: string, twitter?: string, instagram?: string }) => {
		form.website = socials.website || "";
		form.linkedin = socials.linkedin || "";
		form.github = socials.github || "";
		form.twitter = socials.twitter || "";
		form.instagram = socials.instagram || "";
		form.post("/pagi/profile/update", {
			preserveScroll: true,
			onSuccess: () => {
				initFormValues();
				addToast("Social connections updated successfully!", "success");
			}
		});
	};

	// Cropper methods
	const openCropper = (data: { src: string; aspectRatio: number; fileName?: string; fileType?: string }) => {
		cropperImageSrc.value = data.src;
		cropperAspectRatio.value = data.aspectRatio;
		if (data.fileName) originalFileName.value = data.fileName;
		if (data.fileType) originalFileType.value = data.fileType;
		isCropperOpen.value = true;
	};

	const closeCropper = () => {
		isCropperOpen.value = false;
		cropperImageSrc.value = "";
	};

	const handleCropSave = (blob: Blob) => {
		const croppedFile = new File([blob], originalFileName.value, {
			type: originalFileType.value,
			lastModified: Date.now(),
		});

		if (cropperAspectRatio.value === 1) {
			form.foto = croppedFile;
			submitAvatar();
		} else {
			form.banner = croppedFile;
			submitBanner();
		}
		closeCropper();
	};

	const handleTriggerCrop = (data: { src: string; aspectRatio: number; fileName: string; fileType: string }) => {
		openCropper(data);
	};

	return {
		form,
		showLocationModal,
		showLocationOnlyModal,
		showSocialLinksModal,
		showBioModal,
		showAvatarModal,
		showBannerModal,
		showUsernameModal,
		isCropperOpen,
		cropperImageSrc,
		cropperAspectRatio,
		originalFileName,
		originalFileType,
		usernameChangesCount,
		lastUsernameChangedAt,
		initFormValues,
		submitLocation,
		submitLocationOnly,
		submitSocialLinks,
		submitBio,
		submitAvatar,
		submitBanner,
		submitUsername,
		updateSkills,
		updateBio,
		updateLocation,
		updateTimezone,
		updateLanguages,
		updateSocials,
		openCropper,
		closeCropper,
		handleCropSave,
		handleTriggerCrop,
	};
}
