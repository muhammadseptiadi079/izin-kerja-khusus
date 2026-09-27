<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string; label: string; lewatWaktu?: boolean }>();

const warna = computed(() => {
    if (props.status.startsWith('menunggu')) return 'bg-amber-100 text-amber-800';
    return (
        {
            aktif: 'bg-green-100 text-green-800',
            selesai: 'bg-blue-100 text-blue-800',
            ditolak: 'bg-red-100 text-red-800',
            dihentikan: 'bg-red-100 text-red-800',
        }[props.status] ?? 'bg-slate-100 text-slate-600'
    );
});
</script>

<template>
    <span class="inline-flex flex-wrap gap-1">
        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap', warna]">{{ label }}</span>
        <span v-if="lewatWaktu" class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap text-red-800">Lewat waktu</span>
    </span>
</template>
