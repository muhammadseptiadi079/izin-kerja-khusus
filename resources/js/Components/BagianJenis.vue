<script setup lang="ts">
// Satu bagian jenis izin di Home, seperti halaman per jenis di situs IKK sebelumnya.
import JudulJenis from '@/Components/JudulJenis.vue';
import Ikon from '@/Components/Ikon.vue';
import type { JenisIzin } from '@/types/izin';
import { Link } from '@inertiajs/vue3';

defineProps<{ jenis: JenisIzin; terbalik?: boolean }>();
</script>

<template>
    <section :id="jenis.kunci" class="panel grid scroll-mt-20 items-start gap-6 md:grid-cols-[5fr_6fr]">
        <img
            v-if="jenis.gambar"
            :src="jenis.gambar"
            :alt="jenis.label"
            loading="lazy"
            :class="['aspect-video w-full rounded-lg bg-slate-100 object-cover', terbalik ? 'md:order-2' : '']"
        />
        <div>
            <JudulJenis :jenis="jenis" ukuran="sedang" />
            <p class="mt-1 mb-4 font-semibold text-slate-600">{{ jenis.deskripsi }}</p>
            <Link :href="route('izin.create', { jenis: jenis.kunci })" class="tombol-teal w-full sm:w-auto">Klik di sini untuk Registrasi</Link>
            <p class="mt-4 text-justify text-sm leading-relaxed text-slate-700">{{ jenis.penjelasan }}</p>
            <Link :href="route('jenis.show', jenis.kunci)" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-merek-700 hover:underline">
                Lihat bahaya, pengendalian, dan persyaratan <Ikon nama="panah" kelas="h-4 w-4" />
            </Link>
        </div>
    </section>
</template>
