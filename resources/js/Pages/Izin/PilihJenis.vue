<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { JenisIzin } from '@/types/izin';
import { Link } from '@inertiajs/vue3';

defineProps<{ jenis: JenisIzin[] }>();
</script>

<template>
    <AppLayout judul="Registrasi Izin">
        <h1 class="judul-halaman">Registrasi Izin Kerja Khusus</h1>
        <p class="mb-5 text-slate-600">Pilih jenis izin kerja khusus (<em>work permit system</em>) untuk pekerjaan yang akan dilakukan.</p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="j in jenis"
                :key="j.kunci"
                :href="route('izin.create', { jenis: j.kunci })"
                class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-merek-500 hover:shadow-md"
            >
                <img v-if="j.gambar" :src="j.gambar" alt="" loading="lazy" class="aspect-video w-full object-cover" />
                <div class="p-4">
                    <span class="rounded bg-merek-700 px-1.5 text-xs font-bold text-white">{{ j.kode }}</span>
                    <h2 class="mt-1 text-lg font-bold group-hover:text-merek-700">{{ j.label }}</h2>
                    <p class="text-sm text-biru-judul italic">{{ j.label_en }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ j.deskripsi }}</p>
                    <p class="mt-2 text-xs text-slate-500">Maks. {{ j.durasi_maks_jam }} jam<span v-if="j.uji_gas"> · wajib uji gas</span></p>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>
