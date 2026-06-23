/**
 * Centralized route constants for the Trace module.
 * Eliminates hardcoded URL strings across components.
 */
export const TRACE_ROUTES = {
	// Admin
	ADMIN_DASHBOARD: "/trace/admin",
	ADMIN_STATISTICS: "/trace/admin/statistics",
	ADMIN_MAP: "/trace/admin/map",
	ADMIN_MAP_DATA: "/trace/admin/map/data",
	ADMIN_ALUMNI: "/trace/admin/alumni",
	ADMIN_ALUMNI_DETAIL: (id: number) => `/trace/admin/alumni/${id}`,
	ADMIN_QUESTIONNAIRES: "/trace/admin/questionnaires",
	ADMIN_QUESTIONNAIRE_DETAIL: (id: number) =>
		`/trace/admin/questionnaires/${id}`,
	ADMIN_QUESTIONNAIRE_ANALYTICS: (id: number) =>
		`/trace/admin/questionnaires/${id}/analytics`,
	ADMIN_JOBS: "/trace/admin/jobs",
	ADMIN_JOB_DETAIL: (id: number) => `/trace/admin/jobs/${id}`,
	ADMIN_EVENTS: "/trace/admin/events",
	ADMIN_EVENT_DETAIL: (id: number) => `/trace/admin/events/${id}`,
	ADMIN_ACTIVITY_LOG: "/trace/admin/activity-log",
	ADMIN_RESPONDENTS: "/trace/admin/respondents",

	// Alumni
	ALUMNI_DASHBOARD: "/trace",
	ALUMNI_PROFILE: "/trace/profile",
	ALUMNI_CAREER: "/trace/career",
	ALUMNI_JOBS: "/trace/jobs",
	ALUMNI_JOB_DETAIL: (id: number) => `/trace/jobs/${id}`,
	ALUMNI_JOB_APPLY: (id: number) => `/trace/jobs/${id}/apply`,
	ALUMNI_EVENTS: "/trace/events",
	ALUMNI_EVENT_DETAIL: (id: number) => `/trace/events/${id}`,
	ALUMNI_KUESIONER: "/trace/questionnaires",
	ALUMNI_KUESIONER_FILL: (id: number) => `/trace/questionnaires/${id}`,
	ALUMNI_MY_APPLICATIONS: "/trace/my-applications",
	ALUMNI_MY_BOOKMARKS: "/trace/my-bookmarks",

	// Mitra
	MITRA_DASHBOARD: "/trace/mitra",
	MITRA_PROFILE: "/trace/mitra/profile",
	MITRA_JOBS: "/trace/mitra/jobs",
	MITRA_JOB_CREATE: "/trace/mitra/jobs/create",
	MITRA_JOB_DETAIL: (id: number) => `/trace/mitra/jobs/${id}`,

	// API
	API_MAP_DATA: "/trace/admin/map/data",
	API_NOTIFICATIONS_MARK_READ: (id: string) =>
		`/trace/notifications/${id}/read`,
	API_NOTIFICATIONS_MARK_ALL_READ: "/trace/notifications/mark-all-read",
} as const;
