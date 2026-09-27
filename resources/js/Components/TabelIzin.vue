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
    <!-- HP: satu kartu per izin, seluruh kartu bisa diketuk -->
    <ul class="space-y-2 sm:hidden">
        <li v-for="i in izin" :key="i.id">
            <Link
                :href="route(tombol?.rute ?? 'izin.show', i.id)"
                class="block rounded-lg border border-slate-200 p-3 active:bg-slate-50"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-semibold text-merek-700">{{ i.nomor ?? `Draf #${i.id}` }}</div>
                        <div class="text-sm font-semibold">{{ i.label_jenis }}</div>
                    </div>
                    <StatusBadge v-if="kolom.includes('status')" :status="i.status" :label="i.label_status" :lewat-waktu="i.lewat_waktu" />
                </div>
                <div class="mt-1 text-sm text-slate-700">{{ i.lokasi }}</div>
                <div class="line-clamp-1 text-xs text-slate-500">{{ i.uraian_pekerjaan }}</div>
                <div class="mt-2 flex items-center justify-between gap-2 text-xs text-slate-500">
                    <span v-if="kolom.includes('pemohon')">{{ i.pemohon }}</span>
                    <span v-if="kolom.includes('selesai') || kolom.includes('jadwal')">s.d. {{ tanggalJam(i.selesai_at) }}</span>
                    <span v-else-if="kolom.includes('diperbarui')">{{ tanggalJam(i.updated_at) }}</span>
                    <span v-if="tombol" :class="[tombol.sekunder ? 'tombol-sekunder' : 'tombol', 'ml-auto px-3 py-1 text-xs']">{{ tombol.label }}</span>
                </div>
            </Link>
        </li>
    </ul>

    <!-- Laptop dan tablet: tabel -->
    <div class="hidden overflow-x-auto sm:block">
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
