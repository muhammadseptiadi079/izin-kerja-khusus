<script setup lang="ts">
import Statistik from '@/Components/Statistik.vue';
import TabelIzin from '@/Components/TabelIzin.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import type { IzinRingkas } from '@/types/izin';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    perluTindakan: IzinRingkas[];
    aktif: IzinRingkas[];
    izinSaya: IzinRingkas[];
    ringkasan: { aktif: number; lewat_waktu: number; menunggu: number; selesai_bulan_ini: number };
    jenis: { kunci: string; label: string; label_en: string; gambar: string | null }[];
}>();

const user = usePage<PageProps>().props.auth.user!;
</script>

<template>
    <AppLayout judul="Beranda">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Beranda</h1>
                <p class="text-slate-600">Selamat datang, {{ user.name }}.</p>
            </div>
            <Link :href="route('izin.create')" class="tombol hidden lg:inline-flex">+ Ajukan izin</Link>
        </div>

        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            <Statistik :angka="ringkasan.aktif" label="Izin aktif" />
            <Statistik :angka="ringkasan.lewat_waktu" label="Lewat waktu, belum ditutup" :bahaya="ringkasan.lewat_waktu > 0" />
            <Statistik :angka="ringkasan.menunggu" label="Menunggu persetujuan" />
            <Statistik :angka="ringkasan.selesai_bulan_ini" label="Selesai bulan ini" />
        </div>

        <div v-if="perluTindakan.length" class="panel mb-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-bold">Perlu tindakan Anda ({{ perluTindakan.length }})</h2>
                <Link :href="route('tindakan')" class="text-sm font-semibold text-merek-700 hover:underline">Lihat semua</Link>
            </div>
            <TabelIzin :izin="perluTindakan" :kolom="['nomor', 'jenis', 'lokasi', 'pemohon', 'status']" :tombol="{ label: 'Tinjau', rute: 'izin.show' }" />
        </div>

        <div v-if="izinSaya.length" class="panel mb-5">
            <h2 class="mb-3 text-lg font-bold">Draf & izin ditolak milik Anda</h2>
            <TabelIzin :izin="izinSaya" :kolom="['jenis', 'lokasi', 'status', 'diperbarui']" :tombol="{ label: 'Lanjutkan', rute: 'izin.edit', sekunder: true }" />
        </div>

        <div class="panel mb-5">
            <h2 class="mb-3 text-lg font-bold">Ajukan izin baru</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="j in jenis"
                    :key="j.kunci"
                    :href="route('izin.create', { jenis: j.kunci })"
                    class="group overflow-hidden rounded-lg border border-slate-200 transition hover:border-merek-500 hover:shadow"
                >
                    <img v-if="j.gambar" :src="j.gambar" alt="" loading="lazy" class="aspect-video w-full object-cover" />
                    <div class="p-2 text-xs leading-tight">
                        <div class="font-semibold group-hover:text-merek-700">{{ j.label }}</div>
                        <div class="text-slate-500 italic">{{ j.label_en }}</div>
                    </div>
                </Link>
            </div>
        </div>

        <div class="panel">
            <h2 class="mb-3 text-lg font-bold">Izin aktif di lapangan</h2>
            <p v-if="!aktif.length" class="text-slate-500">Tidak ada izin yang sedang berlaku.</p>
            <TabelIzin v-else :izin="aktif" :kolom="['nomor', 'jenis', 'lokasi', 'pemohon', 'selesai', 'status']" />
        </div>
    </AppLayout>
</template>
