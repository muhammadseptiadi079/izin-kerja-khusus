<script setup lang="ts">
import GrafikBatang from '@/Components/GrafikBatang.vue';
import Ikon from '@/Components/Ikon.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Statistik from '@/Components/Statistik.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { tanggalJam } from '@/lib/format';
import type { IzinRingkas } from '@/types/izin';
import { Link, usePoll } from '@inertiajs/vue3';
import { computed } from 'vue';

type Berjalan = IzinRingkas & { jumlah_pekerja: number; uji_gas: boolean; sisa_menit: number; progres: number; keadaan: 'normal' | 'hampir_habis' | 'lewat_waktu' };
type Antri = IzinRingkas & { menunggu_menit: number; mendesak: boolean };

const props = defineProps<{
    diperbarui: string;
    ringkasan: {
        berjalan: number;
        pekerja: number;
        hampir_habis: number;
        lewat_waktu: number;
        menunggu_persetujuan: number;
        menunggu_penutupan: number;
        terjadwal_24_jam: number;
    };
    berjalan: Berjalan[];
    antrian: { status: string; label: string; izin: Antri[] }[];
    penutupan: (IzinRingkas & { ada_insiden: boolean | null; label_insiden: string | null; menunggu_menit: number })[];
    jadwal: (IzinRingkas & { siap: boolean })[];
    perLokasi: { label: string; jumlah: number; pekerja: number }[];
    perJenis: { label: string; jumlah: number; pekerja: number }[];
}>();

// Keadaan lapangan berubah terus: muat ulang data setiap menit tanpa memuat ulang halaman.
usePoll(60_000);

const jamDiperbarui = computed(() =>
    new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' }).format(new Date(props.diperbarui)),
);
const durasi = (menit: number) => {
    const m = Math.abs(menit);
    return m >= 60 ? `${Math.floor(m / 60)} j ${m % 60} m` : `${m} m`;
};
const jamSaja = (iso: string) => new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' }).format(new Date(iso));
const hariSaja = (iso: string) => new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(iso));

const warnaKeadaan = {
    normal: { batang: 'bg-green-500', teks: 'text-green-700', bingkai: 'border-slate-200/80' },
    hampir_habis: { batang: 'bg-amber-500', teks: 'text-amber-700', bingkai: 'border-amber-300' },
    lewat_waktu: { batang: 'bg-red-600', teks: 'text-red-700', bingkai: 'border-red-300 ring-1 ring-red-500/20' },
};
const lokasiGrafik = computed(() => props.perLokasi.map((l) => ({ label: `${l.label} · ${l.pekerja} org`, jumlah: l.jumlah })));
const jenisGrafik = computed(() => props.perJenis.map((l) => ({ label: l.label, jumlah: l.jumlah })));
</script>

<template>
    <AppLayout judul="Monitoring">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Monitoring</h1>
                <p class="mt-1 flex items-center gap-2 text-slate-600">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75" />
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500" />
                    </span>
                    Keadaan saat ini · diperbarui {{ jamDiperbarui }}, otomatis tiap menit
                </p>
            </div>
            <Link :href="route('evaluasi')" class="tombol-sekunder"><Ikon nama="grafik" kelas="h-4 w-4" />Evaluasi hasil</Link>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
            <Statistik :angka="ringkasan.berjalan" label="Pekerjaan berjalan" ikon="aktif" nada="hijau" />
            <Statistik :angka="ringkasan.pekerja" label="Orang di lapangan" ikon="pengguna" nada="biru" />
            <Statistik :angka="ringkasan.hampir_habis" label="Berakhir ≤ 1 jam" ikon="jam" nada="amber" />
            <Statistik :angka="ringkasan.lewat_waktu" label="Lewat waktu" ikon="peringatan" :bahaya="ringkasan.lewat_waktu > 0" nada="hijau" />
            <Statistik :angka="ringkasan.menunggu_persetujuan" label="Menunggu persetujuan" ikon="lonceng" nada="amber" />
            <Statistik :angka="ringkasan.menunggu_penutupan" label="Menunggu penutupan" ikon="selesai" nada="abu" />
        </div>

        <!-- Pekerjaan yang sedang berjalan -->
        <section class="mb-6">
            <h2 class="judul-bagian mb-3">Sedang berjalan di lapangan</h2>
            <div v-if="!berjalan.length" class="panel flex items-center gap-3 text-slate-500">
                <Ikon nama="aktif" kelas="h-5 w-5 text-slate-400" />Tidak ada pekerjaan berisiko tinggi yang sedang berjalan.
            </div>
            <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="i in berjalan"
                    :key="i.id"
                    :href="route('izin.show', i.id)"
                    :class="['block rounded-2xl border bg-white p-4 shadow-kartu transition hover:shadow-angkat', warnaKeadaan[i.keadaan].bingkai]"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-semibold text-merek-700">{{ i.nomor }}</div>
                            <div class="truncate text-sm font-semibold">{{ i.label_jenis }}</div>
                        </div>
                        <span v-if="i.uji_gas" class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Uji gas</span>
                    </div>
                    <div class="mt-1 truncate text-sm text-slate-600">{{ i.lokasi }}</div>
                    <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1"><Ikon nama="pengguna" kelas="h-3.5 w-3.5" />{{ i.jumlah_pekerja }} orang</span>
                        <span class="truncate">{{ i.pemohon }}</span>
                    </div>
                    <div class="mt-3">
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div :class="['h-full rounded-full', warnaKeadaan[i.keadaan].batang]" :style="{ width: `${i.progres}%` }" />
                        </div>
                        <div class="mt-1.5 flex justify-between text-xs">
                            <span class="text-slate-500">{{ jamSaja(i.mulai_at) }} – {{ jamSaja(i.selesai_at) }}</span>
                            <span :class="['font-bold', warnaKeadaan[i.keadaan].teks]">
                                {{ i.keadaan === 'lewat_waktu' ? `Lewat ${durasi(i.sisa_menit)}` : `Sisa ${durasi(i.sisa_menit)}` }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Antrian persetujuan per tahap -->
        <section class="mb-6">
            <h2 class="judul-bagian mb-3">Antrian persetujuan</h2>
            <div class="grid gap-3 md:grid-cols-3">
                <div v-for="t in antrian" :key="t.status" class="panel p-4 sm:p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="font-bold">{{ t.label }}</h3>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ t.izin.length }}</span>
                    </div>
                    <p v-if="!t.izin.length" class="text-sm text-slate-400">Kosong</p>
                    <ul class="space-y-2">
                        <li v-for="i in t.izin" :key="i.id">
                            <Link :href="route('izin.show', i.id)" class="block rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-merek-700">{{ i.nomor }}</span>
                                    <span v-if="i.mendesak" class="rounded-full bg-red-600 px-2 py-0.5 text-[11px] font-semibold text-white">Mendesak</span>
                                </div>
                                <div class="truncate text-xs text-slate-600">{{ i.label_jenis }} · {{ i.lokasi }}</div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    Menunggu {{ durasi(i.menunggu_menit) }} · mulai {{ hariSaja(i.mulai_at) }} {{ jamSaja(i.mulai_at) }}
                                </div>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <!-- Jadwal 24 jam -->
            <section class="panel">
                <h2 class="judul-bagian mb-1">Jadwal 24 jam ke depan</h2>
                <p class="mb-3 text-sm text-slate-500">Izin yang akan mulai. Yang belum disahkan perlu diputuskan sebelum jam mulai.</p>
                <p v-if="!jadwal.length" class="text-sm text-slate-400">Tidak ada jadwal.</p>
                <ol class="relative ml-1 border-l-2 border-slate-200">
                    <li v-for="i in jadwal" :key="i.id" class="relative pb-3 pl-4 last:pb-0">
                        <span :class="['absolute top-1.5 -left-[7px] h-3 w-3 rounded-full ring-2 ring-white', i.siap ? 'bg-green-500' : 'bg-amber-500']" />
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="font-bold tabular-nums">{{ hariSaja(i.mulai_at) }} {{ jamSaja(i.mulai_at) }}</span>
                                <Link :href="route('izin.show', i.id)" class="ml-2 font-semibold text-merek-700 hover:underline">{{ i.nomor }}</Link>
                                <div class="truncate text-sm text-slate-600">{{ i.label_jenis }} · {{ i.lokasi }}</div>
                            </div>
                            <StatusBadge :status="i.status" :label="i.siap ? 'Siap' : i.label_status" />
                        </div>
                    </li>
                </ol>
            </section>

            <!-- Menunggu penutupan -->
            <section class="panel">
                <h2 class="judul-bagian mb-1">Menunggu penutupan</h2>
                <p class="mb-3 text-sm text-slate-500">Pekerjaan sudah dilaporkan selesai, menunggu Pengawas memastikan area aman.</p>
                <p v-if="!penutupan.length" class="text-sm text-slate-400">Tidak ada.</p>
                <ul class="divide-y divide-slate-100">
                    <li v-for="i in penutupan" :key="i.id" class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                        <div class="min-w-0">
                            <Link :href="route('izin.show', i.id)" class="font-semibold text-merek-700 hover:underline">{{ i.nomor }}</Link>
                            <span class="text-sm text-slate-600"> · {{ i.lokasi }}</span>
                            <div class="text-xs text-slate-500">Menunggu {{ durasi(i.menunggu_menit) }}</div>
                        </div>
                        <span
                            v-if="i.label_insiden"
                            :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', i.ada_insiden ? 'bg-red-600 text-white' : 'bg-green-50 text-green-800 ring-1 ring-green-600/20 ring-inset']"
                            >{{ i.label_insiden }}</span
                        >
                    </li>
                </ul>
            </section>
        </div>

        <div v-if="berjalan.length" class="mt-5 grid gap-5 md:grid-cols-2">
            <div class="panel"><h2 class="judul-bagian mb-2">Pekerjaan berjalan per lokasi</h2><GrafikBatang :data="lokasiGrafik" /></div>
            <div class="panel"><h2 class="judul-bagian mb-2">Pekerjaan berjalan per jenis</h2><GrafikBatang :data="jenisGrafik" /></div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">Terakhir diperbarui {{ tanggalJam(diperbarui) }}</p>
    </AppLayout>
</template>
