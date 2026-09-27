<script setup lang="ts">
import type { KesesuaianWaktu } from '@/types/izin';
import { computed } from 'vue';

const props = defineProps<{ nilai: KesesuaianWaktu }>();

const kelas = computed(
    () =>
        ({
            sesuai: 'bg-green-50 text-green-800 ring-green-600/20',
            mulai_awal: 'bg-amber-50 text-amber-800 ring-amber-600/20',
            lewat_waktu: 'bg-orange-50 text-orange-800 ring-orange-600/20',
            sebelum_disahkan: 'bg-red-600 text-white ring-red-700',
        })[props.nilai.kode],
);
</script>

<template>
    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap ring-1 ring-inset', kelas]">{{ nilai.label }}</span>
</template>
