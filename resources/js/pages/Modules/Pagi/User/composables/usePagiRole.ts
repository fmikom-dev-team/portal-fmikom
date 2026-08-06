/**
 * usePagiRole — Shared composable untuk deteksi role aktif di modul PAGI.
 *
 * Mengabstraksi logika pembacaan active_role dari Inertia shared context
 * dan prop roleName ke dalam satu sumber kebenaran tunggal, mencegah
 * inkonsistensi antar halaman (lihat bug: Gallery.vue isMahasiswa undefined).
 *
 * Penggunaan:
 *   import { usePagiRole } from "../composables/usePagiRole";
 *
 *   // Dengan prop roleName dari controller
 *   const { isMahasiswa, isCreator } = usePagiRole(props.roleName);
 *
 *   // Tanpa prop (langsung baca dari Inertia shared context)
 *   const { isMahasiswa, isCreator } = usePagiRole();
 */

import { usePage } from "@inertiajs/vue3";
import { computed, type Ref } from "vue";

/** Slug role yang diakui sebagai admin di modul PAGI. */
const ADMIN_ROLES = [
	"super-admin",
	"admin",
	"prodi",
	"admin-universitas",
	"admin-akademik",
] as const;

/**
 * Slug role yang mendapat tampilan "Kreator" (Navbar penuh + CV Builder + Portfolio).
 * Alumni termasuk karena punya akses serupa Mahasiswa di PAGI.
 * Super Admin & Admin juga disertakan agar pengujian fitur publik selalu mendapat Navbar lengkap.
 */
const CREATOR_ROLES = ["mahasiswa", "alumni", "super-admin", "admin"] as const;

/**
 * Slug role yang mendapat tampilan "Visitor/Umum" (UmumNavbar, tanpa CV).
 */
const VISITOR_ROLES = ["dosen", "mitra"] as const;

export type PagiRoleSlug =
	| "mahasiswa"
	| "alumni"
	| "dosen"
	| "mitra"
	| "super-admin"
	| "admin"
	| "prodi"
	| "admin-universitas"
	| "admin-akademik"
	| string;

/**
 * @param roleProp - Role dari prop komponen (string atau Ref<string>). Jika
 *   tidak diberikan, composable membaca dari Inertia shared context.
 */
export function usePagiRole(roleProp?: string | Ref<string | undefined>) {
	const page = usePage();

	/**
	 * Role aktif yang sudah di-resolve dan di-lowercase.
	 * Priority: roleProp → context.active_role → string kosong.
	 *
	 * TIDAK ada hardcoded fallback "mahasiswa" di sini — jika role tidak
	 * diketahui, semua `is*` flag akan false. Ini lebih aman dari pada
	 * mengasumsikan role tertentu secara diam-diam.
	 */
	const activeRole = computed((): PagiRoleSlug => {
		const fromProp =
			typeof roleProp === "string" ? roleProp : (roleProp?.value ?? undefined);

		const fromContext = (page.props as any).context?.active_role as
			| string
			| undefined;

		return (fromProp || fromContext || "").toLowerCase();
	});

	/** User adalah Mahasiswa aktif. */
	const isMahasiswa = computed(() => activeRole.value === "mahasiswa");

	/** User adalah Alumni FMIKOM. */
	const isAlumni = computed(() => activeRole.value === "alumni");

	/**
	 * "Kreator" = Mahasiswa atau Alumni.
	 * Keduanya mendapat akses penuh: Navbar lengkap, CV Builder, Portfolio editor.
	 */
	const isCreator = computed(() =>
		(CREATOR_ROLES as readonly string[]).includes(activeRole.value),
	);

	/** User adalah Dosen / Struktural. */
	const isDosen = computed(() => activeRole.value === "dosen");

	/** User adalah Mitra Perusahaan. */
	const isMitra = computed(() => activeRole.value === "mitra");

	/**
	 * "Visitor/Umum" = Dosen atau Mitra.
	 * Mendapat UmumNavbar — tanpa CV Builder, tanpa portofolio editor.
	 */
	const isVisitor = computed(() =>
		(VISITOR_ROLES as readonly string[]).includes(activeRole.value),
	);

	/** User memiliki role admin di modul PAGI. */
	const isAdmin = computed(() =>
		(ADMIN_ROLES as readonly string[]).includes(activeRole.value),
	);

	/**
	 * Nama tampilan role yang ramah pengguna (Title Case).
	 */
	const displayRoleName = computed((): string => {
		const map: Record<string, string> = {
			mahasiswa: "Mahasiswa",
			alumni: "Alumni",
			dosen: "Dosen",
			mitra: "Mitra Perusahaan",
			"super-admin": "Super Admin",
			admin: "Admin",
			prodi: "Koordinator Prodi",
			"admin-universitas": "Admin Universitas",
			"admin-akademik": "Admin Akademik",
		};
		return (
			map[activeRole.value] ||
			(activeRole.value
				? activeRole.value.charAt(0).toUpperCase() + activeRole.value.slice(1)
				: "")
		);
	});

	return {
		activeRole,
		isMahasiswa,
		isAlumni,
		isCreator,
		isDosen,
		isMitra,
		isVisitor,
		isAdmin,
		displayRoleName,
	};
}
