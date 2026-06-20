import { type Ref, watch } from "vue";

function debounce<T extends (...args: any[]) => any>(
	fn: T,
	delay: number,
): (...args: Parameters<T>) => void {
	let timeoutId: ReturnType<typeof setTimeout> | null = null;
	return (...args: Parameters<T>) => {
		if (timeoutId) clearTimeout(timeoutId);
		timeoutId = setTimeout(() => {
			fn(...args);
		}, delay);
	};
}

const IDB_NAME = "pagi_work_files";
const IDB_STORE_NAME = "files";

const idbOpen = (): Promise<IDBDatabase> =>
	new Promise((resolve, reject) => {
		if (typeof indexedDB === "undefined") {
			reject(new Error("IDB unavailable"));
			return;
		}
		const req = indexedDB.open(IDB_NAME, 1);
		req.onupgradeneeded = () => {
			if (!req.result.objectStoreNames.contains(IDB_STORE_NAME)) {
				req.result.createObjectStore(IDB_STORE_NAME);
			}
		};
		req.onsuccess = () => resolve(req.result);
		req.onerror = () => reject(req.error);
	});

export const idbPut = async (key: string, file: File): Promise<void> => {
	try {
		const db = await idbOpen();
		await new Promise<void>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readwrite");
			tx.objectStore(IDB_STORE_NAME).put(file, key);
			tx.oncomplete = () => resolve();
			tx.onerror = () => reject(tx.error);
		});
	} catch (e) {
		console.warn("IDB put failed:", e);
	}
};

export const idbGet = async (key: string): Promise<File | null> => {
	try {
		const db = await idbOpen();
		return await new Promise<File | null>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readonly");
			const req = tx.objectStore(IDB_STORE_NAME).get(key);
			req.onsuccess = () => resolve(req.result || null);
			req.onerror = () => reject(req.error);
		});
	} catch (e) {
		console.warn("IDB get failed:", e);
		return null;
	}
};

export const idbClear = async (): Promise<void> => {
	try {
		const db = await idbOpen();
		await new Promise<void>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readwrite");
			tx.objectStore(IDB_STORE_NAME).clear();
			tx.oncomplete = () => resolve();
			tx.onerror = () => reject(tx.error);
		});
	} catch (e) {
		console.warn("IDB clear failed:", e);
	}
};

export const useEditorDraft = (
	form: any,
	globalSpacing: Ref<number>,
	canvasBgColor: Ref<string>,
	canvasTextColor: Ref<string>,
	isEditMode: boolean,
) => {
	const saveDraft = () => {
		if (typeof window === "undefined" || isEditMode) return;

		const contentDraft = form.content.filter(Boolean).map((block: any) => {
			const savedBlock = { ...block };
			if (savedBlock.file) delete savedBlock.file;
			if (savedBlock.files) delete savedBlock.files;
			// Remove initialValue so restored blocks fall back to block.value on next load
			if ("initialValue" in savedBlock) delete savedBlock.initialValue;
			if (
				savedBlock.preview &&
				typeof savedBlock.preview === "string" &&
				savedBlock.preview.startsWith("blob:")
			) {
				delete savedBlock.preview;
			}
			if (savedBlock.previews) {
				savedBlock.previews = savedBlock.previews.filter(
					(p: string) => p && typeof p === "string" && !p.startsWith("blob:"),
				);
			}
			return savedBlock;
		});

		try {
			localStorage.setItem(
				"pagi_work_draft",
				JSON.stringify({
					title: form.title,
					description: form.description,
					category: form.category,
					tags: form.tags,
					tools_used: form.tools_used,
					visibility: form.visibility,
					content: contentDraft,
					canvasBgColor: canvasBgColor.value,
					canvasTextColor: canvasTextColor.value,
					globalSpacing: globalSpacing.value,
				}),
			);
		} catch (e) {
			console.warn("Failed to save work draft:", e);
		}
	};

	const saveDraftDebounced = debounce(saveDraft, 1000);

	// Initialize watchers
	watch(form.content, saveDraftDebounced, { deep: true });
	watch(() => form.title, saveDraftDebounced);
	watch(globalSpacing, saveDraftDebounced);
	watch(canvasBgColor, saveDraftDebounced);
	watch(canvasTextColor, saveDraftDebounced);

	return {
		saveDraft,
		idbPut,
		idbGet,
		idbClear,
	};
};
