import { computed, onMounted, onUnmounted, ref, watch } from "vue";

export const useEditorCanvas = (form: any) => {
	const globalSpacing = ref(50); // Default spacing 50%
	const canvasBgColor = ref("#ffffff"); // Sleek light preset default
	const canvasTextColor = ref("#111827");
	const canvasBorderColor = ref("#e2e8f0");
	const activeSidebarTab = ref("content"); // content or styles
	const coverFit = ref<"cover" | "contain">("cover");

	const stylePresets = [
		{
			name: "Sleek Light",
			bg: "#ffffff",
			text: "#111827",
			desc: "Default clean look",
		},
		{
			name: "Warm Cream",
			bg: "#faf6ee",
			text: "#2d2d2a",
			desc: "Elegant editorial tone",
		},
		{
			name: "Midnight Dark",
			bg: "#0f172a",
			text: "#f8fafc",
			desc: "Modern deep slate dark",
		},
		{
			name: "Obsidian Black",
			bg: "#090d16",
			text: "#f1f5f9",
			desc: "State-of-the-art dark",
		},
		{
			name: "Nordic Forest",
			bg: "#fafaf9",
			text: "#1c1917",
			desc: "Minimal natural stone",
		},
	];

	const updateGlobalSettingsBlock = () => {
		let settingsBlock = form.content.find(
			(b: any) => b && b.type === "settings",
		);
		if (!settingsBlock) {
			settingsBlock = { type: "settings" };
			form.content.push(settingsBlock);
		}
		settingsBlock.globalSpacing = globalSpacing.value;
		settingsBlock.canvasBgColor = canvasBgColor.value;
		settingsBlock.canvasTextColor = canvasTextColor.value;
		settingsBlock.canvasBorderColor = canvasBorderColor.value;
		settingsBlock.coverFit = coverFit.value;
	};

	// Auto-sync settings updates into form.content settings block
	watch(
		[
			globalSpacing,
			canvasBgColor,
			canvasTextColor,
			canvasBorderColor,
			coverFit,
		],
		() => {
			updateGlobalSettingsBlock();
		},
		{ deep: true },
	);

	const spacingInPx = computed(() => {
		return (globalSpacing.value / 100) * 80;
	});

	// Compute dynamic container width based on viewport
	const canvasContainer = ref<HTMLElement | null>(null);
	const containerWidth = ref(1200);

	const updateContainerWidth = () => {
		if (canvasContainer.value) {
			containerWidth.value = canvasContainer.value.clientWidth;
		}
	};

	let resizeObserver: ResizeObserver | null = null;

	const getContainerWidth = (isFullWidth: boolean) => {
		if (isFullWidth) return containerWidth.value;
		return Math.min(containerWidth.value, 896) - 48;
	};

	const windowWidth = ref(
		globalThis.window === undefined ? 1200 : globalThis.window.innerWidth,
	);

	const handleResize = () => {
		windowWidth.value = globalThis.window.innerWidth;
	};

	watch(canvasContainer, (newVal) => {
		if (newVal && typeof ResizeObserver !== "undefined") {
			if (resizeObserver) {
				resizeObserver.disconnect();
			}
			resizeObserver = new ResizeObserver(() => {
				updateContainerWidth();
			});
			resizeObserver.observe(newVal);
		}
	});

	onMounted(() => {
		if (globalThis.window !== undefined) {
			globalThis.addEventListener("resize", handleResize);
		}
		updateContainerWidth();
		if (typeof ResizeObserver !== "undefined" && canvasContainer.value) {
			resizeObserver = new ResizeObserver(() => {
				updateContainerWidth();
			});
			resizeObserver.observe(canvasContainer.value);
		}
	});

	onUnmounted(() => {
		if (globalThis.window !== undefined) {
			globalThis.removeEventListener("resize", handleResize);
		}
		if (resizeObserver) {
			resizeObserver.disconnect();
		}
	});

	return {
		globalSpacing,
		canvasBgColor,
		canvasTextColor,
		canvasBorderColor,
		activeSidebarTab,
		coverFit,
		stylePresets,
		updateGlobalSettingsBlock,
		spacingInPx,
		canvasContainer,
		containerWidth,
		updateContainerWidth,
		getContainerWidth,
		windowWidth,
		handleResize,
	};
};
