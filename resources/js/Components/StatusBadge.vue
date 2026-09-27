<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string; label: string; lewatWaktu?: boolean }>();

const nada = computed(() => {
    if (props.status.startsWith('menunggu')) return { latar: 'bg-amber-50 text-amber-800 ring-amber-600/20', titik: 'bg-amber-500 animate-pulse' };
    return (
        {
            aktif: { latar: 'bg-green-50 text-green-800 ring-green-600/20', titik: 'bg-green-500' },
            selesai: { latar: 'bg-blue-50 text-blue-800 ring-blue-600/20', titik: 'bg-blue-500' },
            ditolak: { latar: 'bg-red-50 text-red-800 ring-red-600/20', titik: 'bg-red-500' },
            dihentikan: { latar: 'bg-red-50 text-red-800 ring-red-600/20', titik: 'bg-red-500' },
        }[props.status] ?? { latar: 'bg-slate-100 text-slate-700 ring-slate-500/20', titik: 'bg-slate-400' }
    );
});
</script>

<template>
    <span class="inline-flex flex-wrap gap-1">
        <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap ring-1 ring-inset', nada.latar]">
            <span :class="['h-1.5 w-1.5 rounded-full', nada.titik]" />{{ label }}
        </span>
        <span v-if="lewatWaktu" class="inline-flex items-center gap-1.5 rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap text-white">
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white" />Lewat waktu
        </span>
    </span>
</template>
