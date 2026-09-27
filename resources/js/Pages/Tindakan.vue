<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import TabelIzin from '@/Components/TabelIzin.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { IzinRingkas } from '@/types/izin';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    menungguKeputusan: IzinRingkas[];
    perluDiperbaiki: IzinRingkas[];
    lewatWaktu: IzinRingkas[];
    draf: IzinRingkas[];
}>();

const kosong = computed(() => !props.menungguKeputusan.length && !props.perluDiperbaiki.length && !props.lewatWaktu.length && !props.draf.length);
</script>

<template>
    <AppLayout judul="Tindakan">
        <h1 class="judul-halaman">Perlu tindakan</h1>
        <p class="mb-5 text-slate-600">Semua izin yang sedang menunggu Anda, diurutkan dari yang paling mendesak.</p>

        <div v-if="kosong" class="panel flex flex-col items-center gap-2 py-10 text-center">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-700"><Ikon nama="centang" kelas="h-6 w-6" /></span>
            <p class="font-semibold">Tidak ada yang menunggu Anda.</p>
            <Link :href="route('izin.create')" class="tombol mt-2">Ajukan izin baru</Link>
        </div>

        <div class="space-y-5">
            <section v-if="lewatWaktu.length" class="panel border-red-200">
                <h2 class="mb-1 flex items-center gap-2 text-lg font-bold text-red-800"><Ikon nama="peringatan" />Lewat waktu, segera tutup ({{ lewatWaktu.length }})</h2>
                <p class="mb-3 text-sm text-slate-600">Jam selesai sudah lewat. Hentikan pekerjaan lalu ajukan penutupan.</p>
                <TabelIzin :izin="lewatWaktu" :kolom="['nomor', 'jenis', 'lokasi', 'selesai']" :tombol="{ label: 'Tutup izin', rute: 'izin.show' }" />
            </section>

            <section v-if="menungguKeputusan.length" class="panel">
                <h2 class="mb-1 text-lg font-bold">Menunggu keputusan Anda ({{ menungguKeputusan.length }})</h2>
                <p class="mb-3 text-sm text-slate-600">Izin dari pekerja lain yang perlu Anda setujui, tolak, atau tutup.</p>
                <TabelIzin :izin="menungguKeputusan" :kolom="['nomor', 'jenis', 'lokasi', 'pemohon', 'status']" :tombol="{ label: 'Tinjau', rute: 'izin.show' }" />
            </section>

            <section v-if="perluDiperbaiki.length" class="panel">
                <h2 class="mb-1 text-lg font-bold">Ditolak, perlu diperbaiki ({{ perluDiperbaiki.length }})</h2>
                <p class="mb-3 text-sm text-slate-600">Baca catatan penolakan, perbaiki, lalu ajukan ulang. Nomor izin tetap sama.</p>
                <TabelIzin :izin="perluDiperbaiki" :kolom="['nomor', 'jenis', 'lokasi', 'diperbarui']" :tombol="{ label: 'Perbaiki', rute: 'izin.edit' }" />
            </section>

            <section v-if="draf.length" class="panel">
                <h2 class="mb-1 text-lg font-bold">Draf belum diajukan ({{ draf.length }})</h2>
                <p class="mb-3 text-sm text-slate-600">Lengkapi lalu ajukan. Draf tidak dihitung di angka merah.</p>
                <TabelIzin :izin="draf" :kolom="['jenis', 'lokasi', 'diperbarui']" :tombol="{ label: 'Lanjutkan', rute: 'izin.edit', sekunder: true }" />
            </section>
        </div>
    </AppLayout>
</template>
