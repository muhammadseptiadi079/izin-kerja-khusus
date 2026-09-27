<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import InputError from '@/Components/InputError.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { tanggalJam, tanggalPanjang, tautanWa } from '@/lib/format';
import type { BatasGas, IzinLengkap, JenisIzin, Tahap } from '@/types/izin';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    izin: IzinLengkap;
    aksi: string[];
    aturan: JenisIzin;
    dokumen: Record<string, string>;
    uji_gas: Record<string, BatasGas>;
    tahap: Tahap[];
}>();

const labelAksi: Record<string, { label: string; kelas: string; konfirmasi?: boolean }> = {
    setujui: { label: 'Setujui', kelas: 'tombol-hijau' },
    tolak: { label: 'Tolak', kelas: 'tombol-merah' },
    batalkan: { label: 'Batalkan izin', kelas: 'tombol-sekunder', konfirmasi: true },
    ajukan_penutupan: { label: 'Pekerjaan selesai, ajukan penutupan', kelas: 'tombol' },
    tutup: { label: 'Konfirmasi area aman & tutup izin', kelas: 'tombol-hijau' },
    hentikan: { label: 'Hentikan pekerjaan', kelas: 'tombol-merah', konfirmasi: true },
};
const tombolAksi = computed(() => Object.keys(labelAksi).filter((a) => props.aksi.includes(a)));

const form = useForm({ aksi: '', catatan: '' });
function jalankan(aksi: string) {
    if (labelAksi[aksi].konfirmasi && !confirm(`Yakin ${labelAksi[aksi].label.toLowerCase()}?`)) return;
    form.aksi = aksi;
    form.post(route('izin.aksi', props.izin.id), { preserveScroll: true, onSuccess: () => form.reset() });
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
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">{{ izin.nomor ?? `Draf #${izin.id}` }} · {{ izin.label_jenis }}</h1>
                <div class="mt-1"><StatusBadge :status="izin.status" :label="izin.label_status" :lewat-waktu="izin.lewat_waktu" /></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link v-if="aksi.includes('ajukan')" :href="route('izin.edit', izin.id)" class="tombol">Ubah & ajukan</Link>
                <a v-if="izin.nomor" :href="route('izin.cetak', izin.id)" target="_blank" class="tombol-sekunder"><Ikon nama="cetak" kelas="h-4 w-4" />Cetak PDF</a>
            </div>
        </div>

        <div v-if="posisi !== -1" class="panel mb-5">
            <h2 class="mb-2 font-bold">Tahapan</h2>
            <ol class="grid grid-cols-2 gap-1.5 sm:grid-cols-5">
                <li
                    v-for="l in langkah"
                    :key="l.status"
                    :class="[
                        'rounded-lg px-2 py-2 text-center text-sm',
                        {
                            'bg-green-100 text-green-800': keadaanLangkah(l.status) === 'lewat',
                            'bg-amber-100 font-bold text-amber-800': keadaanLangkah(l.status) === 'kini',
                            'bg-slate-100 text-slate-500': keadaanLangkah(l.status) === 'nanti',
                        },
                    ]"
                >
                    {{ l.label }}
                </li>
            </ol>
        </div>

        <div v-if="izin.lewat_waktu" class="mb-5 flex gap-2 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            <Ikon nama="peringatan" class="shrink-0" />
            Waktu izin sudah berakhir pada {{ tanggalJam(izin.selesai_at) }}. Hentikan pekerjaan dan ajukan penutupan, atau ajukan izin baru.
        </div>

        <div v-if="tombolAksi.length" class="panel mb-5">
            <h2 class="mb-2 font-bold">Tindakan</h2>
            <label class="label" for="catatan">Catatan</label>
            <textarea id="catatan" v-model="form.catatan" rows="3" class="masukan" placeholder="Wajib diisi untuk penolakan, penghentian, dan penutupan" />
            <InputError :message="form.errors.catatan || (form.errors as Record<string, string>).setujui" />
            <div class="mt-3 flex flex-wrap gap-2">
                <button v-for="a in tombolAksi" :key="a" type="button" :class="labelAksi[a].kelas" :disabled="form.processing" @click="jalankan(a)">
                    {{ labelAksi[a].label }}
                </button>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="panel">
                <h2 class="mb-3 font-bold">Rincian pekerjaan</h2>
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
                <h2 class="mb-3 font-bold">Bahaya & pengendalian</h2>
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
            <h2 class="mb-2 font-bold">Dokumen pendukung</h2>
            <div v-for="(label, k) in dokumen" :key="k" class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2.5 last:border-0">
                <span class="font-semibold">{{ label }}</span>
                <template v-for="d in [izin.dokumen.find((x) => x.jenis === k)]" :key="k">
                    <a v-if="d" :href="d.url" class="tombol-sekunder px-3 py-1 text-xs"><Ikon nama="unduh" kelas="h-4 w-4" />{{ d.nama_asli }} ({{ d.ukuran }})</a>
                    <span v-else class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Belum diunggah</span>
                </template>
            </div>
        </div>

        <div class="panel mt-5">
            <h2 class="mb-3 font-bold">Riwayat</h2>
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
