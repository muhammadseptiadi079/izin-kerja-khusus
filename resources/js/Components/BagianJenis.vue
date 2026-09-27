<script setup lang="ts">
// Satu bagian jenis izin di Home, seperti halaman per jenis di situs IKK sebelumnya.
import Ikon from '@/Components/Ikon.vue';
import JudulJenis from '@/Components/JudulJenis.vue';
import type { JenisIzin } from '@/types/izin';
import { Link } from '@inertiajs/vue3';

defineProps<{ jenis: JenisIzin; terbalik?: boolean; nomor?: number }>();
</script>

<template>
    <section :id="jenis.kunci" class="group panel grid scroll-mt-24 items-center gap-6 p-4 sm:p-6 md:grid-cols-[5fr_6fr] md:gap-8">
        <div :class="['relative overflow-hidden rounded-xl bg-slate-100', terbalik ? 'md:order-2' : '']">
            <img v-if="jenis.gambar" :src="jenis.gambar" :alt="jenis.label" loading="lazy" class="aspect-video w-full object-cover transition duration-500 group-hover:scale-[1.03]" />
            <span v-if="nomor" class="absolute top-3 left-3 rounded-lg bg-arang/80 px-2 py-1 text-xs font-bold text-white backdrop-blur">{{ jenis.kode }} · {{ nomor }}</span>
        </div>
        <div>
            <JudulJenis :jenis="jenis" ukuran="sedang" />
            <p class="mt-1.5 mb-4 font-semibold text-slate-600">{{ jenis.deskripsi }}</p>
            <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-slate-700"><Ikon nama="jam" kelas="h-3.5 w-3.5" />Maks. {{ jenis.durasi_maks_jam }} jam</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-slate-700"><Ikon nama="aktif" kelas="h-3.5 w-3.5" />{{ jenis.pengendalian.length }} pengendalian wajib</span>
                <span v-if="jenis.uji_gas" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-amber-800"><Ikon nama="peringatan" kelas="h-3.5 w-3.5" />Wajib uji gas</span>
            </div>
            <p class="line-clamp-4 text-justify text-sm leading-relaxed text-slate-700 md:line-clamp-none">{{ jenis.penjelasan }}</p>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <Link :href="route('izin.create', { jenis: jenis.kunci })" class="tombol-teal w-full sm:w-auto">Klik di sini untuk Registrasi</Link>
                <Link :href="route('jenis.show', jenis.kunci)" class="inline-flex items-center gap-1 text-sm font-semibold text-merek-700 hover:underline">
                    Persyaratan lengkap <Ikon nama="panah" kelas="h-4 w-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
