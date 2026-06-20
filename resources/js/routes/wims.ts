import { createRoute } from "../wayfinder";

const dashboard = createRoute("/wims/dashboard", "get");
const profile = createRoute("/wims/profil", "get");
const registration = createRoute("/wims/pendaftaran", "get");
const attendance = createRoute("/wims/absensi", "get");
const laporan = createRoute("/wims/laporan", "get");
const logbook = createRoute("/wims/logbook", "get");

const absensi = {
	store: createRoute("/wims/absensi", "post"),
	download: createRoute("/wims/absensi/download", "get"),
	checkout: createRoute("/wims/absensi/checkout", "post"),
};

const registrationActions = {
	index: registration,
	store: createRoute("/wims/pendaftaran", "post"),
};

const logbookActions = {
	index: logbook,
	store: createRoute("/wims/logbook", "post"),
	update: (logbookId: string | number) => createRoute(`/wims/logbook/${logbookId}`, "put"),
	download: createRoute("/wims/logbook/download", "get"),
};

const laporanActions = {
	index: laporan,
	store: createRoute("/wims/laporan", "post"),
	finalReport: {
		view: createRoute("/wims/laporan/final-report/view", "get"),
		download: createRoute("/wims/laporan/final-report/download", "get"),
	},
};

const monitoringShow = (mahasiswa: string | number, options?: { query?: Record<string, unknown> }) =>
	createRoute(`/wims/dosen/monitoring/${mahasiswa}`, "get")(options);

const assessmentBase = (path: string) => createRoute(path, "get");

const assessmentRoutes = {
	index: assessmentBase("/wims/dosen/penilaian-mahasiswa"),
	show: (pendaftaran: string | number) =>
		createRoute(`/wims/dosen/penilaian-mahasiswa/${pendaftaran}`, "get"),
	store: (pendaftaran: string | number) =>
		createRoute(`/wims/dosen/penilaian-mahasiswa/${pendaftaran}`, "post"),
	finalReport: {
		view: (pendaftaran: string | number) =>
			createRoute(`/wims/dosen/penilaian-mahasiswa/${pendaftaran}/final-report/view`, "get"),
		download: (pendaftaran: string | number) =>
			createRoute(`/wims/dosen/penilaian-mahasiswa/${pendaftaran}/final-report/download`, "get"),
	},
};

const admin = {
	dashboard: createRoute("/wims/admin/dashboard", "get"),
	registrations: {
		index: createRoute("/wims/admin/pendaftaran", "get"),
		updateStatus: (pendaftaran: string | number) =>
			createRoute(`/wims/admin/pendaftaran/${pendaftaran}/status`, "patch"),
	},
	placements: {
		index: createRoute("/wims/admin/penempatan", "get"),
		update: (pendaftaran: string | number) =>
			createRoute(`/wims/admin/penempatan/${pendaftaran}`, "put"),
		generateSurat: (pendaftaran: string | number) =>
			createRoute(`/wims/admin/penempatan/${pendaftaran}/generate-surat`, "post"),
		activate: (pendaftaran: string | number) =>
			createRoute(`/wims/admin/penempatan/${pendaftaran}/activate`, "post"),
		complete: (pendaftaran: string | number) =>
			createRoute(`/wims/admin/penempatan/${pendaftaran}/complete`, "post"),
	},
	assessmentTemplates: {
		index: createRoute("/wims/admin/penilaian-template", "get"),
		store: createRoute("/wims/admin/penilaian-template", "post"),
		update: (assessmentTemplate: string | number) =>
			createRoute(`/wims/admin/penilaian-template/${assessmentTemplate}`, "put"),
		destroy: (assessmentTemplate: string | number) =>
			createRoute(`/wims/admin/penilaian-template/${assessmentTemplate}`, "delete"),
	},
};

const dosen = {
	dashboard: createRoute("/wims/dosen/dashboard", "get"),
	monitoring: {
		index: createRoute("/wims/dosen/monitoring", "get"),
		show: monitoringShow,
	},
	assessments: assessmentRoutes,
};

export default {
	dashboard: () => dashboard,
	profile: () => profile,
	registration: () => registration,
	attendance: () => attendance,
	laporan: () => laporan,
	logbook: () => logbook,
	absensi,
	admin,
	dosen,
};
