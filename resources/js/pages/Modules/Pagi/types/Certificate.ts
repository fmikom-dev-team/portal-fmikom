export interface PagiCertificate {
	id: number | string;
	title: string;
	issuer: string;
	date: string;
	credentialId: string | null;
	logo?: string | null;
}
