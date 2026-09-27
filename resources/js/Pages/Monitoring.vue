<script setup lang="ts">
import GrafikBatang from '@/Components/GrafikBatang.vue';
import GrafikHarian from '@/Components/GrafikHarian.vue';
import Ikon from '@/Components/Ikon.vue';
import Statistik from '@/Components/Statistik.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

type Rekap = { label: string; jumlah: number }[];

const props = defineProps<{
    filter: { dari: string; sampai: string; jenis?: string; lokasi?: string; departemen?: string };
    evaluasi: {
        total: number;
        diajukan: number;
        disetujui: number;
        ditolak_sekali: number;
        dihentikan: number;
        selesai: number;
        lewat_waktu: number;
        rata_jam_persetujuan: number | null;
    };
    perJenis: Rekap;
    perLokasi: Rekap;
    perDepartemen: Rekap;
    perStatus: Rekap;
    perHari: { tanggal: string; jumlah: number }[];
    pilihan: { jenis: Record<string, string>; lokasi: string[]; departemen: string[] };
}>();

const f = reactive({
    dari: props.filter.dari,
    sampai: props.filter.sampai,
    jenis: props.filter.jenis ?? '',
    lokasi: props.filter.lokasi ?? '',
    departemen: props.filter.departemen ?? '',
});
const parameter = () => Object.fromEntries(Object.entries(f).filter(([, v]) => v));
const terapkan = () => router.get(route('monitoring'), parameter(), { preserveState: true });
const tautanEkspor = computed(() => route('monitoring.ekspor', parameter()));
const tingkatPenutupan = computed(() => (props.evaluasi.diajukan ? Math.round((props.evaluasi.selesai / props.evaluasi.diajukan) * 100) : 0));
const tanggal = (ymd: string) => new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(ymd + 'T00:00:00'));
</script>

<template>
    <AppLayout judul="Monitoring & Evaluasi">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Monitoring & Evaluasi</h1>
                <p class="text-slate-600">
                    Rekap izin kerja khusus berdasarkan jadwal mulai, {{ tanggal(filter.dari) }} – {{ tanggal(filter.sampai) }}. Draf dan izin yang dibatalkan tidak dihitung.
                </p>
            </div>
            <a :href="tautanEkspor" class="tombol-sekunder"><Ikon nama="unduh" kelas="h-4 w-4" />Unduh Excel</a>
        </div>

        <form class="panel mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1fr_1fr_auto] lg:items-end" @submit.prevent="terapkan">
            <div><label class="label" for="dari">Dari</label><input id="dari" v-model="f.dari" type="date" class="masukan" /></div>
            <div><label class="label" for="sampai">Sampai</label><input id="sampai" v-model="f.sampai" type="date" class="masukan" /></div>
            <div>
                <label class="label" for="jenis">Jenis</label>
                <select id="jenis" v-model="f.jenis" class="masukan">
                    <option value="">Semua</option>
                    <option v-for="(label, k) in pilihan.jenis" :key="k" :value="k">{{ label }}</option>
                </select>
            </div>
            <div>
                <label class="label" for="lokasi">Lokasi</label>
                <select id="lokasi" v-model="f.lokasi" class="masukan">
                    <option value="">Semua</option>
                    <option v-for="l in pilihan.lokasi" :key="l">{{ l }}</option>
                </select>
            </div>
            <div>
                <label class="label" for="departemen">Departemen</label>
                <select id="departemen" v-model="f.departemen" class="masukan">
                    <option value="">Semua</option>
                    <option v-for="d in pilihan.departemen" :key="d">{{ d }}</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="tombol">Terapkan</button>
                <Link :href="route('monitoring')" class="tombol-sekunder">Bulan ini</Link>
            </div>
        </form>

        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            <Statistik :angka="evaluasi.diajukan" label="Izin diajukan" ikon="kirim" nada="oranye" />
            <Statistik :angka="evaluasi.disetujui" label="Disetujui & aktif" ikon="aktif" nada="hijau" />
            <Statistik :angka="evaluasi.selesai" label="Selesai & ditutup" ikon="selesai" nada="biru" />
            <Statistik :angka="evaluasi.rata_jam_persetujuan" label="Rata-rata jam sampai disetujui" ikon="jam" nada="abu" />
            <Statistik :angka="evaluasi.ditolak_sekali" label="Pernah ditolak (perlu perbaikan)" ikon="tolak" nada="amber" />
            <Statistik :angka="evaluasi.dihentikan" label="Dihentikan (stop work)" ikon="henti" :bahaya="evaluasi.dihentikan > 0" />
            <Statistik :angka="evaluasi.lewat_waktu" label="Aktif lewat waktu, belum ditutup" ikon="peringatan" :bahaya="evaluasi.lewat_waktu > 0" />
            <Statistik :angka="`${tingkatPenutupan}%`" label="Tingkat penutupan" ikon="persen" nada="hijau" />
        </div>

        <div v-if="evaluasi.total === 0" class="panel text-slate-500">Belum ada izin pada periode dan filter ini.</div>
        <template v-else>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="panel"><h2 class="judul-bagian mb-2">Per jenis izin</h2><GrafikBatang :data="perJenis" /></div>
                <div class="panel"><h2 class="judul-bagian mb-2">Per lokasi</h2><GrafikBatang :data="perLokasi" /></div>
                <div class="panel"><h2 class="judul-bagian mb-2">Per departemen</h2><GrafikBatang :data="perDepartemen" /></div>
                <div class="panel"><h2 class="judul-bagian mb-2">Per status</h2><GrafikBatang :data="perStatus" /></div>
            </div>
            <div class="panel mt-5"><h2 class="judul-bagian mb-2">Izin per hari mulai</h2><GrafikHarian :data="perHari" /></div>
        </template>
    </AppLayout>
</template>
