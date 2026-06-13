-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 05 Jun 2026 pada 12.09
-- Versi server: 8.4.3
-- Versi PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `portal-fmikom`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_magangs`
--

CREATE TABLE `absensi_magangs` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `waktu_keluar` time DEFAULT NULL,
  `latitude_masuk` decimal(10,8) DEFAULT NULL,
  `longitude_masuk` decimal(11,8) DEFAULT NULL,
  `latitude_keluar` decimal(10,8) DEFAULT NULL,
  `longitude_keluar` decimal(11,8) DEFAULT NULL,
  `lokasi_valid` tinyint(1) NOT NULL DEFAULT '0',
  `foto_bukti_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_bukti_checkout_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `timestamp_masuk` timestamp NULL DEFAULT NULL,
  `timestamp_keluar` timestamp NULL DEFAULT NULL,
  `distance_masuk` double DEFAULT NULL,
  `distance_keluar` double DEFAULT NULL,
  `ip_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `absensi_magangs`
--

INSERT INTO `absensi_magangs` (`id`, `pendaftaran_id`, `tanggal`, `waktu_masuk`, `waktu_keluar`, `latitude_masuk`, `longitude_masuk`, `latitude_keluar`, `longitude_keluar`, `lokasi_valid`, `foto_bukti_path`, `foto_bukti_checkout_path`, `status`, `keterangan`, `created_at`, `updated_at`, `timestamp_masuk`, `timestamp_keluar`, `distance_masuk`, `distance_keluar`, `ip_address`, `user_agent`) VALUES
(25, 7, '2026-05-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'izin', 'izin acara keluarga', '2026-05-12 16:35:26', '2026-05-12 16:35:26', NULL, NULL, NULL, NULL, NULL, NULL),
(26, 7, '2026-05-16', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'izin', 'izin acara keluarga', '2026-05-12 16:35:26', '2026-05-12 16:35:26', NULL, NULL, NULL, NULL, NULL, NULL),
(27, 7, '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(28, 7, '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(29, 7, '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(30, 7, '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(31, 7, '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(32, 7, '2026-05-18', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(33, 7, '2026-05-19', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(34, 7, '2026-05-20', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 15:56:55', '2026-05-21 15:56:55', NULL, NULL, NULL, NULL, NULL, NULL),
(59, 7, '2026-05-21', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-21 17:07:49', '2026-05-21 17:07:49', NULL, NULL, NULL, NULL, NULL, NULL),
(60, 7, '2026-05-22', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-25 17:40:49', '2026-05-25 17:40:49', NULL, NULL, NULL, NULL, NULL, NULL),
(61, 7, '2026-05-23', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-25 17:40:49', '2026-05-25 17:40:49', NULL, NULL, NULL, NULL, NULL, NULL),
(62, 7, '2026-05-25', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-25 17:40:49', '2026-05-25 17:40:49', NULL, NULL, NULL, NULL, NULL, NULL),
(63, 7, '2026-05-26', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-27 03:54:34', '2026-05-27 03:54:34', NULL, NULL, NULL, NULL, NULL, NULL),
(64, 7, '2026-06-02', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'izin', 'Acara nikahan tetangga', '2026-05-27 15:30:43', '2026-05-27 15:30:43', NULL, NULL, NULL, NULL, NULL, NULL),
(65, 7, '2026-05-27', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-28 00:35:03', '2026-05-28 00:35:03', NULL, NULL, NULL, NULL, NULL, NULL),
(66, 7, '2026-05-28', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-05-28 18:36:00', '2026-05-28 18:36:00', NULL, NULL, NULL, NULL, NULL, NULL),
(67, 7, '2026-05-29', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:00:15', '2026-06-03 17:00:15', NULL, NULL, NULL, NULL, NULL, NULL),
(68, 7, '2026-05-30', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:00:15', '2026-06-03 17:00:15', NULL, NULL, NULL, NULL, NULL, NULL),
(69, 7, '2026-06-03', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:00:15', '2026-06-03 17:00:15', NULL, NULL, NULL, NULL, NULL, NULL),
(70, 8, '2026-05-29', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:15:48', '2026-06-03 17:15:48', NULL, NULL, NULL, NULL, NULL, NULL),
(71, 8, '2026-05-30', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:15:48', '2026-06-03 17:15:48', NULL, NULL, NULL, NULL, NULL, NULL),
(72, 8, '2026-06-02', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:15:48', '2026-06-03 17:15:48', NULL, NULL, NULL, NULL, NULL, NULL),
(73, 8, '2026-06-03', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-03 17:15:48', '2026-06-03 17:15:48', NULL, NULL, NULL, NULL, NULL, NULL),
(74, 8, '2026-06-04', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-04 17:54:05', '2026-06-04 17:54:05', NULL, NULL, NULL, NULL, NULL, NULL),
(75, 7, '2026-06-04', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'alfa', 'Tidak hadir tanpa check-in.', '2026-06-04 17:54:05', '2026-06-04 17:54:05', NULL, NULL, NULL, NULL, NULL, NULL),
(76, 9, '2026-06-05', '05:56:05', NULL, -7.66299200, 109.10209800, NULL, NULL, 1, 'absensi/MTmgnrqULqwVoERlDWduv7wSscjwIjk9frXTiCLd.jpg', NULL, 'hadir', NULL, '2026-06-04 22:56:05', '2026-06-04 22:56:05', '2026-06-04 22:56:05', NULL, 4.8811536145763, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_components`
--

CREATE TABLE `assessment_components` (
  `id` bigint UNSIGNED NOT NULL,
  `assessment_template_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `weight_percentage` decimal(5,2) NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assessment_components`
--

INSERT INTO `assessment_components` (`id`, `assessment_template_id`, `name`, `description`, `weight_percentage`, `sort_order`, `created_at`, `updated_at`) VALUES
(23, 3, 'Disiplin', NULL, 15.00, 1, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(24, 3, 'Komunikasi', NULL, 10.00, 2, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(25, 3, 'Kerja Tim', NULL, 15.00, 3, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(26, 3, 'Kerja Mandiri', NULL, 10.00, 4, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(27, 3, 'Penampilan', NULL, 10.00, 5, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(28, 3, 'Perilaku', NULL, 20.00, 6, '2026-05-28 19:30:24', '2026-05-28 19:30:24'),
(29, 3, 'Pengetahuan / Kemampuan Adaptif', NULL, 20.00, 7, '2026-05-28 19:30:24', '2026-05-28 19:30:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_scores`
--

CREATE TABLE `assessment_scores` (
  `id` bigint UNSIGNED NOT NULL,
  `assessment_submission_id` bigint UNSIGNED NOT NULL,
  `assessment_component_id` bigint UNSIGNED NOT NULL,
  `score` decimal(8,2) NOT NULL,
  `weighted_score` decimal(8,2) NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_submissions`
--

CREATE TABLE `assessment_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_magang_id` bigint UNSIGNED NOT NULL,
  `assessment_template_id` bigint UNSIGNED NOT NULL,
  `assessor_id` bigint UNSIGNED NOT NULL,
  `assessor_role` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_score` decimal(8,2) DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_templates`
--

CREATE TABLE `assessment_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `periode_mulai` date NOT NULL,
  `periode_selesai` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assessment_templates`
--

INSERT INTO `assessment_templates` (`id`, `name`, `description`, `periode_mulai`, `periode_selesai`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'Periode 2026-2027', NULL, '2026-01-01', '2026-12-31', 1, 5, '2026-05-28 19:30:24', '2026-06-03 17:08:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-98395f851e3b560d19c1b72811208436', 'i:1;', 1780647729),
('laravel-cache-98395f851e3b560d19c1b72811208436:timer', 'i:1780647729;', 1780647729),
('laravel-cache-a177e431ef2c970e869f2ec15d5631dd', 'i:1;', 1780647627),
('laravel-cache-a177e431ef2c970e869f2ec15d5631dd:timer', 'i:1780647627;', 1780647627);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_respons`
--

CREATE TABLE `detail_respons` (
  `id` bigint UNSIGNED NOT NULL,
  `respons_id` bigint UNSIGNED NOT NULL,
  `pertanyaan_id` bigint UNSIGNED NOT NULL,
  `pilihan_id` bigint UNSIGNED DEFAULT NULL,
  `jawaban_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fakultas`
--

CREATE TABLE `fakultas` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fakultas`
--

INSERT INTO `fakultas` (`id`, `nama`, `kode`, `created_at`, `updated_at`) VALUES
(1, 'FMIKOM', 'FMIKOM', '2026-04-26 10:46:47', '2026-04-26 10:46:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `hari_liburs`
--

CREATE TABLE `hari_liburs` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `hari_liburs`
--

INSERT INTO `hari_liburs` (`id`, `tanggal`, `nama`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2026-01-01', 'Tahun Baru Masehi', 1, '2026-04-25 16:55:00', '2026-04-25 16:55:00'),
(2, '2026-04-03', 'Wafat Isa Almasih', 1, '2026-04-25 16:55:00', '2026-04-25 16:55:00'),
(3, '2026-04-05', 'Paskah', 1, '2026-04-25 16:55:00', '2026-04-25 16:55:00'),
(4, '2026-05-01', 'Hari Buruh Internasional', 1, '2026-04-25 16:55:00', '2026-04-25 16:55:00'),
(5, '2026-05-14', 'Kenaikan Isa Almasih', 1, '2026-04-25 16:55:01', '2026-04-25 16:55:01'),
(6, '2026-06-01', 'Hari Lahir Pancasila', 1, '2026-04-25 16:55:01', '2026-04-25 16:55:01'),
(7, '2026-08-17', 'Hari Ulang Tahun Kemerdekaan Republik Indonesia', 1, '2026-04-25 16:55:01', '2026-04-25 16:55:01'),
(8, '2026-12-25', 'Hari Raya Natal', 1, '2026-04-25 16:55:01', '2026-04-25 16:55:01'),
(9, '2027-01-01', 'Tahun Baru Masehi', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(10, '2027-03-26', 'Wafat Isa Almasih', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(11, '2027-03-28', 'Paskah', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(12, '2027-05-01', 'Hari Buruh Internasional', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(13, '2027-05-06', 'Kenaikan Isa Almasih', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(14, '2027-06-01', 'Hari Lahir Pancasila', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(15, '2027-08-17', 'Hari Ulang Tahun Kemerdekaan Republik Indonesia', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02'),
(16, '2027-12-25', 'Hari Raya Natal', 1, '2026-04-25 16:55:02', '2026-04-25 16:55:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_surats`
--

CREATE TABLE `jenis_surats` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `template_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `karir_alumnis`
--

CREATE TABLE `karir_alumnis` (
  `id` bigint UNSIGNED NOT NULL,
  `alumni_id` bigint UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bidang_industri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `negara` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Indonesia',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `gaji_range` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kerja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ketidakhadiran_magangs`
--

CREATE TABLE `ketidakhadiran_magangs` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_id` bigint UNSIGNED NOT NULL,
  `mahasiswa_id` bigint UNSIGNED NOT NULL,
  `perusahaan_id` bigint UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bukti_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by_mitra_user_id` bigint UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by_mitra_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `catatan_mitra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ketidakhadiran_magangs`
--

INSERT INTO `ketidakhadiran_magangs` (`id`, `pendaftaran_id`, `mahasiswa_id`, `perusahaan_id`, `tanggal_mulai`, `tanggal_selesai`, `jenis`, `alasan`, `bukti_path`, `status`, `reviewed_by_mitra_user_id`, `submitted_at`, `reviewed_by_mitra_at`, `cancelled_at`, `catatan_mitra`, `created_at`, `updated_at`) VALUES
(1, 7, 4, 4, '2026-05-14', '2026-05-16', 'izin', 'izin acara keluarga', NULL, 'approved', 8, '2026-05-12 16:30:43', '2026-05-12 16:35:26', NULL, NULL, '2026-05-12 16:30:43', '2026-05-12 16:35:26'),
(2, 7, 4, 4, '2026-05-18', '2026-05-20', 'izin', 'liburan', NULL, 'rejected', 8, '2026-05-13 03:52:59', '2026-05-13 04:21:57', NULL, NULL, '2026-05-13 03:52:59', '2026-05-13 04:21:57'),
(3, 7, 4, 4, '2026-06-01', '2026-06-02', 'izin', 'Acara nikahan tetangga', NULL, 'approved', 8, '2026-05-27 15:29:14', '2026-05-27 15:30:43', NULL, NULL, '2026-05-27 15:29:14', '2026-05-27 15:30:43'),
(4, 9, 13, 7, '2026-06-05', '2026-06-06', 'izin', 'urusan keluarga', NULL, 'rejected', 12, '2026-06-04 18:38:58', '2026-06-04 18:42:02', NULL, NULL, '2026-06-04 18:38:58', '2026-06-04 18:42:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioners`
--

CREATE TABLE `kuesioners` (
  `id` bigint UNSIGNED NOT NULL,
  `pembuat_id` bigint UNSIGNED NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `periode_mulai` date NOT NULL,
  `periode_selesai` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `tujuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logbook_magangs`
--

CREATE TABLE `logbook_magangs` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `aktivitas_harian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kompetensi_dicapai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_mitra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewed_by_mitra_user_id` bigint UNSIGNED DEFAULT NULL,
  `reviewed_by_mitra_at` timestamp NULL DEFAULT NULL,
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `logbook_magangs`
--

INSERT INTO `logbook_magangs` (`id`, `pendaftaran_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `aktivitas_harian`, `kompetensi_dicapai`, `catatan_mitra`, `reviewed_by_mitra_user_id`, `reviewed_by_mitra_at`, `catatan_dosen`, `status`, `created_at`, `updated_at`) VALUES
(12, 7, '2026-05-20', '10:11:00', '15:00:00', 'qwsqsjishquiqgdyhbdvhfydcf2g1tdr126ry2', 'berhasil bisa', NULL, NULL, NULL, NULL, 'pending', '2026-05-20 14:26:20', '2026-05-20 14:26:20'),
(14, 7, '2026-05-27', '08:00:00', '15:00:00', '1. Membuat desain UI \r\n2. Membuat ketupat', '- memahami tailwindCSS\r\n- memahami membuat ketupat', 'okee', 8, '2026-05-27 16:14:49', NULL, 'disetujui', '2026-05-27 06:29:16', '2026-05-27 16:14:49'),
(15, 7, '2026-06-04', '08:00:00', '16:00:00', '1. membuat design\r\n2. membuat database', '1. mengetahui frontend\r\n2. memahami struktur db', 'good', 8, '2026-06-03 22:16:46', NULL, 'disetujui', '2026-06-03 22:14:18', '2026-06-03 22:16:46'),
(16, 9, '2026-06-05', '08:00:00', '14:10:00', '1. Membuat UI\r\n2. Mempelajari CSS', '1. Memahami FE\r\n2. Memahami tailwind css', 'bagus konsisten', 12, '2026-06-05 07:24:41', NULL, 'disetujui', '2026-06-05 07:23:56', '2026-06-05 07:24:41'),
(17, 7, '2026-06-05', '08:00:00', '15:00:00', '1. Bug Fixing\r\n2. Error Runtime', '1. Mengetahui Error\r\n2. Bug fixed', 'okee sipp', 8, '2026-06-05 08:20:12', NULL, 'disetujui', '2026-06-05 08:01:47', '2026-06-05 08:20:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `logbook_photos`
--

CREATE TABLE `logbook_photos` (
  `id` bigint UNSIGNED NOT NULL,
  `logbook_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `lowongan_infos`
--

CREATE TABLE `lowongan_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `pembuat_id` bigint UNSIGNED NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `poster_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_eksternal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(5, '2026_03_29_180950_create_roles_table', 1),
(6, '2026_03_29_180951_create_fakultas_table', 1),
(7, '2026_03_29_180954_create_program_studis_table', 1),
(8, '2026_03_29_180955_create_perusahaan_mitras_table', 1),
(9, '2026_03_29_180956_create_pembimbing_lapangans_table', 1),
(10, '2026_03_29_180958_create_jenis_surats_table', 1),
(11, '2026_03_29_181000_create_surats_table', 1),
(12, '2026_03_29_181003_create_surat_approval_flows_table', 1),
(13, '2026_03_29_181004_create_surat_lampirans_table', 1),
(14, '2026_03_29_181005_create_pendaftaran_magangs_table', 1),
(15, '2026_03_29_181007_create_absensi_magangs_table', 1),
(16, '2026_03_29_181007_create_logbook_magangs_table', 1),
(17, '2026_03_29_181008_create_penilaian_magangs_table', 1),
(18, '2026_03_29_181009_create_profil_alumnis_table', 1),
(19, '2026_03_29_181010_create_karir_alumnis_table', 1),
(20, '2026_03_29_181011_create_kuesioners_table', 1),
(21, '2026_03_29_181012_create_pertanyaan_kuesioners_table', 1),
(22, '2026_03_29_181013_create_pilihan_jawabans_table', 1),
(23, '2026_03_29_181013_create_respons_kuesioners_table', 1),
(24, '2026_03_29_181014_create_detail_respons_table', 1),
(25, '2026_03_29_181015_create_lowongan_infos_table', 1),
(26, '2026_03_29_181016_add_columns_to_users_table', 1),
(27, '2026_03_29_201237_add_otp_and_password_changed_to_users_table', 2),
(28, '2026_03_29_214305_add_registration_fields_to_users_table', 3),
(29, '2026_04_08_140000_add_working_hours_to_logbook_magangs_table', 4),
(30, '2026_04_16_065813_tambah_kolom_absensi', 5),
(31, '2026_04_16_071005_add_unique_absensi_constraint', 6),
(32, '2026_04_18_000001_add_checkout_photo_to_absensi_magangs_table', 7),
(33, '2026_04_18_235500_create_logbook_photos_table', 8),
(34, '2026_04_21_110000_add_work_rules_to_perusahaan_mitras_table', 9),
(35, '2026_04_22_090000_extend_penilaian_magangs_table', 10),
(36, '2026_04_23_120000_create_surat_penetapans_table', 11),
(37, '2026_04_25_120000_create_hari_liburs_table', 12),
(38, '2026_04_26_090000_add_hari_kerja_to_perusahaan_mitras_table', 13),
(39, '2026_04_26_140000_extend_pendaftaran_magangs_table', 14),
(40, '2026_04_26_160000_add_catatan_revisi_admin_to_pendaftaran_magangs_table', 15),
(41, '2026_04_26_220000_add_laporan_akhir_fields_to_pendaftaran_magangs_table', 16),
(42, '2026_05_07_090000_add_mitra_role', 17),
(43, '2026_05_12_180000_create_ketidakhadiran_magangs_table', 18),
(44, '2026_05_27_140000_link_mitra_accounts_to_companies', 19),
(45, '2026_05_27_150000_drop_legacy_pembimbing_lapangan_dependencies', 20),
(46, '2026_05_27_230000_add_mitra_review_fields_to_logbook_magangs_table', 21),
(47, '2026_05_29_090000_create_assessment_templates_table', 22),
(48, '2026_05_29_100000_create_assessment_submissions_table', 23),
(49, '2026_06_04_120000_add_completed_pkl_flags_and_purge_finished_registrations', 24);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('muchlisinmaruf@gmail.com', '$2y$12$HV9sEnOHXdFBzBUffbISwuXVa2VHXeovZmgdJs1wFWRtymE.sKsJS', '2026-03-29 15:11:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran_magangs`
--

CREATE TABLE `pendaftaran_magangs` (
  `id` bigint UNSIGNED NOT NULL,
  `mahasiswa_id` bigint UNSIGNED NOT NULL,
  `perusahaan_diminati_nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan_diminati_alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_pengajuan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_revisi_admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `laporan_akhir_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_akhir_original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_akhir_uploaded_at` timestamp NULL DEFAULT NULL,
  `perusahaan_id` bigint UNSIGNED DEFAULT NULL,
  `dosen_pembimbing_id` bigint UNSIGNED DEFAULT NULL,
  `surat_tugas_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pendaftaran_magangs`
--

INSERT INTO `pendaftaran_magangs` (`id`, `mahasiswa_id`, `perusahaan_diminati_nama`, `perusahaan_diminati_alamat`, `catatan_pengajuan`, `catatan_revisi_admin`, `laporan_akhir_path`, `laporan_akhir_original_name`, `laporan_akhir_uploaded_at`, `perusahaan_id`, `dosen_pembimbing_id`, `surat_tugas_id`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`) VALUES
(7, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 6, NULL, '2026-05-08', '2026-06-08', 'aktif', '2026-05-08 15:41:57', '2026-05-12 11:04:08'),
(8, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 6, NULL, '2026-05-29', '2026-06-29', 'aktif', '2026-05-28 10:56:02', '2026-05-28 13:08:35'),
(9, 13, NULL, NULL, 'sesuai passion saya', NULL, NULL, NULL, NULL, 7, 6, NULL, '2026-06-05', '2026-06-06', 'aktif', '2026-06-04 06:48:22', '2026-06-04 07:09:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian_magangs`
--

CREATE TABLE `penilaian_magangs` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_id` bigint UNSIGNED NOT NULL,
  `dosen_id` bigint UNSIGNED DEFAULT NULL,
  `nilai_disiplin` decimal(5,2) DEFAULT NULL,
  `nilai_komunikasi` decimal(5,2) DEFAULT NULL,
  `nilai_kerja_tim` decimal(5,2) DEFAULT NULL,
  `nilai_kerja_mandiri` decimal(5,2) DEFAULT NULL,
  `nilai_penampilan` decimal(5,2) DEFAULT NULL,
  `nilai_perilaku` decimal(5,2) DEFAULT NULL,
  `nilai_pengetahuan_adaptif` decimal(5,2) DEFAULT NULL,
  `nilai_logbook` decimal(5,2) DEFAULT NULL,
  `nilai_presentasi` decimal(5,2) DEFAULT NULL,
  `nilai_dari_pembimbing` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_pembimbing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_nilai` datetime DEFAULT NULL,
  `status_penilaian` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `dosen_submitted_at` timestamp NULL DEFAULT NULL,
  `pembimbing_submitted_at` timestamp NULL DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pertanyaan_kuesioners`
--

CREATE TABLE `pertanyaan_kuesioners` (
  `id` bigint UNSIGNED NOT NULL,
  `kuesioner_id` bigint UNSIGNED NOT NULL,
  `teks_pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `wajib_diisi` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `perusahaan_mitras`
--

CREATE TABLE `perusahaan_mitras` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `mitra_jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `radius_valid_meter` decimal(10,2) DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `toleransi_terlambat_menit` int UNSIGNED NOT NULL DEFAULT '0',
  `hari_kerja` json DEFAULT NULL,
  `bidang_industri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kontak_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `perusahaan_mitras`
--

INSERT INTO `perusahaan_mitras` (`id`, `user_id`, `mitra_jabatan`, `nama`, `alamat`, `kota`, `latitude`, `longitude`, `radius_valid_meter`, `jam_masuk`, `jam_pulang`, `toleransi_terlambat_menit`, `hari_kerja`, `bidang_industri`, `kontak_person`, `telepon`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'UNUGHA CILACAP', 'Kesugihan Kidul, Cilacap, Jawa Tengah, Jawa, 53274, Indonesia', 'CILACAP', -7.62508451, 109.11345597, 100.00, '08:00:00', '16:00:00', 15, '[\"monday\", \"wednesday\", \"thursday\", \"friday\", \"tuesday\", \"saturday\"]', 'Universitas', NULL, '085647818779', NULL, 1, '2026-04-16 14:46:05', '2026-04-25 17:44:38'),
(2, NULL, NULL, 'BMKG CILACAP', 'BMKG Kab Cilacap, Jalan Jenderal Gatot Subroto, RW 10 Kelurahan Sidanegara, Sidanegara, Kelurahan Sidanegara Kecamatan Cilacap, Cilacap, Jawa Tengah, Jawa, 53223, Indonesia', 'CILACAP', -7.71906450, 109.01432150, 50.00, '08:00:00', '15:00:00', 15, '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\"]', 'LAYANAN CUACA MASYARAKAT', NULL, NULL, NULL, 1, '2026-04-21 04:23:25', '2026-04-25 17:41:40'),
(4, 8, 'CEO', 'PT Nocola IOT Solution', 'Jalan Insinyur Juanda, RW 16 Kelurahan Sidanegara, Kelurahan Sidanegara Kecamatan Cilacap, Cilacap, Jawa Tengah, Jawa, 53223, Indonesia', 'CILACAP', -7.69094336, 109.02154729, 100.00, '08:00:00', '15:00:00', 5, '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\"]', 'IOT', NULL, NULL, NULL, 1, '2026-05-02 14:39:02', '2026-05-07 03:52:08'),
(5, NULL, 'bebas', 'GOOGLE', 'Tarogong, Tarogong Kidul, Garut, Jawa Barat, Jawa, 44116, Indonesia', 'CILACAP', -7.19263115, 107.89553251, 200.00, '08:00:00', '16:00:00', 13, '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\"]', 'SOFTWARE', NULL, NULL, NULL, 1, '2026-05-13 03:59:39', '2026-05-13 04:01:00'),
(7, 12, 'CEO', 'R CREATIVE', 'Kalisabuk, Cilacap, Jawa Tengah, Jawa, 53274, Indonesia', 'Cilacap', -7.66300195, 109.10214114, 100.00, '08:00:00', '16:00:00', 10, '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\"]', 'Website dan Aplikasi', NULL, NULL, NULL, 1, '2026-06-04 00:32:28', '2026-06-04 06:15:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pilihan_jawabans`
--

CREATE TABLE `pilihan_jawabans` (
  `id` bigint UNSIGNED NOT NULL,
  `pertanyaan_id` bigint UNSIGNED NOT NULL,
  `teks_pilihan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `profil_alumnis`
--

CREATE TABLE `profil_alumnis` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `program_studi_id` bigint UNSIGNED NOT NULL,
  `nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_lulus` int NOT NULL,
  `no_telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `program_studis`
--

CREATE TABLE `program_studis` (
  `id` bigint UNSIGNED NOT NULL,
  `fakultas_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `program_studis`
--

INSERT INTO `program_studis` (`id`, `fakultas_id`, `nama`, `kode`, `created_at`, `updated_at`) VALUES
(1, 1, 'INFORMATIKA', 'IF', '2026-04-26 10:46:47', '2026-04-26 10:46:47'),
(2, 1, 'SISTEM INFORMASI', 'SI', '2026-04-26 10:46:47', '2026-04-26 10:46:47'),
(3, 1, 'MATEMATIKA', 'MTK', '2026-04-26 10:46:47', '2026-04-26 10:46:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `respons_kuesioners`
--

CREATE TABLE `respons_kuesioners` (
  `id` bigint UNSIGNED NOT NULL,
  `kuesioner_id` bigint UNSIGNED NOT NULL,
  `alumni_id` bigint UNSIGNED NOT NULL,
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal_isi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `nama`, `slug`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', NULL, '2026-03-29 13:23:50', '2026-03-29 13:23:50'),
(2, 'User / Mahasiswa', 'user', NULL, '2026-03-29 13:23:50', '2026-03-29 13:23:50'),
(3, 'Dosen Pembimbing', 'dosen', 'Role untuk dosen pembimbing WIMS', '2026-04-20 09:57:22', '2026-04-20 09:57:22'),
(4, 'Pembimbing Lapangan Mitra', 'mitra', 'Akun pembimbing lapangan dari perusahaan mitra.', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8QPPGMBAmZfgdk18LfB4FGRlJcYdPwQORBgiZfpL', 8, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidVJTNkZ5bmxkeThOVUt3S1U1REpvekZBWWZnUXlyY0x3Uk9XbGpVMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6ODt9', 1780646580),
('fq8TfK1gt0C1NKG1t80v5UVqMBbeCU6Rj3XiqrfR', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiV0dkVVI5OFBzZUcxTmpXQ3BqMEFTd1pxaUJtVkNlTng1VXVHY0RPTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780645672),
('Lb8McwR2m2BsP7PU1tei8uheAZfXQQutqC8auw4P', NULL, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWWxTUkxvb3plcFMxVTZlc2R0VHZJeU1WTFRCemh5cFBEbTV3Qk41NyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3dpbXMvbG9nYm9vayI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1780658431),
('SJ9Ly1MRVApeMglSJpeMslssAS5OoLwqgsY1PhPj', 4, '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibGsybjJHRDFOT1JqRnM0ajdTZzlmNmw3bFAzVVdYR2wxbDc2ZXZPdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDt9', 1780647675);

-- --------------------------------------------------------

--
-- Struktur dari tabel `surats`
--

CREATE TABLE `surats` (
  `id` bigint UNSIGNED NOT NULL,
  `jenis_surat_id` bigint UNSIGNED NOT NULL,
  `pemohon_id` bigint UNSIGNED NOT NULL,
  `nomor_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keperluan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `isi_surat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_pengajuan` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_approval_flows`
--

CREATE TABLE `surat_approval_flows` (
  `id` bigint UNSIGNED NOT NULL,
  `surat_id` bigint UNSIGNED NOT NULL,
  `approver_id` bigint UNSIGNED NOT NULL,
  `urutan` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_aksi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_lampirans`
--

CREATE TABLE `surat_lampirans` (
  `id` bigint UNSIGNED NOT NULL,
  `surat_id` bigint UNSIGNED NOT NULL,
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_penetapans`
--

CREATE TABLE `surat_penetapans` (
  `id` bigint UNSIGNED NOT NULL,
  `pendaftaran_id` bigint UNSIGNED NOT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fast',
  `fast_reference_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `surat_penetapans`
--

INSERT INTO `surat_penetapans` (`id`, `pendaftaran_id`, `requested_by`, `status`, `provider`, `fast_reference_id`, `nomor_surat`, `file_url`, `requested_at`, `generated_at`, `error_message`, `payload_snapshot`, `created_at`, `updated_at`) VALUES
(3, 7, 5, 'requested', 'fast', NULL, NULL, NULL, '2026-05-12 10:49:52', NULL, 'Menunggu integrasi modul FASt.', '{\"periode\": {\"mulai\": \"2026-05-07T17:00:00.000000Z\", \"selesai\": \"2026-06-07T17:00:00.000000Z\"}, \"mahasiswa\": {\"nama\": \"Rifal Abdussyakur\", \"email\": \"rifalj73@gmail.com\", \"nomor_induk\": \"22EO10040\"}, \"perusahaan\": {\"id\": 4, \"nama\": \"PT Nocola IOT Solution\"}, \"pendaftaran_id\": 7, \"dosen_pembimbing_id\": 6}', '2026-05-12 10:49:52', '2026-05-12 10:49:52'),
(4, 8, 5, 'requested', 'fast', NULL, NULL, NULL, '2026-05-28 12:56:18', NULL, 'Menunggu integrasi modul FASt.', '{\"periode\": {\"mulai\": \"2026-05-28T17:00:00.000000Z\", \"selesai\": \"2026-06-28T17:00:00.000000Z\"}, \"mahasiswa\": {\"nama\": \"DZIKRI NAJA\", \"email\": \"rifalfalj11@gmail.com\", \"nomor_induk\": \"22EO10041\"}, \"perusahaan\": {\"id\": 2, \"nama\": \"BMKG CILACAP\"}, \"pendaftaran_id\": 8, \"dosen_pembimbing_id\": 6}', '2026-05-28 12:56:18', '2026-05-28 12:56:18'),
(5, 9, 5, 'requested', 'fast', NULL, NULL, NULL, '2026-06-04 07:09:04', NULL, 'Menunggu integrasi modul FASt.', '{\"periode\": {\"mulai\": \"2026-06-04T17:00:00.000000Z\", \"selesai\": \"2026-06-05T17:00:00.000000Z\"}, \"mahasiswa\": {\"nama\": \"Killian Mbadog\", \"email\": \"killianmbadog@gmail.com\", \"nomor_induk\": \"22EO10042\"}, \"perusahaan\": {\"id\": 7, \"nama\": \"R CREATIVE\"}, \"pendaftaran_id\": 9, \"dosen_pembimbing_id\": 6}', '2026-06-04 07:09:04', '2026-06-04 07:09:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_induk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_approval` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `program_studi_id` bigint UNSIGNED DEFAULT NULL,
  `nim_nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `has_completed_pkl` tinyint(1) NOT NULL DEFAULT '0',
  `pkl_completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `nomor_induk`, `status_approval`, `email_verified_at`, `password`, `otp_code`, `otp_expires_at`, `password_changed_at`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`, `role_id`, `program_studi_id`, `nim_nip`, `no_telepon`, `foto_path`, `is_active`, `has_completed_pkl`, `pkl_completed_at`) VALUES
(3, 'Maruf Muchlisin', 'cahwangon27112003@gmail.com', '22eo10013', 'approved', NULL, '$2y$12$pTB6zUDqA7NvUZk0qp/o0.Bi4PYI9qqusho8spU.D4hHIXcrvDF2.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 14:50:10', '2026-03-29 14:50:10', 2, NULL, NULL, NULL, NULL, 1, 0, NULL),
(4, 'Rifal Abdussyakur', 'rifalj73@gmail.com', '22EO10040', 'approved', '2026-04-20 09:57:22', '$2y$12$lQV1NpoUymz2aFdUBwLCmuRyKeUAK1ZK3jPWoVpNueTDLYczd7DAe', NULL, NULL, NULL, NULL, NULL, NULL, 'TNrDmSXMtHVmY55uNyXsiO7UV6QJVNTOcvZoVjolPwP8t2IG6GK9zaPB8B4v', '2026-04-07 22:22:21', '2026-06-05 07:36:51', 2, 1, NULL, '085647623106', 'profile-photos/mz5RNsKtZD6DTOC8DDPPnWLOUfVHKgklhCneji9M.png', 1, 1, '2026-06-04 00:21:57'),
(5, 'Admin WIMS', 'akunadmin@gmail.com', NULL, 'approved', '2026-04-20 09:57:22', '$2y$12$PsBJrqJDLQlhK.VBo7EhFeA.YLuMpzHo9z8UhQ3yKrk/hpxG7RfF2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-20 09:57:22', '2026-04-20 09:57:22', 1, NULL, NULL, NULL, NULL, 1, 0, NULL),
(6, 'Dr. Lionel Messi, S.Kom., M.Kom. ', 'akundosen@gmail.com', NULL, 'approved', '2026-04-20 09:57:22', '$2y$12$bU4tZLSEUXcfnm1v2eTPvOBD8jD36SFXtSQ7cCUpwXYWbtdOhwe3.', NULL, NULL, NULL, NULL, NULL, NULL, 'LQGUnPQIqSaaDjvOAytFUFUuPTjMyPhXd7viWWEo5PohAIZIdBni6mO3ISV3', '2026-04-20 09:57:22', '2026-04-20 09:57:22', 3, NULL, NULL, NULL, NULL, 1, 0, NULL),
(7, 'DZIKRI NAJA', 'rifalfalj11@gmail.com', '22EO10041', 'approved', NULL, '$2y$12$Zu7spvHy7lCUznM/baaMquRCfofVqJdfI.NRt8mRIAp4CiTfMknCa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-26 10:44:20', '2026-05-26 00:54:35', 2, 2, '22EO10041', '085842230361', 'profile-photos/HP51BqQdKe0moX23eMngMoi8MIyDYv2sGGUiWb0X.jpg', 1, 1, '2026-06-04 00:21:57'),
(8, 'Pandu Wijaya', 'mitranocola@gmail.com', NULL, 'approved', '2026-05-07 03:51:53', '$2y$12$jy4DjNhpXbRA2CIORZfzsevrm66ocq0RCTl4bSvjrEiGCiTiU4Hpa', NULL, NULL, NULL, NULL, NULL, NULL, 'bEhPCSgxc6EhLebRrmY6peh1gsGmxylYAIpkS7oH2orXA6JgJpJmRFjn6uNF', '2026-05-07 03:51:53', '2026-05-07 03:51:53', 4, NULL, NULL, NULL, NULL, 1, 0, NULL),
(12, 'Alexander', 'mitrarcreative@gmail.com', NULL, 'approved', '2026-06-04 06:15:38', '$2y$12$Um3duetsdXz9WBVKYt4YjOdoBkcB2kvQ2qMJaB/XUvYY67bkB253K', NULL, NULL, NULL, NULL, NULL, NULL, 'zZ5KIjxTjsnt8P7r4KwE6Obbd4Lwg7ozu3EQmwcfTYhTy21fwLQq7k5WR52H', '2026-06-04 06:15:39', '2026-06-04 06:15:39', 4, NULL, NULL, '088678878778', NULL, 1, 0, NULL),
(13, 'Killian Mbadog', 'killianmbadog@gmail.com', '22EO10042', 'approved', NULL, '$2y$12$o16ATAqR4170KizoVnueHu/Pvh6hEL6ZtbCWVheynoirjLg3frE26', NULL, NULL, NULL, NULL, NULL, NULL, '3f29aAXCfixVLI7hIE2KJsmqRkwFwElIfzi8oggSAdRsnJ97jfeNBoezzxbS', '2026-06-04 06:32:58', '2026-06-04 06:32:58', 2, 3, '22EO10042', '088686868686', NULL, 1, 0, NULL);

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `absensi_magangs`
--
ALTER TABLE `absensi_magangs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_absensi_per_hari` (`pendaftaran_id`,`tanggal`),
  ADD KEY `absensi_magangs_pendaftaran_id_foreign` (`pendaftaran_id`);

--
-- Indeks untuk tabel `assessment_components`
--
ALTER TABLE `assessment_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_components_assessment_template_id_foreign` (`assessment_template_id`);

--
-- Indeks untuk tabel `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assessment_scores_unique_component` (`assessment_submission_id`,`assessment_component_id`),
  ADD KEY `assessment_scores_assessment_component_id_foreign` (`assessment_component_id`);

--
-- Indeks untuk tabel `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assessment_submissions_unique_submission` (`pendaftaran_magang_id`,`assessment_template_id`,`assessor_id`,`assessor_role`),
  ADD KEY `assessment_submissions_assessment_template_id_foreign` (`assessment_template_id`),
  ADD KEY `assessment_submissions_assessor_id_foreign` (`assessor_id`);

--
-- Indeks untuk tabel `assessment_templates`
--
ALTER TABLE `assessment_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_templates_created_by_foreign` (`created_by`),
  ADD KEY `assessment_templates_periode_mulai_periode_selesai_index` (`periode_mulai`,`periode_selesai`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `detail_respons`
--
ALTER TABLE `detail_respons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_respons_respons_id_foreign` (`respons_id`),
  ADD KEY `detail_respons_pertanyaan_id_foreign` (`pertanyaan_id`),
  ADD KEY `detail_respons_pilihan_id_foreign` (`pilihan_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fakultas_kode_unique` (`kode`);

--
-- Indeks untuk tabel `hari_liburs`
--
ALTER TABLE `hari_liburs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hari_liburs_tanggal_unique` (`tanggal`);

--
-- Indeks untuk tabel `jenis_surats`
--
ALTER TABLE `jenis_surats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karir_alumnis`
--
ALTER TABLE `karir_alumnis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karir_alumnis_alumni_id_foreign` (`alumni_id`);

--
-- Indeks untuk tabel `ketidakhadiran_magangs`
--
ALTER TABLE `ketidakhadiran_magangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ketidakhadiran_magangs_mahasiswa_id_foreign` (`mahasiswa_id`),
  ADD KEY `ketidakhadiran_magangs_perusahaan_id_foreign` (`perusahaan_id`),
  ADD KEY `ketidakhadiran_magangs_reviewed_by_mitra_user_id_foreign` (`reviewed_by_mitra_user_id`),
  ADD KEY `ketidakhadiran_magangs_pendaftaran_id_status_index` (`pendaftaran_id`,`status`),
  ADD KEY `ketidakhadiran_magangs_tanggal_mulai_tanggal_selesai_index` (`tanggal_mulai`,`tanggal_selesai`);

--
-- Indeks untuk tabel `kuesioners`
--
ALTER TABLE `kuesioners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kuesioners_pembuat_id_foreign` (`pembuat_id`);

--
-- Indeks untuk tabel `logbook_magangs`
--
ALTER TABLE `logbook_magangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logbook_magangs_pendaftaran_id_foreign` (`pendaftaran_id`),
  ADD KEY `logbook_magangs_reviewed_by_mitra_user_id_foreign` (`reviewed_by_mitra_user_id`);

--
-- Indeks untuk tabel `logbook_photos`
--
ALTER TABLE `logbook_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logbook_photos_logbook_id_foreign` (`logbook_id`);

--
-- Indeks untuk tabel `lowongan_infos`
--
ALTER TABLE `lowongan_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lowongan_infos_pembuat_id_foreign` (`pembuat_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pendaftaran_magangs`
--
ALTER TABLE `pendaftaran_magangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_magangs_mahasiswa_id_foreign` (`mahasiswa_id`),
  ADD KEY `pendaftaran_magangs_perusahaan_id_foreign` (`perusahaan_id`),
  ADD KEY `pendaftaran_magangs_dosen_pembimbing_id_foreign` (`dosen_pembimbing_id`),
  ADD KEY `pendaftaran_magangs_surat_tugas_id_foreign` (`surat_tugas_id`);

--
-- Indeks untuk tabel `penilaian_magangs`
--
ALTER TABLE `penilaian_magangs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penilaian_magangs_pendaftaran_id_unique` (`pendaftaran_id`),
  ADD KEY `penilaian_magangs_pendaftaran_id_foreign` (`pendaftaran_id`),
  ADD KEY `penilaian_magangs_dosen_id_foreign` (`dosen_id`);

--
-- Indeks untuk tabel `pertanyaan_kuesioners`
--
ALTER TABLE `pertanyaan_kuesioners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pertanyaan_kuesioners_kuesioner_id_foreign` (`kuesioner_id`);

--
-- Indeks untuk tabel `perusahaan_mitras`
--
ALTER TABLE `perusahaan_mitras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perusahaan_mitras_user_id_unique` (`user_id`);

--
-- Indeks untuk tabel `pilihan_jawabans`
--
ALTER TABLE `pilihan_jawabans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pilihan_jawabans_pertanyaan_id_foreign` (`pertanyaan_id`);

--
-- Indeks untuk tabel `profil_alumnis`
--
ALTER TABLE `profil_alumnis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profil_alumnis_nim_unique` (`nim`),
  ADD KEY `profil_alumnis_user_id_foreign` (`user_id`),
  ADD KEY `profil_alumnis_program_studi_id_foreign` (`program_studi_id`);

--
-- Indeks untuk tabel `program_studis`
--
ALTER TABLE `program_studis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_studis_kode_unique` (`kode`),
  ADD KEY `program_studis_fakultas_id_foreign` (`fakultas_id`);

--
-- Indeks untuk tabel `respons_kuesioners`
--
ALTER TABLE `respons_kuesioners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `respons_kuesioners_kuesioner_id_foreign` (`kuesioner_id`),
  ADD KEY `respons_kuesioners_alumni_id_foreign` (`alumni_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `surats`
--
ALTER TABLE `surats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surats_jenis_surat_id_foreign` (`jenis_surat_id`),
  ADD KEY `surats_pemohon_id_foreign` (`pemohon_id`);

--
-- Indeks untuk tabel `surat_approval_flows`
--
ALTER TABLE `surat_approval_flows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_approval_flows_surat_id_foreign` (`surat_id`),
  ADD KEY `surat_approval_flows_approver_id_foreign` (`approver_id`);

--
-- Indeks untuk tabel `surat_lampirans`
--
ALTER TABLE `surat_lampirans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_lampirans_surat_id_foreign` (`surat_id`);

--
-- Indeks untuk tabel `surat_penetapans`
--
ALTER TABLE `surat_penetapans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `surat_penetapans_pendaftaran_id_unique` (`pendaftaran_id`),
  ADD KEY `surat_penetapans_requested_by_foreign` (`requested_by`),
  ADD KEY `surat_penetapans_status_provider_index` (`status`,`provider`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_program_studi_id_foreign` (`program_studi_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi_magangs`
--
ALTER TABLE `absensi_magangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `assessment_components`
--
ALTER TABLE `assessment_components`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `assessment_scores`
--
ALTER TABLE `assessment_scores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `assessment_templates`
--
ALTER TABLE `assessment_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `detail_respons`
--
ALTER TABLE `detail_respons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fakultas`
--
ALTER TABLE `fakultas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `hari_liburs`
--
ALTER TABLE `hari_liburs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `jenis_surats`
--
ALTER TABLE `jenis_surats`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `karir_alumnis`
--
ALTER TABLE `karir_alumnis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ketidakhadiran_magangs`
--
ALTER TABLE `ketidakhadiran_magangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kuesioners`
--
ALTER TABLE `kuesioners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logbook_magangs`
--
ALTER TABLE `logbook_magangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `logbook_photos`
--
ALTER TABLE `logbook_photos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `lowongan_infos`
--
ALTER TABLE `lowongan_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran_magangs`
--
ALTER TABLE `pendaftaran_magangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `penilaian_magangs`
--
ALTER TABLE `penilaian_magangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pertanyaan_kuesioners`
--
ALTER TABLE `pertanyaan_kuesioners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `perusahaan_mitras`
--
ALTER TABLE `perusahaan_mitras`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pilihan_jawabans`
--
ALTER TABLE `pilihan_jawabans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `profil_alumnis`
--
ALTER TABLE `profil_alumnis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `program_studis`
--
ALTER TABLE `program_studis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `respons_kuesioners`
--
ALTER TABLE `respons_kuesioners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `surats`
--
ALTER TABLE `surats`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `surat_approval_flows`
--
ALTER TABLE `surat_approval_flows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `surat_lampirans`
--
ALTER TABLE `surat_lampirans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `surat_penetapans`
--
ALTER TABLE `surat_penetapans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_magangs`
--
ALTER TABLE `absensi_magangs`
  ADD CONSTRAINT `absensi_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_components`
--
ALTER TABLE `assessment_components`
  ADD CONSTRAINT `assessment_components_assessment_template_id_foreign` FOREIGN KEY (`assessment_template_id`) REFERENCES `assessment_templates` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `assessment_scores_assessment_component_id_foreign` FOREIGN KEY (`assessment_component_id`) REFERENCES `assessment_components` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_scores_assessment_submission_id_foreign` FOREIGN KEY (`assessment_submission_id`) REFERENCES `assessment_submissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD CONSTRAINT `assessment_submissions_assessment_template_id_foreign` FOREIGN KEY (`assessment_template_id`) REFERENCES `assessment_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_submissions_assessor_id_foreign` FOREIGN KEY (`assessor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_submissions_pendaftaran_magang_id_foreign` FOREIGN KEY (`pendaftaran_magang_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_templates`
--
ALTER TABLE `assessment_templates`
  ADD CONSTRAINT `assessment_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_respons`
--
ALTER TABLE `detail_respons`
  ADD CONSTRAINT `detail_respons_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan_kuesioners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_respons_pilihan_id_foreign` FOREIGN KEY (`pilihan_id`) REFERENCES `pilihan_jawabans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detail_respons_respons_id_foreign` FOREIGN KEY (`respons_id`) REFERENCES `respons_kuesioners` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `karir_alumnis`
--
ALTER TABLE `karir_alumnis`
  ADD CONSTRAINT `karir_alumnis_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `profil_alumnis` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ketidakhadiran_magangs`
--
ALTER TABLE `ketidakhadiran_magangs`
  ADD CONSTRAINT `ketidakhadiran_magangs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ketidakhadiran_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ketidakhadiran_magangs_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan_mitras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ketidakhadiran_magangs_reviewed_by_mitra_user_id_foreign` FOREIGN KEY (`reviewed_by_mitra_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kuesioners`
--
ALTER TABLE `kuesioners`
  ADD CONSTRAINT `kuesioners_pembuat_id_foreign` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `logbook_magangs`
--
ALTER TABLE `logbook_magangs`
  ADD CONSTRAINT `logbook_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logbook_magangs_reviewed_by_mitra_user_id_foreign` FOREIGN KEY (`reviewed_by_mitra_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `logbook_photos`
--
ALTER TABLE `logbook_photos`
  ADD CONSTRAINT `logbook_photos_logbook_id_foreign` FOREIGN KEY (`logbook_id`) REFERENCES `logbook_magangs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lowongan_infos`
--
ALTER TABLE `lowongan_infos`
  ADD CONSTRAINT `lowongan_infos_pembuat_id_foreign` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pendaftaran_magangs`
--
ALTER TABLE `pendaftaran_magangs`
  ADD CONSTRAINT `pendaftaran_magangs_dosen_pembimbing_id_foreign` FOREIGN KEY (`dosen_pembimbing_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pendaftaran_magangs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_magangs_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan_mitras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_magangs_surat_tugas_id_foreign` FOREIGN KEY (`surat_tugas_id`) REFERENCES `surats` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penilaian_magangs`
--
ALTER TABLE `penilaian_magangs`
  ADD CONSTRAINT `penilaian_magangs_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penilaian_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pertanyaan_kuesioners`
--
ALTER TABLE `pertanyaan_kuesioners`
  ADD CONSTRAINT `pertanyaan_kuesioners_kuesioner_id_foreign` FOREIGN KEY (`kuesioner_id`) REFERENCES `kuesioners` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perusahaan_mitras`
--
ALTER TABLE `perusahaan_mitras`
  ADD CONSTRAINT `perusahaan_mitras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pilihan_jawabans`
--
ALTER TABLE `pilihan_jawabans`
  ADD CONSTRAINT `pilihan_jawabans_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan_kuesioners` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `profil_alumnis`
--
ALTER TABLE `profil_alumnis`
  ADD CONSTRAINT `profil_alumnis_program_studi_id_foreign` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profil_alumnis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `program_studis`
--
ALTER TABLE `program_studis`
  ADD CONSTRAINT `program_studis_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `respons_kuesioners`
--
ALTER TABLE `respons_kuesioners`
  ADD CONSTRAINT `respons_kuesioners_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `profil_alumnis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respons_kuesioners_kuesioner_id_foreign` FOREIGN KEY (`kuesioner_id`) REFERENCES `kuesioners` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `surats`
--
ALTER TABLE `surats`
  ADD CONSTRAINT `surats_jenis_surat_id_foreign` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surats_pemohon_id_foreign` FOREIGN KEY (`pemohon_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `surat_approval_flows`
--
ALTER TABLE `surat_approval_flows`
  ADD CONSTRAINT `surat_approval_flows_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surat_approval_flows_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `surat_lampirans`
--
ALTER TABLE `surat_lampirans`
  ADD CONSTRAINT `surat_lampirans_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `surat_penetapans`
--
ALTER TABLE `surat_penetapans`
  ADD CONSTRAINT `surat_penetapans_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surat_penetapans_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_program_studi_id_foreign` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studis` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
