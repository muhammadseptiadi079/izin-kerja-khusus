<script setup lang="ts">
import StatusBadge from '@/Components/StatusBadge.vue';
import { tanggalJam } from '@/lib/format';
import type { IzinRingkas } from '@/types/izin';
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        izin: IzinRingkas[];
        kolom?: ('nomor' | 'jenis' | 'lokasi' | 'pemohon' | 'jadwal' | 'selesai' | 'status' | 'diperbarui')[];
        tombol?: { label: string; rute: string; sekunder?: boolean };
    }>(),
    { kolom: () => ['nomor', 'jenis', 'lokasi', 'pemohon', 'jadwal', 'status'] },
);
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs tracking-wide text-slate-500 uppercase">
                    <th v-if="kolom.includes('nomor')" class="px-2 py-2">Nomor</th>
                    <th v-if="kolom.includes('jenis')" class="px-2 py-2">Jenis</th>
                    <th v-if="kolom.includes('lokasi')" class="px-2 py-2">Lokasi & pekerjaan</th>
                    <th v-if="kolom.includes('pemohon')" class="px-2 py-2">Pemohon</th>
                    <th v-if="kolom.includes('jadwal')" class="px-2 py-2">Jadwal</th>
                    <th v-if="kolom.includes('selesai')" class="px-2 py-2">Berlaku sampai</th>
                    <th v-if="kolom.includes('diperbarui')" class="px-2 py-2">Diperbarui</th>
                    <th v-if="kolom.includes('status')" class="px-2 py-2">Status</th>
                    <th v-if="tombol" class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="i in izin" :key="i.id" class="border-b border-slate-100 align-top last:border-0">
                    <td v-if="kolom.includes('nomor')" class="px-2 py-2.5 whitespace-nowrap">
                        <Link :href="route('izin.show', i.id)" class="font-semibold text-merek-700 hover:underline">{{ i.nomor ?? `Draf #${i.id}` }}</Link>
                    </td>
                    <td v-if="kolom.includes('jenis')" class="px-2 py-2.5">{{ i.label_jenis }}</td>
                    <td v-if="kolom.includes('lokasi')" class="px-2 py-2.5">
                        <div class="font-semibold">{{ i.lokasi }}</div>
                        <div class="line-clamp-2 text-xs text-slate-500">{{ i.uraian_pekerjaan }}</div>
                    </td>
                    <td v-if="kolom.includes('pemohon')" class="px-2 py-2.5">{{ i.pemohon }}</td>
                    <td v-if="kolom.includes('jadwal')" class="px-2 py-2.5 text-xs whitespace-nowrap">
                        {{ tanggalJam(i.mulai_at) }}<br />s.d. {{ tanggalJam(i.selesai_at) }}
                    </td>
                    <td v-if="kolom.includes('selesai')" class="px-2 py-2.5 whitespace-nowrap">{{ tanggalJam(i.selesai_at) }}</td>
                    <td v-if="kolom.includes('diperbarui')" class="px-2 py-2.5 whitespace-nowrap">{{ tanggalJam(i.updated_at) }}</td>
                    <td v-if="kolom.includes('status')" class="px-2 py-2.5"><StatusBadge :status="i.status" :label="i.label_status" :lewat-waktu="i.lewat_waktu" /></td>
                    <td v-if="tombol" class="px-2 py-2.5 text-right">
                        <Link :href="route(tombol.rute, i.id)" :class="[tombol.sekunder ? 'tombol-sekunder' : 'tombol', 'px-3 py-1 text-xs']">{{ tombol.label }}</Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
