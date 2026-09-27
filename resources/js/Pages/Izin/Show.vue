<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import InputError from '@/Components/InputError.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormEvaluasi from '@/Components/FormEvaluasi.vue';
import KesesuaianBadge from '@/Components/KesesuaianBadge.vue';
import { keInputWaktu, tanggalJam, tanggalPanjang, tautanWa } from '@/lib/format';
import type { BatasGas, IzinLengkap, JenisIzin, Tahap } from '@/types/izin';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    izin: IzinLengkap;
    aksi: string[];
    aturan: JenisIzin;
    dokumen: Record<string, string>;
    uji_gas: Record<string, BatasGas>;
    tahap: Tahap[];
    insiden: Record<string, string>;
    pemeriksaanPenutupan: string[];
}>();

const labelAksi: Record<string, { label: string; kelas: string; konfirmasi?: boolean }> = {
    setujui: { label: 'Setujui', kelas: 'tombol-hijau' },
    tolak: { label: 'Tolak', kelas: 'tombol-merah' },
    batalkan: { label: 'Batalkan izin', kelas: 'tombol-sekunder', konfirmasi: true },
    ajukan_penutupan: { label: 'Pekerjaan selesai, ajukan penutupan', kelas: 'tombol' },
    tutup: { label: 'Konfirmasi area aman & tutup izin', kelas: 'tombol-hijau' },
    hentikan: { label: 'Hentikan pekerjaan', kelas: 'tombol-merah' },
};
const tombolAksi = computed(() => Object.keys(labelAksi).filter((a) => props.aksi.includes(a)));

const form = useForm({
    aksi: '',
    catatan: '',
    ada_insiden: null as boolean | null,
    kategori_insiden: '',
    uraian_insiden: '',
    tindakan_insiden: '',
    mulai_aktual_at: keInputWaktu(props.izin.disahkan_at && props.izin.disahkan_at > props.izin.mulai_at ? props.izin.disahkan_at : props.izin.mulai_at),
    selesai_aktual_at: keInputWaktu(new Date().toISOString()),
    pemeriksaan_penutupan: [] as string[],
});

// Penutupan dan penghentian membuka isian evaluasi pasca pekerjaan lebih dulu.
const modeEvaluasi = ref<'' | 'ajukan_penutupan' | 'hentikan'>('');

function jalankan(aksi: string) {
    if ((aksi === 'ajukan_penutupan' || aksi === 'hentikan') && modeEvaluasi.value !== aksi) {
        modeEvaluasi.value = aksi;
        form.clearErrors();
        return;
    }
    if (labelAksi[aksi].konfirmasi && !confirm(`Yakin ${labelAksi[aksi].label.toLowerCase()}?`)) return;
    form.aksi = aksi;
    form.post(route('izin.aksi', props.izin.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            modeEvaluasi.value = '';
        },
    });
}

// Tahapan: persetujuan → pekerjaan berjalan → penutupan
const urutan = computed(() => [...props.tahap.map((t) => t.status), 'aktif', 'menunggu_penutupan', 'selesai']);
const posisi = computed(() => urutan.value.indexOf(props.izin.status));
const langkah = computed(() => [
    ...props.tahap.map((t) => ({ label: t.label, status: t.status })),
    { label: 'Pekerjaan berjalan', status: 'aktif' },
    { label: 'Penutupan', status: 'menunggu_penutupan' },
]);
function keadaanLangkah(status: string) {
    if (props.izin.status === 'selesai') return 'lewat';
    const i = urutan.value.indexOf(status);
    if (i === posisi.value) return 'kini';
    return i < posisi.value ? 'lewat' : 'nanti';
}

function aman(kunci: string, nilai: unknown): boolean | null {
    if (nilai === null || nilai === '' || nilai === undefined) return null;
    const b = props.uji_gas[kunci];
    return Number(nilai) >= b.min && Number(nilai) <= b.maks;
}
</script>

<template>
    <AppLayout :judul="izin.nomor ?? 'Draf Izin'">
        <section class="panel mb-5 overflow-hidden p-0 sm:p-0">
            <div class="grid md:grid-cols-[260px_1fr]">
                <img v-if="aturan.gambar" :src="aturan.gambar" :alt="aturan.label" class="aspect-[16/7] h-full w-full bg-slate-100 object-cover sm:aspect-video md:aspect-auto" />
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-merek-700">{{ izin.label_jenis }} · <em class="font-normal text-slate-500">{{ aturan.label_en }}</em></p>
                            <h1 class="judul-halaman mt-0.5 tabular-nums">{{ izin.nomor ?? `Draf #${izin.id}` }}</h1>
                            <div class="mt-2"><StatusBadge :status="izin.status" :label="izin.label_status" :lewat-waktu="izin.lewat_waktu" /></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link v-if="aksi.includes('ajukan')" :href="route('izin.edit', izin.id)" class="tombol">Ubah & ajukan</Link>
                            <a v-if="izin.nomor" :href="route('izin.cetak', izin.id)" target="_blank" class="tombol-sekunder"><Ikon nama="cetak" kelas="h-4 w-4" />Cetak PDF</a>
                        </div>
                    </div>
                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <dt class="flex items-center gap-1.5 text-xs text-slate-500"><Ikon nama="rumah" kelas="h-3.5 w-3.5" />Lokasi</dt>
                            <dd class="mt-0.5 font-semibold">{{ izin.lokasi }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <dt class="flex items-center gap-1.5 text-xs text-slate-500"><Ikon nama="jam" kelas="h-3.5 w-3.5" />Jadwal</dt>
                            <dd class="mt-0.5 font-semibold">{{ tanggalJam(izin.mulai_at) }}<br /><span class="font-normal text-slate-500">s.d.</span> {{ tanggalJam(izin.selesai_at) }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <dt class="flex items-center gap-1.5 text-xs text-slate-500"><Ikon nama="profil" kelas="h-3.5 w-3.5" />Pemohon</dt>
                            <dd class="mt-0.5 font-semibold">{{ izin.pemohon }}</dd>
                            <dd class="text-xs text-slate-500">{{ izin.departemen }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Tahapan -->
            <ol v-if="posisi !== -1" class="flex border-t border-slate-100 px-3 py-4 sm:px-6">
                <li v-for="(l, i) in langkah" :key="l.status" class="relative flex flex-1 flex-col items-center text-center">
                    <span
                        v-if="i > 0"
                        :class="['absolute top-4 right-1/2 h-0.5 w-full -translate-y-1/2', keadaanLangkah(l.status) === 'nanti' ? 'bg-slate-200' : 'bg-green-500']"
                    />
                    <span
                        :class="[
                            'relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold ring-4 ring-white',
                            {
                                'bg-green-600 text-white': keadaanLangkah(l.status) === 'lewat',
                                'bg-amber-500 text-white shadow-[0_0_0_6px_rgb(245_158_11/.2)]': keadaanLangkah(l.status) === 'kini',
                                'bg-slate-200 text-slate-500': keadaanLangkah(l.status) === 'nanti',
                            },
                        ]"
                    >
                        <Ikon v-if="keadaanLangkah(l.status) === 'lewat'" nama="centang" kelas="h-4 w-4" />
                        <template v-else>{{ i + 1 }}</template>
                    </span>
                    <span :class="['mt-1.5 px-0.5 text-[11px] leading-tight sm:text-xs', keadaanLangkah(l.status) === 'kini' ? 'font-bold text-amber-800' : 'text-slate-600']">
                        {{ l.label }}
                    </span>
                </li>
            </ol>
        </section>

        <div v-if="izin.lewat_waktu" class="mb-5 flex gap-2 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            <Ikon nama="peringatan" class="shrink-0" />
            Waktu izin sudah berakhir pada {{ tanggalJam(izin.selesai_at) }}. Hentikan pekerjaan dan ajukan penutupan, atau ajukan izin baru.
        </div>

        <div v-if="tombolAksi.length" class="panel mb-5 border-merek-200 ring-1 ring-merek-500/10">
            <h2 class="judul-bagian mb-1 flex items-center gap-2"><Ikon nama="lonceng" kelas="h-5 w-5 text-merek-600" />Tindakan Anda</h2>
            <p class="mb-3 text-sm text-slate-500">Periksa rincian di bawah sebelum memutuskan.</p>
            <label class="label" for="catatan">Catatan</label>
            <textarea
                id="catatan"
                v-model="form.catatan"
                rows="3"
                class="masukan"
                :placeholder="modeEvaluasi === 'hentikan' ? 'Alasan pekerjaan dihentikan' : modeEvaluasi === 'ajukan_penutupan' ? 'Ringkasan pekerjaan yang sudah diselesaikan' : 'Wajib diisi untuk penolakan, penghentian, dan penutupan'"
            />
            <InputError :message="form.errors.catatan || (form.errors as Record<string, string>).setujui" />

            <div v-if="modeEvaluasi" class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                <h3 class="judul-bagian mb-4">{{ modeEvaluasi === 'hentikan' ? 'Penghentian pekerjaan' : 'Evaluasi pasca pekerjaan' }}</h3>
                <FormEvaluasi
                    :form="form"
                    :mode="modeEvaluasi"
                    :insiden="insiden"
                    :pemeriksaan="pemeriksaanPenutupan"
                    :jadwal="{ mulai: tanggalJam(izin.mulai_at), selesai: tanggalJam(izin.selesai_at) }"
                />
                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" :class="modeEvaluasi === 'hentikan' ? 'tombol-merah' : 'tombol'" :disabled="form.processing" @click="jalankan(modeEvaluasi)">
                        {{ modeEvaluasi === 'hentikan' ? 'Hentikan sekarang' : 'Kirim laporan penutupan' }}
                    </button>
                    <button type="button" class="tombol-sekunder" @click="modeEvaluasi = ''">Batal</button>
                </div>
            </div>

            <div v-else class="mt-3 flex flex-wrap gap-2">
                <button v-for="a in tombolAksi" :key="a" type="button" :class="labelAksi[a].kelas" :disabled="form.processing" @click="jalankan(a)">
                    {{ labelAksi[a].label }}
                </button>
            </div>
        </div>

        <!-- Hasil evaluasi pasca pekerjaan -->
        <section v-if="izin.ada_insiden !== null || izin.kesesuaian_waktu" class="panel mb-5">
            <h2 class="judul-bagian mb-4">Evaluasi pasca pekerjaan</h2>
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold">Kesesuaian waktu</h3>
                        <KesesuaianBadge v-if="izin.kesesuaian_waktu" :nilai="izin.kesesuaian_waktu" />
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500"><th class="pb-1 font-semibold"></th><th class="pb-1 font-semibold">Diajukan</th><th class="pb-1 font-semibold">Sebenarnya</th></tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-slate-100"><td class="py-1.5 text-slate-500">Mulai</td><td>{{ tanggalJam(izin.mulai_at) }}</td><td class="font-semibold">{{ tanggalJam(izin.mulai_aktual_at) }}</td></tr>
                            <tr class="border-t border-slate-100"><td class="py-1.5 text-slate-500">Selesai</td><td>{{ tanggalJam(izin.selesai_at) }}</td><td class="font-semibold">{{ tanggalJam(izin.selesai_aktual_at) }}</td></tr>
                            <tr v-if="izin.disahkan_at" class="border-t border-slate-100"><td class="py-1.5 text-slate-500">Disahkan</td><td colspan="2">{{ tanggalJam(izin.disahkan_at) }}</td></tr>
                        </tbody>
                    </table>
                    <ul v-if="izin.kesesuaian_waktu?.temuan.length" class="mt-3 space-y-1">
                        <li v-for="t in izin.kesesuaian_waktu.temuan" :key="t" class="flex gap-2 text-sm text-red-800"><Ikon nama="peringatan" kelas="mt-0.5 h-4 w-4 shrink-0" />{{ t }}</li>
                    </ul>
                    <p v-else-if="!izin.kesesuaian_waktu" class="mt-3 text-sm text-slate-500">Jam mulai sebenarnya tidak dilaporkan (pekerjaan dihentikan).</p>
                </div>

                <div :class="['rounded-xl border p-4', izin.ada_insiden ? 'border-red-200 bg-red-50/50' : 'border-slate-200']">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold">Insiden</h3>
                        <span
                            v-if="izin.label_insiden"
                            :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', izin.ada_insiden ? 'bg-red-600 text-white' : 'bg-green-50 text-green-800 ring-1 ring-green-600/20 ring-inset']"
                            >{{ izin.label_insiden }}</span
                        >
                    </div>
                    <template v-if="izin.ada_insiden">
                        <p class="text-xs font-semibold text-slate-500">Kronologi</p>
                        <p class="mb-2 text-sm whitespace-pre-line">{{ izin.uraian_insiden }}</p>
                        <p class="text-xs font-semibold text-slate-500">Tindakan</p>
                        <p class="text-sm whitespace-pre-line">{{ izin.tindakan_insiden }}</p>
                    </template>
                    <p v-else class="text-sm text-slate-600">Tidak ada insiden yang dilaporkan selama pekerjaan.</p>
                    <template v-if="izin.pemeriksaan_penutupan.length">
                        <p class="mt-3 text-xs font-semibold text-slate-500">Kondisi area dipastikan</p>
                        <ul class="mt-1 space-y-1">
                            <li v-for="p in izin.pemeriksaan_penutupan" :key="p" class="flex gap-2 text-sm"><Ikon nama="centang" kelas="mt-0.5 h-4 w-4 shrink-0 text-green-600" />{{ p }}</li>
                        </ul>
                    </template>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="panel">
                <h2 class="judul-bagian mb-3">Rincian pekerjaan</h2>
                <dl class="grid gap-x-4 gap-y-2 text-sm sm:grid-cols-[150px_1fr]">
                    <dt class="text-slate-500">Pemohon</dt>
                    <dd>{{ izin.pemohon }}<span v-if="izin.pemohon_jabatan"> · {{ izin.pemohon_jabatan }}</span></dd>
                    <dt class="text-slate-500">NIK</dt>
                    <dd>{{ izin.nik }}</dd>
                    <dt class="text-slate-500">Nomor WA</dt>
                    <dd>
                        <a :href="tautanWa(izin.nomor_wa)" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-green-700 hover:underline">
                            <Ikon nama="whatsapp" kelas="h-4 w-4" />{{ izin.nomor_wa }}
                        </a>
                    </dd>
                    <dt class="text-slate-500">Departemen</dt>
                    <dd>{{ izin.departemen }}</dd>
                    <dt class="text-slate-500">Lokasi</dt>
                    <dd>{{ izin.lokasi }}</dd>
                    <dt class="text-slate-500">Pekerjaan</dt>
                    <dd class="whitespace-pre-line">{{ izin.uraian_pekerjaan }}</dd>
                    <dt class="text-slate-500">Peralatan</dt>
                    <dd>{{ izin.peralatan || '—' }}</dd>
                    <dt class="text-slate-500">Mulai</dt>
                    <dd>{{ tanggalPanjang(izin.mulai_at) }}</dd>
                    <dt class="text-slate-500">Selesai</dt>
                    <dd>{{ tanggalPanjang(izin.selesai_at) }}</dd>
                    <dt class="text-slate-500">Pekerja</dt>
                    <dd class="whitespace-pre-line">{{ izin.pekerja }}</dd>
                    <template v-if="izin.catatan_penutupan">
                        <dt class="text-slate-500">Catatan penutupan</dt>
                        <dd class="whitespace-pre-line">{{ izin.catatan_penutupan }}</dd>
                    </template>
                </dl>
            </div>

            <div class="panel text-sm">
                <h2 class="judul-bagian mb-3">Bahaya & pengendalian</h2>
                <h3 class="mb-1 font-semibold">Bahaya teridentifikasi</h3>
                <ul class="mb-3 list-disc pl-5">
                    <li v-for="b in izin.bahaya" :key="b">{{ b }}</li>
                    <li v-if="izin.bahaya_lain" class="whitespace-pre-line">{{ izin.bahaya_lain }}</li>
                    <li v-if="!izin.bahaya.length && !izin.bahaya_lain" class="text-slate-500">Belum dipilih</li>
                </ul>
                <h3 class="mb-1 font-semibold">Pengendalian</h3>
                <ul class="mb-3 space-y-1">
                    <li v-for="p in aturan.pengendalian" :key="p" class="flex gap-2">
                        <span :class="['mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded', izin.pengendalian.includes(p) ? 'bg-green-600 text-white' : 'border border-slate-300']">
                            <Ikon v-if="izin.pengendalian.includes(p)" nama="centang" kelas="h-3 w-3" />
                        </span>
                        {{ p }}
                    </li>
                    <li v-if="izin.pengendalian_tambahan" class="whitespace-pre-line text-slate-700">+ {{ izin.pengendalian_tambahan }}</li>
                </ul>
                <h3 class="mb-1 font-semibold">APD</h3>
                <p class="mb-3">{{ izin.apd.join(', ') || '—' }}</p>

                <template v-if="aturan.uji_gas">
                    <h3 class="mb-1 font-semibold">Uji gas</h3>
                    <table class="w-full">
                        <tr v-for="(b, k) in uji_gas" :key="k" class="border-b border-slate-100">
                            <td class="py-1.5">{{ b.label }}</td>
                            <td
                                :class="[
                                    'py-1.5 font-semibold',
                                    aman(k as string, izin.uji_gas?.[k]) === false ? 'text-red-700' : aman(k as string, izin.uji_gas?.[k]) ? 'text-green-700' : 'text-slate-400',
                                ]"
                            >
                                {{ izin.uji_gas?.[k] ?? '—' }} {{ izin.uji_gas?.[k] != null && izin.uji_gas?.[k] !== '' ? b.satuan : '' }}
                            </td>
                            <td class="py-1.5 text-xs text-slate-500">{{ b.min }}–{{ b.maks }} {{ b.satuan }}</td>
                        </tr>
                    </table>
                    <p class="mt-1 text-xs text-slate-500">Diuji oleh {{ izin.uji_gas_oleh || '—' }}<span v-if="izin.uji_gas_at">, {{ tanggalJam(izin.uji_gas_at) }}</span></p>
                </template>
            </div>
        </div>

        <div class="panel mt-5">
            <h2 class="judul-bagian mb-2">Dokumen pendukung</h2>
            <div v-for="(label, k) in dokumen" :key="k" class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2.5 last:border-0">
                <span class="font-semibold">{{ label }}</span>
                <template v-for="d in [izin.dokumen.find((x) => x.jenis === k)]" :key="k">
                    <a v-if="d" :href="d.url" class="tombol-sekunder px-3 py-1 text-xs"><Ikon nama="unduh" kelas="h-4 w-4" />{{ d.nama_asli }} ({{ d.ukuran }})</a>
                    <span v-else class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Belum diunggah</span>
                </template>
            </div>
        </div>

        <div class="panel mt-5">
            <h2 class="judul-bagian mb-3">Riwayat</h2>
            <ol class="relative ml-2 border-l-2 border-slate-200">
                <li v-for="r in izin.riwayat" :key="r.id" class="relative pb-4 pl-5 last:pb-0">
                    <span class="absolute top-1.5 -left-[7px] h-3 w-3 rounded-full bg-merek-600" />
                    <div class="text-sm"><strong>{{ r.label_aksi }}</strong> — {{ r.oleh }} ({{ r.peran }})</div>
                    <div class="text-xs text-slate-500">{{ tanggalJam(r.waktu) }} · {{ r.status_ke }}</div>
                    <div v-if="r.catatan" class="mt-1 rounded-md bg-slate-100 px-2.5 py-1.5 text-sm whitespace-pre-line">{{ r.catatan }}</div>
                </li>
            </ol>
        </div>
    </AppLayout>
</template>
