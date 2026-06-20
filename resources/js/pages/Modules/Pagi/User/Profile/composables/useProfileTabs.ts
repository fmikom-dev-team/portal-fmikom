import { ref, watch } from "vue";

export function useProfileTabs() {
	const getInitialTab = () => {
		if (typeof globalThis.window === "undefined") return "Work";
		const path = globalThis.window.location.pathname;
		const segments = path.split("/").filter(Boolean);
		if (segments.length >= 3 && segments[0].toLowerCase() === "pagi") {
			const tabSegment = segments[2].toLowerCase();
			if (tabSegment === "gallery") return "Gallery";
			if (tabSegment === "certificates") return "Certificates";
			if (tabSegment === "sertifikat") return "Certificates";
			if (tabSegment === "about") return "About";
			if (tabSegment === "work") return "Work";
		}
		const params = new URLSearchParams(globalThis.window.location.search);
		const queryTab = params.get("tab");
		if (queryTab) {
			const qLower = queryTab.toLowerCase();
			if (qLower === "sertifikat" || qLower === "certificates")
				return "Certificates";
			if (qLower === "work") return "Work";
			if (qLower === "gallery") return "Gallery";
			if (qLower === "about") return "About";
		}
		return "Work";
	};

	const activeTab = ref(getInitialTab());

	watch(activeTab, (newTab) => {
		if (typeof globalThis.window !== "undefined") {
			const path = globalThis.window.location.pathname;
			const segments = path.split("/").filter(Boolean);
			if (segments.length >= 2 && segments[0].toLowerCase() === "pagi") {
				const prefix = segments[0];
				const username = segments[1];
				const tabLower = newTab.toLowerCase();
				let newPathname = "";
				if (tabLower === "work") {
					newPathname = `/${prefix}/${username}`;
				} else {
					newPathname = `/${prefix}/${username}/${tabLower}`;
				}
				const url = new URL(globalThis.window.location.href);
				url.pathname = newPathname;
				url.searchParams.delete("tab");
				globalThis.window.history.replaceState(null, "", url.toString());
			} else {
				const url = new URL(globalThis.window.location.href);
				url.searchParams.set("tab", newTab);
				globalThis.window.history.replaceState(null, "", url.toString());
			}
		}
	});

	return {
		activeTab,
		getInitialTab,
	};
}
