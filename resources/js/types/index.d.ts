export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    peran: 'pemohon' | 'pengawas' | 'hse' | 'manajer' | 'admin';
    label_peran: string;
    nik: string | null;
    nomor_wa: string | null;
    jabatan: string | null;
    departemen: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    pesan: string | null;
    jumlahTindakan: number;
};
