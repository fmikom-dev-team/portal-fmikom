import { onMounted, onUnmounted, ref } from "vue";

export function useSkeleton() {
	const windowWidth = ref(
		typeof window !== "undefined" ? window.innerWidth : 1024,
	);

	function handleResize() {
		windowWidth.value = window.innerWidth;
	}

	onMounted(() => {
		if (typeof window !== "undefined") {
			window.addEventListener("resize", handleResize);
			handleResize();
		}
	});

	onUnmounted(() => {
		if (typeof window !== "undefined") {
			window.removeEventListener("resize", handleResize);
		}
	});

	function getResponsiveCount(
		desktopCount: number,
		mobileCount: number = 1,
	): number {
		if (windowWidth.value < 640) return mobileCount;
		if (windowWidth.value < 1024) return Math.min(desktopCount, 2);
		return desktopCount;
	}

	return {
		windowWidth,
		getResponsiveCount,
	};
}
