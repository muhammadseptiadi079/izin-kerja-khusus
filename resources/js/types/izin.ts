export interface JenisIzin {
    kunci: string;
    label: string;
    label_en: string;
    kode: string;
    deskripsi: string;
    penjelasan: string;
    durasi_maks_jam: number;
    uji_gas: boolean;
    bahaya: string[];
    pengendalian: string[];
    gambar: string | null;
}

export interface BatasGas {
    label: string;
    satuan: string;
    min: number;
    maks: number;
}

export interface Tahap {
    status: string;
    label: string;
    peran: string;
}

export interface Katalog {
    lokasi: string[];
    departemen: string[];
    dokumen: Record<string, string>;
    dokumen_maks_kb: number;
    uji_gas: Record<string, BatasGas>;
    apd: string[];
    tahap: Tahap[];
    status: Record<string, string>;
    peran: Record<string, string>;
}

export interface IzinRingkas {
    id: number;
    nomor: string | null;
    jenis: string;
    label_jenis: string;
    status: string;
    label_status: string;
    lewat_waktu: boolean;
    lokasi: string;
    uraian_pekerjaan: string;
    pemohon: string;
    mulai_at: string;
    selesai_at: string;
    diajukan_at: string | null;
    updated_at: string;
}

export interface Dokumen {
    jenis: string;
    label: string;
    nama_asli: string;
    ukuran: string;
    url: string;
}

export interface Riwayat {
    id: number;
    aksi: string;
    label_aksi: string;
    status_ke: string;
    catatan: string | null;
    oleh: string;
    peran: string;
    waktu: string;
}

export interface IzinLengkap extends IzinRingkas {
    nik: string;
    nomor_wa: string;
    departemen: string;
    lokasi_pilihan: string;
    lokasi_detail: string | null;
    peralatan: string | null;
    pekerja: string;
    bahaya: string[];
    bahaya_lain: string | null;
    pengendalian: string[];
    pengendalian_tambahan: string | null;
    apd: string[];
    uji_gas: Record<string, number | string | null> | null;
    uji_gas_oleh: string | null;
    uji_gas_at: string | null;
    disahkan_at: string | null;
    ditutup_at: string | null;
    catatan_penutupan: string | null;
    pemohon_jabatan: string | null;
    dokumen: Dokumen[];
    riwayat: Riwayat[];
}

export interface Halaman<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
