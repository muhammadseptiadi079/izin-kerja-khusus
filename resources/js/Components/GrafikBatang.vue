<script setup lang="ts">
// Grafik batang horizontal dengan SVG native: satu baris per kategori.
import { computed } from 'vue';

const props = defineProps<{ data: { label: string; jumlah: number }[] }>();

const tinggiBaris = 32;
const lebarLabel = 170;
const lebarBatang = 170;
const maks = computed(() => Math.max(1, ...props.data.map((d) => d.jumlah)));
const tinggi = computed(() => props.data.length * tinggiBaris);
</script>

<template>
    <svg :viewBox="`0 0 ${lebarLabel + lebarBatang + 40} ${tinggi}`" class="w-full max-w-xl" role="img">
        <g v-for="(d, i) in data" :key="d.label" :transform="`translate(0 ${i * tinggiBaris})`">
            <title>{{ d.label }}: {{ d.jumlah }}</title>
            <text :x="lebarLabel - 8" :y="tinggiBaris / 2" text-anchor="end" dominant-baseline="middle" class="fill-slate-700 text-[13px]">
                {{ d.label.length > 24 ? d.label.slice(0, 23) + '…' : d.label }}
            </text>
            <rect :x="lebarLabel" :y="8" :width="lebarBatang" :height="tinggiBaris - 16" rx="6" class="fill-slate-100" />
            <rect
                :x="lebarLabel"
                :y="8"
                :width="Math.max(6, (d.jumlah / maks) * lebarBatang)"
                :height="tinggiBaris - 16"
                rx="6"
                class="fill-merek-600"
            />
            <text :x="lebarLabel + lebarBatang + 10" :y="tinggiBaris / 2" dominant-baseline="middle" class="fill-slate-900 text-[14px] font-bold">
                {{ d.jumlah }}
            </text>
        </g>
    </svg>
</template>
