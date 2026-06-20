import axios from "axios";
import { ref } from "vue";
import type { AuthUser } from "../types";

export function useE2EE(authUser: AuthUser) {
	const activeSharedKey = ref<CryptoKey | null>(null);
	const e2eStatus = ref<"encrypted" | "unencrypted" | "generating">(
		"unencrypted",
	);
	let initPromise: Promise<void> | null = null;
	const keyRefreshPromises = new Map<number, Promise<any>>();

	function bufToHex(buf: ArrayBuffer): string {
		return Array.from(new Uint8Array(buf))
			.map((b) => b.toString(16).padStart(2, "0"))
			.join("");
	}

	function hexToBuf(hex: string): ArrayBuffer {
		const bytes = new Uint8Array(hex.length / 2);
		for (let i = 0; i < bytes.length; i++) {
			bytes[i] = parseInt(hex.substring(i * 2, i * 2 + 2), 16);
		}
		return bytes.buffer;
	}

	// Test if the keys are a matching pair by comparing coordinates of public and private JWK representations
	async function verifyKeyAlignment(
		privKeyJwk: any,
		pubKeyJwk: any,
	): Promise<boolean> {
		if (!privKeyJwk || !pubKeyJwk) return false;
		return privKeyJwk.x === pubKeyJwk.x && privKeyJwk.y === pubKeyJwk.y;
	}

	// Initialize ECDH P-256 keys on client
	async function initE2EKeys() {
		if (initPromise) return initPromise;
		initPromise = (async () => {
			try {
				let privKeyJwk = authUser.metadata?.pagi_e2e_privkey;
				const pubKeyJwk = authUser.metadata?.pagi_e2e_pubkey;

				const localPrivStr = localStorage.getItem("pagi_e2e_privkey");
				const localPriv = localPrivStr ? JSON.parse(localPrivStr) : null;

				let needsRegen = false;

				if (privKeyJwk && pubKeyJwk) {
					const isAligned = await verifyKeyAlignment(privKeyJwk, pubKeyJwk);
					if (!isAligned) {
						needsRegen = true;
					}
				} else if (localPriv && pubKeyJwk) {
					const isAligned = await verifyKeyAlignment(localPriv, pubKeyJwk);
					if (!isAligned) {
						needsRegen = true;
					} else {
						await axios.post("/pagi/messages/pubkey", {
							public_key: pubKeyJwk,
							private_key: localPriv,
						});
						authUser.metadata = authUser.metadata || {};
						authUser.metadata.pagi_e2e_privkey = localPriv;
						privKeyJwk = localPriv;
					}
				} else {
					needsRegen = true;
				}

				if (needsRegen) {
					e2eStatus.value = "generating";
					const keyPair = await window.crypto.subtle.generateKey(
						{ name: "ECDH", namedCurve: "P-256" },
						true,
						["deriveKey"],
					);

					const newPrivJwk = await window.crypto.subtle.exportKey(
						"jwk",
						keyPair.privateKey,
					);
					const newPubJwk = await window.crypto.subtle.exportKey(
						"jwk",
						keyPair.publicKey,
					);

					localStorage.setItem("pagi_e2e_privkey", JSON.stringify(newPrivJwk));
					await axios.post("/pagi/messages/pubkey", {
						public_key: newPubJwk,
						private_key: newPrivJwk,
					});

					authUser.metadata = authUser.metadata || {};
					authUser.metadata.pagi_e2e_pubkey = newPubJwk;
					authUser.metadata.pagi_e2e_privkey = newPrivJwk;
				} else {
					if (privKeyJwk) {
						localStorage.setItem(
							"pagi_e2e_privkey",
							JSON.stringify(privKeyJwk),
						);
					}
				}
			} catch (err) {
				console.error("Failed to initialize E2EE keys:", err);
			}
		})();
		return initPromise;
	}

	// Derive AES-GCM 256 key
	async function establishSharedKey(partnerPubKeyJwk: any) {
		try {
			await initE2EKeys();
			const privKeyJwkStr = localStorage.getItem("pagi_e2e_privkey");
			if (!privKeyJwkStr || !partnerPubKeyJwk) {
				activeSharedKey.value = null;
				e2eStatus.value = "unencrypted";
				return;
			}

			const privKey = await window.crypto.subtle.importKey(
				"jwk",
				JSON.parse(privKeyJwkStr),
				{ name: "ECDH", namedCurve: "P-256" },
				true,
				["deriveKey"],
			);

			const pubKey = await window.crypto.subtle.importKey(
				"jwk",
				partnerPubKeyJwk,
				{ name: "ECDH", namedCurve: "P-256" },
				true,
				[],
			);

			activeSharedKey.value = await window.crypto.subtle.deriveKey(
				{ name: "ECDH", public: pubKey },
				privKey,
				{ name: "AES-GCM", length: 256 },
				true,
				["encrypt", "decrypt"],
			);
			e2eStatus.value = "encrypted";
		} catch (err) {
			console.error("Failed to derive shared key:", err);
			activeSharedKey.value = null;
			e2eStatus.value = "unencrypted";
		}
	}

	// Encrypt plaintext
	async function encryptText(text: string): Promise<string> {
		if (!activeSharedKey.value) return text;
		try {
			const enc = new TextEncoder();
			const iv = window.crypto.getRandomValues(new Uint8Array(12));
			const ciphertext = await window.crypto.subtle.encrypt(
				{ name: "AES-GCM", iv },
				activeSharedKey.value,
				enc.encode(text),
			);
			return `E2E:${bufToHex(iv.buffer)}:${bufToHex(ciphertext)}`;
		} catch (err) {
			console.error("Encryption failed:", err);
			return text;
		}
	}

	// Decrypt ciphertext
	async function decryptText(
		encryptedText: string,
		customKey?: CryptoKey | null,
		partnerId?: number,
		hasRetried = false,
	): Promise<string> {
		if (!encryptedText?.startsWith("E2E:")) return encryptedText;
		const key = customKey !== undefined ? customKey : activeSharedKey.value;
		if (!key) {
			if (partnerId && !hasRetried) {
				try {
					console.log(
						`E2EE: No active key for partner ${partnerId}. Fetching key for auto-recovery...`,
					);
					let refreshPromise = keyRefreshPromises.get(partnerId);
					if (!refreshPromise) {
						refreshPromise = axios
							.get(`/pagi/messages/${partnerId}`)
							.finally(() => {
								keyRefreshPromises.delete(partnerId);
							});
						keyRefreshPromises.set(partnerId, refreshPromise);
					}
					const res = await refreshPromise;
					const latestPubKey = res.data.partner?.metadata?.pagi_e2e_pubkey;
					if (latestPubKey) {
						await establishSharedKey(latestPubKey);
						return await decryptText(
							encryptedText,
							activeSharedKey.value,
							partnerId,
							true,
						);
					}
				} catch (retryErr) {
					console.error("E2EE: Auto-recovery key fetch failed:", retryErr);
				}
			}
			return "🔒 [Pesan Terenkripsi]";
		}
		try {
			const parts = encryptedText.split(":");
			const iv = new Uint8Array(hexToBuf(parts[1]));
			const ciphertext = hexToBuf(parts[2]);
			const decrypted = await window.crypto.subtle.decrypt(
				{ name: "AES-GCM", iv },
				key,
				ciphertext,
			);
			const dec = new TextDecoder();
			return dec.decode(decrypted);
		} catch (err) {
			if (partnerId && !hasRetried) {
				try {
					console.log(
						`E2EE: Decryption failed for partner ${partnerId}. Refreshing key for auto-recovery...`,
					);
					let refreshPromise = keyRefreshPromises.get(partnerId);
					if (!refreshPromise) {
						refreshPromise = axios
							.get(`/pagi/messages/${partnerId}`)
							.finally(() => {
								keyRefreshPromises.delete(partnerId);
							});
						keyRefreshPromises.set(partnerId, refreshPromise);
					}
					const res = await refreshPromise;
					const latestPubKey = res.data.partner?.metadata?.pagi_e2e_pubkey;
					if (latestPubKey) {
						await establishSharedKey(latestPubKey);
						return await decryptText(
							encryptedText,
							activeSharedKey.value,
							partnerId,
							true,
						);
					}
				} catch (retryErr) {
					console.error("E2EE: Auto-recovery key refresh failed:", retryErr);
				}
			}
			console.warn(
				"E2EE: Failed to decrypt message (likely encrypted with an older/different key pair):",
				err,
			);
			return "🔒 [Pesan terenkripsi - Kunci perangkat berbeda]";
		}
	}

	// Decrypt message using partner public key (for previews of inactive conversations)
	async function decryptMessageForPartner(
		encryptedText: string,
		partnerPubKey: any,
	): Promise<string> {
		if (!encryptedText?.startsWith("E2E:")) return encryptedText;
		if (!partnerPubKey) return "🔒 [Pesan Terenkripsi]";
		try {
			await initE2EKeys();
			const privKeyJwkStr = localStorage.getItem("pagi_e2e_privkey");
			if (!privKeyJwkStr) return "🔒 [Pesan Terenkripsi]";

			const privKey = await window.crypto.subtle.importKey(
				"jwk",
				JSON.parse(privKeyJwkStr),
				{ name: "ECDH", namedCurve: "P-256" },
				true,
				["deriveKey"],
			);
			const pubKey = await window.crypto.subtle.importKey(
				"jwk",
				partnerPubKey,
				{ name: "ECDH", namedCurve: "P-256" },
				true,
				[],
			);
			const tempKey = await window.crypto.subtle.deriveKey(
				{ name: "ECDH", public: pubKey },
				privKey,
				{ name: "AES-GCM", length: 256 },
				true,
				["decrypt"],
			);
			return await decryptText(encryptedText, tempKey);
		} catch (e) {
			console.warn("E2EE: Failed to decrypt message for partner:", e);
			return "🔒 [Pesan Terenkripsi]";
		}
	}

	// Decrypt previews in sidebar
	async function decryptSidebarPreviews(conversationsList: any[]) {
		await Promise.all(
			conversationsList.map(async (conv) => {
				if (conv.last_message?.startsWith("E2E:")) {
					const partnerPubKey = conv.metadata?.pagi_e2e_pubkey;
					conv.last_message = await decryptMessageForPartner(
						conv.last_message,
						partnerPubKey,
					);
				}
			}),
		);
	}

	return {
		activeSharedKey,
		e2eStatus,
		initE2EKeys,
		establishSharedKey,
		encryptText,
		decryptText,
		decryptMessageForPartner,
		decryptSidebarPreviews,
	};
}
