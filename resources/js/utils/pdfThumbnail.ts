/**
 * PDF.js Dynamic Loader & Thumbnail Generation Utilities
 */

export const loadPdfJs = (): Promise<any> => {
	return new Promise<any>((resolve, reject) => {
		if ((globalThis as any).pdfjsLib) {
			resolve((globalThis as any).pdfjsLib);
			return;
		}
		const script = document.createElement("script");
		script.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js";
		script.onload = () => {
			const pdfjs = (globalThis as any).pdfjsLib;
			// Load worker code via fetch and convert to Blob URL to bypass CORS limits
			fetch("https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js")
				.then((res) => res.text())
				.then((workerCode) => {
					const blob = new Blob([workerCode], {
						type: "application/javascript",
					});
					pdfjs.GlobalWorkerOptions.workerSrc = URL.createObjectURL(blob);
					resolve(pdfjs);
				})
				.catch(() => {
					// Fallback to direct CDN URL
					pdfjs.GlobalWorkerOptions.workerSrc =
						"https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
					resolve(pdfjs);
				});
		};
		script.onerror = reject;
		document.head.appendChild(script);
	});
};

export const generatePdfThumbnail = async (file: File): Promise<Blob> => {
	const pdfjs = await loadPdfJs();
	const arrayBuffer = await file.arrayBuffer();
	const pdf = await pdfjs.getDocument({ data: arrayBuffer }).promise;
	const page = await pdf.getPage(1);

	const viewport = page.getViewport({ scale: 1 });
	const canvas = document.createElement("canvas");
	const context = canvas.getContext("2d");

	const desiredWidth = 400;
	const scale = desiredWidth / viewport.width;
	const scaledViewport = page.getViewport({ scale });

	canvas.width = scaledViewport.width;
	canvas.height = scaledViewport.height;

	if (context) {
		await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
		return new Promise<Blob>((resolve) => {
			canvas.toBlob(
				(blob) => {
					if (blob) {
						resolve(blob);
					} else {
						canvas.toBlob(
							(jpgBlob) => {
								resolve(jpgBlob || new Blob());
							},
							"image/jpeg",
							0.8,
						);
					}
				},
				"image/webp",
				0.8,
			);
		});
	}
	throw new Error("Canvas context not available");
};

export const generatePdfThumbnailFromUrl = async (url: string): Promise<string> => {
	const pdfjs = await loadPdfJs();
	const pdf = await pdfjs.getDocument(url).promise;
	const page = await pdf.getPage(1);

	const viewport = page.getViewport({ scale: 1 });
	const canvas = document.createElement("canvas");
	const context = canvas.getContext("2d");

	const desiredWidth = 150;
	const scale = desiredWidth / viewport.width;
	const scaledViewport = page.getViewport({ scale });

	canvas.width = scaledViewport.width;
	canvas.height = scaledViewport.height;

	if (context) {
		await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
		return canvas.toDataURL("image/jpeg", 0.85);
	}
	throw new Error("Canvas context not available");
};
