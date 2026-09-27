<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import JudulJenis from '@/Components/JudulJenis.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { BatasGas, JenisIzin, Tahap } from '@/types/izin';
import { Link } from '@inertiajs/vue3';

defineProps<{ jenis: JenisIzin; dokumen: Record<string, string>; uji_gas: Record<string, BatasGas>; tahap: Tahap[] }>();
</script>

<template>
    <AppLayout :judul="jenis.label">
        <Link :href="route('beranda') + '#jenis'" class="mb-3 inline-flex items-center gap-1 text-sm text-merek-700 hover:underline">
            <Ikon nama="kembali" kelas="h-4 w-4" /> Semua jenis izin
        </Link>

        <div class="panel mb-5 text-center">
            <JudulJenis :jenis="jenis" />
            <p class="mt-1 mb-4 font-semibold text-slate-600">{{ jenis.deskripsi }}</p>
            <Link :href="route('izin.create', { jenis: jenis.kunci })" class="tombol-teal">Klik di sini untuk Registrasi</Link>
            <img v-if="jenis.gambar" :src="jenis.gambar" :alt="jenis.label" class="mx-auto my-5 aspect-video w-full max-w-3xl rounded-lg object-cover" />
            <p class="mx-auto max-w-3xl text-justify leading-relaxed text-slate-700">{{ jenis.penjelasan }}</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="panel">
                <h2 class="mb-2 font-bold">Bahaya yang harus diidentifikasi</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li v-for="b in jenis.bahaya" :key="b">{{ b }}</li>
                </ul>
            </div>
            <div class="panel">
                <h2 class="mb-1 font-bold">Pengendalian wajib</h2>
                <p class="mb-2 text-sm text-slate-500">Semua butir harus dipastikan di lapangan sebelum izin bisa diajukan.</p>
                <ul class="space-y-1">
                    <li v-for="p in jenis.pengendalian" :key="p" class="flex gap-2">
                        <Ikon nama="centang" kelas="mt-0.5 h-4 w-4 shrink-0 text-green-700" />{{ p }}
                    </li>
                </ul>
            </div>
            <div class="panel">
                <h2 class="mb-2 font-bold">Persyaratan pengajuan</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li v-for="(label, k) in dokumen" :key="k">Unggah {{ label }}</li>
                    <li>Durasi izin maksimal {{ jenis.durasi_maks_jam }} jam. Pekerjaan lebih lama diajukan per shift.</li>
                    <li v-if="jenis.uji_gas">
                        Hasil uji gas dalam batas aman:
                        <span v-for="(b, k, i) in uji_gas" :key="k">{{ b.label }} {{ b.min }}–{{ b.maks }} {{ b.satuan }}{{ i < Object.keys(uji_gas).length - 1 ? ', ' : '' }}</span>
                    </li>
                </ul>
            </div>
            <div class="panel">
                <h2 class="mb-2 font-bold">Alur persetujuan</h2>
                <ol class="list-decimal space-y-1 pl-5">
                    <li v-for="t in tahap" :key="t.status">{{ t.label }}</li>
                    <li>Izin aktif, pekerjaan boleh dimulai</li>
                    <li>Penutupan oleh Pengawas Area</li>
                </ol>
            </div>
        </div>
    </AppLayout>
</template>
