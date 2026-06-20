import { ref, watch } from "vue";

// Singletons defined outside the hook so they are shared globally across components
const show = ref(false);
const title = ref("");
const percent = ref(0);
const mode = ref<"upload" | "download" | "publish">("upload");
const statusText = ref("");
const isProcessing = ref(false);

export function usePagiProgress() {
	const startProgress = (
		taskMode: "upload" | "download" | "publish",
		taskTitle: string,
	) => {
		mode.value = taskMode;
		title.value = taskTitle;
		percent.value = 0;
		statusText.value =
			taskMode === "upload"
				? "Menghubungkan..."
				: taskMode === "download"
					? "Menghubungkan..."
					: "Menyiapkan...";
		isProcessing.value = false;
		show.value = true;
	};

	const updateProgress = (value: number, text?: string) => {
		percent.value = Math.max(0, Math.min(100, value));
		if (text) statusText.value = text;
	};

	const setProcessing = (processing: boolean, text?: string) => {
		isProcessing.value = processing;
		if (text) statusText.value = text;
	};

	const finishProgress = () => {
		// A tiny delay before hiding so the user sees 100% / success
		setTimeout(() => {
			show.value = false;
			percent.value = 0;
			isProcessing.value = false;
		}, 600);
	};

	const trackUpload = async (
		apiCall: (config: any) => Promise<any>,
		taskTitle: string,
		modeType: "upload" | "publish" = "upload",
	) => {
		startProgress(modeType, taskTitle);
		try {
			const res = await apiCall({
				onUploadProgress: (progressEvent: any) => {
					if (progressEvent.total) {
						const pct = Math.round(
							(progressEvent.loaded * 100) / progressEvent.total,
						);
						if (pct >= 100) {
							updateProgress(99, "Selesai mengunggah, memproses di server...");
							setProcessing(true);
						} else {
							updateProgress(pct, `Mengunggah... ${pct}%`);
						}
					} else {
						updateProgress(50, "Mengunggah berkas...");
					}
				},
			});
			updateProgress(100, "Selesai!");
			finishProgress();
			return res;
		} catch (err) {
			show.value = false;
			throw err;
		}
	};

	const trackDownload = async (
		apiCall: (config: any) => Promise<any>,
		taskTitle: string,
	) => {
		startProgress("download", taskTitle);
		try {
			const res = await apiCall({
				responseType: "blob",
				onDownloadProgress: (progressEvent: any) => {
					if (progressEvent.total) {
						const pct = Math.round(
							(progressEvent.loaded * 100) / progressEvent.total,
						);
						if (pct >= 100) {
							updateProgress(100, "Selesai mengunduh!");
						} else {
							updateProgress(pct, `Mengunduh... ${pct}%`);
						}
					} else {
						updateProgress(50, "Mengunduh berkas...");
					}
				},
			});
			finishProgress();
			return res;
		} catch (err) {
			show.value = false;
			throw err;
		}
	};

	// Bridges Inertia forms (with form.processing and form.progress) to the global overlay
	const trackInertiaForm = (
		form: any,
		taskTitle: string,
		modeType: "upload" | "publish" = "publish",
	) => {
		let unwatchProgress: (() => void) | null = null;

		const unwatchProcessing = watch(
			() => form.processing,
			(processing) => {
				if (processing) {
					startProgress(modeType, taskTitle);

					if (form.progress) {
						updateProgress(
							form.progress.percentage || 0,
							`Mengunggah... ${form.progress.percentage || 0}%`,
						);
					}

					// Set up watch on form.progress
					unwatchProgress = watch(
						() => form.progress,
						(prog) => {
							if (prog) {
								const pct = prog.percentage || 0;
								if (pct >= 100) {
									updateProgress(
										99,
										"Selesai mengunggah, memproses di server...",
									);
									setProcessing(true);
								} else {
									updateProgress(pct, `Mengunggah... ${pct}%`);
								}
							}
						},
						{ deep: true },
					);
				} else {
					if (unwatchProgress) {
						unwatchProgress();
						unwatchProgress = null;
					}
					updateProgress(100, "Selesai!");
					finishProgress();
				}
			},
		);

		return {
			stopTracking: () => {
				unwatchProcessing();
				if (unwatchProgress) {
					unwatchProgress();
				}
			},
		};
	};

	return {
		show,
		title,
		percent,
		mode,
		statusText,
		isProcessing,
		startProgress,
		updateProgress,
		setProcessing,
		finishProgress,
		trackUpload,
		trackDownload,
		trackInertiaForm,
	};
}
