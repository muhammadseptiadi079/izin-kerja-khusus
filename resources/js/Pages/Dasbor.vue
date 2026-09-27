<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
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

const jam = Number(new Intl.DateTimeFormat('id-ID', { hour: 'numeric', hourCycle: 'h23', timeZone: 'Asia/Jakarta' }).format(new Date()));
const salam = jam < 11 ? 'Selamat pagi' : jam < 15 ? 'Selamat siang' : jam < 18 ? 'Selamat sore' : 'Selamat malam';
const hariIni = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date());
</script>

<template>
    <AppLayout judul="Beranda">
        <section class="relative mb-5 overflow-hidden rounded-3xl bg-arang p-5 text-white shadow-angkat sm:p-7">
            <div class="pointer-events-none absolute -top-20 -right-16 h-64 w-64 rounded-full bg-merek-600/35 blur-3xl" aria-hidden="true" />
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-400">{{ hariIni }}</p>
                    <h1 class="mt-0.5 text-2xl font-bold tracking-tight sm:text-3xl">{{ salam }}, {{ user.name.split(' ')[0] }}</h1>
                    <p class="mt-1 text-sm text-slate-300">
                        <span class="rounded-full bg-white/10 px-2 py-0.5 font-semibold text-white">{{ user.label_peran }}</span>
                        <span v-if="perluTindakan.length" class="ml-2">{{ perluTindakan.length }} izin menunggu keputusan Anda.</span>
                        <span v-else class="ml-2">Tidak ada izin yang menunggu keputusan Anda.</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link v-if="perluTindakan.length" :href="route('tindakan')" class="tombol-sekunder border-white/20 bg-white/5 text-white shadow-none hover:bg-white/10">
                        Tinjau sekarang
                    </Link>
                    <Link :href="route('izin.create')" class="tombol hidden lg:inline-flex"><Ikon nama="tambah" kelas="h-4 w-4" />Ajukan izin</Link>
                </div>
            </div>
        </section>

        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            <Statistik :angka="ringkasan.aktif" label="Izin aktif" ikon="aktif" nada="hijau" />
            <Statistik :angka="ringkasan.lewat_waktu" label="Lewat waktu, belum ditutup" ikon="jam" :bahaya="ringkasan.lewat_waktu > 0" />
            <Statistik :angka="ringkasan.menunggu" label="Menunggu persetujuan" ikon="lonceng" nada="amber" />
            <Statistik :angka="ringkasan.selesai_bulan_ini" label="Selesai bulan ini" ikon="selesai" nada="biru" />
        </div>

        <div v-if="perluTindakan.length" class="panel mb-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="judul-bagian">Perlu tindakan Anda ({{ perluTindakan.length }})</h2>
                <Link :href="route('tindakan')" class="text-sm font-semibold text-merek-700 hover:underline">Lihat semua</Link>
            </div>
            <TabelIzin :izin="perluTindakan" :kolom="['nomor', 'jenis', 'lokasi', 'pemohon', 'status']" :tombol="{ label: 'Tinjau', rute: 'izin.show' }" />
        </div>

        <div v-if="izinSaya.length" class="panel mb-5">
            <h2 class="judul-bagian mb-3">Draf & izin ditolak milik Anda</h2>
            <TabelIzin :izin="izinSaya" :kolom="['jenis', 'lokasi', 'status', 'diperbarui']" :tombol="{ label: 'Lanjutkan', rute: 'izin.edit', sekunder: true }" />
        </div>

        <div class="panel mb-5">
            <h2 class="judul-bagian mb-3">Ajukan izin baru</h2>
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
            <h2 class="judul-bagian mb-3">Izin aktif di lapangan</h2>
            <p v-if="!aktif.length" class="text-slate-500">Tidak ada izin yang sedang berlaku.</p>
            <TabelIzin v-else :izin="aktif" :kolom="['nomor', 'jenis', 'lokasi', 'pemohon', 'selesai', 'status']" />
        </div>
    </AppLayout>
</template>
