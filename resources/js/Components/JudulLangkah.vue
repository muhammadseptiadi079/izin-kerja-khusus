<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';

defineProps<{ nomor: number | string; judul: string; keterangan?: string; selesai?: boolean }>();
</script>

<template>
    <div class="mb-4 flex items-start gap-3">
        <span
            :class="[
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold transition',
                selesai ? 'bg-green-600 text-white' : 'bg-arang text-white',
            ]"
        >
            <Ikon v-if="selesai" nama="centang" kelas="h-4 w-4" />
            <template v-else>{{ nomor }}</template>
        </span>
        <div>
            <h2 class="judul-bagian leading-8">{{ judul }}</h2>
            <p v-if="keterangan" class="-mt-0.5 text-sm text-slate-500">{{ keterangan }}</p>
        </div>
    </div>
</template>
