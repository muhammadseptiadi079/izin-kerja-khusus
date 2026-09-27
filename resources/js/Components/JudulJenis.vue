<script setup lang="ts">
import type { JenisIzin } from '@/types/izin';

defineProps<{ jenis: JenisIzin; ukuran?: 'besar' | 'sedang' }>();
</script>

<template>
    <h2 :class="['leading-tight font-bold tracking-wide text-teal-judul uppercase', ukuran === 'sedang' ? 'text-xl' : 'text-2xl sm:text-3xl']">
        {{ jenis.label }} / <em class="text-biru-judul">{{ jenis.label_en }}</em>
    </h2>
</template>
