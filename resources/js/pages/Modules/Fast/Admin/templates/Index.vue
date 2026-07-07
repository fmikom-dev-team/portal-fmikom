<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/templates/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import {
    Plus,
    Eye,
    Save,
    Copy,
    ToggleLeft,
    ToggleRight,
    Trash2,
    X,
    Search,
    Settings,
    ChevronDown,
    ChevronUp,
    FileText,
    CheckCircle2,
    AlertCircle,
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const layout = reactive({
    margin_top: '',
    margin_right: '',
    margin_bottom: '',
    margin_left: '',
    body_indent: 0,
    paragraph_indent: 0,
    table_indent: 0,
});
const deletingTemplate = ref(false);
const defaultLayoutState = () => ({
    margin_top: '',
    margin_right: '',
    margin_bottom: '',
    margin_left: '',
    body_indent: 0,
    paragraph_indent: 0,
    table_indent: 0,
});
type SuratKomponen =
    | {
          type: 'judul';
          teks: string;
          align: 'left' | 'center' | 'right';
          font_size?: string;
          margin_left?: number;
          bold?: boolean;
          underline?: boolean;
      }
    | {
          type: 'subjudul';
          teks: string;
          align?: 'left' | 'center' | 'right';
          font_size?: string;
          margin_left?: number;
          bold?: boolean;
          underline?: boolean;
      }
    | {
          type: 'paragraf';
          teks: string;
          align: 'left' | 'justify';
          italic?: boolean;
          bold?: boolean;
          text_indent?: number;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'paragraf_indent';
          teks: string;
          align: 'left' | 'justify';
          indent?: number;
          italic?: boolean;
          bold?: boolean;
          text_indent?: number;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'header_surat';
          nomor: string;
          lampiran: string;
          perihal: string;
          kota: string;
          tanggal: string;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'kepada_yth';
          penerima: string[];
          lokasi: string;
          tempat: string;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'tabel_data';
          rows: Array<{ label: string; nilai: string; __auto_nilai?: boolean }>;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'tabel_biasa';
          headers: string[];
          rows: Array<{ cells: string[] }>;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'tabel_indent';
          rows: Array<{ label: string; nilai: string; __auto_nilai?: boolean }>;
          indent?: number;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'tanda_tangan';
          kolom: Array<{
              jabatan: string;
              nama: string;
              nik: string;
              posisi?: 'kiri' | 'kanan' | 'tengah';
              jabatan_bold?: boolean;
              jabatan_underline?: boolean;
              nama_bold?: boolean;
              nama_underline?: boolean;
              nik_bold?: boolean;
              nik_underline?: boolean;
          }>;
          tanggal?: string;
          show_tanggal?: boolean;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'qr_validasi';
          align?: 'left' | 'center' | 'right';
          title?: string;
          caption?: string;
          font_size?: string;
          margin_left?: number;
      }
    | {
          type: 'tembusan';
          items: string[];
          font_size?: string;
          margin_left?: number;
      }
    | { type: 'spasi'; tinggi: number }
    | { type: 'garis'; font_size?: string; margin_left?: number };
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    placeholder: string;
    help: string;
    options: FieldOption[];
    repeatable?: boolean;
    add_label?: string;
    item_label?: string;
    sumber_data?: 'data_pemohon' | 'data_kampus' | 'data_sistem';
    editable_role?: 'mahasiswa' | 'admin' | 'sistem';
    mode_form_pemohon?: 'editable' | 'readonly' | 'hidden';
    __auto_name?: boolean;
};
type Template = {
    id: number;
    name: string;
    template_header?: string | null;
    template_body: string;
    template_footer?: string | null;
    template_components?: SuratKomponen[];
    version: number;
    preview_url: string;
    placeholders: any[];
};
type JenisSuratItem = {
    id: number;
    nama: string;
    slug?: string | null;
    is_active: boolean;
    letter_mode?: 'personal' | 'institution';
    letter_mode_label?: string;
    category?: { id?: number | null; nama?: string | null } | null;
    template?: { id: number; name: string; version: number } | null;
};
type RoleRef = {
    id?: number | null;
    nama?: string | null;
    slug?: string | null;
};
type JenisSurat = {
    id: number;
    nama: string;
    slug?: string | null;
    kode_surat?: string | null;
    kode_klasifikasi?: string | null;
    deskripsi?: string | null;
    is_active: boolean;
    perlu_approval: boolean;
    letter_mode?: 'personal' | 'institution';
    letter_mode_label?: string;
    requires_subject_user?: boolean;
    category?: { id?: number | null; nama?: string | null } | null;
    allowed_role?: RoleRef | null;
    approval_role?: RoleRef | null;
    field_config: FieldConfig[];
    template?: Template | null;
};
type CategoryOption = { id: number; nama: string };
type RoleOption = { id: number; nama: string; slug: string };
type GlobalSetting = {
    key: string;
    label: string;
    value?: string | null;
    tipe: string;
};
type PageProps = {
    flash?: {
        success?: string;
        error?: string;
    };
    errors?: Record<string, string | string[]>;
};
const props = withDefaults(
    defineProps<{
        jenisSurats?: JenisSuratItem[];
        selectedJenisSurat: JenisSurat | null;
        selectedJenisSuratId?: number | null;
        categories?: CategoryOption[];
        approvalRoles?: RoleOption[];
        creatorRoles?: RoleOption[];
        globalSettings?: GlobalSetting[];
    }>(),
    {
        jenisSurats: () => [],
        categories: () => [],
        approvalRoles: () => [],
        creatorRoles: () => [],
        globalSettings: () => [],
    },
);
const page = usePage<PageProps>();
const { can } = useFastPermissions();
const sidebarSearch = ref('');
const categoryFilter = ref<'all' | string>('all');
const statusFilter = ref<'all' | 'active' | 'inactive'>('all');
const modeFilter = ref<'all' | 'personal' | 'institution'>('all');
const showAddDialog = ref(false);
const showGlobalSettings = ref(false);
const activeTab = ref<'template' | 'fields' | 'meta'>('template');
const toastMessage = ref('');
const toastVariant = ref<'success' | 'error'>('success');
let toastTimer: number | null = null;

function showToast(message: string, variant: 'success' | 'error' = 'success') {
    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
        toastTimer = null;
    }

    toastMessage.value = '';
    toastVariant.value = variant;
    window.setTimeout(() => {
        toastMessage.value = message;
        toastTimer = window.setTimeout(() => {
            toastMessage.value = '';
            toastTimer = null;
        }, 2800);
    }, 0);
}

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (typeof success === 'string' && success.trim()) {
            showToast(success, 'success');
            return;
        }
        if (typeof error === 'string' && error.trim()) {
            showToast(error, 'error');
        }
    },
    { immediate: true },
);
function openAddDialog() {
    showAddDialog.value = true;
}
function closeAddDialog() {
    showAddDialog.value = false;
}
function openGlobalSettings() {
    showGlobalSettings.value = true;
}
function closeGlobalSettings() {
    showGlobalSettings.value = false;
}
const filteredJenisSurats = computed(() => {
    const q = sidebarSearch.value.toLowerCase().trim();
    let items = props.jenisSurats ?? [];

    if (categoryFilter.value !== 'all') {
        items = items.filter(
            (j) => String(j.category?.id ?? '') === String(categoryFilter.value),
        );
    }

    if (statusFilter.value !== 'all') {
        items = items.filter((j) =>
            statusFilter.value === 'active'
                ? isTruthy(j.is_active)
                : !isTruthy(j.is_active),
        );
    }

    if (modeFilter.value !== 'all') {
        items = items.filter(
            (j) => String(j.letter_mode ?? 'personal') === modeFilter.value,
        );
    }

    if (!q) return items;

    return items.filter(
        (j) =>
            normalizeSearchText(j.nama).includes(q) ||
            normalizeSearchText(j.slug).includes(q) ||
            normalizeSearchText(j.category?.nama).includes(q) ||
            normalizeSearchText(j.template?.name).includes(q),
    );
});
const categoryFilterOptions = computed(() => [
    { label: 'Semua kategori', value: 'all' },
    ...(props.categories ?? []).map((category) => ({
        label: category.nama,
        value: String(category.id),
    })),
]);
function resetTemplateFilters() {
    sidebarSearch.value = '';
    categoryFilter.value = 'all';
    statusFilter.value = 'all';
    modeFilter.value = 'all';
}
function normalizeSearchText(value: unknown) {
    return String(value ?? '').toLowerCase().trim();
}
function isTruthy(value: unknown) {
    return value === true || value === 1 || value === '1' || value === 'true';
}
function cloneFieldConfig(fieldConfig?: FieldConfig[]) {
    return JSON.parse(JSON.stringify(fieldConfig ?? [])) as FieldConfig[];
}
function prepareFieldConfigForUi(fieldConfig?: FieldConfig[]) {
    return cloneFieldConfig(fieldConfig).map((field) => ({
        ...field,
        __auto_name:
            !String(field.name ?? '').trim() ||
            slugifyLabel(field.label ?? '') === slugifyLabel(field.name ?? ''),
    }));
}
function cloneKomponen(items?: SuratKomponen[]) {
    return JSON.parse(JSON.stringify(items ?? [])) as SuratKomponen[];
}
function prepareKomponenForUi(items?: SuratKomponen[]) {
    return cloneKomponen(items).map((item) => {
        if (!item || typeof item !== 'object') return item;
        if (item.type === 'tanda_tangan') {
            const fallbackPosisi = (item as any).posisi ?? 'kanan';
            const kolom = Array.isArray((item as any).kolom) ? (item as any).kolom : [];
            const { posisi, ...rest } = item as any;

            return {
                ...rest,
                kolom: kolom.length > 0
                    ? kolom.map((entry: any) => ({
                          ...entry,
                          posisi: entry?.posisi ?? fallbackPosisi,
                          jabatan_bold: !!entry?.jabatan_bold,
                          jabatan_underline: !!entry?.jabatan_underline,
                          nama_bold: entry?.nama_bold ?? true,
                          nama_underline: !!entry?.nama_underline,
                          nik_bold: !!entry?.nik_bold,
                          nik_underline: !!entry?.nik_underline,
                      }))
                    : [{
                          jabatan: 'Jabatan',
                          nama: 'Nama',
                          nik: 'NIP/NIK',
                          posisi: fallbackPosisi,
                          jabatan_bold: false,
                          jabatan_underline: false,
                          nama_bold: true,
                          nama_underline: false,
                          nik_bold: false,
                          nik_underline: false,
                      }],
            } as SuratKomponen;
        }
        if (item.type === 'tabel_biasa') {
            const headers = Array.isArray((item as any).headers)
                ? (item as any).headers.filter((header: unknown) => String(header ?? '').trim() !== '')
                : [];
            const rows = Array.isArray((item as any).rows) ? (item as any).rows : [];
            const normalizedHeaders = headers.length > 0 ? headers : ['Kolom 1', 'Kolom 2'];

            return {
                ...item,
                headers: normalizedHeaders,
                rows: rows.length > 0
                    ? rows.map((row: any) => {
                          const cells = Array.isArray(row?.cells) ? row.cells : [];
                          return {
                              ...row,
                              cells: normalizedHeaders.map((_: string, index: number) =>
                                  String(cells[index] ?? ''),
                              ),
                          };
                      })
                    : [
                          { cells: normalizedHeaders.map(() => '') },
                          { cells: normalizedHeaders.map(() => '') },
                      ],
            } as SuratKomponen;
        }

        if (!('rows' in item) || !Array.isArray((item as any).rows)) return item;

        return {
            ...item,
            rows: (item as any).rows.map((row: any) => ({
                ...row,
                __auto_nilai:
                    !String(row.nilai ?? '').trim() ||
                    suggestPlaceholderKey(row.label ?? '') ===
                        String(row.nilai ?? '').trim(),
            })),
        } as SuratKomponen;
    });
}
function normalizeKomponenFontSize(items: SuratKomponen[]): SuratKomponen[] {
    return items.map((item) => {
        if (!item || typeof item !== 'object') return item;
        if (item.type === 'spasi' || item.type === 'garis') return item;
        if (typeof item.font_size === 'string' && item.font_size.trim() !== '') return item;
        return { ...item, font_size: '12pt' };
    });
}
function createFormState(source: JenisSurat | null) {
    return {
        jenis_surat_nama: source?.nama ?? '',
        letter_mode: source?.letter_mode ?? 'personal',
        template_header: source?.template?.template_header ?? '',
        template_body: source?.template?.template_body ?? '',
        template_footer: source?.template?.template_footer ?? '',
        field_config: prepareFieldConfigForUi(source?.field_config ?? []),
        kode_klasifikasi: source?.kode_klasifikasi ?? '',
        category_id: source?.category?.id ?? '',
        approval_role_id: source?.approval_role?.id ?? '',
        allowed_role_id: source?.allowed_role?.id ?? '',
        perlu_approval: source?.perlu_approval ?? false,
        is_active: source?.is_active ?? true,
    };
}
const komponen = ref<SuratKomponen[]>(
    prepareKomponenForUi(props.selectedJenisSurat?.template?.template_components),
);
const form = useForm(createFormState(props.selectedJenisSurat));
const lastHydratedJenisSuratId = ref<number | null>(null);
const selectedTemplateBody = computed(
    () =>
        props.selectedJenisSurat?.template?.template_body ??
        form.template_body ??
        '',
);
const selectedTemplateComponents = computed(
    () =>
        props.selectedJenisSurat?.template?.template_components ??
        parseKomponen(selectedTemplateBody.value),
);
function hasValidationErrors() {
    return Object.keys(page.props.errors ?? {}).length > 0;
}

function firstFieldConfigIssue() {
    const normalizedNames = form.field_config.map((field) => slugifyLabel(field.name || ''));
    const duplicates = normalizedNames.reduce<Record<string, number[]>>((carry, name, index) => {
        if (!name) return carry;
        carry[name] = [...(carry[name] ?? []), index];
        return carry;
    }, {});

    for (let index = 0; index < form.field_config.length; index += 1) {
        const field = form.field_config[index];
        if (!String(field.label ?? '').trim()) {
            return { index, prop: 'label' };
        }
        if (!slugifyLabel(field.name || '')) {
            return { index, prop: 'name' };
        }

        const normalizedName = normalizedNames[index];
        if (normalizedName && (duplicates[normalizedName] ?? []).length > 1) {
            return { index, prop: 'name' };
        }
    }

    return null;
}

async function focusErrorTarget(selector: string) {
    await nextTick();
    const element = document.querySelector<HTMLElement>(selector);
    if (!element) return;
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if ('focus' in element) {
        window.setTimeout(() => element.focus(), 120);
    }
}

async function navigateToFirstError(errors: Record<string, string | string[]>) {
    const errorKeys = Object.keys(errors ?? {});
    const fieldConfigKey = errorKeys.find((key) => key.startsWith('field_config.'));

    if (fieldConfigKey || errorKeys.includes('field_config')) {
        activeTab.value = 'fields';

        const matched = fieldConfigKey?.match(/^field_config\.(\d+)\.(\w+)/);
        if (matched) {
            const [, rawIndex, prop] = matched;
            await focusErrorTarget(
                `[data-field-index="${rawIndex}"] [data-field-prop="${prop}"]`,
            );
            return;
        }

        const fallbackIssue = firstFieldConfigIssue();
        if (fallbackIssue) {
            await focusErrorTarget(
                `[data-field-index="${fallbackIssue.index}"] [data-field-prop="${fallbackIssue.prop}"]`,
            );
        }
        return;
    }

    const metaTargets = [
        'jenis_surat_nama',
        'kode_klasifikasi',
        'category_id',
        'approval_role_id',
        'allowed_role_id',
        'letter_mode',
    ];
    const firstMetaKey = metaTargets.find((key) => errorKeys.includes(key));
    if (firstMetaKey) {
        activeTab.value = 'meta';
        await focusErrorTarget(`[data-meta-field="${firstMetaKey}"]`);
        return;
    }

    activeTab.value = 'template';
    await focusErrorTarget('[data-template-save]');
}

function hydrateEditorState(value: JenisSurat | null) {
    const nextState = createFormState(value);
    form.defaults(nextState);
    form.reset();
    Object.assign(form, nextState);
    komponen.value = prepareKomponenForUi(
        value?.template?.template_components,
    );
    Object.assign(layout, defaultLayoutState());
    lastHydratedJenisSuratId.value = value?.id ?? null;
}

watch(
    () => props.selectedJenisSurat,
    (value) => {
        const nextId = value?.id ?? null;
        const sameSelectedJenisSurat = nextId === lastHydratedJenisSuratId.value;

        if (sameSelectedJenisSurat && hasValidationErrors()) {
            return;
        }

        hydrateEditorState(value);
    },
    { immediate: true },
);
const placeholderUmum = [
    { key: 'nomor_surat', label: 'Nomor Surat' },
    { key: 'tanggal_surat_panjang', label: 'Tanggal Panjang' },
    { key: 'kota_surat', label: 'Kota' },
    { key: 'perihal', label: 'Perihal' },
    { key: 'nama', label: 'Nama' },
    { key: 'nim', label: 'NIM' },
    { key: 'program_studi', label: 'Program Studi' },
    { key: 'fakultas', label: 'Fakultas' },
    { key: 'nama_pemohon', label: 'Nama Pemohon' },
    { key: 'nim_pemohon', label: 'NIM Pemohon' },
    { key: 'nama_prodi', label: 'Nama Prodi' },
    { key: 'nama_dekan', label: 'Nama Dekan' },
    { key: 'nama_kaprodi', label: 'Nama Kaprodi' },
] as const;
const fieldTypeOptions = [
    { label: 'Teks', value: 'text' },
    { label: 'Area Teks', value: 'textarea' },
    { label: 'Angka', value: 'number' },
    { label: 'Tanggal', value: 'date' },
    { label: 'Email', value: 'email' },
    { label: 'Telepon', value: 'tel' },
    { label: 'Pilihan', value: 'select' },
    { label: 'Centang', value: 'checkbox' },
];
const fieldSourceOptions = [
    { label: 'Data Pemohon', value: 'data_pemohon' },
    { label: 'Data Kampus', value: 'data_kampus' },
    { label: 'Data Sistem', value: 'data_sistem' },
];
const fieldEditableRoleOptions = [
    { label: 'Mahasiswa', value: 'mahasiswa' },
    { label: 'Admin', value: 'admin' },
    { label: 'Sistem', value: 'sistem' },
];
const fieldModeOptions = [
    { label: 'Bisa diisi pemohon', value: 'editable' },
    { label: 'Hanya dibaca pemohon', value: 'readonly' },
    { label: 'Tidak tampil di form pemohon', value: 'hidden' },
];
function fieldSourceLabel(value?: string) {
    if (value === 'data_kampus') return 'Data Kampus';
    if (value === 'data_sistem') return 'Data Sistem';
    return 'Data Pemohon';
}
function fieldEditableRoleLabel(value?: string) {
    if (value === 'admin') return 'Admin';
    if (value === 'sistem') return 'Sistem';
    return 'Mahasiswa';
}
function fieldModeLabel(value?: string) {
    if (value === 'readonly') return 'Hanya dibaca pemohon';
    if (value === 'hidden') return 'Tidak tampil di form pemohon';
    return 'Bisa diisi pemohon';
}
function isApplicantIdentityFieldName(name: string): boolean {
    const normalized = slugifyLabel(name);

    return [
        'nama',
        'name',
        'nama_pemohon',
        'nama_mahasiswa',
        'nim',
        'nim_nip',
        'nomor_induk',
        'nomor_induk_pemohon',
        'nomor_induk_mahasiswa',
        'program_studi',
        'program_studi_pemohon',
        'program_studi_mahasiswa',
        'fakultas',
        'prodi',
    ].includes(normalized);
}
function applyFieldSourcePreset(field: FieldConfig) {
    if (field.sumber_data === 'data_pemohon') {
        field.editable_role = 'mahasiswa';
        if (isApplicantIdentityFieldName(field.name)) {
            field.mode_form_pemohon = 'readonly';
        }
        return;
    }

    if (field.sumber_data === 'data_kampus') {
        field.editable_role = 'admin';
        return;
    }

    if (field.sumber_data === 'data_sistem') {
        field.editable_role = 'sistem';
        return;
    }

    field.editable_role = 'mahasiswa';
}
watch(
    selectedTemplateComponents,
    (value) => {
        komponen.value = normalizeKomponenFontSize(cloneKomponen(value));
    },
    { immediate: true },
);
function createKomponenDefaults(type: SuratKomponen['type']): SuratKomponen {
    switch (type) {
        case 'judul':
            return { type, teks: 'JUDUL SURAT', align: 'center', bold: true, font_size: '12pt' };
        case 'subjudul':
            return { type, teks: 'Sub judul surat', align: 'center', bold: false, underline: false, font_size: '12pt', margin_left: 0 };
        case 'paragraf':
            return { type, teks: 'Teks paragraf', align: 'justify', margin_left: 0, text_indent: 0, font_size: '12pt' };
        case 'paragraf_indent':
            return { type, teks: 'Teks paragraf', align: 'justify', indent: 0, margin_left: 0, text_indent: 0, font_size: '12pt' };
        case 'header_surat':
            return {
                type,
                nomor: '{{nomor_surat}}',
                lampiran: '-',
                perihal: '{{perihal}}',
                kota: '{{kota_surat}}',
                tanggal: '{{tanggal_surat_panjang}}',
                margin_left: 0,
                font_size: '12pt',
            };
        case 'kepada_yth':
            return {
                type,
                penerima: ['Bapak/Ibu'],
                lokasi: 'di-',
                tempat: 'Tempat',
                margin_left: 0,
                font_size: '12pt',
            };
        case 'tabel_data':
            return { type, rows: [{ label: 'Label', nilai: 'Nilai' }], margin_left: 0, font_size: '12pt' };
        case 'tabel_biasa':
            return {
                type,
                headers: ['Kolom 1', 'Kolom 2'],
                rows: [
                    { cells: ['Isi 1', 'Isi 2'] },
                    { cells: ['Isi 3', 'Isi 4'] },
                ],
                margin_left: 0,
                font_size: '12pt',
            };
        case 'tabel_indent':
            return { type, rows: [{ label: 'Label', nilai: 'Nilai' }], indent: 0, margin_left: 0, font_size: '12pt' };
        case 'tanda_tangan':
            return {
                type,
                kolom: [{
                    jabatan: 'Jabatan',
                    nama: 'Nama',
                    nik: 'NIP/NIK',
                    posisi: 'kanan',
                    jabatan_bold: false,
                    jabatan_underline: false,
                    nama_bold: true,
                    nama_underline: false,
                    nik_bold: false,
                    nik_underline: false,
                }],
                tanggal: '',
                show_tanggal: true,
                margin_left: 0,
                font_size: '12pt',
            };
        case 'qr_validasi':
            return {
                type,
                align: 'right',
                title: 'Komponen warisan',
                caption: 'Disimpan untuk kompatibilitas template lama.',
                margin_left: 0,
                font_size: '11pt',
            };
        case 'tembusan':
            return { type, items: [''], margin_left: 0, font_size: '12pt' };
        case 'spasi':
            return { type, tinggi: 12 };
        case 'garis':
            return { type };
    }
}
function updateKomponen(updater: (items: SuratKomponen[]) => void) {
    updater(komponen.value);
}
function addKomponen(type: SuratKomponen['type']) {
    komponen.value.push(createKomponenDefaults(type));
}
function moveUp(index: number) {
    if (index <= 0) return;
    const items = komponen.value;
    [items[index - 1], items[index]] = [items[index], items[index - 1]];
}
function moveDown(index: number) {
    const items = komponen.value;
    if (index >= items.length - 1) return;
    [items[index + 1], items[index]] = [items[index], items[index + 1]];
}
function removeKomponen(index: number) {
    komponen.value.splice(index, 1);
}
function insertPH(komp: any, key: string) {
    if (!komp || typeof komp.teks !== 'string') return;
    komp.teks = `${komp.teks ?? ''}{{${key}}}`;
}
function addPenerima(komp: any) {
    komp.penerima = Array.isArray(komp.penerima) ? komp.penerima : [];
    komp.penerima.push('');
}
function removePenerima(komp: any, index: number) {
    komp.penerima = Array.isArray(komp.penerima) ? komp.penerima : [];
    komp.penerima.splice(index, 1);
}
function addRow(komp: any) {
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.rows.push({ label: '', nilai: '', __auto_nilai: true });
}
function addTableRow(komp: any) {
    komp.headers = Array.isArray(komp.headers) ? komp.headers : [];
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.rows.push({
        cells: Array.from({ length: komp.headers.length }, () => ''),
    });
}
function addTableColumn(komp: any) {
    komp.headers = Array.isArray(komp.headers) ? komp.headers : [];
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.headers.push(`Kolom ${komp.headers.length + 1}`);
    komp.rows.forEach((row: any) => {
        row.cells = Array.isArray(row.cells) ? row.cells : [];
        row.cells.push('');
    });
}
function removeTableColumn(komp: any, index: number) {
    komp.headers = Array.isArray(komp.headers) ? komp.headers : [];
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.headers.splice(index, 1);
    komp.rows.forEach((row: any) => {
        row.cells = Array.isArray(row.cells) ? row.cells : [];
        row.cells.splice(index, 1);
    });
}
function removeTableRow(komp: any, index: number) {
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.rows.splice(index, 1);
}
function resizeTableHeaders(komp: any, nextCount: number) {
    const count = Math.max(1, Number.isFinite(nextCount) ? Math.floor(nextCount) : 1);
    const headers = Array.isArray(komp.headers) ? komp.headers : [];
    const rows = Array.isArray(komp.rows) ? komp.rows : [];

    while (headers.length < count) {
        headers.push(`Kolom ${headers.length + 1}`);
    }

    if (headers.length > count) {
        headers.splice(count);
    }

    rows.forEach((row: any) => {
        row.cells = Array.isArray(row.cells) ? row.cells : [];
        while (row.cells.length < count) {
            row.cells.push('');
        }
        if (row.cells.length > count) {
            row.cells.splice(count);
        }
    });

    komp.headers = headers;
    komp.rows = rows;
}
function resizeTableRows(komp: any, nextCount: number) {
    const count = Math.max(1, Number.isFinite(nextCount) ? Math.floor(nextCount) : 1);
    komp.headers = Array.isArray(komp.headers) ? komp.headers : ['Kolom 1', 'Kolom 2'];
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];

    while (komp.rows.length < count) {
        komp.rows.push({
            cells: Array.from({ length: komp.headers.length }, () => ''),
        });
    }

    if (komp.rows.length > count) {
        komp.rows.splice(count);
    }
}
function removeRow(komp: any, index: number) {
    komp.rows = Array.isArray(komp.rows) ? komp.rows : [];
    komp.rows.splice(index, 1);
}
function addKolom(komp: any) {
    komp.kolom = Array.isArray(komp.kolom) ? komp.kolom : [];
    komp.kolom.push({
        jabatan: '',
        nama: '',
        nik: '',
        posisi: 'kanan',
        jabatan_bold: false,
        jabatan_underline: false,
        nama_bold: true,
        nama_underline: false,
        nik_bold: false,
        nik_underline: false,
    });
}
function removeKolom(komp: any, index: number) {
    komp.kolom = Array.isArray(komp.kolom) ? komp.kolom : [];
    komp.kolom.splice(index, 1);
}
function addTembusan(komp: any) {
    komp.items = Array.isArray(komp.items) ? komp.items : [];
    komp.items.push('');
}
function removeTembusan(komp: any, index: number) {
    komp.items = Array.isArray(komp.items) ? komp.items : [];
    komp.items.splice(index, 1);
}
function addField() {
    form.field_config.push({
        name: '',
        label: '',
        type: 'text',
        required: false,
        placeholder: '',
        help: '',
        options: [],
        sumber_data: 'data_pemohon',
        editable_role: 'mahasiswa',
        mode_form_pemohon: 'editable',
        __auto_name: true,
    });
}
function createApplicantPresetFields(): FieldConfig[] {
    return [
        {
            name: 'nama',
            label: 'Nama',
            type: 'text',
            required: true,
            placeholder: '',
            help: 'Diisi otomatis dari akun pemohon.',
            options: [],
            sumber_data: 'data_pemohon',
            editable_role: 'mahasiswa',
            mode_form_pemohon: 'readonly',
            __auto_name: false,
        },
        {
            name: 'nim',
            label: 'NIM',
            type: 'text',
            required: true,
            placeholder: '',
            help: 'Diisi otomatis dari akun pemohon.',
            options: [],
            sumber_data: 'data_pemohon',
            editable_role: 'mahasiswa',
            mode_form_pemohon: 'readonly',
            __auto_name: false,
        },
        {
            name: 'program_studi',
            label: 'Program Studi',
            type: 'text',
            required: true,
            placeholder: '',
            help: 'Diisi otomatis dari akun pemohon.',
            options: [],
            sumber_data: 'data_pemohon',
            editable_role: 'mahasiswa',
            mode_form_pemohon: 'readonly',
            __auto_name: false,
        },
        {
            name: 'fakultas',
            label: 'Fakultas',
            type: 'text',
            required: true,
            placeholder: '',
            help: 'Diisi otomatis dari akun pemohon.',
            options: [],
            sumber_data: 'data_pemohon',
            editable_role: 'mahasiswa',
            mode_form_pemohon: 'readonly',
            __auto_name: false,
        },
    ];
}
function addApplicantPresetFields() {
    const presetFields = createApplicantPresetFields();

    presetFields.forEach((preset) => {
        const normalizedName = slugifyLabel(preset.name);
        const existingIndex = form.field_config.findIndex(
            (field) => slugifyLabel(field.name || '') === normalizedName,
        );

        if (existingIndex >= 0) {
            form.field_config[existingIndex] = {
                ...form.field_config[existingIndex],
                ...preset,
                name: preset.name,
                label: preset.label,
                type: preset.type,
                required: preset.required,
                help: preset.help,
                options: [],
                sumber_data: preset.sumber_data,
                editable_role: preset.editable_role,
                mode_form_pemohon: preset.mode_form_pemohon,
                __auto_name: false,
            };
            return;
        }

        form.field_config.push({ ...preset });
    });
}
function removeField(index: number) {
    form.field_config.splice(index, 1);
}
function moveField(index: number, direction: 'up' | 'down') {
    const targetIndex = direction === 'up' ? index - 1 : index + 1;

    if (targetIndex < 0 || targetIndex >= form.field_config.length) {
        return;
    }

    const nextFields = [...form.field_config];
    const currentField = nextFields[index];

    nextFields[index] = nextFields[targetIndex];
    nextFields[targetIndex] = currentField;

    form.field_config = nextFields;
}
function slugifyLabel(label: string): string {
    return (label || '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}
function suggestPlaceholderKey(label: string): string {
    const key = slugifyLabel(label);
    return key ? `{{${key}}}` : '';
}
function syncName(field: FieldConfig, force = false) {
    const suggested = slugifyLabel(field.label || '');
    if (!suggested) return;

    if (force || field.__auto_name || !field.name?.trim()) {
        field.name = suggested;
        field.__auto_name = true;
    }
}
function markFieldNameManual(field: FieldConfig) {
    const suggested = slugifyLabel(field.label || '');
    const current = slugifyLabel(field.name || '');
    field.__auto_name = !suggested || current === suggested;
}
function syncRowPlaceholder(row: any, force = false) {
    const suggested = suggestPlaceholderKey(row.label || '');
    if (!suggested) return;

    if (force || row.__auto_nilai || !String(row.nilai ?? '').trim()) {
        row.nilai = suggested;
        row.__auto_nilai = true;
    }
}
function markRowValueManual(row: any) {
    const suggested = suggestPlaceholderKey(row.label || '');
    row.__auto_nilai = !suggested || String(row.nilai ?? '').trim() === suggested;
}
function toPlaceholderKey(label: string): string {
    return suggestPlaceholderKey(label) || 'Nilai atau {{placeholder}}';
}
function stripUiMetaFromFieldConfig(fields: FieldConfig[]): FieldConfig[] {
    return fields.map((field) => {
        const { __auto_name, ...rest } = field as FieldConfig & { __auto_name?: boolean };
        return rest;
    });
}
function stripUiMetaFromKomponen(items: SuratKomponen[]): SuratKomponen[] {
    return items.map((item) => {
        if (!item || typeof item !== 'object') return item;
        if (item.type === 'tanda_tangan') {
            const { posisi, ...rest } = item as any;
            return rest as SuratKomponen;
        }
        if ('rows' in item && Array.isArray((item as any).rows)) {
            return {
                ...item,
                rows: (item as any).rows.map((row: any) => {
                    const { __auto_nilai, ...rest } = row;
                    return rest;
                }),
            } as SuratKomponen;
        }
        return item;
    });
}
function saveTemplate() {
    if (!props.selectedJenisSurat) return;
    form.field_config.forEach((field) => syncName(field));
    form.transform((data) => ({
        ...data,
        name: data.jenis_surat_nama,
        layout: { ...layout },
        field_config: stripUiMetaFromFieldConfig(form.field_config),
        template_body: JSON.stringify(stripUiMetaFromKomponen(komponen.value)),
    })).put(`/admin/templates/${props.selectedJenisSurat.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
            showToast('Template berhasil disimpan.', 'success');
        },
        onError: async (errors) => {
            const firstMessage = Object.values(errors).find(
                (value) => typeof value === 'string' && value.trim(),
            );

            await navigateToFirstError(errors);

            showToast(
                firstMessage || 'Gagal menyimpan template. Periksa kembali data.',
                'error',
            );
        },
    });
}
const isJson = (s?: string | null): boolean => {
    if (!s) return false;
    const t = s.trim();
    if (!t.startsWith('[')) return false;
    try {
        const d = JSON.parse(t);
        return (
            Array.isArray(d) && d.length > 0 && typeof d[0]?.type === 'string'
        );
    } catch {
        return false;
    }
};
function parseKomponen(body?: string | null): SuratKomponen[] {
    if (!body) return [];
    try {
        if (isJson(body)) return JSON.parse(body) as SuratKomponen[];
    } catch {
        return [];
    }
    const stripped = body
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    return stripped
        ? [{ type: 'paragraf', teks: stripped, align: 'justify' }]
        : [];
}
const tipeLabel: Record<string, string> = {
    judul: 'Judul',
    subjudul: 'Sub Judul',
    paragraf: 'Paragraf',
    paragraf_indent: 'Paragraf Indent',
    header_surat: 'Header Surat (No/Lamp/Perihal)',
    kepada_yth: 'Kepada Yth.',
    tabel_data: 'Tabel Data',
    tabel_biasa: 'Tabel Biasa',
    tabel_indent: 'Tabel Indent',
    tanda_tangan: 'Tanda Tangan',
    qr_validasi: 'Warisan Nonaktif',
    tembusan: 'Tembusan',
    spasi: 'Spasi',
    garis: 'Garis',
};
const tipeBorder: Record<string, string> = {
    judul: 'border-blue-300 bg-blue-50',
    subjudul: 'border-indigo-200 bg-indigo-50',
    paragraf: 'border-slate-200 bg-white',
    paragraf_indent: 'border-slate-200 bg-slate-50',
    header_surat: 'border-purple-200 bg-purple-50',
    kepada_yth: 'border-cyan-200 bg-cyan-50',
    tabel_data: 'border-blue-200 bg-blue-50',
    tabel_biasa: 'border-blue-300 bg-white',
    tabel_indent: 'border-blue-100 bg-blue-50/50',
    tanda_tangan: 'border-amber-200 bg-amber-50',
    qr_validasi: 'border-slate-200 bg-slate-50',
    tembusan: 'border-slate-200 bg-slate-50',
    spasi: 'border-dashed border-slate-300 bg-slate-50',
    garis: 'border-slate-200 bg-slate-50',
};
const tipeGroups = [
    { label: 'Struktur Surat', items: ['header_surat', 'kepada_yth'] },
    { label: 'Teks', items: ['judul', 'subjudul', 'paragraf'] },
    { label: 'Tabel', items: ['tabel_data', 'tabel_biasa'] },
    { label: 'Lainnya', items: ['tanda_tangan', 'tembusan', 'spasi', 'garis'] },
];
// layout: form.layout,         layout: layout,     }, { preserveScroll: true }); }
function deleteTemplate() {
    if (!props.selectedJenisSurat) return;
    if (
        !confirm(
            `Hapus template aktif untuk jenis surat "${props.selectedJenisSurat.nama}"?\n\nTemplate aktif akan dinonaktifkan/dihapus, tetapi surat lama, riwayat, dan arsip tetap aman.`,
        )
    ) {
        return;
    }
    deletingTemplate.value = true;
    router.delete(`/admin/templates/${props.selectedJenisSurat.id}`, {
        preserveScroll: true,
        onSuccess: () => router.visit('/admin/templates'),
        onFinish: () => {
            deletingTemplate.value = false;
        },
        onError: () => {
            deletingTemplate.value = false;
            alert('Template gagal dihapus. Coba lagi atau cek log server.');
        },
    });
}
const addForm = useForm({
    nama: '',
    kode_surat: '',
    kode_klasifikasi: '',
    category_id: '' as number | '',
    deskripsi: '',
    letter_mode: 'personal' as 'personal' | 'institution',
    allowed_role_id: '' as number | '',
    approval_role_id: '' as number | '',
    perlu_approval: false,
    is_active: true,
});
function submitAdd() {
    addForm.post('/admin/templates', {
        onSuccess: () => {
            showAddDialog.value = false;
            addForm.reset();
            showToast('Jenis surat baru berhasil dibuat.', 'success');
        },
        onError: () => {
            showToast('Gagal membuat jenis surat baru.', 'error');
        },
    });
}
const settingsData = ref<Record<string, string>>(
    Object.fromEntries(
        (props.globalSettings ?? []).map((s) => [s.key, s.value ?? '']),
    ),
);
const defaultKopLogoUrl = '/images/kop-logo-temp.png';
const logoFile = ref<File | null>(null);
const logoInputRef = ref<HTMLInputElement | null>(null);
const logoBlobUrl = ref<string | null>(null);
const logoPreviewUrl = ref(resolveLogoPreviewUrl(settingsData.value['logo_path']));
const logoPreviewRoute = '/admin/settings/template/logo-preview';

function resolveLogoPreviewUrl(path?: string | null): string {
    const value = (path ?? '').trim();

    if (!value) {
        return defaultKopLogoUrl;
    }

    if (
        value.startsWith('http://')
        || value.startsWith('https://')
        || value.startsWith('data:')
    ) {
        if (value.startsWith('/public/')) {
            return `/${value.slice('/public/'.length)}`;
        }

        return value;
    }

    if (value.startsWith('/public/')) {
        return `/${value.slice('/public/'.length)}`;
    }

    if (value.startsWith('public/')) {
        return `/${value.slice('public/'.length)}`;
    }

    if (value.startsWith('/private/') || value.startsWith('private/') || value.startsWith('fast/')) {
        return logoPreviewRoute;
    }

    if (value.startsWith('/images/') || value.startsWith('images/') || value.startsWith('/asset/') || value.startsWith('asset/')) {
        return `/${value.replace(/^\/+/, '')}`;
    }

    return logoPreviewRoute;
}

function syncLogoPreview(path?: string | null) {
    if (logoBlobUrl.value) {
        URL.revokeObjectURL(logoBlobUrl.value);
        logoBlobUrl.value = null;
    }

    logoPreviewUrl.value = resolveLogoPreviewUrl(path);
}

function onLogoFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    logoFile.value = file;

    if (!file) {
        syncLogoPreview(settingsData.value['logo_path']);
        return;
    }

    if (logoBlobUrl.value) {
        URL.revokeObjectURL(logoBlobUrl.value);
    }

    const objectUrl = URL.createObjectURL(file);
    logoBlobUrl.value = objectUrl;
    logoPreviewUrl.value = objectUrl;
}

function clearLogoSelection() {
    logoFile.value = null;
    if (logoInputRef.value) {
        logoInputRef.value.value = '';
    }
    syncLogoPreview(settingsData.value['logo_path']);
}

watch(
    () => props.globalSettings,
    (newSettings) => {
        settingsData.value = Object.fromEntries(
            (newSettings ?? []).map((s) => [s.key, s.value ?? '']),
        );

        if (!logoFile.value) {
            syncLogoPreview(settingsData.value['logo_path']);
        }
    },
    { deep: true },
);

watch(showGlobalSettings, (isOpen) => {
    if (isOpen && !logoFile.value) {
        syncLogoPreview(settingsData.value['logo_path']);
    }
});

onBeforeUnmount(() => {
    if (logoBlobUrl.value) {
        URL.revokeObjectURL(logoBlobUrl.value);
    }
});

function saveGlobalSettings() {
    router.post(
        '/admin/settings/template',
        { settings: settingsData.value, logo_file: logoFile.value },
        {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showGlobalSettings.value = false;
                logoFile.value = null;
                if (logoInputRef.value) {
                    logoInputRef.value.value = '';
                }
                showToast('Pengaturan kop & footer berhasil disimpan.', 'success');
            },
            onError: () => {
                showToast('Gagal menyimpan pengaturan kop & footer.', 'error');
            },
        },
    );
}

function toggleActive(id: number, nama: string, current: boolean) {
    if (confirm(`${current ? 'Nonaktifkan' : 'Aktifkan'} "${nama}"?`)) {
        router.patch(
            `/admin/templates/${id}/toggle-active`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showToast(
                        `${current ? 'Template dinonaktifkan.' : 'Template diaktifkan.'}`,
                        'success',
                    );
                },
                onError: () => {
                    showToast('Gagal memperbarui status template.', 'error');
                },
            },
        );
    }
}

function duplicate(id: number) {
    if (confirm('Duplikat jenis surat ini beserta semua isinya?')) {
        router.post(`/admin/templates/${id}/duplicate`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Template berhasil diduplikasi.', 'success');
            },
            onError: () => {
                showToast('Gagal menduplikasi template.', 'error');
            },
        });
    }
}

// Keys per kelompok di dialog pengaturan
const kopKeys = [
    'nama_instansi',
    'nama_fakultas',
    'singkatan',
    'keputusan',
];
const footerKeys = [
    'nama_instansi_footer',
    'alamat_footer',
    'telepon',
    'website',
    'email',
    'fax',
];
const tampilanKeys = ['warna_primer'];
const fontFamilyOptions = [
    'Times New Roman',
    'Cambria',
    'Calibri',
    'Arial',
    'Georgia',
    'Tahoma',
    'Verdana',
    'Courier New',
];
const nomorKeys = [
    'kode_prefix_nomor_surat',
    'kode_fakultas_nomor_surat',
    'kota_surat',
];
const fontKeys = [
    'font_size_kop_instansi',
    'font_size_kop_fakultas',
    'font_size_footer_instansi',
    'font_size_footer_detail',
];
const garisKeys = ['kop_border_thickness', 'footer_border_thickness'];
const marginKeys = [
    'margin_top',
    'margin_right',
    'margin_bottom',
    'margin_left',
];

function settingLabel(key: string): string {
    const m: Record<string, string> = {
        nama_instansi: 'Nama Instansi (Kop)',
        nama_fakultas: 'Nama Fakultas',
        singkatan: 'Singkatan',
        keputusan: 'Teks Keputusan',
        logo_path: 'Logo (path relatif)',
        nama_instansi_footer: 'Nama Instansi (Footer)',
        alamat_footer: 'Alamat (Footer)',
        telepon: 'Telepon',
        website: 'Website',
        email: 'Email',
        fax: 'Fax',
        logo_kop_position: 'Posisi Logo Kop',
        warna_primer: 'Warna Primer',
        font_family_all: 'Font Semua Bagian',
        font_family_kop: 'Font Kop',
        font_family_body: 'Font Isi Surat',
        font_family_footer: 'Font Footer',
        font_size_kop_instansi: 'Font Kop Instansi',
        font_size_kop_fakultas: 'Font Kop Fakultas',
        font_size_footer_instansi: 'Font Footer Instansi',
        font_size_footer_detail: 'Font Footer Detail',
        kop_border_thickness: 'Garis Kop',
        footer_border_thickness: 'Garis Footer',
        format_nomor: 'Format Nomor Surat',
        kode_prefix_nomor_surat: 'Prefix Nomor Surat',
        kode_fakultas_nomor_surat: 'Kode Fakultas Nomor Surat',
        kota_surat: 'Kota Default',
        margin_top: 'Margin Atas',
        margin_right: 'Margin Kanan',
        margin_bottom: 'Margin Bawah',
        margin_left: 'Margin Kiri',
        font_size_default: 'Ukuran Font Default',
    };
    return ((props.globalSettings ?? []).find((s) => s.key === key)?.label ?? m[key] ?? key);
}
</script>
<template>
        <AdminLayout
            title="Template Surat"
            subtitle="Kelola format dan jenis surat"
            active-menu="templates"
            :breadcrumbs="[{ label: 'Template Surat' }]"
        >
        <Head title="Template Surat" />
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="mt-1 text-lg font-bold text-slate-900 sm:text-xl">
                        Template Surat
                    </h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Atur format, komponen, dan field dinamis untuk setiap jenis surat.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <button
                        v-if="can('fast.admin.settings.manage')"
                        type="button"
                        class="fast-btn fast-btn-outline inline-flex h-11 w-full items-center justify-center gap-1.5 px-4 text-sm font-medium text-slate-700 sm:w-auto"
                        @click="openGlobalSettings"
                    >
                        <Settings class="size-4 text-slate-500" /> Pengaturan Kop & Footer
                    </button>
                    <button
                        v-if="can('fast.admin.template.manage')"
                        type="button"
                        class="fast-btn fast-btn-primary inline-flex h-11 w-full items-center justify-center gap-1.5 px-4 text-sm font-semibold sm:w-auto"
                        @click="openAddDialog"
                    >
                        <Plus class="size-4" /> Tambah Jenis Surat
                    </button>
                </div>
            </div>
        </div>
        <div
            v-if="!selectedJenisSurat"
            class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
        >
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_200px_200px_200px_auto] lg:items-end">
                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        Cari template
                    </span>
                    <div class="relative">
                        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="sidebarSearch"
                            type="text"
                            placeholder="Cari template surat..."
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white pr-4 pl-10 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        Kategori
                    </span>
                    <select
                        v-model="categoryFilter"
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                        <option
                            v-for="option in categoryFilterOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        Status
                    </span>
                    <select
                        v-model="statusFilter"
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        Mode
                    </span>
                    <select
                        v-model="modeFilter"
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">Semua mode</option>
                        <option value="personal">Surat Personal</option>
                        <option value="institution">Surat Institusi</option>
                    </select>
                </label>

                <button
                    type="button"
                    class="h-11 w-full rounded-2xl border border-blue-200 bg-blue-50 px-5 text-sm font-medium text-blue-700 transition-colors hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800 sm:w-auto"
                    @click="resetTemplateFilters"
                >
                    Reset Filter
                </button>
            </div>
        </div>
        <div v-if="!selectedJenisSurat" class="space-y-4">
            <div
                v-if="filteredJenisSurats.length === 0"
                class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-slate-200 bg-white py-20 text-center"
            >
                <div
                    class="grid size-16 place-items-center rounded-2xl border-2 border-blue-200 bg-blue-50 text-blue-600"
                >
                    <FileText class="size-8" stroke-width="2" />
                </div>
                <p class="text-sm text-slate-400">
                    Tidak ditemukan jenis surat
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="jenis in filteredJenisSurats"
                    :key="jenis.id"
                    :href="`/admin/templates?jenis_surat_id=${jenis.id}`"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:border-blue-300 hover:shadow-lg"
                >
                    <!-- Top colored stripe -->
                    <div
                        class="absolute top-0 right-0 left-0 h-1"
                        :class="
                            jenis.is_active ? 'bg-blue-400' : 'bg-slate-300'
                        "
                    />
                    <div class="flex items-start justify-between">
                        <div
                            class="grid size-12 place-items-center rounded-2xl border-2"
                            :class="
                                jenis.is_active
                                    ? 'border-blue-300 bg-blue-100 text-blue-700'
                                    : 'border-slate-300 bg-slate-200 text-slate-600'
                            "
                        >
                            <FileText class="size-6" stroke-width="2.5" />
                        </div>
                        <span
                            class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                            :class="
                                jenis.is_active
                                    ? 'border-blue-200 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 bg-slate-100 text-slate-500'
                            "
                        >
                            {{ jenis.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <h3
                        class="mt-4 truncate text-sm font-bold text-slate-900 transition-colors group-hover:text-blue-700"
                    >
                        {{ jenis.nama }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ jenis.category?.nama ?? 'Tanpa kategori' }}
                    </p>
                    <div class="mt-3">
                        <span
                            class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                            :class="
                                jenis.letter_mode === 'institution'
                                    ? 'border-blue-200 bg-blue-50 text-blue-700'
                                    : 'border-amber-200 bg-amber-50 text-amber-700'
                            "
                        >
                            {{ jenis.letter_mode_label ?? (jenis.letter_mode === 'institution' ? 'Surat Institusi' : 'Surat Personal') }}
                        </span>
                    </div>
                    <div
                        class="mt-4 flex items-center gap-3 text-[10px] text-slate-400"
                    >
                        <span class="font-mono">{{
                            jenis.template
                                ? `v${jenis.template.version}`
                                : 'Belum ada template'
                        }}</span>
                        <span>{{
                            jenis.template ? 'Siap pakai' : 'Perlu diatur'
                        }}</span>
                    </div>
                    <div
                        class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3"
                    >
                        <span
                            class="flex items-center gap-1 text-xs font-medium text-blue-600"
                        >
                            Edit Template
                            <ChevronDown class="size-3 rotate-[-90deg]" />
                        </span>
                    </div>
                </Link>
            </div>
        </div>
        <div v-else class="space-y-4">
            <!-- Back bar -->
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    href="/admin/templates"
                    class="fast-btn fast-btn-outline inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600"
                >
                    <ChevronDown class="size-3.5 rotate-90" /> Kembali ke
                    Gallery
                </Link>
                <span class="text-xs text-slate-400"
                    >{{ filteredJenisSurats.length }} jenis surat</span
                >
            </div>
            <!-- Header + aksi -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <div
                            class="grid size-11 shrink-0 place-items-center rounded-2xl border-2 border-blue-200 bg-blue-50 text-blue-600"
                        >
                            <FileText class="size-5" stroke-width="2" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">
                                {{ selectedJenisSurat.category?.nama }}
                            </p>
                            <h2 class="break-words text-lg font-bold text-slate-900">
                                {{ selectedJenisSurat.nama }}
                            </h2>
                            <div class="mt-2">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                                    :class="
                                        selectedJenisSurat.letter_mode === 'institution'
                                            ? 'border-blue-200 bg-blue-50 text-blue-700'
                                            : 'border-amber-200 bg-amber-50 text-amber-700'
                                    "
                                >
                                    {{ selectedJenisSurat.letter_mode_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:flex xl:shrink-0 xl:flex-wrap xl:justify-end">
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn fast-btn-outline inline-flex w-full items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-slate-700 xl:w-auto"
                            @click="duplicate(selectedJenisSurat.id)"
                        >
                            <Copy class="size-3.5 text-slate-500" /> Duplikat
                        </button>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn inline-flex w-full items-center justify-center gap-1 rounded-xl border px-3 py-2 text-xs font-semibold xl:w-auto"
                            :class="
                                selectedJenisSurat.is_active
                                    ? 'fast-btn-danger'
                                    : 'fast-btn-soft text-blue-700'
                            "
                            @click="
                                toggleActive(
                                    selectedJenisSurat.id,
                                    selectedJenisSurat.nama,
                                    selectedJenisSurat.is_active,
                                )
                            "
                        >
                            <component
                                :is="
                                    selectedJenisSurat.is_active
                                        ? ToggleRight
                                        : ToggleLeft
                                "
                                class="size-3.5"
                            />
                            {{
                                selectedJenisSurat.is_active
                                    ? 'Nonaktifkan'
                                    : 'Aktifkan'
                            }}
                        </button>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn fast-btn-danger inline-flex w-full items-center justify-center gap-1 rounded-xl px-3 py-2 text-xs font-semibold disabled:cursor-not-allowed disabled:opacity-60 xl:w-auto"
                            :disabled="deletingTemplate"
                            @click="deleteTemplate"
                        >
                            <Trash2 class="size-3.5" />
                            {{ deletingTemplate ? 'Menghapus...' : 'Hapus Template Aktif' }}
                        </button>
                    </div>
                </div>
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <div class="flex flex-wrap items-center gap-1.5 justify-start">
                        <button
                            v-for="tab in ['template', 'fields', 'meta'] as const"
                            :key="tab"
                            type="button"
                            class="fast-btn inline-flex w-auto min-w-[112px] justify-center rounded-lg px-2.5 py-[5px] text-[10px] font-medium transition sm:min-w-[126px] sm:px-3 sm:py-1.5 sm:text-[11px]"
                            :aria-pressed="activeTab === tab"
                            :class="
                                activeTab === tab
                                    ? 'fast-btn-primary'
                                    : 'fast-btn-outline'
                            "
                            @click="activeTab = tab"
                        >
                            {{
                                tab === 'template'
                                    ? 'Isi Surat'
                                    : tab === 'fields'
                                      ? 'Field Dinamis'
                                      : 'Info & Meta'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed top-5 left-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border px-4 py-3 shadow-lg"
                :class="
                    toastVariant === 'success'
                        ? 'border-blue-200 bg-blue-50 text-blue-800'
                        : 'border-red-200 bg-red-50 text-red-800'
                "
            >
                <div class="flex items-center gap-2.5">
                    <CheckCircle2
                        v-if="toastVariant === 'success'"
                        class="size-5 shrink-0 text-blue-500"
                    />
                    <AlertCircle
                        v-else
                        class="size-5 shrink-0 text-red-500"
                    />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
            <div v-if="activeTab === 'template'" class="space-y-4">
                <div
                    class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-[11px] text-blue-800"
                >
                    <p class="font-semibold">
                        Kop & footer otomatis dari Pengaturan Global
                    </p>
                    <p class="mt-0.5">
                        Buat isi surat di bawah. Kop dan footer akan ditambahkan
                        otomatis.
                    </p>
                </div>
                <!-- Tambah Komponen -->
                <div class="space-y-3">
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="group in tipeGroups"
                            :key="group.label"
                            class="flex flex-col items-start gap-2 sm:flex-row sm:flex-wrap sm:items-center"
                        >
                            <span
                                class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase sm:mr-1"
                                >{{ group.label }}</span
                            >
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="tipe in group.items"
                                    :key="tipe"
                                    type="button"
                                    class="fast-btn fast-btn-primary rounded-lg px-2 py-1.5 text-[10px] font-medium sm:text-[11px]"
                                    @click="addKomponen(tipe as any)"
                                >
                                    {{ tipeLabel[tipe] }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span
                            class="ml-1 text-[10px] text-slate-400"
                            >Placeholder:</span
                        >
                        <code
                            v-for="p in placeholderUmum"
                            :key="p.key"
                            class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-600"
                            >&#123;&#123;{{ p.key }}&#125;&#125;</code
                        >
                    </div>
                </div>
                <!-- Daftar komponen -->
                <div
                    v-if="komponen.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 py-16 text-center"
                >
                    <p class="text-sm text-slate-400">
                        Belum ada komponen. Gunakan tombol di atas untuk
                        menambahkan.
                    </p>
                </div>
                <div
                    v-for="(komp, idx) in komponen"
                    :key="idx"
                    class="rounded-2xl border p-4 shadow-sm"
                    :class="
                        tipeBorder[komp.type] ?? 'border-slate-200 bg-white'
                    "
                >
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700">{{
                            tipeLabel[komp.type]
                        }}</span>
                        <div
                            v-if="can('fast.admin.template.manage')"
                            class="flex items-center gap-1"
                        >
                            <button
                                type="button"
                                class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:text-slate-700 disabled:opacity-30"
                                :disabled="idx === 0"
                                @click="moveUp(idx)"
                            >
                                <ChevronUp class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:text-slate-700 disabled:opacity-30"
                                :disabled="idx === komponen.length - 1"
                                @click="moveDown(idx)"
                            >
                                <ChevronDown class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:bg-red-50 hover:text-red-500"
                                @click="removeKomponen(idx)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                    </div>
                    <!-- Pengaturan font size per komponen -->
                    <div
                        v-if="!['spasi', 'garis'].includes(komp.type)"
                        class="mb-2 flex items-center gap-2"
                    >
                        <span class="text-[10px] text-slate-400"
                            >Ukuran font:</span
                        >
                        <select
                            v-model="(komp as any).font_size"
                            class="h-6 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                        >
                            <option value="">Default (12pt)</option>
                            <option
                                v-for="s in [
                                    '8pt',
                                    '9pt',
                                    '9.5pt',
                                    '10pt',
                                    '10.5pt',
                                    '11pt',
                                    '12pt',
                                    '13pt',
                                    '14pt',
                                ]"
                                :key="s"
                                :value="s"
                            >
                                {{ s }}
                            </option>
                        </select>
                        <!-- todo -->
                        <!-- <span class="text-[10px] text-slate-400 ml-2">Indent:</span>                                 <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"                                     class="h-6 w-14 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />                                 <span class="text-[10px] text-slate-400">px</span> -->
                    </div>
                    <!-- HEADER SURAT -->
                    <div v-if="komp.type === 'header_surat'" class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Nomor</span
                                >
                                <input
                                    v-model="(komp as any).nomor"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="{{nomor_surat}}"
                            /></label>
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Lampiran</span
                                >
                                <input
                                    v-model="(komp as any).lampiran"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    placeholder="-"
                            /></label>
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Perihal</span
                                >
                                <input
                                    v-model="(komp as any).perihal"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Judul surat atau {{perihal}}"
                            /></label>
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Kota, Tanggal (kanan)</span
                                >
                                <input
                                    v-model="(komp as any).kota"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="{{kota_surat}}, {{tanggal_surat_panjang}}"
                            /></label>
                        </div>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- KEPADA YTH -->
                    <div
                        v-else-if="komp.type === 'kepada_yth'"
                        class="space-y-2"
                    >
                        <div
                            v-for="(p, pi) in (komp as any).penerima"
                            :key="pi"
                            class="flex gap-2"
                        >
                            <input
                                v-model="(komp as any).penerima[pi]"
                                type="text"
                                class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Bapak/Ibu Nama Jabatan"
                            />
                            <button
                                v-if="can('fast.admin.template.manage')"
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removePenerima(komp, pi)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn fast-btn-outline h-8 px-3 text-xs font-medium text-blue-700"
                            @click="addPenerima(komp)"
                        >
                            <Plus class="size-3.5" /> Tambah Penerima
                        </button>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Lokasi</span
                                >
                                <input
                                    v-model="(komp as any).lokasi"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                            /></label>
                            <label class="space-y-1"
                                ><span
                                    class="text-[10px] font-medium text-slate-600"
                                    >Tempat</span
                                >
                                <input
                                    v-model="(komp as any).tempat"
                                    type="text"
                                    class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                            /></label>
                        </div>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- JUDUL -->
                    <div v-else-if="komp.type === 'judul'" class="space-y-2">
                        <input
                            v-model="(komp as any).teks"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 uppercase placeholder-slate-400 outline-none focus:border-blue-400"
                            :style="{
                                textAlign: (komp as any).align || 'center',
                            }"
                            placeholder="JUDUL SURAT"
                        />
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex gap-1">
                                <button
                                    v-for="a in [
                                        ['left', 'Kiri'],
                                        ['center', 'Tengah'],
                                        ['right', 'Kanan'],
                                    ]"
                                    :key="a[0]"
                                    type="button"
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs"
                                    :class="
                                        (komp as any).align === a[0]
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="(komp as any).align = a[0]"
                                >
                                    {{ a[1] }}
                                </button>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        (komp as any).bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).bold = !(komp as any).bold
                                    "
                                    title="Bold judul"
                                    aria-label="Bold judul"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-semibold transition-all"
                                    :class="
                                        (komp as any).underline
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).underline = !(komp as any)
                                            .underline
                                    "
                                    title="Underline judul"
                                    aria-label="Underline judul"
                                >
                                    <span class="leading-none underline decoration-2">U</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- SUBJUDUL -->
                    <div v-else-if="komp.type === 'subjudul'" class="space-y-2">
                        <input
                            v-model="(komp as any).teks"
                            type="text"
                            class="h-9 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            :style="{
                                textAlign: (komp as any).align || 'center',
                            }"
                            placeholder="Sub judul, misal: Nomor: {{nomor_surat}}"
                        />
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex gap-1">
                                <button
                                    v-for="a in [
                                        ['left', 'Kiri'],
                                        ['center', 'Tengah'],
                                        ['right', 'Kanan'],
                                    ]"
                                    :key="a[0]"
                                    type="button"
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs"
                                    :class="
                                        (komp as any).align === a[0]
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="(komp as any).align = a[0]"
                                >
                                    {{ a[1] }}
                                </button>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        (komp as any).bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).bold = !(komp as any).bold
                                    "
                                    title="Bold subjudul"
                                    aria-label="Bold subjudul"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-semibold transition-all"
                                    :class="
                                        (komp as any).underline
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).underline = !(komp as any)
                                            .underline
                                    "
                                    title="Underline subjudul"
                                    aria-label="Underline subjudul"
                                >
                                    <span class="leading-none underline decoration-2">U</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- PARAGRAF & PARAGRAF INDENT -->
                    <div
                        v-else-if="
                            komp.type === 'paragraf' ||
                            komp.type === 'paragraf_indent'
                        "
                        class="space-y-2"
                    >
                        <textarea
                            v-model="(komp as any).teks"
                            rows="3"
                            class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            :style="{
                                textAlign: (komp as any).align || 'justify',
                                textIndent:
                                    ((komp as any).text_indent || 0) + 'px',
                                paddingLeft:
                                    ((komp as any).margin_left || 0) + 'px',
                            }"
                            placeholder="Ketik isi paragraf. Gunakan {{placeholder}} untuk data otomatis."
                        />
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-slate-600"
                                    >Rata:</span
                                >
                                <button
                                    v-for="a in [
                                        ['left', 'Kiri'],
                                        ['justify', 'Kanan-kiri'],
                                    ]"
                                    :key="a[0]"
                                    type="button"
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs"
                                    :class="
                                        (komp as any).align === a[0]
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="(komp as any).align = a[0]"
                                >
                                    {{ a[1] }}
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-slate-600"
                                    >Indent baris 1:</span
                                >
                                <input
                                    v-model.number="(komp as any).text_indent"
                                    type="number"
                                    min="0"
                                    max="120"
                                    class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none"
                                />
                                <span class="text-xs text-slate-400">px</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-slate-600">Style:</span>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs italic transition-all"
                                    :class="
                                        (komp as any).italic
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).italic = !(komp as any).italic
                                    "
                                    title="Italic paragraf"
                                    aria-label="Italic paragraf"
                                >
                                    <span class="leading-none">I</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        (komp as any).bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="
                                        (komp as any).bold = !(komp as any).bold
                                    "
                                    title="Bold paragraf"
                                    aria-label="Bold paragraf"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-slate-600"
                                    >Indent kiri:</span
                                >
                                <input
                                    v-model.number="(komp as any).margin_left"
                                    type="number"
                                    min="0"
                                    max="200"
                                    class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none"
                                />
                                <span class="text-xs text-slate-400">px</span>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    v-for="p in placeholderUmum.slice(0, 5)"
                                    :key="p.key"
                                    type="button"
                                    class="rounded-lg border border-blue-200 bg-blue-50 px-1.5 py-0.5 font-mono text-[10px] text-blue-700 hover:bg-blue-100"
                                    @click="insertPH(komp, p.key)"
                                >
                                    +{{ p.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- TABEL BIASA -->
                    <div
                        v-else-if="komp.type === 'tabel_biasa'"
                        class="space-y-3"
                    >
                        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <label class="flex items-center gap-2">
                                <span class="text-[10px] font-medium text-slate-500">Kolom</span>
                                <input
                                    :value="(komp as any).headers?.length ?? 2"
                                    type="number"
                                    min="1"
                                    max="8"
                                    class="h-8 w-18 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    @input="resizeTableHeaders(komp, Number(($event.target as HTMLInputElement).value))"
                                />
                            </label>
                            <label class="flex items-center gap-2">
                                <span class="text-[10px] font-medium text-slate-500">Baris</span>
                                <input
                                    :value="(komp as any).rows?.length ?? 2"
                                    type="number"
                                    min="1"
                                    max="12"
                                    class="h-8 w-18 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    @input="resizeTableRows(komp, Number(($event.target as HTMLInputElement).value))"
                                />
                            </label>
                            <p class="text-[10px] text-slate-400">
                                Ubah ukuran tabel dari sini, lalu isi sel langsung di bawah.
                            </p>
                        </div>
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-300 bg-white"
                        >
                            <div
                                class="grid bg-slate-100 text-[10px] font-medium text-slate-600"
                                :style="{
                                    gridTemplateColumns: `repeat(${Math.max((komp as any).headers?.length ?? 0, 1)}, minmax(0, 1fr))`,
                                }"
                            >
                                <div
                                    v-for="(header, hi) in (komp as any).headers"
                                    :key="hi"
                                    class="border-r border-slate-300 px-3 py-2 last:border-r-0"
                                >
                                    <input
                                        v-model="(komp as any).headers[hi]"
                                        type="text"
                                        class="h-8 w-full border-0 bg-transparent px-0 text-[11px] font-semibold text-slate-700 outline-none placeholder:text-slate-400"
                                        placeholder="Kolom"
                                    >
                                </div>
                            </div>
                            <div class="divide-y divide-slate-200">
                                <div
                                    v-for="(row, ri) in (komp as any).rows"
                                    :key="ri"
                                    class="grid items-stretch"
                                    :style="{
                                        gridTemplateColumns: `repeat(${Math.max((komp as any).headers?.length ?? 0, 1)}, minmax(0, 1fr))`,
                                    }"
                                >
                                    <div
                                        v-for="(cell, ci) in (row.cells ?? [])"
                                        :key="ci"
                                        class="border-r border-slate-200 p-2 last:border-r-0"
                                    >
                                        <input
                                            v-model="row.cells[ci]"
                                            type="text"
                                            class="h-9 w-full border-0 bg-transparent px-0 text-xs text-slate-800 outline-none placeholder:text-slate-400"
                                            placeholder="Isi sel"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400">Indent kiri:</span>
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- TABEL DATA / TABEL INDENT -->
                    <div
                        v-else-if="
                            komp.type === 'tabel_data' ||
                            komp.type === 'tabel_indent'
                        "
                        class="space-y-2"
                    >
                        <div
                            v-if="komp.type === 'tabel_indent'"
                            class="mb-2 flex items-center gap-2"
                        >
                            <span class="text-xs text-slate-600">Indent:</span>
                            <input
                                v-model.number="(komp as any).indent"
                                type="number"
                                min="0"
                                max="120"
                                class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none"
                            />
                            <span class="text-xs text-slate-400">px</span>
                        </div>
                        <div
                            v-for="(row, ri) in (komp as any).rows"
                            :key="ri"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="row.label"
                                type="text"
                                class="h-8 w-32 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Label"
                                @input="syncRowPlaceholder(row)"
                                @keydown.enter.prevent="syncRowPlaceholder(row, true)"
                            />
                            <span class="font-semibold text-slate-500">:</span>
                            <input
                                v-model="row.nilai"
                                type="text"
                                class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                :placeholder="toPlaceholderKey(row.label)"
                                @input="markRowValueManual(row)"
                            />
                            <button
                                v-if="can('fast.admin.template.manage')"
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removeRow(komp, ri)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="flex items-center gap-1 text-xs font-medium text-blue-700"
                            @click="addRow(komp)"
                        >
                            <Plus class="size-3.5" /> Tambah Baris
                        </button>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- KOMPONEN WARISAN -->
                    <div v-else-if="komp.type === 'qr_validasi'" class="space-y-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold text-slate-700">
                                Komponen warisan dinonaktifkan
                            </p>
                            <p class="mt-1 text-[11px] leading-4 text-slate-500">
                                Komponen ini dipertahankan hanya untuk kompatibilitas template lama.
                            </p>
                            <div class="mt-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] text-slate-600">
                                <p class="font-medium text-slate-700">
                                    Label lama: {{ (komp as any).title || 'Komponen warisan' }}
                                </p>
                                <p class="mt-1">
                                    Keterangan lama: {{ (komp as any).caption || 'Disimpan untuk data template lama.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- TANDA TANGAN -->
                    <div
                        v-else-if="komp.type === 'tanda_tangan'"
                        class="space-y-3"
                    >
                        <!-- Tanggal (opsional) -->
                        <label class="flex items-center gap-2">
                            <input
                                v-model="(komp as any).show_tanggal"
                                type="checkbox"
                                class="rounded border-slate-300"
                            />
                            <span class="text-xs text-slate-700"
                                >Tampilkan tanggal</span
                            >
                        </label>
                        <div
                            v-if="(komp as any).show_tanggal"
                            class="space-y-1"
                        >
                            <input
                                v-model="(komp as any).tanggal"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="{{kota_surat}}, {{tanggal_surat_panjang}}"
                            />
                            <p class="text-[10px] text-slate-400">
                                Kosongkan untuk tidak menampilkan tanggal
                                meskipun checkbox aktif.
                            </p>
                        </div>
                        <div
                            v-for="(kol, ki) in (komp as any).kolom"
                            :key="ki"
                            class="space-y-2 rounded-xl border border-amber-200 bg-white p-3"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-semibold text-slate-700"
                                    >Kolom {{ ki + 1 }}</span
                                >
                            <button
                                v-if="can('fast.admin.template.manage')"
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removeKolom(komp, ki)"
                            >
                                <X class="size-3.5" />
                            </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-500"
                                    >Posisi kolom:</span
                                >
                                <div class="flex gap-1">
                                    <button
                                        v-for="p in [
                                            ['kiri', 'Kiri'],
                                            ['tengah', 'Tengah'],
                                            ['kanan', 'Kanan'],
                                        ]"
                                        :key="p[0]"
                                        type="button"
                                        class="fast-btn rounded-lg px-2 py-1 text-[11px]"
                                        :class="
                                            (kol as any).posisi === p[0]
                                                ? 'fast-btn-primary'
                                                : 'fast-btn-outline'
                                        "
                                        @click="(kol as any).posisi = p[0]"
                                    >
                                        {{ p[1] }}
                                    </button>
                                </div>
                            </div>
                            <input
                                v-model="kol.jabatan"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Jabatan (Ketua, Dekan...)"
                            />
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-slate-500">Style:</span>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        kol.jabatan_bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.jabatan_bold = !kol.jabatan_bold"
                                    title="Bold jabatan"
                                    aria-label="Bold jabatan"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-semibold transition-all"
                                    :class="
                                        kol.jabatan_underline
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.jabatan_underline = !kol.jabatan_underline"
                                    title="Underline jabatan"
                                    aria-label="Underline jabatan"
                                    >
                                        <span class="leading-none underline decoration-2">U</span>
                                    </button>
                            </div>
                            <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/70 px-3 py-2 text-center">
                                <p class="text-[11px] font-semibold text-amber-800">
                                    Slot QR otomatis
                                </p>
                                <p class="mt-0.5 text-[10px] leading-4 text-amber-700">
                                    QR akan muncul di antara jabatan dan nama pada output akhir.
                                </p>
                            </div>
                            <input
                                v-model="kol.nama"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Nama atau {{nama_pemohon}}"
                            />
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-slate-500">Style:</span>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        kol.nama_bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.nama_bold = !kol.nama_bold"
                                    title="Bold nama"
                                    aria-label="Bold nama"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-semibold transition-all"
                                    :class="
                                        kol.nama_underline
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.nama_underline = !kol.nama_underline"
                                    title="Underline nama"
                                    aria-label="Underline nama"
                                >
                                    <span class="leading-none underline decoration-2">U</span>
                                </button>
                            </div>
                            <input
                                v-model="kol.nik"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="NIK/NIM"
                            />
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-slate-500">Style:</span>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-black transition-all"
                                    :class="
                                        kol.nik_bold
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.nik_bold = !kol.nik_bold"
                                    title="Bold NIK/NIM"
                                    aria-label="Bold NIK/NIM"
                                >
                                    <span class="leading-none">B</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-semibold transition-all"
                                    :class="
                                        kol.nik_underline
                                            ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                    @click="kol.nik_underline = !kol.nik_underline"
                                    title="Underline NIK/NIM"
                                    aria-label="Underline NIK/NIM"
                                >
                                    <span class="leading-none underline decoration-2">U</span>
                                </button>
                            </div>
                        </div>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn fast-btn-outline h-8 px-3 text-xs font-medium text-blue-700"
                            @click="addKolom(komp)"
                        >
                            <Plus class="size-3.5" /> Tambah Kolom
                        </button>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- TEMBUSAN -->
                    <div v-else-if="komp.type === 'tembusan'" class="space-y-2">
                        <div
                            v-for="(item, ti) in (komp as any).items"
                            :key="ti"
                            class="flex items-center gap-2"
                        >
                            <span class="w-5 text-center text-xs text-slate-500"
                                >{{ ti + 1 }}.</span
                            >
                            <input
                                v-model="(komp as any).items[ti]"
                                type="text"
                                class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Nama penerima"
                            />
                            <button
                                v-if="can('fast.admin.template.manage')"
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removeTembusan(komp, ti)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="fast-btn fast-btn-outline h-8 px-3 text-xs font-medium text-blue-700"
                            @click="addTembusan(komp)"
                        >
                            <Plus class="size-3.5" /> Tambah
                        </button>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="shrink-0 text-[10px] text-slate-400"
                                >Indent kiri:</span
                            >
                            <input
                                v-model.number="(komp as any).margin_left"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none"
                            />
                            <span class="text-[10px] text-slate-400">px</span>
                        </div>
                    </div>
                    <!-- SPASI -->
                    <div
                        v-else-if="komp.type === 'spasi'"
                        class="flex items-center gap-3"
                    >
                        <span class="text-xs text-slate-600">Tinggi:</span>
                        <input
                            v-model.number="(komp as any).tinggi"
                            type="number"
                            min="4"
                            max="120"
                            class="h-8 w-20 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-blue-400"
                        />
                        <span class="text-xs text-slate-500">px</span>
                    </div>
                    <!-- GARIS -->
                    <div v-else-if="komp.type === 'garis'" class="py-1">
                        <hr class="border-slate-300" />
                    </div>
                </div>
                <!-- Layout & Margin per Template -->
                <div
                    v-if="false"
                    class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5"
                >
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">
                            Layout & Margin
                        </h4>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Atur margin halaman dan indent konten khusus untuk
                            template ini
                        </p>
                    </div>
                    <!-- Margin halaman -->
                    <div>
                        <p class="mb-2 text-xs font-semibold text-slate-600">
                            Margin Halaman
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Atas</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.margin_top"
                                        type="text"
                                        placeholder="12mm"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Bawah</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.margin_bottom"
                                        type="text"
                                        placeholder="25mm"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Kiri</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.margin_left"
                                        type="text"
                                        placeholder="15mm"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Kanan</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.margin_right"
                                        type="text"
                                        placeholder="15mm"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                </div>
                            </label>
                        </div>
                        <p class="mt-1 text-[10px] text-slate-400">
                            Format: mm, cm, px - contoh: 15mm, 2cm, 50px
                        </p>
                    </div>
                    <!-- Indent konten -->
                    <div>
                        <p class="mb-2 text-xs font-semibold text-slate-600">
                            Indent Konten
                        </p>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Body (kiri+kanan)</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.body_indent"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                    <span
                                        class="shrink-0 text-[11px] text-slate-400"
                                        >px</span
                                    >
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Indent Paragraf</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.paragraph_indent"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                    <span
                                        class="shrink-0 text-[11px] text-slate-400"
                                        >px</span
                                    >
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-[11px] text-slate-500"
                                    >Indent Tabel</span
                                >
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="layout.table_indent"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    />
                                    <span
                                        class="shrink-0 text-[11px] text-slate-400"
                                        >px</span
                                    >
                                </div>
                            </label>
                        </div>
                        <p class="mt-1 text-[10px] text-slate-400">
                            Indent body menambah padding kiri & kanan seluruh
                            konten surat
                        </p>
                    </div>
                </div>
                <!-- Simpan -->
                <div
                    class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <a
                        v-if="selectedJenisSurat.template?.preview_url"
                        :href="selectedJenisSurat.template.preview_url"
                        target="_blank"
                        class="fast-btn fast-btn-outline inline-flex w-full items-center justify-center gap-1.5 px-4 py-2 text-xs font-medium text-slate-700 sm:w-auto"
                    >
                        <Eye class="size-3.5 text-slate-500" /> Preview PDF
                    </a>
                    <div class="sm:ml-auto">
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            data-template-save
                            class="fast-btn fast-btn-primary inline-flex w-full items-center justify-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold sm:w-auto sm:text-xs"
                            @click="saveTemplate"
                        >
                            <Save class="size-3.5" /> Simpan Isi Surat
                        </button>
                    </div>
                </div>
            </div>
            <div
                v-if="activeTab === 'fields'"
                class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5"
            >
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-[13px] font-semibold text-slate-800 sm:text-sm">
                            Field yang Muncul Saat Buat Surat
                        </h3>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ form.field_config.length }} field
                        </p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 sm:w-auto sm:text-xs"
                            @click="addApplicantPresetFields"
                        >
                            <Plus class="size-3" /> Preset Data Pemohon
                        </button>
                        <button
                            v-if="can('fast.admin.template.manage')"
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-blue-100 px-3 py-1.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-200 sm:w-auto sm:text-xs"
                            @click="addField"
                        >
                            <Plus class="size-3" /> Tambah Field
                        </button>
                    </div>
                </div>
                <div class="space-y-3">
                    <div
                        v-if="form.field_config.length === 0"
                        class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-[13px] text-slate-400"
                    >
                        Belum ada field.
                    </div>
                    <div
                        v-for="(field, idx) in form.field_config"
                        :key="idx"
                        :data-field-index="idx"
                        class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4"
                    >
                        <div class="mb-3 flex items-start justify-between gap-3 sm:mb-4">
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <code
                                        class="rounded-lg bg-blue-100 px-2 py-0.5 font-mono text-[9px] font-semibold text-blue-800 sm:text-[10px]"
                                        >&#123;&#123;{{
                                            field.name || 'nama_field'
                                        }}&#125;&#125;</code
                                    >
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[9px] font-semibold sm:text-[10px]"
                                        :class="
                                            field.sumber_data === 'data_kampus'
                                                ? 'bg-amber-50 text-amber-700'
                                                : field.sumber_data ===
                                                            'data_sistem'
                                                  ? 'bg-slate-100 text-slate-600'
                                                  : 'bg-emerald-50 text-emerald-700'
                                        "
                                    >
                                        {{ fieldSourceLabel(field.sumber_data) }}
                                    </span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[9px] font-semibold sm:text-[10px]"
                                        :class="
                                            field.mode_form_pemohon === 'readonly'
                                                ? 'bg-blue-50 text-blue-700'
                                                : field.mode_form_pemohon ===
                                                            'hidden'
                                                  ? 'bg-slate-100 text-slate-600'
                                                  : 'bg-emerald-50 text-emerald-700'
                                        "
                                    >
                                        {{ fieldModeLabel(field.mode_form_pemohon) }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-semibold text-slate-600 sm:text-[10px]">
                                        {{ fieldEditableRoleLabel(field.editable_role) }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400 sm:text-[11px]">
                                    Atur label, placeholder, dan kontrol akses
                                    field dari panel ini.
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button
                                    v-if="can('fast.admin.template.manage')"
                                    type="button"
                                    class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30 sm:size-8"
                                    :disabled="idx === 0"
                                    title="Pindah ke atas"
                                    @click="moveField(idx, 'up')"
                                >
                                    <ChevronUp class="size-3.5 sm:size-4" />
                                </button>
                                <button
                                    v-if="can('fast.admin.template.manage')"
                                    type="button"
                                    class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30 sm:size-8"
                                    :disabled="idx === form.field_config.length - 1"
                                    title="Pindah ke bawah"
                                    @click="moveField(idx, 'down')"
                                >
                                    <ChevronDown class="size-3.5 sm:size-4" />
                                </button>
                                <button
                                    v-if="can('fast.admin.template.manage')"
                                    type="button"
                                    class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-500 sm:size-8"
                                    title="Hapus field"
                                    @click="removeField(idx)"
                                >
                                    <Trash2 class="size-3.5 sm:size-4" />
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="grid gap-3 lg:grid-cols-2">
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Label</span>
                                    <input
                                        v-model="field.label"
                                        data-field-prop="label"
                                        type="text"
                                        placeholder="Contoh: NIM Mahasiswa"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                        @input="syncName(field)"
                                        @keydown.enter.prevent="syncName(field, true)"
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Key (placeholder)</span>
                                    <input
                                        v-model="field.name"
                                        data-field-prop="name"
                                        type="text"
                                        :placeholder="suggestPlaceholderKey(field.label) || 'nim_mahasiswa'"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-[13px] text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                        @input="markFieldNameManual(field)"
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Tipe Input</span>
                                    <select
                                        v-model="field.type"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                    >
                                        <option
                                            v-for="opt in fieldTypeOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Sumber Data</span>
                                    <select
                                        v-model="field.sumber_data"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                        @change="applyFieldSourcePreset(field)"
                                    >
                                        <option
                                            v-for="opt in fieldSourceOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </label>
                            </div>

                            <div class="grid gap-3 lg:grid-cols-2">
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Dapat diedit oleh</span>
                                    <select
                                        v-model="field.editable_role"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                    >
                                        <option
                                            v-for="opt in fieldEditableRoleOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[9px] font-medium text-slate-600 sm:text-[10px]">Tampil di form pemohon</span>
                                    <select
                                        v-model="field.mode_form_pemohon"
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white sm:h-10 sm:text-sm"
                                    >
                                        <option
                                            v-for="opt in fieldModeOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </label>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input
                                        v-model="field.required"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-blue-600"
                                    />
                                    <span class="text-[11px] font-medium text-slate-700">Wajib diisi</span>
                                </label>
                                <span class="text-[10px] text-slate-400">
                                    Field akan muncul sesuai mode yang dipilih.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="form.field_config.length > 0"
                    class="mt-4 flex justify-end"
                >
                    <button
                        v-if="can('fast.admin.template.manage')"
                        type="button"
                        class="fast-btn fast-btn-primary inline-flex w-full items-center justify-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold sm:w-auto sm:text-xs"
                        @click="saveTemplate"
                    >
                        <Save class="size-3" /> Simpan Field Dinamis
                    </button>
                </div>
                <p
                    v-if="form.errors.field_config"
                    class="mt-2 text-xs text-red-500"
                >
                    {{ form.errors.field_config }}
                </p>
            </div>
            <div
                v-if="activeTab === 'meta'"
                class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5"
            >
                <h3 class="mb-3 text-[13px] font-semibold text-slate-800 sm:mb-4 sm:text-sm">
                    Informasi Jenis Surat
                </h3>
                <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-2 sm:col-span-2">
                        <span class="text-[11px] font-medium text-slate-700">Mode Surat</span>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label data-meta-field="letter_mode" class="flex items-start gap-2.5 rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5">
                                <input v-model="form.letter_mode" type="radio" value="personal" class="mt-1" />
                                <div>
                                    <p class="text-[13px] font-semibold text-slate-900">Surat Personal</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Wajib pilih subjek karena surat mewakili individu tertentu.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5">
                                <input v-model="form.letter_mode" type="radio" value="institution" class="mt-1" />
                                <div>
                                    <p class="text-[13px] font-semibold text-slate-900">Surat Institusi</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Subjek opsional karena surat diterbitkan atas nama kampus atau fakultas.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <label class="space-y-1.5 sm:col-span-2"
                        ><span class="text-[11px] font-medium text-slate-700"
                            >Nama Jenis Surat</span
                        >
                                <input
                                    v-model="form.jenis_surat_nama"
                                    data-meta-field="jenis_surat_nama"
                                    type="text"
                                    class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400 sm:h-10 sm:text-sm"
                                    placeholder="Contoh: Surat Keterangan Lulus"
                    /></label>
                    <label class="space-y-1.5"
                        ><span class="text-[11px] font-medium text-slate-700"
                            >Kode Klasifikasi</span
                        >
                        <input
                            v-model="form.kode_klasifikasi"
                            type="text"
                            class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-[13px] text-slate-800 uppercase placeholder-slate-400 outline-none focus:border-blue-400 sm:h-10 sm:text-sm"
                            placeholder="KU"
                    /></label>
                    <label class="space-y-1.5"
                        ><span class="text-[11px] font-medium text-slate-700"
                            >Kategori</span
                        >
                        <select
                            v-model="form.category_id"
                            data-meta-field="category_id"
                            class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none focus:border-blue-400 sm:h-10 sm:text-sm"
                        >
                            <option value="">Tanpa kategori</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.nama }}
                            </option>
                        </select></label
                    >
                    <label class="space-y-1.5"
                        ><span class="text-[11px] font-medium text-slate-700"
                            >Role Approver</span
                        >
                        <select
                            v-model="form.approval_role_id"
                            data-meta-field="approval_role_id"
                            class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none focus:border-blue-400 sm:h-10 sm:text-sm"
                        >
                            <option value="">
                                Tidak ada (langsung selesai)
                            </option>
                            <option
                                v-for="role in approvalRoles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.nama }}
                            </option>
                        </select></label
                    >
                    <label class="space-y-1.5"
                        ><span class="text-[11px] font-medium text-slate-700"
                            >Role yang Boleh Buat</span
                        >
                        <select
                            v-model="form.allowed_role_id"
                            data-meta-field="allowed_role_id"
                            class="h-9 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-[13px] text-slate-800 outline-none focus:border-blue-400 sm:h-10 sm:text-sm"
                        >
                            <option value="">Semua role</option>
                            <option
                                v-for="role in creatorRoles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.nama }}
                            </option>
                        </select></label
                    >
                    <div class="col-span-2 flex flex-wrap items-center gap-4">
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="form.perlu_approval"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-[13px] text-slate-700"
                                >Perlu Approval</span
                            ></label
                        >
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-[13px] text-slate-700"
                                >Aktif</span
                            ></label
                        >
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        v-if="can('fast.admin.template.manage')"
                        type="button"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold sm:text-xs"
                        @click="saveTemplate"
                    >
                        <Save class="size-3" /> Simpan Info Jenis Surat
                    </button>
                </div>
                <p
                    v-if="form.errors.field_config"
                    class="mt-2 text-xs text-red-500"
                >
                    {{ form.errors.field_config }}
                </p>
            </div>
        </div>
        <Dialog
            :open="showAddDialog"
            @update:open="(v) => (v ? null : closeAddDialog())"
        >
            <DialogContent
                class="max-h-[90vh] w-[min(520px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-xl"
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle
                            class="text-base font-semibold text-slate-900"
                            >Tambah Jenis Surat Baru</DialogTitle
                        >
                        <DialogDescription class="mt-1 text-xs text-slate-500"
                            >Template bisa diisi setelah
                            disimpan.</DialogDescription
                        >
                    </DialogHeader>
                </div>
                <form
                    id="add-form"
                    class="space-y-4 px-6 py-5"
                    @submit.prevent="submitAdd"
                >
                    <label class="block space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Nama Surat
                            <span class="text-red-500">*</span></span
                        >
                        <input
                            v-model="addForm.nama"
                            type="text"
                            required
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="Contoh: Surat Undangan Yudisium"
                        />
                        <p
                            v-if="addForm.errors.nama"
                            class="text-xs text-red-500"
                        >
                            {{ addForm.errors.nama }}
                        </p></label
                    >
                    <div class="space-y-2">
                        <span class="text-xs font-medium text-slate-700">Mode Surat</span>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-start gap-3 rounded-xl border border-slate-300 bg-slate-50 px-3 py-3">
                                <input v-model="addForm.letter_mode" type="radio" value="personal" class="mt-1" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Surat Personal</p>
                                    <p class="mt-1 text-xs text-slate-500">Untuk surat atas nama mahasiswa atau dosen.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 rounded-xl border border-slate-300 bg-slate-50 px-3 py-3">
                                <input v-model="addForm.letter_mode" type="radio" value="institution" class="mt-1" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Surat Institusi</p>
                                    <p class="mt-1 text-xs text-slate-500">Untuk undangan atau surat resmi kampus/fakultas.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="space-y-1.5"
                            ><span class="text-xs font-medium text-slate-700"
                                >Kode Klasifikasi</span
                            >
                            <input
                                v-model="addForm.kode_klasifikasi"
                                type="text"
                                class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-sm text-slate-800 uppercase placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="KU"
                        /></label>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="space-y-1.5"
                            ><span class="text-xs font-medium text-slate-700"
                                >Kategori</span
                            >
                            <select
                                v-model="addForm.category_id"
                                class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                            >
                                <option value="">Tanpa kategori</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.nama }}
                                </option>
                            </select></label
                        >
                    </div>
                    <label class="space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Role Approver</span
                        >
                        <select
                            v-model="addForm.approval_role_id"
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                        >
                            <option value="">
                                Tidak ada (langsung selesai)
                            </option>
                            <option
                                v-for="role in approvalRoles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.nama }}
                            </option>
                        </select></label
                    >
                    <div class="flex items-center gap-6">
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="addForm.perlu_approval"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-sm text-slate-700"
                                >Perlu Approval</span
                            ></label
                        >
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="addForm.is_active"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-sm text-slate-700"
                                >Aktif</span
                            ></label
                        >
                    </div>
                </form>
                <div
                    class="flex justify-end gap-2 border-t border-slate-100 px-6 py-4"
                >
                    <Button
                        type="button"
                        variant="outline"
                        class="rounded-xl border-blue-200 bg-blue-50 text-blue-700 hover:border-blue-300 hover:bg-blue-100"
                        @click="closeAddDialog"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        form="add-form"
                        class="fast-btn fast-btn-primary rounded-xl"
                        :disabled="addForm.processing"
                        >{{
                            addForm.processing ? 'Menyimpan...' : 'Simpan'
                        }}</Button
                    >
                </div>
            </DialogContent>
        </Dialog>
        <Dialog
            :open="showGlobalSettings"
            @update:open="(v) => (v ? null : closeGlobalSettings())"
        >
            <DialogContent
                class="max-h-[92vh] w-[min(1400px,calc(100vw-1rem))] overflow-y-auto rounded-[28px] border border-slate-200 bg-slate-50 p-0 shadow-[0_20px_70px_rgba(15,23,42,0.18)]"
            >
                <div class="border-b border-slate-200 bg-white px-8 py-6">
                    <DialogHeader>
                        <DialogTitle
                            class="text-lg font-semibold text-slate-900"
                            >Pengaturan Kop & Footer Global</DialogTitle
                        >
                        <DialogDescription class="mt-1 text-sm text-slate-500"
                            >Berlaku untuk
                            <strong>semua surat</strong>.</DialogDescription
                        >
                    </DialogHeader>
                </div>
                <div class="space-y-6 px-8 py-6">
                    <!-- KOP / HEADER -->
                    <div
                        v-if="false"
                        class="space-y-4 rounded-3xl border border-blue-200 bg-blue-50 p-5"
                    >
                        <p class="text-sm font-bold text-blue-800">
                            Kop / Header Surat
                        </p>
                        <template v-for="key in kopKeys" :key="key">
                            <label
                                v-if="settingsData[key] !== undefined"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                    >{{ settingLabel(key) }}</span
                                >
                                <input
                                    v-model="settingsData[key]"
                                    type="text"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                />
                            </label>
                        </template>
                        <p class="text-xs leading-5 text-blue-600">
                            "Nama Instansi" tampil di baris pertama kop (misal:
                            UNUGHA CILACAP). "Singkatan" tampil dalam kurung
                            setelah nama fakultas - kosongkan jika tidak perlu.
                        </p>
                    </div>
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Pengaturan Utama Kop & Footer
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Mode standar untuk pengguna umum.
                                </p>
                            </div>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600">
                                Layout aman
                            </div>
                        </div>

                        <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.95fr)]">
                            <div class="space-y-4 rounded-[24px] border border-blue-200 bg-blue-50 p-5">
                                <div>
                                    <p class="text-sm font-bold text-blue-800">Kop Surat</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-700">
                                        Nama instansi, fakultas, singkatan, dan logo.
                                    </p>
                                </div>
                                <template v-for="key in kopKeys" :key="key">
                                    <label
                                        v-if="settingsData[key] !== undefined"
                                        class="block space-y-1.5"
                                    >
                                        <span
                                            class="text-xs font-medium text-slate-700"
                                            >{{ settingLabel(key) }}</span
                                        >
                                        <input
                                            v-model="settingsData[key]"
                                            type="text"
                                            class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                        />
                                    </label>
                                </template>
                                <div class="space-y-3 rounded-2xl border border-blue-100 bg-white/80 p-4">
                                    <div class="flex items-start gap-4">
                                        <div class="h-18 w-18 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                            <img
                                                :src="logoPreviewUrl"
                                                alt="Preview Logo Kop"
                                                class="h-full w-full object-contain p-1"
                                            />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium text-slate-700">
                                                Logo Kop Surat
                                            </p>
                                            <p class="mt-1 break-all text-xs text-slate-500">
                                                {{ settingsData['logo_path'] || defaultKopLogoUrl }}
                                            </p>
                                            <input
                                                ref="logoInputRef"
                                                type="file"
                                                accept="image/*"
                                                class="sr-only"
                                                @change="onLogoFileChange"
                                            />
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <button
                                                    v-if="can('fast.admin.template.manage')"
                                                    type="button"
                                                    class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                                    @click="logoInputRef?.click()"
                                                >
                                                    Upload / Ganti Logo
                                                </button>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                                                    @click="clearLogoSelection"
                                                >
                                                    Reset Preview
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs leading-5 text-blue-600">
                                        Logo ini dipakai untuk kop surat saja. Jika kosong, sistem otomatis memakai file default kop lama.
                                    </p>
                                </div>
                                <label
                                    v-if="settingsData['logo_kop_position'] !== undefined"
                                    class="block space-y-1.5"
                                >
                                    <span
                                        class="text-xs font-medium text-slate-700"
                                        >{{ settingLabel('logo_kop_position') }}</span
                                    >
                                    <select
                                        v-model="settingsData['logo_kop_position']"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 outline-none focus:border-blue-400"
                                    >
                                        <option value="top">Logo di atas</option>
                                        <option value="side">Logo di samping</option>
                                    </select>
                                </label>
                                <p class="text-xs leading-5 text-blue-600">
                                    Logo diambil dari `Logo Universitas`. Kosongkan `Singkatan` jika tidak perlu.
                                </p>
                            </div>

                            <div class="space-y-4 rounded-[24px] border border-blue-200 bg-blue-50 p-5">
                                <div>
                                    <p class="text-sm font-bold text-blue-800">Footer Surat</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-700">
                                        Kontak footer bisa beda dari kop bila diperlukan.
                                    </p>
                                </div>
                                <template v-for="key in footerKeys" :key="key">
                                    <label
                                        v-if="settingsData[key] !== undefined"
                                        class="block space-y-1.5"
                                    >
                                        <span
                                            class="text-xs font-medium text-slate-700"
                                            >{{ settingLabel(key) }}</span
                                        >
                                        <input
                                            v-model="settingsData[key]"
                                            type="text"
                                            class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                        />
                                    </label>
                                </template>
                                <p class="text-xs leading-5 text-blue-600">
                                    Kosongkan bila ingin mengikuti nilai kop secara default.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[24px] border border-purple-200 bg-purple-50 p-5">
                            <p class="text-sm font-bold text-purple-800">
                                Tampilan Cepat
                            </p>
                            <p class="mt-1 text-xs leading-5 text-purple-700">
                                Atur warna utama tanpa menyentuh layout.
                            </p>
                            <label
                                v-if="settingsData['warna_primer'] !== undefined"
                                class="mt-4 block space-y-1.5"
                            >
                                <span class="text-xs font-medium text-slate-700"
                                    >Warna Primer Kop & Footer</span
                                >
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="settingsData['warna_primer']"
                                        type="color"
                                        class="h-10 w-12 cursor-pointer rounded-xl border border-slate-300 bg-white p-0.5"
                                    />
                                    <input
                                        v-model="settingsData['warna_primer']"
                                        type="text"
                                        class="h-10 w-32 rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-800 outline-none focus:border-purple-400"
                                        placeholder="#00b050"
                                    />
                                    <div
                                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2"
                                    >
                                        <div
                                            class="size-4 rounded border border-slate-200"
                                            :style="{
                                                backgroundColor:
                                                    settingsData['warna_primer'] ||
                                                    '#00b050',
                                            }"
                                        />
                                        <span class="text-xs text-slate-600"
                                            >Preview</span
                                        >
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- FONT FAMILY -->
                    <div class="space-y-4 rounded-[24px] border border-sky-200 bg-sky-50 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-sky-800">
                                    Font Surat
                                </p>
                                <p class="mt-1 text-xs leading-5 text-sky-700">
                                    Pilih font yang familiar seperti di Microsoft Word.
                                </p>
                            </div>
                            <div class="rounded-full border border-sky-200 bg-white/80 px-3 py-1.5 text-xs font-medium text-sky-700">
                                Word-like
                            </div>
                        </div>

                        <label
                            v-if="settingsData['font_family_all'] !== undefined"
                            class="block space-y-1.5 rounded-2xl border border-sky-100 bg-white/80 p-4"
                        >
                            <span class="text-xs font-medium text-slate-700">
                                {{ settingLabel('font_family_all') }}
                            </span>
                            <select
                                v-model="settingsData['font_family_all']"
                                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 outline-none focus:border-sky-400"
                            >
                                <option value="">Gunakan font per bagian</option>
                                <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                    {{ font }}
                                </option>
                            </select>
                            <p class="text-xs leading-5 text-sky-700">
                                Jika diisi, font kop, isi surat, dan footer mengikuti pilihan ini.
                            </p>
                        </label>

                        <div class="grid gap-4 md:grid-cols-3">
                            <label
                                v-if="settingsData['font_family_kop'] !== undefined"
                                class="space-y-1.5 rounded-2xl border border-sky-100 bg-white/80 p-4"
                            >
                                <span class="text-xs font-medium text-slate-700">
                                    {{ settingLabel('font_family_kop') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_kop']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-xs leading-5 text-slate-500">Header surat / kop.</p>
                            </label>

                            <label
                                v-if="settingsData['font_family_body'] !== undefined"
                                class="space-y-1.5 rounded-2xl border border-sky-100 bg-white/80 p-4"
                            >
                                <span class="text-xs font-medium text-slate-700">
                                    {{ settingLabel('font_family_body') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_body']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-xs leading-5 text-slate-500">Isi surat utama.</p>
                            </label>

                            <label
                                v-if="settingsData['font_family_footer'] !== undefined"
                                class="space-y-1.5 rounded-2xl border border-sky-100 bg-white/80 p-4"
                            >
                                <span class="text-xs font-medium text-slate-700">
                                    {{ settingLabel('font_family_footer') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_footer']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-xs leading-5 text-slate-500">Footer surat.</p>
                            </label>
                        </div>
                    </div>

                    <!-- UKURAN FONT -->
                    <div
                        class="space-y-4 rounded-[24px] border border-violet-200 bg-violet-50 p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-violet-800">
                                    Ukuran Font Kop & Footer
                                </p>
                                <p class="mt-1 text-xs leading-5 text-violet-700">
                                    Mengatur ukuran teks utama pada kop dan footer.
                                </p>
                            </div>
                            <div class="rounded-full border border-violet-200 bg-white/80 px-3 py-1.5 text-xs font-medium text-violet-700">
                                pt
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <template v-for="key in fontKeys" :key="key">
                                <label
                                    v-if="settingsData[key] !== undefined"
                                    class="space-y-1.5 rounded-2xl border border-violet-100 bg-white/80 p-4"
                                >
                                    <span
                                        class="text-xs font-medium text-slate-700"
                                        >{{ settingLabel(key) }}</span
                                    >
                                    <input
                                        v-model="settingsData[key]"
                                        type="text"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-violet-400"
                                        placeholder="17pt"
                                    />
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- GARIS -->
                    <div class="space-y-4 rounded-[24px] border border-emerald-200 bg-emerald-50 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-emerald-800">
                                    Garis Kop & Footer
                                </p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    Atur ketebalan garis pemisah agar proporsional.
                                </p>
                            </div>
                            <div class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1.5 text-xs font-medium text-emerald-700">
                                px / mm
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <template v-for="key in garisKeys" :key="key">
                                <label
                                    v-if="settingsData[key] !== undefined"
                                    class="space-y-1.5 rounded-2xl border border-emerald-100 bg-white/80 p-4"
                                >
                                    <span
                                        class="text-xs font-medium text-slate-700"
                                        >{{ settingLabel(key) }}</span
                                    >
                                    <input
                                        v-model="settingsData[key]"
                                        type="text"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                                        placeholder="2px"
                                    />
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- FORMAT NOMOR -->
                    <div class="space-y-4 rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-bold text-slate-700">
                            Format Nomor & Kota
                        </p>
                        <template v-for="key in nomorKeys" :key="key">
                            <label
                                v-if="settingsData[key] !== undefined"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                    >{{ settingLabel(key) }}</span
                                >
                                <input
                                    v-model="settingsData[key]"
                                    type="text"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                />
                            </label>
                        </template>
                        <div
                            class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-5 text-blue-700"
                        >
                            Contoh hasil: <strong>CUTI-MHS/0042/IV/2026</strong>
                        </div>
                    </div>
                    <!-- HTML KUSTOM (collapsed) -->
                    <details class="rounded-[24px] border border-slate-200 bg-slate-50">
                        <summary
                            class="cursor-pointer px-5 py-4 text-sm font-semibold text-slate-700"
                        >
                            Opsi Lanjutan
                        </summary>
                        <div class="space-y-4 px-5 pb-5">
                            <p class="text-xs leading-5 text-slate-500">
                                Gunakan ini hanya jika ingin layout kop/footer kustom penuh.
                            </p>
                            <label
                                v-if="settingsData['kop_html'] !== undefined"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-600"
                                    >HTML Kop Surat</span
                                >
                                <textarea
                                    v-model="settingsData['kop_html']"
                                    rows="4"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-3 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Kosongkan untuk otomatis"
                                />
                            </label>
                            <label
                                v-if="settingsData['footer_html'] !== undefined"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-600"
                                    >HTML Footer Surat</span
                                >
                                <textarea
                                    v-model="settingsData['footer_html']"
                                    rows="3"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-3 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Kosongkan untuk otomatis"
                                />
                            </label>
                        </div>
                    </details>
                </div>
                <div
                    class="flex justify-end gap-3 border-t border-slate-200 bg-white px-8 py-5"
                >
                    <Button
                        type="button"
                        variant="outline"
                        class="rounded-xl border-blue-200 bg-blue-50 px-5 py-2.5 text-sm text-blue-700 hover:border-blue-300 hover:bg-blue-100"
                        @click="closeGlobalSettings"
                        >Batal</Button
                    >
                    <Button
                        v-if="can('fast.admin.settings.manage')"
                        type="button"
                        class="fast-btn fast-btn-primary rounded-xl px-5 py-2.5 text-sm"
                        @click="saveGlobalSettings"
                        >Simpan Semua Pengaturan</Button
                    >
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
