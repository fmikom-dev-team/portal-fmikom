/**
 * Composable Moderasi Gambar Sisi Klien (Client-Side Pre-scan)
 * Berfungsi sebagai Lapis 1 (0 Token, Instant 0ms) di browser pengguna
 */

export function useImageModeration() {
	/**
	 * Pindai file gambar di browser sebelum di-upload ke server
	 */
	async function scanImageClientSide(file: File | Blob): Promise<{
		isFlagged: boolean;
		reason?: string;
		category?: string;
	}> {
		if (!file?.type.startsWith("image/")) {
			return { isFlagged: false };
		}

		return new Promise((resolve) => {
			const reader = new FileReader();
			reader.onload = (e) => {
				const img = new Image();
				img.onload = () => {
					try {
						const canvas = document.createElement("canvas");
						const ctx = canvas.getContext("2d");
						if (!ctx) return resolve({ isFlagged: false });

						// Resize gambar ke sampel analisis (150x150)
						canvas.width = 150;
						canvas.height = 150;
						ctx.drawImage(img, 0, 0, 150, 150);

						const imageData = ctx.getImageData(0, 0, 150, 150);
						const data = imageData.data;
						let skinPixelCount = 0;
						const totalPixels = 150 * 150;

						// Analisis R, G, B piksel warna kulit (RGB Skin Tone Heuristics)
						for (let i = 0; i < data.length; i += 4) {
							const r = data[i];
							const g = data[i + 1];
							const b = data[i + 2];

							// Heuristik standar warna kulit manusia
							const isSkin =
								r > 95 &&
								g > 40 &&
								b > 20 &&
								r > g &&
								r > b &&
								Math.max(r, g, b) - Math.min(r, g, b) > 15 &&
								Math.abs(r - g) > 15;

							if (isSkin) skinPixelCount++;
						}

						const skinRatio = skinPixelCount / totalPixels;

						// Jika rasio piksel kulit melebihi 48% dari total gambar -> Flagged NSFW Seksual
						if (skinRatio > 0.48) {
							return resolve({
								isFlagged: true,
								category: "sexual",
								reason: "Gambar terdeteksi memuat konten visual sensitif/NSFW.",
							});
						}

						resolve({ isFlagged: false });
					} catch (err) {
						resolve({ isFlagged: false });
					}
				};
				img.onerror = () => resolve({ isFlagged: false });
				img.src = e.target?.result as string;
			};
			reader.onerror = () => resolve({ isFlagged: false });
			reader.readAsDataURL(file);
		});
	}

	return {
		scanImageClientSide,
	};
}
