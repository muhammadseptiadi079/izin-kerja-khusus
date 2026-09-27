<script setup lang="ts">
import TabelIzin from '@/Components/TabelIzin.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Halaman, IzinRingkas } from '@/types/izin';
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    izin: Halaman<IzinRingkas>;
    filter: { status?: string; jenis?: string; cari?: string };
    jenisList: Record<string, string>;
    statusList: Record<string, string>;
    melihatSemua: boolean;
}>();

const f = reactive({ cari: props.filter.cari ?? '', jenis: props.filter.jenis ?? '', status: props.filter.status ?? '' });

function terapkan() {
    router.get(route('izin.index'), Object.fromEntries(Object.entries(f).filter(([, v]) => v)), { preserveState: true });
}
</script>

<template>
    <AppLayout judul="Daftar Izin">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Daftar Izin</h1>
                <p class="text-slate-600">{{ melihatSemua ? 'Semua izin kerja khusus.' : 'Izin yang Anda ajukan.' }}</p>
            </div>
            <Link :href="route('izin.create')" class="tombol hidden lg:inline-flex">+ Ajukan izin</Link>
        </div>

        <form class="panel mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end" @submit.prevent="terapkan">
            <div>
                <label class="label" for="cari">Cari</label>
                <input id="cari" v-model="f.cari" class="masukan" placeholder="Nomor, lokasi, atau pekerjaan" />
            </div>
            <div>
                <label class="label" for="jenis">Jenis</label>
                <select id="jenis" v-model="f.jenis" class="masukan">
                    <option value="">Semua jenis</option>
                    <option v-for="(label, k) in jenisList" :key="k" :value="k">{{ label }}</option>
                </select>
            </div>
            <div>
                <label class="label" for="status">Status</label>
                <select id="status" v-model="f.status" class="masukan">
                    <option value="">Semua status</option>
                    <option v-for="(label, k) in statusList" :key="k" :value="k">{{ label }}</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="tombol">Terapkan</button>
                <Link :href="route('izin.index')" class="tombol-sekunder">Atur ulang</Link>
            </div>
        </form>

        <div class="panel">
            <p v-if="!izin.data.length" class="text-slate-500">Belum ada izin yang cocok dengan filter ini.</p>
            <template v-else>
                <TabelIzin :izin="izin.data" />
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-slate-500">Menampilkan {{ izin.from }}–{{ izin.to }} dari {{ izin.total }}</span>
                    <span class="flex gap-2">
                        <Link v-if="izin.prev_page_url" :href="izin.prev_page_url" class="tombol-sekunder px-3 py-1">‹ Sebelumnya</Link>
                        <Link v-if="izin.next_page_url" :href="izin.next_page_url" class="tombol-sekunder px-3 py-1">Berikutnya ›</Link>
                    </span>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
