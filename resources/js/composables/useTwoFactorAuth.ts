import { type ComputedRef, computed, type Ref, ref } from "vue";
import { recoveryCodes } from "@/routes/two-factor/index";

export type UseTwoFactorAuthReturn = {
	qrCodeSvg: Ref<string | null>;
	manualSetupKey: Ref<string | null>;
	recoveryCodesList: Ref<string[]>;
	errors: Ref<string[]>;
	hasSetupData: ComputedRef<boolean>;
	clearSetupData: () => void;
	clearErrors: () => void;
	clearTwoFactorAuthData: () => void;
	fetchQrCode: () => Promise<void>;
	fetchSetupKey: () => Promise<void>;
	fetchSetupData: () => Promise<void>;
	fetchRecoveryCodes: () => Promise<void>;
};

const fetchJson = async <T>(url: string): Promise<T> => {
	const response = await fetch(url, {
		headers: { Accept: "application/json" },
	});

	if (!response.ok) {
		throw new Error(`Failed to fetch: ${response.status}`);
	}

	return response.json();
};

const errors = ref<string[]>([]);
const manualSetupKey = ref<string | null>(null);
const qrCodeSvg = ref<string | null>(null);
const recoveryCodesList = ref<string[]>([]);

const hasSetupData = computed<boolean>(
	() => qrCodeSvg.value !== null && manualSetupKey.value !== null,
);

export const useTwoFactorAuth = (): UseTwoFactorAuthReturn => {
	const fetchQrCode = async (): Promise<void> => {
		// We no longer fetch them separately, setupData fetches both
	};

	const fetchSetupKey = async (): Promise<void> => {
		// Handled by setupData
	};

	const clearSetupData = (): void => {
		manualSetupKey.value = null;
		qrCodeSvg.value = null;
		clearErrors();
	};

	const clearErrors = (): void => {
		errors.value = [];
	};

	const clearTwoFactorAuthData = (): void => {
		clearSetupData();
		clearErrors();
		recoveryCodesList.value = [];
	};

	const fetchRecoveryCodes = async (): Promise<void> => {
		try {
			clearErrors();
			recoveryCodesList.value = await fetchJson<string[]>(recoveryCodes.url());
		} catch {
			errors.value.push("Failed to fetch recovery codes");
			recoveryCodesList.value = [];
		}
	};

	const fetchSetupData = async (): Promise<void> => {
		try {
			clearErrors();
			const response = await fetch("/auth/mfa/setup", {
				method: "POST",
				headers: {
					Accept: "application/json",
					"X-CSRF-TOKEN":
						document.head
							.querySelector('meta[name="csrf-token"]')
							?.getAttribute("content") || "",
				},
			});

			if (!response.ok) throw new Error("Setup failed");
			const data = await response.json();

			// Backend returns base64 svg, need to format it for display
			qrCodeSvg.value = data.qr_code_svg.startsWith("<svg")
				? data.qr_code_svg
				: `<img src="data:image/svg+xml;base64,${data.qr_code_svg}" class="w-full h-full" />`;
			manualSetupKey.value = data.secret;
		} catch {
			errors.value.push("Failed to fetch setup data from Enterprise MFA");
			qrCodeSvg.value = null;
			manualSetupKey.value = null;
		}
	};

	return {
		qrCodeSvg,
		manualSetupKey,
		recoveryCodesList,
		errors,
		hasSetupData,
		clearSetupData,
		clearErrors,
		clearTwoFactorAuthData,
		fetchQrCode,
		fetchSetupKey,
		fetchSetupData,
		fetchRecoveryCodes,
	};
};
