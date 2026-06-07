/**
 * Safe dynamic import wrapper - returns null on failure instead of crashing.
 */
export const safeImport = async (modPromise) => {
	try {
		const m = await modPromise;
		return m.default || m;
	} catch (e) {
		console.warn("[EditorJS] Plugin load failed:", e.message);
		return null;
	}
};
