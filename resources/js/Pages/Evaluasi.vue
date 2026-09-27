<script setup lang="ts">
import GrafikBatang from '@/Components/GrafikBatang.vue';
import GrafikHarian from '@/Components/GrafikHarian.vue';
import Ikon from '@/Components/Ikon.vue';
import Statistik from '@/Components/Statistik.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import KesesuaianBadge from '@/Components/KesesuaianBadge.vue';
import { tanggalJam } from '@/lib/format';
import type { IzinRingkas, KesesuaianWaktu } from '@/types/izin';
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
    pasca: {
        dievaluasi: number;
        insiden: number;
        tingkat_insiden: number | null;
        dilaporkan_waktu: number;
        sesuai_jadwal: number;
        lewat_waktu: number;
        sebelum_disahkan: number;
        rata_menit_lewat: number | null;
        rata_jam_lapor: number | null;
    };
    perKategoriInsiden: Rekap;
    perKesesuaian: Rekap;
    daftarInsiden: (IzinRingkas & { label_insiden: string; uraian_insiden: string })[];
    daftarWaktu: (IzinRingkas & { kesesuaian_waktu: KesesuaianWaktu })[];
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
const terapkan = () => router.get(route('evaluasi'), parameter(), { preserveState: true });
const tautanEkspor = computed(() => route('evaluasi.ekspor', parameter()));
const tingkatPenutupan = computed(() => (props.evaluasi.diajukan ? Math.round((props.evaluasi.selesai / props.evaluasi.diajukan) * 100) : 0));
const persenSesuai = computed(() => (props.pasca.dilaporkan_waktu ? Math.round((props.pasca.sesuai_jadwal / props.pasca.dilaporkan_waktu) * 100) : null));
const durasi = (menit: number | null) => (menit === null ? null : menit >= 60 ? `${Math.floor(menit / 60)} j ${menit % 60} m` : `${menit} m`);
const tanggal = (ymd: string) => new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(ymd + 'T00:00:00'));
</script>

<template>
    <AppLayout judul="Evaluasi">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Evaluasi</h1>
                <p class="text-slate-600">
                    Hasil izin kerja khusus berdasarkan jadwal mulai, {{ tanggal(filter.dari) }} – {{ tanggal(filter.sampai) }}. Draf dan izin yang dibatalkan tidak dihitung.
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
                <Link :href="route('evaluasi')" class="tombol-sekunder">Bulan ini</Link>
            </div>
        </form>

        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            <Statistik :angka="evaluasi.diajukan" label="Izin diajukan" ikon="kirim" nada="oranye" />
            <Statistik :angka="evaluasi.disetujui" label="Lolos persetujuan" ikon="aktif" nada="hijau" />
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

            <!-- Evaluasi pasca pekerjaan -->
            <section class="mt-8">
                <p class="text-sm font-semibold text-merek-700">Setelah pekerjaan</p>
                <h2 class="judul-halaman mt-1">Evaluasi pasca pekerjaan</h2>
                <p class="mb-4 text-slate-600">
                    Dari {{ pasca.dievaluasi }} izin yang sudah dilaporkan selesai atau dihentikan. Toleransi waktu 15 menit.
                </p>

                <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <Statistik :angka="pasca.insiden" label="Izin dengan insiden" ikon="peringatan" :bahaya="pasca.insiden > 0" nada="hijau" />
                    <Statistik :angka="pasca.tingkat_insiden === null ? null : `${pasca.tingkat_insiden}%`" label="Tingkat insiden" ikon="persen" :nada="pasca.insiden ? 'amber' : 'hijau'" />
                    <Statistik :angka="persenSesuai === null ? null : `${persenSesuai}%`" label="Sesuai jadwal (mulai &amp; selesai)" ikon="jam" nada="hijau" />
                    <Statistik :angka="pasca.lewat_waktu" label="Selesai lewat waktu" ikon="jam" nada="amber" />
                    <Statistik :angka="pasca.sebelum_disahkan" label="Mulai sebelum disahkan" ikon="henti" :bahaya="pasca.sebelum_disahkan > 0" />
                    <Statistik :angka="durasi(pasca.rata_menit_lewat)" label="Rata-rata keterlambatan" ikon="jam" nada="abu" />
                    <Statistik :angka="pasca.rata_jam_lapor === null ? null : `${pasca.rata_jam_lapor} j`" label="Rata-rata jeda lapor penutupan" ikon="kirim" nada="abu" />
                    <Statistik :angka="pasca.dilaporkan_waktu" label="Jam kerja sebenarnya dilaporkan" ikon="formulir" nada="biru" />
                </div>

                <div v-if="pasca.dievaluasi === 0" class="panel text-slate-500">Belum ada izin yang dilaporkan selesai pada periode ini.</div>
                <template v-else>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="panel">
                            <h3 class="judul-bagian mb-2">Insiden per kategori</h3>
                            <GrafikBatang v-if="perKategoriInsiden.length" :data="perKategoriInsiden" />
                            <p v-else class="flex items-center gap-2 text-sm text-green-700"><Ikon nama="centang" kelas="h-4 w-4" />Tidak ada insiden dilaporkan.</p>
                        </div>
                        <div class="panel">
                            <h3 class="judul-bagian mb-2">Kesesuaian waktu kerja</h3>
                            <GrafikBatang v-if="perKesesuaian.length" :data="perKesesuaian" />
                            <p v-else class="text-sm text-slate-500">Belum ada jam kerja sebenarnya yang dilaporkan.</p>
                        </div>
                    </div>

                    <div v-if="daftarWaktu.length" class="panel mt-5">
                        <h3 class="judul-bagian mb-1">Izin tidak sesuai jadwal</h3>
                        <p class="mb-3 text-sm text-slate-500">"Mulai sebelum disahkan" berarti pekerjaan berjalan tanpa izin yang sah dan perlu ditindaklanjuti.</p>
                        <ul class="divide-y divide-slate-100">
                            <li v-for="i in daftarWaktu" :key="i.id" class="flex flex-wrap items-start justify-between gap-2 py-3">
                                <div class="min-w-0">
                                    <Link :href="route('izin.show', i.id)" class="font-semibold text-merek-700 hover:underline">{{ i.nomor }}</Link>
                                    <span class="text-sm text-slate-600"> · {{ i.label_jenis }} · {{ i.lokasi }} · {{ i.pemohon }}</span>
                                    <ul class="mt-0.5 text-sm text-slate-700">
                                        <li v-for="t in i.kesesuaian_waktu.temuan" :key="t">{{ t }}</li>
                                    </ul>
                                </div>
                                <KesesuaianBadge :nilai="i.kesesuaian_waktu" />
                            </li>
                        </ul>
                    </div>

                    <div v-if="daftarInsiden.length" class="panel mt-5">
                        <h3 class="judul-bagian mb-3">Daftar insiden</h3>
                        <ul class="divide-y divide-slate-100">
                            <li v-for="i in daftarInsiden" :key="i.id" class="py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <Link :href="route('izin.show', i.id)" class="font-semibold text-merek-700 hover:underline">{{ i.nomor }}</Link>
                                        <span class="text-sm text-slate-600"> · {{ i.label_jenis }} · {{ i.lokasi }}</span>
                                    </div>
                                    <span class="rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-semibold text-white">{{ i.label_insiden }}</span>
                                </div>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-700">{{ i.uraian_insiden }}</p>
                                <p class="text-xs text-slate-500">{{ i.pemohon }} · {{ tanggalJam(i.selesai_at) }}</p>
                            </li>
                        </ul>
                    </div>
                </template>
            </section>
        </template>
    </AppLayout>
</template>
