<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const user = usePage<PageProps>().props.auth.user!;

const menu = [
    { label: 'Profil & data diri', keterangan: 'Nama, NIK, nomor WA, kata sandi', rute: 'profile.edit', ikon: 'profil' },
    { label: 'Monitoring & Evaluasi', keterangan: 'Rekap, grafik, unduh Excel', rute: 'monitoring', ikon: 'grafik' },
    { label: 'Jenis izin kerja khusus', keterangan: 'Penjelasan dan persyaratan', rute: 'beranda', ikon: 'buku' },
    ...(user.peran === 'admin' ? [{ label: 'Kelola pengguna', keterangan: 'Tambah akun, atur peran', rute: 'pengguna.index', ikon: 'pengguna' }] : []),
];
</script>

<template>
    <AppLayout judul="Akun">
        <div class="panel mb-4 flex items-center gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-merek-600 text-xl font-bold text-white">{{ user.name.charAt(0) }}</span>
            <div class="min-w-0">
                <div class="truncate text-lg font-bold">{{ user.name }}</div>
                <div class="text-sm text-slate-600">{{ user.label_peran }}<span v-if="user.departemen"> · {{ user.departemen }}</span></div>
                <div class="truncate text-xs text-slate-500">{{ user.email }}</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <Link
                v-for="m in menu"
                :key="m.rute"
                :href="route(m.rute)"
                class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 last:border-0 hover:bg-slate-50"
            >
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700"><Ikon :nama="m.ikon" /></span>
                <span class="flex-1">
                    <span class="block font-semibold">{{ m.label }}</span>
                    <span class="text-xs text-slate-500">{{ m.keterangan }}</span>
                </span>
                <Ikon nama="kanan" kelas="h-5 w-5 text-slate-400" />
            </Link>
        </div>

        <Link :href="route('logout')" method="post" as="button" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 font-semibold text-red-700">
            <Ikon nama="keluar" />Keluar
        </Link>
    </AppLayout>
</template>
