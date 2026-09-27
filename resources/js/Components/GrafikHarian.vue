<script setup lang="ts">
// Grafik kolom per hari dengan SVG native.
import { hariPendek } from '@/lib/format';
import { computed } from 'vue';

const props = defineProps<{ data: { tanggal: string; jumlah: number }[] }>();

const tinggiArea = 140;
const lebarKolom = 36;
const jarak = 12;
const maks = computed(() => Math.max(1, ...props.data.map((d) => d.jumlah)));
const lebar = computed(() => Math.max(300, props.data.length * (lebarKolom + jarak) + jarak));
</script>

<template>
    <div class="overflow-x-auto">
        <!-- Ukuran tetap per kolom: grafik tidak membesar bila datanya hanya beberapa hari. -->
        <svg :viewBox="`0 0 ${lebar} ${tinggiArea + 40}`" :width="lebar" :height="tinggiArea + 40" class="max-w-none" role="img">
            <line x1="0" :y1="tinggiArea" :x2="lebar" :y2="tinggiArea" class="stroke-slate-300" />
            <g v-for="(d, i) in data" :key="d.tanggal" :transform="`translate(${jarak + i * (lebarKolom + jarak)} 0)`">
                <title>{{ hariPendek(d.tanggal) }}: {{ d.jumlah }} izin</title>
                <rect
                    x="0"
                    :y="tinggiArea - (d.jumlah / maks) * (tinggiArea - 20)"
                    :width="lebarKolom"
                    :height="(d.jumlah / maks) * (tinggiArea - 20)"
                    rx="5"
                    class="fill-merek-600"
                />
                <text :x="lebarKolom / 2" :y="tinggiArea - (d.jumlah / maks) * (tinggiArea - 20) - 6" text-anchor="middle" class="fill-slate-900 text-[11px] font-bold">
                    {{ d.jumlah }}
                </text>
                <text :x="lebarKolom / 2" :y="tinggiArea + 16" text-anchor="middle" class="fill-slate-600 text-[10px]">
                    {{ hariPendek(d.tanggal).split(', ')[1] ?? d.tanggal.slice(5) }}
                </text>
            </g>
        </svg>
    </div>
</template>
