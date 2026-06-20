import type { Ref } from "vue";

export interface JustifiedRow<T> {
	items: T[];
	height: number;
	isLast: boolean;
}

export const normalizeSrc = (src: string): string => {
	if (src.startsWith("http") || src.startsWith("blob:")) return src;
	return `/storage/${src}`;
};

export const loadImageAspectRatios = (
	previews: string[],
	aspectRatios: Ref<Record<string, number>>,
): void => {
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

function partitionItems<T>(items: T[], seq: number[], k: number): T[][] {
	const n = seq.length;
	if (k <= 0) return [];
	if (k >= n) return items.map(x => [x]);

	const table = Array.from({ length: n }, () => Array(k).fill(0));
	const solution = Array.from({ length: n - 1 }, () => Array(k - 1).fill(0));

	let sum = 0;
	for (let i = 0; i < n; i++) {
		sum += seq[i];
		table[i][0] = sum;
	}

	for (let j = 0; j < k; j++) {
		table[0][j] = seq[0];
	}

	for (let i = 1; i < n; i++) {
		for (let j = 1; j < k; j++) {
			let minMax = Infinity;
			let bestX = 0;
			for (let x = 0; x < i; x++) {
				const cost = Math.max(table[x][j - 1], table[i][0] - table[x][0]);
				if (cost < minMax) {
					minMax = cost;
					bestX = x;
				}
			}
			table[i][j] = minMax;
			solution[i - 1][j - 1] = bestX;
		}
	}

	let i = n - 2;
	let j = k - 2;
	const ans: T[][] = [];
	while (j >= 0) {
		if (i < 0) {
			ans.unshift([]);
		} else {
			const split = solution[i][j];
			ans.unshift(items.slice(split + 1, i + 2));
			i = split - 1;
		}
		j--;
	}
	ans.unshift(items.slice(0, i + 2));
	return ans;
}

export const getJustifiedLayout = <T>(
	items: T[],
	containerWidth: number,
	targetHeight: number,
	gap: number,
	getAspectRatio: (item: T) => number,
): JustifiedRow<T>[] => {
	const itemsArray = items || [];
	const n = itemsArray.length;
	if (n === 0) return [];

	const aspectRatios = itemsArray.map(getAspectRatio);
	const totalSum = aspectRatios.reduce((sum, ar) => sum + ar, 0);

	// Ideal row width ratio
	const idealRowWidthRatio = containerWidth / targetHeight;
	
	// Estimate the number of rows k based on Behance-style hybrid constraints
	let k = 1;
	if (n <= 3) {
		k = 1;
	} else if (n === 4) {
		k = 2; // Splits into 2 and 2
	} else if (n === 5) {
		k = 2; // Splits into 3 and 2
	} else if (n === 6) {
		k = 2; // Splits into 3 and 3
	} else {
		const avgAspectRatio = totalSum / n;
		if (avgAspectRatio < 0.95) {
			// Portrait images (narrower, fits more per row)
			if (n <= 7) k = 1;      // 7 portrait photos on 1 row
			else if (n <= 10) k = 2; // 8-10 portrait photos on 2 rows (e.g. 4 & 4)
			else k = Math.max(2, Math.round(totalSum / idealRowWidthRatio));
		} else {
			// Landscape / Square images (wider, needs more rows)
			if (n <= 6) k = 2;
			else if (n <= 8) k = 3;
			else k = Math.max(3, Math.round(totalSum / idealRowWidthRatio));
		}
	}

	// k cannot exceed number of items
	if (k > n) k = n;

	// Partition itemsArray into k rows
	const partition = partitionItems(itemsArray, aspectRatios, k);

	const rows: JustifiedRow<T>[] = [];
	partition.forEach((rowItems, idx) => {
		if (rowItems.length === 0) return;
		const rowSum = rowItems.reduce((sum, item) => sum + getAspectRatio(item), 0);
		
		const totalGapsWidth = (rowItems.length - 1) * gap;
		const availableWidth = containerWidth - totalGapsWidth;
		const calculatedHeight = availableWidth / rowSum;

		const isLastRow = idx === partition.length - 1;
		let finalHeight = calculatedHeight;
		
		// For the last row, if it contains very few items and the calculated height is too large,
		// we cap it at targetHeight * 1.5 to prevent a single photo from spanning huge vertical space.
		// However, if the entire grid contains only 1 image (1 row total), we let it span full width naturally.
		if (isLastRow && rowItems.length < 2 && calculatedHeight > targetHeight * 1.5) {
			if (partition.length > 1) {
				finalHeight = targetHeight;
			} else {
				finalHeight = calculatedHeight;
			}
		}

		rows.push({
			items: rowItems,
			height: finalHeight,
			isLast: isLastRow,
		});
	});

	return rows;
};
