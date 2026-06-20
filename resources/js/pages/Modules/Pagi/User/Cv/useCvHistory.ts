import { type Ref, ref } from "vue";

export function useCvHistory(cvData: Ref<any>, setModified: () => void) {
	const undoStack = ref<string[]>([]);
	const redoStack = ref<string[]>([]);
	let isApplyingHistory = false;

	const pushStateToHistory = () => {
		if (isApplyingHistory) return;
		const state = JSON.stringify(cvData.value);

		// Only push if different from last element
		if (
			undoStack.value.length === 0 ||
			undoStack.value[undoStack.value.length - 1] !== state
		) {
			undoStack.value.push(state);
			// Limit stack size to 25
			if (undoStack.value.length > 25) {
				undoStack.value.shift();
			}
			redoStack.value = []; // Clear redo stack on new action
		}
	};

	const undo = () => {
		if (undoStack.value.length > 1) {
			isApplyingHistory = true;
			const currentState = undoStack.value.pop();
			if (currentState) redoStack.value.push(currentState);

			const prevState = undoStack.value[undoStack.value.length - 1];
			if (prevState) {
				cvData.value = JSON.parse(prevState);
				setModified();
			}
			isApplyingHistory = false;
		}
	};

	const redo = () => {
		if (redoStack.value.length > 0) {
			isApplyingHistory = true;
			const nextState = redoStack.value.pop();
			if (nextState) {
				undoStack.value.push(nextState);
				cvData.value = JSON.parse(nextState);
				setModified();
			}
			isApplyingHistory = false;
		}
	};

	return {
		undoStack,
		redoStack,
		pushStateToHistory,
		undo,
		redo,
	};
}
