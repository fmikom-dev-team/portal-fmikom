/**
 * Centralized TypeScript interfaces for the Trace module.
 * Eliminates duplicate interface definitions across Vue components.
 */

// ──────────────────────────────────────────
// Common / Shared
// ──────────────────────────────────────────

export interface PaginatedResponse<T> {
	data: T[];
	links: PaginationLinks;
	meta?: PaginationMeta;
	current_page?: number;
	last_page?: number;
	per_page?: number;
	total?: number;
}

export interface PaginationLinks {
	first: string | null;
	last: string | null;
	prev: string | null;
	next: string | null;
}

export interface PaginationMeta {
	current_page: number;
	from: number | null;
	last_page: number;
	per_page: number;
	to: number | null;
	total: number;
}

export interface TraceFilters {
	search?: string;
	status?: string;
	prodi?: string;
	angkatan?: string | number;
	[key: string]: string | number | undefined;
}

// ──────────────────────────────────────────
// User & Auth
// ──────────────────────────────────────────

export interface TraceUser {
	id: number;
	name: string;
	email: string;
	nomor_induk?: string;
	no_telepon?: string;
	foto_path?: string | null;
	user_type: string;
	program_studi_id?: number;
	tahun_lulus?: number;
	program_studi?: ProgramStudi | null;
}

export interface ProgramStudi {
	id: number;
	nama: string;
}

// ──────────────────────────────────────────
// Alumni
// ──────────────────────────────────────────

export interface ProfilAlumni {
	id: number;
	user_id: number;
	angkatan: number;
	alamat_rumah?: string | null;
	latitude_rumah?: number | null;
	longitude_rumah?: number | null;
	jenis_kelamin?: string | null;
	nik_masked?: string | null;
	npwp_masked?: string | null;
	provinsi_id?: number | null;
	kota_id?: number | null;
	completeness_percentage: number;
	user?: TraceUser;
	careers?: CareerHistory[];
	education_histories?: EducationHistory[];
	provinsi?: Lokasi | null;
	kota?: Lokasi | null;
}

export interface CareerHistory {
	id: number;
	profil_alumni_id: number;
	status: "bekerja" | "wirausaha" | "lanjut_studi" | "mencari_kerja";
	is_current: boolean;
	tanggal_mulai?: string | null;
	tanggal_selesai?: string | null;
	latitude?: number | null;
	longitude?: number | null;
	kota_id?: number | null;
	provinsi_id?: number | null;
	employment?: Employment | null;
	education?: Education | null;
	provinsi?: Lokasi | null;
	kota?: Lokasi | null;
}

export interface Employment {
	id: number;
	career_history_id: number;
	nama_perusahaan: string;
	jabatan?: string | null;
	sektor_industri?: string | null;
	gaji_range?: string | null;
}

export interface Education {
	id: number;
	career_history_id: number;
	nama_universitas: string;
	program_studi_lanjutan?: string | null;
	jenjang?: string | null;
}

export interface EducationHistory {
	id: number;
	profil_alumni_id: number;
	nama_institusi: string;
	jenjang: string;
	tahun_masuk?: number | null;
	tahun_lulus?: number | null;
}

export interface Lokasi {
	id: number;
	name: string;
}

// ──────────────────────────────────────────
// Jobs
// ──────────────────────────────────────────

export interface JobListing {
	id: number;
	title: string;
	description: string;
	location?: string | null;
	tipe_kerja?: string | null;
	location_type?: string | null;
	salary_min?: number | null;
	salary_max?: number | null;
	is_salary_visible: boolean;
	status: string;
	deadline?: string | null;
	job_category_id?: number | null;
	mitra_id?: number | null;
	category?: JobCategory | null;
	mitra?: MitraProfile | null;
	user?: TraceUser | null;
	applicants_count?: number;
	created_at: string;
	updated_at?: string;
}

export interface JobCategory {
	id: number;
	nama: string;
}

export interface JobApplicant {
	id: number;
	job_id: number;
	alumni_id: number;
	cover_letter?: string | null;
	attached_cv_ids?: number[] | null;
	attached_portfolio_ids?: number[] | null;
	status: "applied" | "reviewed" | "accepted" | "rejected";
	applied_at: string;
	alumni?: ProfilAlumni;
	pagi_works?: Array<{ id: number; title: string; [key: string]: unknown }>;
	pagi_cvs?: Array<{ id: number; title: string; [key: string]: unknown }>;
}

// ──────────────────────────────────────────
// Mitra
// ──────────────────────────────────────────

export interface MitraProfile {
	id: number;
	user_id: number;
	nama_perusahaan: string;
	logo_path?: string | null;
	logo_url?: string | null;
	deskripsi?: string | null;
	alamat?: string | null;
	website?: string | null;
	sektor_industri?: string | null;
	user?: TraceUser;
	job_listings?: JobListing[];
}

// ──────────────────────────────────────────
// Events
// ──────────────────────────────────────────

export interface TraceEvent {
	id: number;
	title: string;
	description?: string | null;
	event_date: string;
	event_time?: string | null;
	location?: string | null;
	poster_path?: string | null;
	poster_url?: string | null;
	max_participants?: number | null;
	status: string;
	created_by?: number;
	registrations_count?: number;
	created_at: string;
}

export interface EventRegistration {
	id: number;
	event_id: number;
	user_id: number;
	registered_at: string;
	user?: TraceUser;
	event?: TraceEvent;
}

// ──────────────────────────────────────────
// Kuesioner
// ──────────────────────────────────────────

export interface Kuesioner {
	id: number;
	judul: string;
	deskripsi?: string | null;
	tahun?: number | null;
	status: string;
	kategori?: string | null;
	tipe_kuesioner?: string | null;
	date_mulai?: string | null;
	date_selesai?: string | null;
	sections?: KuesionerSection[];
	sections_count?: number;
	responses_count?: number;
}

export interface KuesionerSection {
	id: number;
	kuesioner_id: number;
	judul: string;
	urutan: number;
	pertanyaans?: Pertanyaan[];
}

export interface Pertanyaan {
	id: number;
	section_id: number;
	kuesioner_id: number;
	teks: string;
	tipe:
		| "text"
		| "textarea"
		| "radio"
		| "checkbox"
		| "dropdown"
		| "date"
		| "number"
		| "scale"
		| "matrix";
	is_required: boolean;
	urutan: number;
	meta?: Record<string, unknown> | null;
	opsi_jawabans?: OpsiJawaban[];
}

export interface OpsiJawaban {
	id: number;
	pertanyaan_id: number;
	label: string;
	value?: string | null;
	urutan: number;
}

// ──────────────────────────────────────────
// Statistics & Analytics
// ──────────────────────────────────────────

export interface ProdiStat {
	prodi: string;
	total: number;
	absorbed: number;
	rate: number;
}

export interface KuesionerStat {
	id: number;
	judul: string;
	tahun: number;
	status: string;
	responses: number;
	response_rate: number;
}

export interface WaitingTimeProdi {
	prodi: string;
	avg_months: number;
	count: number;
	min_months: number;
	max_months: number;
}

export interface WaitingDistribution {
	label: string;
	count: number;
}

export interface CareerStatusItem {
	label: string;
	value: number;
}

export interface AngkatanItem {
	angkatan: number;
	total: number;
}

export interface SektorItem {
	sektor_industri: string;
	total: number;
}

export interface ProdiDistributionItem {
	program_studi: string;
	total: number;
}

// ──────────────────────────────────────────
// Map
// ──────────────────────────────────────────

export interface MapMarker {
	nama_lengkap: string;
	profil_alumni_id: number;
	nim: string;
	angkatan: number;
	program_studi: string;
	instansi: string;
	detail: string;
	sektor_industri: string;
	nama_kota: string;
	latitude: number;
	longitude: number;
	tipe_lokasi: string;
}

export interface MapMeta {
	total_alumni: number;
	mapped_count: number;
	completion_rate: number;
	is_filter_active: boolean;
	filtered: {
		total: number;
		mapped: number;
		rate: number;
	};
}

// ──────────────────────────────────────────
// Activity Log
// ──────────────────────────────────────────

export interface ActivityLogEntry {
	id: number;
	user_id: number;
	action: string;
	description: string;
	loggable_type?: string | null;
	loggable_id?: number | null;
	metadata?: Record<string, unknown> | null;
	created_at: string;
	user?: TraceUser;
}

// ──────────────────────────────────────────
// Bookmarks
// ──────────────────────────────────────────

export interface Bookmark {
	id: number;
	user_id: number;
	job_id: number;
	created_at: string;
	job?: JobListing;
}

// ──────────────────────────────────────────
// Dashboard
// ──────────────────────────────────────────

export interface DashboardStats {
	totalAlumni: number;
	employmentRate: number;
	studiLanjut: number;
	[key: string]: number;
}

export interface ProfileCompleteness {
	percentage: number;
	items: CompletenessItem[];
}

export interface CompletenessItem {
	label: string;
	completed: boolean;
	weight: number;
}

export interface UpcomingEvent {
	id: number;
	title: string;
	event_date: string;
	location?: string | null;
	poster_url?: string | null;
	registrations_count?: number;
	is_registered?: boolean;
}

export interface RecentApplication {
	id: number;
	job_title: string;
	company_name: string;
	status: string;
	applied_at: string;
}
