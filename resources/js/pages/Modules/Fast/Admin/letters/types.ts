export type FastLetterFormState = {
    jenis_surat_id: number;
    subject_name: string;
    keperluan: string;
    perihal: string;
    kepada_yth: string[];
    lampiran_keterangan: string;
    lampiran_judul: string;
    lampiran_orientation: string;
    lampiran_judul_align: string;
    lampiran_judul_bold: string;
    lampiran_label_no: string;
    lampiran_label_nama: string;
    lampiran_label_nim: string;
    lampiran_label_prodi: string;
    lampiran_mode: string;
    lampiran_mahasiswa: Array<{ nama: string; nim: string; prodi: string }>;
    lampiran_columns: Array<{
        key: string;
        label: string;
        align: 'left' | 'center' | 'right';
        bold: boolean;
    }>;
    lampiran_rows: Array<Record<string, string>>;
    form_data: Record<string, any>;
    return_to?: string;
};
