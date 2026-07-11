# FAST Permission Matrix

Scope: FAST module only.

This document maps the current Laravel routes, the middleware that actually protects them, and the recommended permission model for future policy enforcement.

---

## 1. Route Groups and Real Middleware

### 1.1 Public document routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/documents/public` | `documents.public.surat.template-preview`, `documents.public.surat.generated-document`, `documents.public.surat.pdf`, `documents.public.lampiran.preview` | `signed` | Signed public access for preview/download links |

### 1.2 Authenticated document routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/documents` | `documents.surat.template-preview`, `documents.surat.generated-document`, `documents.surat.generate`, `documents.surat.pdf`, `documents.lampiran.preview` | `auth`, `verified` | Protected document access for logged-in users |

### 1.3 Notification routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/notifications` | `notifications.read`, `notifications.read-all` | `auth`, `verified` | Mark notification as read |

### 1.4 Compatibility alias for old admin document URLs

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/admin` | `admin.surat.template-preview`, `admin.surat.generated-document`, `admin.surat.generate`, `admin.surat.pdf`, `admin.lampiran.preview` | `auth`, `verified` | Legacy document URL compatibility |

### 1.5 FAST user routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/fast/user` | `fast.user.dashboard`, `fast.user.ajukan`, `fast.user.submissions.store`, `fast.user.history`, `fast.user.history.show`, `fast.user.surat.cancel` | `auth`, `verified`, `module.context:fast` | Legacy user entry point for FAST applicant role |

### 1.6 FAST mahasiswa routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/mahasiswa` | `mahasiswa.dashboard`, `mahasiswa.ajukan`, `mahasiswa.submissions.store`, `mahasiswa.history`, `mahasiswa.history.show`, `mahasiswa.surat.cancel`, `mahasiswa.jenis-surat.show` | `auth`, `verified`, `module.context:fast` | Main mahasiswa FAST flow |

### 1.7 FAST dosen routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/dosen` | `dosen.dashboard`, `dosen.ajukan`, `dosen.submissions.store`, `dosen.history`, `dosen.history.show`, `dosen.surat.cancel`, `dosen.jenis-surat.show` | `auth`, `verified`, `module.context:fast` | Main dosen FAST flow |

### 1.8 FAST admin routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/admin` | `admin.dashboard`, `admin.archive.index`, `admin.surat.show`, `admin.surat.create`, `admin.surat.select-type`, `admin.surat.form`, `admin.surat.subjects.search`, `admin.surat.preview-html`, `admin.surat.preview-page`, `admin.surat.preview`, `admin.surat.store`, `admin.surat.index`, `admin.surat.edit`, `admin.surat.update`, `admin.surat.approve`, `admin.surat.reject`, `admin.history`, `admin.templates.index`, `admin.templates.store`, `admin.templates.preview`, `admin.templates.duplicate`, `admin.templates.toggle-active`, `admin.templates.update`, `admin.templates.destroy`, `admin.categories.index`, `admin.categories.store`, `admin.categories.update`, `admin.categories.destroy`, `admin.qr.index`, `admin.qr.revoke`, `admin.settings.template`, `admin.settings.template.logo-preview` | `auth`, `verified`, `admin.access` | Admin FAST operations |

### 1.9 FAST approval routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/approval` | `approval.dashboard`, `approval.antrian`, `approval.arsip`, `approval.surat.detail`, `approval.surat.show`, `approval.lampiran.preview`, `approval.surat.approve`, `approval.surat.reject`, `approval.surat.final-reject`, `approval.surat.note` | `auth`, `verified`, `approval.access` | Shared approval access for kaprodi/dekan |
| `/kaprodi` | same as above with `kaprodi.*` | `auth`, `verified`, `approval.access` | Kaprodi approval access |
| `/dekan` | same as above with `dekan.*` | `auth`, `verified`, `approval.access` | Dekan approval access |

### 1.10 FAST API routes

| Route prefix | Named routes | Middleware | Purpose |
| --- | --- | --- | --- |
| `/api/fast` | `api.fast.surat.index`, `api.fast.surat.store`, `api.fast.surat.show` | `auth`, `verified` | Read/write Surat API base |
| `/api/fast` | `api.fast.surat.admin-validation`, `api.fast.surat.generate-document` | `auth`, `verified`, `admin.access` | Admin-only API actions |
| `/api/fast` | `api.fast.surat.approval` | `auth`, `verified`, `approval.access` | Approval-only API action |

---

## 2. Recommended Permission Keys

Use these permission keys as the canonical FAST vocabulary.

| Permission key | Meaning |
| --- | --- |
| `fast.dashboard.view` | Open dashboard pages |
| `fast.submission.create` | Open and submit application form |
| `fast.submission.view` | View own submission history/details |
| `fast.submission.cancel` | Cancel own submission |
| `fast.admin.dashboard.view` | Open admin dashboard |
| `fast.admin.queue.view` | Open admin submission queue |
| `fast.admin.surat.view` | View admin surat list/detail |
| `fast.admin.surat.create` | Create surat as admin |
| `fast.admin.surat.update` | Edit pending/rejected surat as admin |
| `fast.admin.surat.approve` | Approve or reject from admin queue |
| `fast.admin.history.view` | View admin history |
| `fast.admin.archive.view` | View admin archive |
| `fast.admin.template.manage` | Create/update/delete template |
| `fast.admin.category.manage` | Create/update/delete category |
| `fast.admin.qr.manage` | View/revoke QR documents |
| `fast.admin.settings.manage` | Update global FAST settings |
| `fast.approval.dashboard.view` | Open approval dashboard |
| `fast.approval.queue.view` | Open approval queue |
| `fast.approval.archive.view` | Open approval archive |
| `fast.approval.surat.view` | View approval surat detail |
| `fast.approval.surat.approve` | Approve surat |
| `fast.approval.surat.reject` | Reject surat |
| `fast.approval.note.write` | Write approval note |
| `fast.document.preview` | Preview generated or uploaded document |
| `fast.document.download` | Download PDF |

---

## 3. Controller to Policy Schema

This is the recommended policy layer if FAST is moved from middleware-only checks to Laravel policy checks.

| Controller | Recommended policy | Key abilities |
| --- | --- | --- |
| `App\Modules\Fast\Controllers\Shared\User\DashboardController` | `FastSubmissionPolicy` | `viewAny`, `viewOwnStats` |
| `App\Modules\Fast\Controllers\Shared\User\SubmissionController` | `FastSubmissionPolicy` | `create`, `store`, `viewOwnFieldConfig` |
| `App\Modules\Fast\Controllers\Shared\User\HistoryController` | `FastSubmissionPolicy` | `viewAny`, `view`, `cancel` |
| `App\Modules\Fast\Controllers\Shared\User\LetterTypeController` | `FastTemplateAccessPolicy` | `view` |
| `App\Modules\Fast\Controllers\Admin\DashboardController` | `FastSuratAdminPolicy` | `viewAny`, `view`, `previewDocument`, `downloadPdf`, `approve`, `reject` |
| `App\Modules\Fast\Controllers\Admin\LetterController` | `FastSuratAdminPolicy` | `create`, `store`, `edit`, `update`, `preview`, `generate`, `searchSubjects` |
| `App\Modules\Fast\Controllers\Admin\ApprovalController` | `FastApprovalPolicy` | `viewAny`, `viewQueue`, `viewArchive`, `viewDetail`, `approve`, `reject`, `finalReject`, `note`, `previewAttachment`, `download` |
| `App\Modules\Fast\Controllers\Admin\TemplateController` | `FastTemplatePolicy` | `viewAny`, `create`, `update`, `delete`, `duplicate`, `toggleActive`, `preview` |
| `App\Modules\Fast\Controllers\Admin\CategoryController` | `FastCategoryPolicy` | `viewAny`, `create`, `update`, `delete` |
| `App\Modules\Fast\Controllers\Admin\QrManageController` | `FastQrPolicy` | `viewAny`, `revoke` |
| `App\Modules\Fast\Controllers\Admin\GlobalSettingsController` | `FastSettingsPolicy` | `update`, `previewLogo` |
| `App\Modules\Fast\Controllers\Admin\ArchiveController` | `FastArchivePolicy` | `viewAny` |
| `App\Modules\Fast\Controllers\Admin\HistoryController` | `FastHistoryPolicy` | `viewAny` |
| `App\Modules\Fast\Controllers\Shared\Approval\ApprovalController` | `FastApprovalPolicy` | same as admin approval abilities |
| `App\Modules\Fast\Controllers\Kaprodi\ApprovalController` | `FastApprovalPolicy` | same as admin approval abilities |
| `App\Modules\Fast\Controllers\Dekan\ApprovalController` | `FastApprovalPolicy` | same as admin approval abilities |
| `App\Modules\Fast\Controllers\Dosen\DashboardController` | `FastSubmissionPolicy` | `viewAny` |
| `App\Modules\Fast\Controllers\Dosen\SubmissionController` | `FastSubmissionPolicy` | `create`, `store` |
| `App\Modules\Fast\Controllers\Dosen\HistoryController` | `FastSubmissionPolicy` | `viewAny`, `view`, `cancel` |

### Policy enforcement suggestion

Use controller-level policy checks for resource operations and keep middleware for coarse access gates:

- `module.context:fast` -> module level access
- `admin.access` -> admin role scope
- `approval.access` -> kaprodi/dekan scope
- `authorize()` / `can()` -> action-level protection

---

## 4. Sidebar Menu to Permission Mapping

### 4.1 Admin sidebar

| Menu label | Route | Permission |
| --- | --- | --- |
| Dashboard | `/admin/dashboard` | `fast.admin.dashboard.view` |
| Buat Surat | `/admin/surat/create` | `fast.admin.surat.create` |
| Pengajuan | `/admin/surat` | `fast.admin.queue.view` |
| Riwayat Admin | `/admin/history` | `fast.admin.history.view` |
| Arsip | `/admin/archive` | `fast.admin.archive.view` |
| Template | `/admin/templates` | `fast.admin.template.manage` |
| Kategori | `/admin/categories` | `fast.admin.category.manage` |
| QR Code | `/admin/qr` | `fast.admin.qr.manage` |

### 4.2 Approval sidebar

| Menu label | Route | Permission |
| --- | --- | --- |
| Dashboard | `/{role}/dashboard` | `fast.approval.dashboard.view` |
| Antrian Approval | `/{role}/antrian` | `fast.approval.queue.view` |
| Riwayat Approval | `/{role}/arsip` | `fast.approval.archive.view` |

### 4.3 Mahasiswa / Dosen sidebar

| Menu label | Route | Permission |
| --- | --- | --- |
| Dashboard | `/{role}/dashboard` | `fast.dashboard.view` |
| Ajukan Surat | `/{role}/ajukan` | `fast.submission.create` |
| Riwayat Surat | `/{role}/history` | `fast.submission.view` |

---

## 5. Practical Notes

1. The current runtime protection is middleware-based, not policy-based.
2. The safest migration path is:
   - keep `module.context:fast`, `admin.access`, and `approval.access`
   - add Laravel policies per controller action
   - use the permission keys above for menu visibility
3. For `dekan` and `kaprodi`, the same approval policy can be reused because both share the same action set.
4. For document endpoints, `fast.document.preview` and `fast.document.download` can be checked inside the service/controller layer, because the same endpoint may be used by multiple roles.

---

## 6. Final Button Map by Role

This is the current FAST UI action map after the frontend guards and policy tightening.

### 6.1 Admin

| Area | Buttons / Actions | Permission |
| --- | --- | --- |
| Dashboard / queue | Lihat, Proses, Lengkapi / Proses Ulang, Tolak | `fast.admin.surat.view`, `fast.admin.surat.approve`, `fast.admin.surat.update` |
| Riwayat Admin | Lihat, PDF | `fast.admin.history.view`, `fast.document.download` |
| Arsip Admin | Lihat, PDF | `fast.admin.archive.view`, `fast.document.download` |
| Template Surat | Tambah Jenis Surat, Pengaturan Kop & Footer, Preview PDF, Simpan Isi Surat, Preset Data Pemohon, Tambah Field, Pindah Atas/Bawah, Hapus Field, Duplikat, Aktif/Nonaktif, Hapus Template | `fast.admin.template.manage`, `fast.admin.settings.manage` |
| Kategori | Tambah, Edit, Aktif/Nonaktif, Hapus | `fast.admin.category.manage` |
| QR Code | Lihat QR, Cabut QR, Lihat Surat | `fast.admin.qr.manage` |
| Detail Surat | Preview Dokumen, Download PDF, Edit & Teruskan | `fast.document.preview`, `fast.document.download`, `fast.admin.surat.update` |

### 6.2 Dekan / Kaprodi

| Area | Buttons / Actions | Permission |
| --- | --- | --- |
| Dashboard Approval | Lihat, Preview Dokumen, Download PDF | `fast.approval.dashboard.view`, `fast.document.preview`, `fast.document.download` |
| Antrian Approval | Lihat, Setujui, Kembalikan, Tolak Final, Download PDF, Catatan | `fast.approval.queue.view`, `fast.approval.surat.view`, `fast.approval.surat.approve`, `fast.approval.surat.reject`, `fast.document.download` |
| Arsip Approval | Lihat, Unduh PDF | `fast.approval.archive.view`, `fast.document.download` |
| Detail Approval | Preview Dokumen, Download PDF, Lihat Lampiran, Kembalikan, Tolak Final, Catatan | `fast.approval.surat.view`, `fast.document.preview`, `fast.document.download`, `fast.approval.surat.approve`, `fast.approval.surat.reject` |

### 6.3 Mahasiswa / Dosen

| Area | Buttons / Actions | Permission |
| --- | --- | --- |
| Dashboard | Lihat, Download PDF, Ajukan Surat | `fast.submission.view`, `fast.document.download`, `fast.submission.create` |
| Ajukan Surat | Pilih Jenis Surat, Simpan / Kirim Pengajuan, Upload Lampiran | `fast.submission.create` |
| Riwayat Surat | Lihat, PDF, Catatan, Batal | `fast.submission.view`, `fast.document.download`, `fast.submission.cancel` |
| Detail Surat | Preview Dokumen, Download PDF, Lihat Lampiran | `fast.document.preview`, `fast.document.download` |

Notes:
- `Dosen` mengikuti permission dan halaman yang sama dengan `Mahasiswa` pada FAST.
- Tombol internal editor template yang masih ada di halaman admin tetap bergantung pada `fast.admin.template.manage`.
