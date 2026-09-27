<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const cuplikan = ['ketinggian', 'ruang_terbatas', 'pengangkatan', 'kerja_panas'];
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-[1fr_1.05fr]">
        <div class="flex flex-col items-center justify-center px-4 py-10">
            <Link href="/" class="flex flex-col items-center gap-3">
                <img src="/img/logo.png" alt="Logo" class="h-20 w-20 rounded-2xl shadow-angkat" />
                <span class="text-xl font-bold tracking-tight">Izin Kerja Khusus</span>
            </Link>
            <div class="mt-6 w-full max-w-md animate-muncul rounded-2xl border border-slate-200/80 bg-white px-6 py-6 shadow-angkat sm:px-8">
                <slot />
            </div>
            <Link href="/" class="mt-6 text-sm text-slate-500 hover:text-slate-800">← Kembali ke beranda</Link>
        </div>

        <!-- Panel ilustrasi, hanya di layar lebar -->
        <aside class="relative hidden overflow-hidden bg-arang p-10 text-white lg:flex lg:flex-col lg:justify-between" aria-hidden="true">
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-merek-600/40 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-24 -left-10 h-80 w-80 rounded-full bg-merek-800/30 blur-3xl" />
            <div class="relative">
                <span class="rounded-full border border-merek-500/40 bg-merek-500/10 px-3 py-1 text-xs font-semibold text-merek-200">SMKP · Izin Kerja Khusus</span>
                <h2 class="mt-5 max-w-md text-4xl leading-tight font-extrabold tracking-tight">
                    Bekerja aman dimulai dari <span class="text-merek-400">izin yang benar.</span>
                </h2>
                <p class="mt-3 max-w-md text-slate-300">Identifikasi bahaya, pastikan pengendalian, dan dapatkan persetujuan sebelum pekerjaan berisiko tinggi dimulai.</p>
            </div>
            <div class="relative grid grid-cols-2 gap-4 pb-6">
                <img
                    v-for="(k, i) in cuplikan"
                    :key="k"
                    :src="`/img/jenis/${k}.svg`"
                    alt=""
                    :class="['aspect-video w-full rounded-xl object-cover shadow-2xl ring-1 ring-white/10', i % 2 ? 'translate-y-6' : '']"
                />
            </div>
        </aside>
    </div>
</template>
