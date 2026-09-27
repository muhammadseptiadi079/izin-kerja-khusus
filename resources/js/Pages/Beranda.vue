<script setup lang="ts">
import BagianJenis from '@/Components/BagianJenis.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import type { JenisIzin, Tahap } from '@/types/izin';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{ jenis: JenisIzin[]; dokumen: Record<string, string>; tahap: Tahap[] }>();

const user = usePage<PageProps>().props.auth.user;
</script>

<template>
    <AppLayout judul="Home">
        <section class="mb-5 flex flex-col-reverse items-start gap-6 rounded-xl bg-linear-to-br from-arang to-[#2a1a0f] p-6 text-white sm:p-8 md:flex-row md:items-center">
            <div class="flex-1">
                <p class="mb-2 text-xs font-semibold tracking-[.12em] text-orange-300 uppercase">Sistem Manajemen Keselamatan Pertambangan</p>
                <h1 class="text-3xl leading-tight font-bold sm:text-4xl">Pengajuan & Registrasi Izin Kerja Khusus</h1>
                <p class="mt-3 max-w-xl text-slate-300">
                    Ajukan izin kerja berisiko tinggi secara online, lampirkan dokumen pendukung, dan pantau persetujuannya sampai pekerjaan selesai.
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <Link :href="route('izin.create')" class="tombol">Registrasi izin</Link>
                    <Link :href="route('monitoring')" class="tombol-sekunder border-slate-600 bg-transparent text-white hover:bg-white/10">Monitoring & Evaluasi</Link>
                    <Link v-if="!user" :href="route('register')" class="tombol-sekunder border-slate-600 bg-transparent text-white hover:bg-white/10">Buat akun pekerja</Link>
                </div>
            </div>
            <img src="/img/logo-512.png" alt="Logo" class="h-24 w-24 rounded-2xl md:h-44 md:w-44" />
        </section>

        <div class="panel mb-6">
            <h2 class="mb-2 text-lg font-bold">Apa itu Izin Kerja Khusus?</h2>
            <p class="leading-relaxed text-slate-700">
                Dalam <strong>SMKP (Sistem Manajemen Keselamatan Pertambangan)</strong>, <strong>Izin Kerja Khusus (IKK)</strong> adalah
                <strong>izin yang diberikan kepada pekerja atau pihak tertentu untuk melaksanakan pekerjaan yang memiliki potensi bahaya tinggi</strong>.
                Pekerjaan tersebut hanya boleh dilakukan setelah melalui proses identifikasi bahaya, pengendalian risiko, serta verifikasi kondisi aman di lapangan.
            </p>
        </div>

        <h2 id="jenis" class="mb-3 scroll-mt-20 text-lg font-bold">Jenis izin kerja khusus</h2>
        <nav class="mb-5 flex flex-wrap gap-2">
            <a
                v-for="(j, i) in jenis"
                :key="j.kunci"
                :href="`#${j.kunci}`"
                class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm hover:border-teal-judul hover:text-teal-judul"
            >
                {{ i + 1 }}. {{ j.label }} / <em>{{ j.label_en }}</em>
            </a>
        </nav>

        <div class="space-y-5">
            <BagianJenis v-for="(j, i) in jenis" :key="j.kunci" :jenis="j" :terbalik="i % 2 === 1" />
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="panel">
                <h2 class="mb-2 text-lg font-bold">Dokumen yang wajib dilampirkan</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li v-for="(label, k) in dokumen" :key="k">{{ label }}</li>
                </ul>
                <p class="mt-3 text-sm text-slate-500">Format PDF atau dokumen Word, maksimal 10 MB per berkas.</p>
            </div>
            <div class="panel">
                <h2 class="mb-2 text-lg font-bold">Alur persetujuan</h2>
                <ol class="list-decimal space-y-1 pl-5">
                    <li>Pekerja mengisi registrasi, identifikasi bahaya, pengendalian, dan dokumen.</li>
                    <li v-for="t in tahap" :key="t.status">Diperiksa dan disetujui {{ t.label }}.</li>
                    <li>Izin aktif: pekerjaan boleh dimulai. Izin dicetak dan dipasang di lokasi.</li>
                    <li>Pekerjaan selesai, Pengawas memastikan area aman lalu izin ditutup.</li>
                </ol>
            </div>
        </div>
    </AppLayout>
</template>
