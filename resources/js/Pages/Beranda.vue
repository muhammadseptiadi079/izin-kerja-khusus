<script setup lang="ts">
import BagianJenis from '@/Components/BagianJenis.vue';
import Ikon from '@/Components/Ikon.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import type { JenisIzin, Tahap } from '@/types/izin';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ jenis: JenisIzin[]; dokumen: Record<string, string>; tahap: Tahap[] }>();

const user = usePage<PageProps>().props.auth.user;
const cuplikan = computed(() => ['ketinggian', 'kerja_panas', 'pengangkatan'].map((k) => props.jenis.find((j) => j.kunci === k)).filter(Boolean) as JenisIzin[]);

const alur = computed(() => [
    { ikon: 'formulir', judul: 'Registrasi', isi: 'Isi data pekerjaan, identifikasi bahaya, pengendalian, APD, dan unggah dokumen.' },
    { ikon: 'aktif', judul: props.tahap.map((t) => t.label.split(' /')[0]).join(' → '), isi: 'Diperiksa dan disetujui berjenjang. Pemohon tidak bisa menyetujui izinnya sendiri.' },
    { ikon: 'cetak', judul: 'Izin aktif', isi: 'Pekerjaan boleh dimulai. Izin dicetak PDF dan dipasang di lokasi kerja.' },
    { ikon: 'selesai', judul: 'Penutupan', isi: 'Pekerjaan selesai, Pengawas memastikan area aman lalu izin ditutup.' },
]);
</script>

<template>
    <AppLayout judul="Home">
        <!-- Banner -->
        <section class="relative mb-6 overflow-hidden rounded-3xl bg-arang text-white shadow-angkat">
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[.07]" aria-hidden="true">
                <defs>
                    <pattern id="kisi" width="28" height="28" patternUnits="userSpaceOnUse"><path d="M28 0H0v28" fill="none" stroke="#fff" stroke-width="1" /></pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#kisi)" />
            </svg>
            <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-merek-600/40 blur-3xl" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-32 left-10 h-72 w-72 rounded-full bg-merek-800/30 blur-3xl" aria-hidden="true" />

            <div class="relative grid items-center gap-8 p-6 sm:p-10 lg:grid-cols-[1.1fr_1fr]">
                <div>
                    <div class="mb-4 flex items-center gap-3">
                        <img src="/img/logo-512.png" alt="" class="h-12 w-12 rounded-xl ring-1 ring-white/10" />
                        <span class="rounded-full border border-merek-500/40 bg-merek-500/10 px-3 py-1 text-xs font-semibold tracking-wide text-merek-200">
                            Sistem Manajemen Keselamatan Pertambangan
                        </span>
                    </div>
                    <h1 class="text-3xl leading-[1.1] font-extrabold tracking-tight sm:text-5xl">
                        Izin Kerja Khusus,<br /><span class="bg-linear-to-r from-merek-400 to-amber-300 bg-clip-text text-transparent">cepat, jelas, tercatat.</span>
                    </h1>
                    <p class="mt-4 max-w-xl text-base text-slate-300 sm:text-lg">
                        Ajukan izin kerja berisiko tinggi secara online, lampirkan dokumen pendukung, dan pantau persetujuannya sampai pekerjaan selesai.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2.5">
                        <Link :href="route('izin.create')" class="tombol px-5 py-3 text-base"><Ikon nama="tambah" kelas="h-5 w-5" />Registrasi izin</Link>
                        <Link v-if="!user" :href="route('register')" class="tombol-sekunder border-white/20 bg-white/5 px-5 py-3 text-base text-white shadow-none hover:bg-white/10">
                            Buat akun pekerja
                        </Link>
                        <Link v-else :href="route('monitoring')" class="tombol-sekunder border-white/20 bg-white/5 px-5 py-3 text-base text-white shadow-none hover:bg-white/10">
                            Monitoring
                        </Link>
                    </div>
                    <dl class="mt-8 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
                        <div><dt class="text-xs text-slate-400">Jenis izin</dt><dd class="text-2xl font-bold">{{ jenis.length }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Tahap persetujuan</dt><dd class="text-2xl font-bold">{{ tahap.length }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Dokumen wajib</dt><dd class="text-2xl font-bold">{{ Object.keys(dokumen).length }}</dd></div>
                    </dl>
                </div>

                <!-- Cuplikan animasi jenis izin -->
                <div class="relative hidden h-80 lg:block" aria-hidden="true">
                    <img
                        v-for="(j, i) in cuplikan"
                        :key="j.kunci"
                        :src="j.gambar ?? ''"
                        alt=""
                        :class="[
                            'absolute aspect-video w-72 rounded-2xl object-cover shadow-2xl ring-1 ring-white/15 transition duration-500 hover:z-10 hover:scale-105 hover:rotate-0',
                            ['top-0 right-4 rotate-3', 'top-24 left-0 -rotate-4', 'right-0 bottom-0 rotate-2'][i],
                        ]"
                    />
                </div>
            </div>
        </section>

        <section class="panel mb-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr] lg:items-center">
                <div>
                    <p class="text-sm font-semibold text-merek-700">Tentang IKK</p>
                    <h2 class="judul-halaman mt-1">Apa itu Izin Kerja Khusus?</h2>
                </div>
                <p class="leading-relaxed text-slate-700">
                    Dalam <strong>SMKP</strong>, <strong>Izin Kerja Khusus (IKK)</strong> adalah
                    <strong>izin yang diberikan kepada pekerja atau pihak tertentu untuk melaksanakan pekerjaan yang memiliki potensi bahaya tinggi</strong>.
                    Pekerjaan tersebut hanya boleh dilakukan setelah melalui identifikasi bahaya, pengendalian risiko, serta verifikasi kondisi aman di lapangan.
                </p>
            </div>
        </section>

        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-merek-700">Pilih sesuai pekerjaan</p>
                <h2 id="jenis" class="judul-halaman mt-1 scroll-mt-24">Jenis izin kerja khusus</h2>
            </div>
        </div>
        <nav class="-mx-4 mb-6 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
            <a
                v-for="(j, i) in jenis"
                :key="j.kunci"
                :href="`#${j.kunci}`"
                class="flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pr-3.5 pl-1 text-sm shadow-kartu transition hover:border-merek-400 hover:text-merek-700"
            >
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-arang text-xs font-bold text-white">{{ i + 1 }}</span>
                {{ j.label }}
            </a>
        </nav>

        <div class="space-y-6">
            <BagianJenis v-for="(j, i) in jenis" :key="j.kunci" :jenis="j" :nomor="i + 1" :terbalik="i % 2 === 1" />
        </div>

        <section class="mt-10">
            <p class="text-sm font-semibold text-merek-700">Alur</p>
            <h2 class="judul-halaman mt-1 mb-5">Dari registrasi sampai penutupan</h2>
            <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="(a, i) in alur" :key="i" class="panel relative">
                    <span class="absolute top-5 right-5 text-4xl font-extrabold text-slate-100">{{ i + 1 }}</span>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-merek-100 text-merek-700"><Ikon :nama="a.ikon" kelas="h-6 w-6" /></span>
                    <h3 class="mt-4 font-bold">{{ a.judul }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ a.isi }}</p>
                </li>
            </ol>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-merek-200 bg-linear-to-br from-merek-50 to-white p-6 shadow-kartu sm:p-8">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="judul-bagian">Siapkan dokumen ini sebelum registrasi</h2>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-3">
                        <li v-for="(label, k) in dokumen" :key="k" class="flex items-center gap-2.5 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold shadow-kartu">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-merek-100 text-merek-700"><Ikon nama="berkas" kelas="h-4 w-4" /></span>
                            {{ label }}
                        </li>
                    </ul>
                    <p class="mt-3 text-sm text-slate-500">Format PDF atau dokumen Word, maksimal 10 MB per berkas.</p>
                </div>
                <Link :href="route('izin.create')" class="tombol px-6 py-3 text-base">Mulai registrasi <Ikon nama="panah" kelas="h-5 w-5" /></Link>
            </div>
        </section>
    </AppLayout>
</template>
