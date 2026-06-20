<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/templates/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
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
          font_size?: string;
          margin_left?: number;
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
          kolom: Array<{ jabatan: string; nama: string; nik: string }>;
          posisi?: 'kiri' | 'kanan' | 'tengah' | 'full';
          tanggal?: string;
          show_tanggal?: boolean;
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
    | { type: 'garis' };
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
    is_active: boolean;
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
const sidebarSearch = ref('');
const categoryFilter = ref<'all' | string>('all');
const statusFilter = ref<'all' | 'active' | 'inactive'>('all');
const showAddDialog = ref(false);
const showGlobalSettings = ref(false);
const activeTab = ref<'template' | 'fields' | 'meta'>('template');
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
        if (['spasi', 'garis'].includes(item.type)) return item;
        if (typeof item.font_size === 'string' && item.font_size.trim() !== '') return item;
        return { ...item, font_size: '12pt' };
    });
}
function createFormState(source: JenisSurat | null) {
    return {
        jenis_surat_nama: source?.nama ?? '',
        name: source?.template?.name ?? source?.nama ?? '',
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
watch(
    () => props.selectedJenisSurat,
    (value) => {
        const nextState = createFormState(value);
        form.defaults(nextState);
        form.reset();
        Object.assign(form, nextState);
        komponen.value = prepareKomponenForUi(
            value?.template?.template_components,
        );
        Object.assign(layout, defaultLayoutState());
    },
    { immediate: true },
);
const placeholderUmum = [
    { key: 'nomor_surat', label: 'Nomor Surat' },
    { key: 'tanggal_surat_panjang', label: 'Tanggal Panjang' },
    { key: 'kota_surat', label: 'Kota' },
    { key: 'perihal', label: 'Perihal' },
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
function applyFieldSourcePreset(field: FieldConfig) {
    if (field.sumber_data === 'data_kampus') {
        field.editable_role = 'admin';
        field.mode_form_pemohon = 'readonly';
        return;
    }

    if (field.sumber_data === 'data_sistem') {
        field.editable_role = 'sistem';
        field.mode_form_pemohon = 'hidden';
        return;
    }

    field.editable_role = 'mahasiswa';
    field.mode_form_pemohon = 'editable';
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
            return { type, teks: 'Sub judul surat', font_size: '12pt', margin_left: 0 };
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
                lokasi: 'di Tempat',
                tempat: '',
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
                kolom: [{ jabatan: 'Jabatan', nama: 'Nama', nik: 'NIP/NIK' }],
                posisi: 'kanan',
                tanggal: '',
                show_tanggal: true,
                margin_left: 0,
                font_size: '12pt',
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
function addCenteredNomorPreset() {
    komponen.value.push(createKomponenDefaults('header_surat'));
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
    komp.kolom.push({ jabatan: '', nama: '', nik: '' });
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
function removeField(index: number) {
    form.field_config.splice(index, 1);
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
    form.transform((data) => ({
        ...data,
        layout: { ...layout },
        field_config: stripUiMetaFromFieldConfig(form.field_config),
        template_body: JSON.stringify(stripUiMetaFromKomponen(komponen.value)),
    })).put(`/admin/templates/${props.selectedJenisSurat.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
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
        },
    });
}
const settingsData = ref<Record<string, string>>(
    Object.fromEntries(
        (props.globalSettings ?? []).map((s) => [s.key, s.value ?? '']),
    ),
);

function saveGlobalSettings() {
    router.post(
        '/admin/settings/template',
        { settings: settingsData.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                showGlobalSettings.value = false;
            },
        },
    );
}

function toggleActive(id: number, nama: string, current: boolean) {
    if (confirm(`${current ? 'Nonaktifkan' : 'Aktifkan'} "${nama}"?`)) {
        router.patch(
            `/admin/templates/${id}/toggle-active`,
            {},
            { preserveScroll: true },
        );
    }
}

function duplicate(id: number) {
    if (confirm('Duplikat jenis surat ini beserta semua isinya?')) {
        router.post(`/admin/templates/${id}/duplicate`);
    }
}

// Keys per kelompok di dialog pengaturan
const kopKeys = [
    'nama_instansi',
    'nama_fakultas',
    'singkatan',
    'keputusan',
    'logo_path',
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
                        type="button"
                        class="fast-btn fast-btn-outline inline-flex h-11 w-full items-center justify-center gap-1.5 px-4 text-sm font-medium text-slate-700 sm:w-auto"
                        @click="openGlobalSettings"
                    >
                        <Settings class="size-4 text-slate-500" /> Pengaturan Kop & Footer
                    </button>
                    <button
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
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_200px_200px_auto] lg:items-end">
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

                <button
                    type="button"
                    class="fast-btn fast-btn-soft h-11 px-4 text-sm font-semibold text-blue-700"
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
            <div class="flex items-center gap-3">
                <Link
                    href="/admin/templates"
                        class="fast-btn fast-btn-outline flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600"
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
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-11 place-items-center rounded-2xl border-2 border-blue-200 bg-blue-50 text-blue-600"
                        >
                            <FileText class="size-5" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">
                                {{ selectedJenisSurat.category?.nama }}
                            </p>
                            <h2 class="text-lg font-bold text-slate-900">
                                {{ selectedJenisSurat.nama }}
                            </h2>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap justify-end gap-2">
                        <button
                            type="button"
                        class="fast-btn fast-btn-outline flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-700"
                            @click="duplicate(selectedJenisSurat.id)"
                        >
                            <Copy class="size-3.5 text-slate-500" /> Duplikat
                        </button>
                        <button
                            type="button"
                            class="fast-btn flex items-center gap-1 rounded-xl border px-3 py-1.5 text-xs font-semibold"
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
                            type="button"
                            class="fast-btn fast-btn-danger flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="deletingTemplate"
                            @click="deleteTemplate"
                        >
                            <Trash2 class="size-3.5" />
                            {{ deletingTemplate ? 'Menghapus...' : 'Hapus Template Aktif' }}
                        </button>
                    </div>
                </div>
                <div class="mt-4 flex gap-1 border-t border-slate-100 pt-4">
                    <button
                        v-for="tab in ['template', 'fields', 'meta'] as const"
                        :key="tab"
                        type="button"
                        class="fast-btn px-3 py-1.5 text-xs font-medium"
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
            <div v-if="activeTab === 'template'" class="space-y-4">
                <div
                    class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800"
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
                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            v-for="group in tipeGroups"
                            :key="group.label"
                            class="flex items-center gap-1"
                        >
                            <span
                                class="mr-1 text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                >{{ group.label }}</span
                            >
                            <button
                                v-for="tipe in group.items"
                                :key="tipe"
                                type="button"
                                class="fast-btn fast-btn-outline rounded-lg px-2.5 py-1 text-[11px] font-medium text-slate-700"
                                @click="addKomponen(tipe as any)"
                            >
                                {{ tipeLabel[tipe] }}
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            type="button"
                            class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-blue-700 transition-colors hover:bg-blue-100"
                            @click="addCenteredNomorPreset"
                        >
                            Preset Nomor Tengah
                        </button>
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
                    class="rounded-2xl border p-4"
                    :class="
                        tipeBorder[komp.type] ?? 'border-slate-200 bg-white'
                    "
                >
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700">{{
                            tipeLabel[komp.type]
                        }}</span>
                        <div class="flex items-center gap-1">
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
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removePenerima(komp, pi)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
                            type="button"
                            class="flex items-center gap-1 text-xs font-medium text-blue-700"
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
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs font-bold"
                                    :class="
                                        (komp as any).bold
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="
                                        (komp as any).bold = !(komp as any).bold
                                    "
                                >
                                    B
                                </button>
                                <button
                                    type="button"
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs underline"
                                    :class="
                                        (komp as any).underline
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="
                                        (komp as any).underline = !(komp as any)
                                            .underline
                                    "
                                >
                                    U
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
                                marginLeft:
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
                            <label class="flex items-center gap-1.5">
                                <input
                                    v-model="(komp as any).italic"
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                />
                                <span class="text-xs text-slate-600 italic"
                                    >Italic</span
                                >
                            </label>
                            <label class="flex items-center gap-1.5">
                                <input
                                    v-model="(komp as any).bold"
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                />
                                <span class="text-xs font-bold text-slate-600"
                                    >Bold</span
                                >
                            </label>
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
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removeRow(komp, ri)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
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
                    <!-- TANDA TANGAN -->
                    <div
                        v-else-if="komp.type === 'tanda_tangan'"
                        class="space-y-3"
                    >
                        <!-- Posisi tanda tangan -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-600">Posisi:</span>
                            <div class="flex gap-1">
                                <button
                                    v-for="p in [
                                        ['kanan', 'Kanan'],
                                        ['kiri', 'Kiri'],
                                        ['tengah', 'Tengah'],
                                        ['full', 'Penuh'],
                                    ]"
                                    :key="p[0]"
                                    type="button"
                                    class="fast-btn rounded-lg px-2.5 py-1 text-xs"
                                    :class="
                                        (komp as any).posisi === p[0]
                                            ? 'fast-btn-primary'
                                            : 'fast-btn-outline'
                                    "
                                    @click="(komp as any).posisi = p[0]"
                                >
                                    {{ p[1] }}
                                </button>
                            </div>
                        </div>
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
                                    type="button"
                                    class="text-slate-400 hover:text-red-500"
                                    @click="removeKolom(komp, ki)"
                                >
                                    <X class="size-3.5" />
                                </button>
                            </div>
                            <input
                                v-model="kol.jabatan"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Jabatan (Ketua, Dekan...)"
                            />
                            <input
                                v-model="kol.nama"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Nama atau {{nama_pemohon}}"
                            />
                            <input
                                v-model="kol.nik"
                                type="text"
                                class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="NIK/NIM"
                            />
                        </div>
                        <button
                            type="button"
                            class="flex items-center gap-1 text-xs font-medium text-blue-700"
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
                                type="button"
                                class="text-slate-400 hover:text-red-500"
                                @click="removeTembusan(komp, ti)"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <button
                            type="button"
                            class="flex items-center gap-1 text-xs font-medium text-blue-700"
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
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4"
                >
                    <a
                        v-if="selectedJenisSurat.template?.preview_url"
                        :href="selectedJenisSurat.template.preview_url"
                        target="_blank"
                        class="fast-btn fast-btn-outline flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-slate-700"
                    >
                        <Eye class="size-3.5 text-slate-500" /> Preview PDF
                    </a>
                    <div class="ml-auto">
                    <button
                        type="button"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-5 py-2 text-xs font-semibold"
                        @click="saveTemplate"
                    >
                            <Save class="size-3.5" /> Simpan Isi Surat
                        </button>
                    </div>
                </div>
            </div>
            <div
                v-if="activeTab === 'fields'"
                class="rounded-2xl border border-slate-200 bg-white p-5"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">
                            Field yang Muncul Saat Buat Surat
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ form.field_config.length }} field
                        </p>
                    </div>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                        @click="addField"
                    >
                        <Plus class="size-3.5" /> Tambah Field
                    </button>
                </div>
                <div class="space-y-3">
                    <div
                        v-if="form.field_config.length === 0"
                        class="rounded-xl border border-dashed border-slate-200 py-10 text-center text-sm text-slate-400"
                    >
                        Belum ada field.
                    </div>
                    <div
                        v-for="(field, idx) in form.field_config"
                        :key="idx"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <code
                                        class="rounded-lg bg-blue-100 px-2.5 py-1 font-mono text-[10px] font-semibold text-blue-800"
                                        >&#123;&#123;{{
                                            field.name || 'nama_field'
                                        }}&#125;&#125;</code
                                    >
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
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
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
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
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">
                                        {{ fieldEditableRoleLabel(field.editable_role) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Atur label, placeholder, dan kontrol akses
                                    field dari panel ini.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                @click="removeField(idx)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid gap-3 lg:grid-cols-2">
                                <label class="space-y-1.5">
                                    <span class="text-[10px] font-medium text-slate-600">Label</span>
                                    <input
                                        v-model="field.label"
                                        type="text"
                                        placeholder="Contoh: NIM Mahasiswa"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:bg-white"
                                        @input="syncName(field)"
                                        @keydown.enter.prevent="syncName(field, true)"
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[10px] font-medium text-slate-600">Key (placeholder)</span>
                                    <input
                                        v-model="field.name"
                                        type="text"
                                        :placeholder="suggestPlaceholderKey(field.label) || 'nim_mahasiswa'"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:bg-white"
                                        @input="markFieldNameManual(field)"
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-[10px] font-medium text-slate-600">Tipe Input</span>
                                    <select
                                        v-model="field.type"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white"
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
                                    <span class="text-[10px] font-medium text-slate-600">Sumber Data</span>
                                    <select
                                        v-model="field.sumber_data"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white"
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
                                    <span class="text-[10px] font-medium text-slate-600">Dapat diedit oleh</span>
                                    <select
                                        v-model="field.editable_role"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white"
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
                                    <span class="text-[10px] font-medium text-slate-600">Tampil di form pemohon</span>
                                    <select
                                        v-model="field.mode_form_pemohon"
                                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white"
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

                            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input
                                        v-model="field.required"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-blue-600"
                                    />
                                    <span class="text-xs font-medium text-slate-700">Wajib diisi</span>
                                </label>
                                <span class="text-[11px] text-slate-400">
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
                        type="button"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-4 py-2 text-xs font-semibold"
                        @click="saveTemplate"
                    >
                        <Save class="size-3.5" /> Simpan Field Dinamis
                    </button>
                </div>
            </div>
            <div
                v-if="activeTab === 'meta'"
                class="rounded-2xl border border-slate-200 bg-white p-5"
            >
                <h3 class="mb-4 text-sm font-semibold text-slate-800">
                    Informasi Jenis Surat
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="space-y-1.5 sm:col-span-2"
                        ><span class="text-xs font-medium text-slate-700"
                            >Nama Jenis Surat</span
                        >
                        <input
                            v-model="form.jenis_surat_nama"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="Contoh: Surat Keterangan Lulus"
                    /></label>
                    <label class="space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Kode Klasifikasi</span
                        >
                        <input
                            v-model="form.kode_klasifikasi"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-sm text-slate-800 uppercase placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="KU"
                    /></label>
                    <label class="space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Kategori</span
                        >
                        <select
                            v-model="form.category_id"
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
                    <label class="space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Role Approver</span
                        >
                        <select
                            v-model="form.approval_role_id"
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
                    <label class="space-y-1.5"
                        ><span class="text-xs font-medium text-slate-700"
                            >Role yang Boleh Buat</span
                        >
                        <select
                            v-model="form.allowed_role_id"
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
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
                    <div class="col-span-2 flex items-center gap-6">
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="form.perlu_approval"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-sm text-slate-700"
                                >Perlu Approval</span
                            ></label
                        >
                        <label class="flex cursor-pointer items-center gap-2"
                            ><input
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-slate-300 text-blue-600"
                            /><span class="text-sm text-slate-700"
                                >Aktif</span
                            ></label
                        >
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-4 py-2 text-xs font-semibold"
                        @click="saveTemplate"
                    >
                        <Save class="size-3.5" /> Simpan Info Jenis Surat
                    </button>
                </div>
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
                        class="rounded-xl text-slate-700"
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
                class="max-h-[90vh] w-[min(620px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-xl"
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle
                            class="text-base font-semibold text-slate-900"
                            >Pengaturan Kop & Footer Global</DialogTitle
                        >
                        <DialogDescription class="mt-1 text-xs text-slate-500"
                            >Berlaku untuk
                            <strong>semua surat</strong>.</DialogDescription
                        >
                    </DialogHeader>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <!-- KOP / HEADER -->
                    <div
                        v-if="false"
                        class="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4"
                    >
                        <p class="text-xs font-bold text-blue-800">
                            Kop / Header Surat
                        </p>
                        <template v-for="key in kopKeys" :key="key">
                            <label
                                v-if="settingsData[key] !== undefined"
                                class="block space-y-1"
                            >
                                <span
                                    class="text-[10px] font-medium text-slate-700"
                                    >{{ settingLabel(key) }}</span
                                >
                                <input
                                    v-model="settingsData[key]"
                                    type="text"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                />
                            </label>
                        </template>
                        <p class="text-[10px] text-blue-600">
                            "Nama Instansi" tampil di baris pertama kop (misal:
                            UNUGHA CILACAP). "Singkatan" tampil dalam kurung
                            setelah nama fakultas - kosongkan jika tidak perlu.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-slate-900">
                                    Pengaturan Utama Kop & Footer
                                </p>
                                <p class="mt-0.5 text-[10px] text-slate-500">
                                    Mode standar untuk pengguna umum.
                                </p>
                            </div>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-medium text-slate-600">
                                Layout aman
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div class="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
                                <div>
                                    <p class="text-xs font-bold text-blue-800">Kop Surat</p>
                                    <p class="mt-0.5 text-[10px] text-blue-700">
                                        Nama instansi, fakultas, singkatan, dan logo.
                                    </p>
                                </div>
                                <template v-for="key in kopKeys" :key="key">
                                    <label
                                        v-if="settingsData[key] !== undefined"
                                        class="block space-y-1"
                                    >
                                        <span
                                            class="text-[10px] font-medium text-slate-700"
                                            >{{ settingLabel(key) }}</span
                                        >
                                        <input
                                            v-model="settingsData[key]"
                                            type="text"
                                            class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                        />
                                    </label>
                                </template>
                                <label
                                    v-if="settingsData['logo_kop_position'] !== undefined"
                                    class="block space-y-1"
                                >
                                    <span
                                        class="text-[10px] font-medium text-slate-700"
                                        >{{ settingLabel('logo_kop_position') }}</span
                                    >
                                    <select
                                        v-model="settingsData['logo_kop_position']"
                                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-blue-400"
                                    >
                                        <option value="top">Logo di atas</option>
                                        <option value="side">Logo di samping</option>
                                    </select>
                                </label>
                                <p class="text-[10px] text-blue-600">
                                    Logo diambil dari `Logo Universitas`. Kosongkan `Singkatan` jika tidak perlu.
                                </p>
                            </div>

                            <div class="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
                                <div>
                                    <p class="text-xs font-bold text-blue-800">Footer Surat</p>
                                    <p class="mt-0.5 text-[10px] text-blue-700">
                                        Kontak footer bisa beda dari kop bila diperlukan.
                                    </p>
                                </div>
                                <template v-for="key in footerKeys" :key="key">
                                    <label
                                        v-if="settingsData[key] !== undefined"
                                        class="block space-y-1"
                                    >
                                        <span
                                            class="text-[10px] font-medium text-slate-700"
                                            >{{ settingLabel(key) }}</span
                                        >
                                        <input
                                            v-model="settingsData[key]"
                                            type="text"
                                            class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                        />
                                    </label>
                                </template>
                                <p class="text-[10px] text-blue-600">
                                    Kosongkan bila ingin mengikuti nilai kop secara default.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-purple-200 bg-purple-50 p-4">
                            <p class="text-xs font-bold text-purple-800">
                                Tampilan Cepat
                            </p>
                            <p class="mt-0.5 text-[10px] text-purple-700">
                                Atur warna utama tanpa menyentuh layout.
                            </p>
                            <label
                                v-if="settingsData['warna_primer'] !== undefined"
                                class="mt-3 block space-y-1"
                            >
                                <span class="text-[10px] font-medium text-slate-700"
                                    >Warna Primer Kop & Footer</span
                                >
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="settingsData['warna_primer']"
                                        type="color"
                                        class="h-9 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-0.5"
                                    />
                                    <input
                                        v-model="settingsData['warna_primer']"
                                        type="text"
                                        class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 outline-none focus:border-purple-400"
                                        placeholder="#00b050"
                                    />
                                    <div
                                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5"
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
                    <div class="space-y-3 rounded-xl border border-sky-200 bg-sky-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-sky-800">
                                    Font Surat
                                </p>
                                <p class="mt-0.5 text-[10px] text-sky-700">
                                    Pilih font yang familiar seperti di Microsoft Word.
                                </p>
                            </div>
                            <div class="rounded-full border border-sky-200 bg-white/80 px-2.5 py-1 text-[10px] font-medium text-sky-700">
                                Word-like
                            </div>
                        </div>

                        <label
                            v-if="settingsData['font_family_all'] !== undefined"
                            class="block space-y-1 rounded-lg border border-sky-100 bg-white/80 p-3"
                        >
                            <span class="text-[10px] font-medium text-slate-700">
                                {{ settingLabel('font_family_all') }}
                            </span>
                            <select
                                v-model="settingsData['font_family_all']"
                                class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-sky-400"
                            >
                                <option value="">Gunakan font per bagian</option>
                                <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                    {{ font }}
                                </option>
                            </select>
                            <p class="text-[10px] text-sky-700">
                                Jika diisi, font kop, isi surat, dan footer mengikuti pilihan ini.
                            </p>
                        </label>

                        <div class="grid gap-3 md:grid-cols-3">
                            <label
                                v-if="settingsData['font_family_kop'] !== undefined"
                                class="space-y-1 rounded-lg border border-sky-100 bg-white/80 p-3"
                            >
                                <span class="text-[10px] font-medium text-slate-700">
                                    {{ settingLabel('font_family_kop') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_kop']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-[10px] text-slate-500">Header surat / kop.</p>
                            </label>

                            <label
                                v-if="settingsData['font_family_body'] !== undefined"
                                class="space-y-1 rounded-lg border border-sky-100 bg-white/80 p-3"
                            >
                                <span class="text-[10px] font-medium text-slate-700">
                                    {{ settingLabel('font_family_body') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_body']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-[10px] text-slate-500">Isi surat utama.</p>
                            </label>

                            <label
                                v-if="settingsData['font_family_footer'] !== undefined"
                                class="space-y-1 rounded-lg border border-sky-100 bg-white/80 p-3"
                            >
                                <span class="text-[10px] font-medium text-slate-700">
                                    {{ settingLabel('font_family_footer') }}
                                </span>
                                <select
                                    v-model="settingsData['font_family_footer']"
                                    :disabled="(settingsData['font_family_all'] ?? '').trim() !== ''"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-sky-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option v-for="font in fontFamilyOptions" :key="font" :value="font">
                                        {{ font }}
                                    </option>
                                </select>
                                <p class="text-[10px] text-slate-500">Footer surat.</p>
                            </label>
                        </div>
                    </div>

                    <!-- UKURAN FONT -->
                    <div
                        class="space-y-3 rounded-xl border border-violet-200 bg-violet-50 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-violet-800">
                                    Ukuran Font Kop & Footer
                                </p>
                                <p class="mt-0.5 text-[10px] text-violet-700">
                                    Mengatur ukuran teks utama pada kop dan footer.
                                </p>
                            </div>
                            <div class="rounded-full border border-violet-200 bg-white/80 px-2.5 py-1 text-[10px] font-medium text-violet-700">
                                pt
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <template v-for="key in fontKeys" :key="key">
                                <label
                                    v-if="settingsData[key] !== undefined"
                                    class="space-y-1 rounded-lg border border-violet-100 bg-white/80 p-3"
                                >
                                    <span
                                        class="text-[10px] font-medium text-slate-700"
                                        >{{ settingLabel(key) }}</span
                                    >
                                    <input
                                        v-model="settingsData[key]"
                                        type="text"
                                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-violet-400"
                                        placeholder="17pt"
                                    />
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- GARIS -->
                    <div class="space-y-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-emerald-800">
                                    Garis Kop & Footer
                                </p>
                                <p class="mt-0.5 text-[10px] text-emerald-700">
                                    Atur ketebalan garis pemisah agar proporsional.
                                </p>
                            </div>
                            <div class="rounded-full border border-emerald-200 bg-white/80 px-2.5 py-1 text-[10px] font-medium text-emerald-700">
                                px / mm
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <template v-for="key in garisKeys" :key="key">
                                <label
                                    v-if="settingsData[key] !== undefined"
                                    class="space-y-1 rounded-lg border border-emerald-100 bg-white/80 p-3"
                                >
                                    <span
                                        class="text-[10px] font-medium text-slate-700"
                                        >{{ settingLabel(key) }}</span
                                    >
                                    <input
                                        v-model="settingsData[key]"
                                        type="text"
                                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                                        placeholder="2px"
                                    />
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- MARGIN -->
                    <div class="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold text-amber-800">
                            Margin Halaman
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <template v-for="key in marginKeys" :key="key">
                                <label
                                    v-if="settingsData[key] !== undefined"
                                    class="space-y-1"
                                >
                                    <span
                                        class="text-[10px] font-medium text-slate-700"
                                        >{{ settingLabel(key) }}</span
                                    >
                                    <input
                                        v-model="settingsData[key]"
                                        type="text"
                                        class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-amber-400"
                                        placeholder="12mm"
                                    />
                                </label>
                            </template>
                        </div>
                        <p class="text-[10px] text-amber-600">
                            Contoh: 12mm, 15mm, 1cm, 1in
                        </p>
                    </div>
                    <!-- FORMAT NOMOR -->
                    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold text-slate-700">
                            Format Nomor & Kota
                        </p>
                        <template v-for="key in nomorKeys" :key="key">
                            <label
                                v-if="settingsData[key] !== undefined"
                                class="block space-y-1"
                            >
                                <span
                                    class="text-[10px] font-medium text-slate-700"
                                    >{{ settingLabel(key) }}</span
                                >
                                <input
                                    v-model="settingsData[key]"
                                    type="text"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                />
                            </label>
                        </template>
                        <div
                            class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-[10px] text-blue-700"
                        >
                            Contoh hasil: <strong>CUTI-MHS/0042/IV/2026</strong>
                        </div>
                    </div>
                    <!-- HTML KUSTOM (collapsed) -->
                    <details class="rounded-xl border border-slate-200 bg-slate-50">
                        <summary
                            class="cursor-pointer px-4 py-3 text-xs font-semibold text-slate-700"
                        >
                            Opsi Lanjutan
                        </summary>
                        <div class="space-y-3 px-4 pb-4">
                            <p class="text-[10px] text-slate-500">
                                Gunakan ini hanya jika ingin layout kop/footer kustom penuh.
                            </p>
                            <label
                                v-if="settingsData['kop_html'] !== undefined"
                                class="block space-y-1"
                            >
                                <span
                                    class="text-[10px] font-medium text-slate-600"
                                    >HTML Kop Surat</span
                                >
                                <textarea
                                    v-model="settingsData['kop_html']"
                                    rows="4"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Kosongkan untuk otomatis"
                                />
                            </label>
                            <label
                                v-if="settingsData['footer_html'] !== undefined"
                                class="block space-y-1"
                            >
                                <span
                                    class="text-[10px] font-medium text-slate-600"
                                    >HTML Footer Surat</span
                                >
                                <textarea
                                    v-model="settingsData['footer_html']"
                                    rows="3"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    placeholder="Kosongkan untuk otomatis"
                                />
                            </label>
                        </div>
                    </details>
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-100 px-6 py-4"
                >
                    <Button
                        type="button"
                        variant="outline"
                        class="rounded-xl text-slate-700"
                        @click="closeGlobalSettings"
                        >Batal</Button
                    >
                    <Button
                        type="button"
                        class="fast-btn fast-btn-primary rounded-xl"
                        @click="saveGlobalSettings"
                        >Simpan Semua Pengaturan</Button
                    >
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
