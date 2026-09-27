<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{ angka: number | string | null; label: string; ikon?: string; nada?: 'oranye' | 'hijau' | 'merah' | 'biru' | 'amber' | 'abu'; bahaya?: boolean }>(),
    { ikon: 'grafik', nada: 'abu' },
);

const warna = computed(() => {
    const n = props.bahaya ? 'merah' : props.nada;
    return {
        oranye: 'bg-merek-100 text-merek-700',
        hijau: 'bg-green-100 text-green-700',
        merah: 'bg-red-100 text-red-700',
        biru: 'bg-blue-100 text-blue-700',
        amber: 'bg-amber-100 text-amber-700',
        abu: 'bg-slate-100 text-slate-600',
    }[n];
});
</script>

<template>
    <div :class="['rounded-2xl border bg-white p-4 shadow-kartu', bahaya ? 'border-red-200' : 'border-slate-200/80']">
        <div class="flex items-start justify-between gap-2">
            <div :class="['text-3xl leading-none font-bold tracking-tight tabular-nums', bahaya ? 'text-red-700' : 'text-slate-900']">{{ angka ?? '—' }}</div>
            <span :class="['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl', warna]"><Ikon :nama="ikon" kelas="h-5 w-5" /></span>
        </div>
        <div class="mt-2 text-sm leading-snug text-slate-600">{{ label }}</div>
    </div>
</template>
