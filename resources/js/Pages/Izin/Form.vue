<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import InputError from '@/Components/InputError.vue';
import JudulLangkah from '@/Components/JudulLangkah.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { keInputWaktu } from '@/lib/format';
import type { PageProps } from '@/types';
import type { IzinLengkap, JenisIzin, Katalog } from '@/types/izin';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps<{
    izin: Partial<IzinLengkap> & { id: number | null; jenis: string };
    penolakan: { oleh: string; catatan: string } | null;
    aturan: JenisIzin;
    katalog: Katalog;
    aiTersedia: boolean;
    masalah: string[];
}>();

const user = usePage<PageProps>().props.auth.user!;
const kunciGas = Object.keys(props.katalog.uji_gas);

const form = useForm({
    jenis: props.izin.jenis,
    nik: props.izin.nik ?? '',
    nomor_wa: props.izin.nomor_wa ?? '',
    departemen: props.izin.departemen ?? '',
    lokasi: props.izin.lokasi_pilihan ?? '',
    lokasi_detail: props.izin.lokasi_detail ?? '',
    peralatan: props.izin.peralatan ?? '',
    uraian_pekerjaan: props.izin.uraian_pekerjaan ?? '',
    mulai_at: keInputWaktu(props.izin.mulai_at),
    selesai_at: keInputWaktu(props.izin.selesai_at),
    pekerja: props.izin.pekerja ?? '',
    bahaya: [...(props.izin.bahaya ?? [])],
    bahaya_lain: props.izin.bahaya_lain ?? '',
    pengendalian: [...(props.izin.pengendalian ?? [])],
    pengendalian_tambahan: props.izin.pengendalian_tambahan ?? '',
    apd: [...(props.izin.apd ?? [])],
    uji_gas: Object.fromEntries(kunciGas.map((k) => [k, props.izin.uji_gas?.[k] ?? ''])) as Record<string, string | number>,
    uji_gas_oleh: props.izin.uji_gas_oleh ?? '',
    uji_gas_at: keInputWaktu(props.izin.uji_gas_at),
    dokumen: Object.fromEntries(Object.keys(props.katalog.dokumen).map((k) => [k, null])) as Record<string, File | null>,
    ajukan: false,
});

const dokumenAda = computed(() => Object.fromEntries((props.izin.dokumen ?? []).map((d) => [d.jenis, d])));

// Pemeriksaan di layar agar pemohon tahu yang kurang sebelum menekan ajukan. Server tetap memeriksa ulang.
const cek = computed(() => ({
    pemohon: !!(form.nik && form.nomor_wa && form.departemen),
    pekerjaan: !!(form.lokasi && form.uraian_pekerjaan && form.mulai_at && form.selesai_at && form.pekerja),
    pengendalian: props.aturan.pengendalian.every((p) => form.pengendalian.includes(p)),
    apd: form.apd.length > 0,
    gas:
        !props.aturan.uji_gas ||
        (kunciGas.every((k) => aman(k, form.uji_gas[k]) === true) && !!form.uji_gas_oleh && !!form.uji_gas_at),
    dokumen: Object.keys(props.katalog.dokumen).every((k) => form.dokumen[k] || dokumenAda.value[k]),
}));
const daftarCek = computed(() =>
    [
        { label: 'data pemohon', ok: cek.value.pemohon },
        { label: 'pekerjaan', ok: cek.value.pekerjaan },
        { label: 'pengendalian', ok: cek.value.pengendalian },
        { label: 'APD', ok: cek.value.apd },
        ...(props.aturan.uji_gas ? [{ label: 'uji gas', ok: cek.value.gas }] : []),
        { label: 'dokumen', ok: cek.value.dokumen },
    ],
);
const jumlahLengkap = computed(() => daftarCek.value.filter((c) => c.ok).length);
const siap = computed(() => jumlahLengkap.value === daftarCek.value.length);

function aman(kunci: string, nilai: string | number): boolean | null {
    if (nilai === '' || nilai === null) return null;
    const b = props.katalog.uji_gas[kunci];
    const n = Number(nilai);
    return n >= b.min && n <= b.maks;
}

function simpan(ajukan: boolean) {
    form.ajukan = ajukan;
    if (props.izin.id) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(route('izin.update', props.izin.id), { forceFormData: true, preserveScroll: false });
    } else {
        form.post(route('izin.store'), { forceFormData: true });
    }
}

// Asisten AI: hanya MENYARANKAN bahaya, bahaya lain, pengendalian tambahan, dan APD.
// Pengendalian wajib tetap harus dicentang sendiri setelah dipastikan di lapangan.
const ai = ref<{ memuat: boolean; galat: string; catatan: string; disarankan: string[] }>({ memuat: false, galat: '', catatan: '', disarankan: [] });

async function mintaSaran() {
    ai.value = { memuat: true, galat: '', catatan: '', disarankan: [] };
    try {
        const { data } = await axios.post(route('izin.saran-ai'), {
            jenis: form.jenis,
            uraian_pekerjaan: form.uraian_pekerjaan,
            lokasi: [form.lokasi, form.lokasi_detail].filter(Boolean).join(' — '),
            peralatan: form.peralatan,
        });
        form.bahaya = Array.from(new Set([...form.bahaya, ...data.bahaya]));
        form.apd = Array.from(new Set([...form.apd, ...data.apd]));
        if (data.bahaya_lain) form.bahaya_lain = [form.bahaya_lain, data.bahaya_lain].filter(Boolean).join('\n');
        if (data.pengendalian_tambahan) form.pengendalian_tambahan = [form.pengendalian_tambahan, data.pengendalian_tambahan].filter(Boolean).join('\n');
        ai.value = { memuat: false, galat: '', catatan: data.catatan, disarankan: [...data.bahaya, ...data.apd] };
    } catch (e: any) {
        const pesan = e.response?.data?.errors ? Object.values(e.response.data.errors).flat()[0] : e.response?.data?.message;
        ai.value = { memuat: false, galat: (pesan as string) ?? 'Saran AI gagal dimuat.', catatan: '', disarankan: [] };
    }
}
</script>

<template>
    <AppLayout :judul="izin.id ? 'Ubah Izin' : 'Registrasi Izin'">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">{{ aturan.label }} / <em class="text-biru-judul">{{ aturan.label_en }}</em></h1>
                <p class="text-slate-600">
                    {{ aturan.deskripsi }} Durasi maksimal {{ aturan.durasi_maks_jam }} jam.
                    <Link :href="route('jenis.show', aturan.kunci)" class="text-merek-700 hover:underline">Baca persyaratan</Link>
                </p>
            </div>
            <Link v-if="izin.id" :href="route('izin.show', izin.id)" class="tombol-sekunder">Kembali ke izin</Link>
        </div>

        <div v-if="penolakan" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            <strong>Ditolak oleh {{ penolakan.oleh }}:</strong> {{ penolakan.catatan }}
        </div>
        <div v-if="masalah.length" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            <strong>Izin belum bisa diajukan:</strong>
            <ul class="mt-1 list-disc pl-5">
                <li v-for="m in masalah" :key="m">{{ m }}</li>
            </ul>
        </div>
        <div v-if="Object.keys(form.errors).length" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            Periksa kembali isian yang ditandai merah.
        </div>

        <form class="space-y-5" @submit.prevent="simpan(true)">
            <section class="panel">
                <JudulLangkah :nomor="1" judul="Data pemohon" :selesai="cek.pemohon" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="nama">Nama / <em>Your name</em></label>
                        <input id="nama" :value="user.name" readonly class="masukan bg-slate-100" />
                        <p class="mt-1 text-xs text-slate-500">Diambil dari akun Anda.</p>
                    </div>
                    <div>
                        <label class="label" for="nik">NIK / <em>Your ID</em></label>
                        <input id="nik" v-model="form.nik" required class="masukan" />
                        <InputError :message="form.errors.nik" />
                    </div>
                    <div>
                        <label class="label" for="nomor_wa">Nomor WA / <em>WhatsApp</em></label>
                        <input id="nomor_wa" v-model="form.nomor_wa" required inputmode="tel" placeholder="08xxxxxxxxxx" class="masukan" />
                        <InputError :message="form.errors.nomor_wa" />
                    </div>
                    <div>
                        <label class="label" for="departemen">Departemen pelapor / <em>Your department</em></label>
                        <select id="departemen" v-model="form.departemen" required class="masukan">
                            <option value="" disabled>Pilih</option>
                            <option v-for="d in katalog.departemen" :key="d">{{ d }}</option>
                        </select>
                        <InputError :message="form.errors.departemen" />
                    </div>
                </div>
            </section>

            <section class="panel">
                <JudulLangkah :nomor="2" judul="Pekerjaan" keterangan="Lokasi, jadwal, dan orang yang bekerja." :selesai="cek.pekerjaan" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="lokasi">Lokasi / <em>Location</em></label>
                        <select id="lokasi" v-model="form.lokasi" required class="masukan">
                            <option value="" disabled>Pilih</option>
                            <option v-for="l in katalog.lokasi" :key="l">{{ l }}</option>
                        </select>
                        <InputError :message="form.errors.lokasi" />
                    </div>
                    <div>
                        <label class="label" for="lokasi_detail">Detail lokasi</label>
                        <input id="lokasi_detail" v-model="form.lokasi_detail" class="masukan" placeholder="Contoh: Front loading blok B, tangki T-201" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="peralatan">Peralatan yang digunakan</label>
                        <input id="peralatan" v-model="form.peralatan" class="masukan" placeholder="Mesin las, chainsaw, crane 50 ton, dll." />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="uraian">Uraian pekerjaan</label>
                        <textarea id="uraian" v-model="form.uraian_pekerjaan" required rows="3" class="masukan" />
                        <InputError :message="form.errors.uraian_pekerjaan" />
                    </div>
                    <div>
                        <label class="label" for="mulai">Mulai</label>
                        <input id="mulai" v-model="form.mulai_at" type="datetime-local" required class="masukan" />
                        <InputError :message="form.errors.mulai_at" />
                    </div>
                    <div>
                        <label class="label" for="selesai">Selesai</label>
                        <input id="selesai" v-model="form.selesai_at" type="datetime-local" required class="masukan" />
                        <InputError :message="form.errors.selesai_at" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="pekerja">Nama pekerja</label>
                        <textarea id="pekerja" v-model="form.pekerja" required rows="3" placeholder="Satu nama per baris" class="masukan" />
                        <p class="mt-1 text-xs text-slate-500">Tulis semua orang yang akan bekerja di bawah izin ini, satu per baris.</p>
                        <InputError :message="form.errors.pekerja" />
                    </div>
                </div>
            </section>

            <section v-if="aiTersedia" class="rounded-xl border border-violet-200 bg-violet-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 font-bold text-violet-900"><Ikon nama="ai" /> Saran AI (Google Gemini)</h2>
                        <p class="text-sm text-violet-800">
                            AI membaca uraian pekerjaan lalu menyarankan bahaya, pengendalian tambahan, dan APD. Periksa kembali sarannya. Pengendalian wajib tetap Anda centang
                            sendiri setelah dipastikan di lapangan.
                        </p>
                    </div>
                    <button type="button" class="tombol border-violet-700 bg-violet-700 hover:bg-violet-600" :disabled="ai.memuat" @click="mintaSaran">
                        <Ikon nama="ai" kelas="h-4 w-4" />{{ ai.memuat ? 'Meminta saran…' : 'Minta saran AI' }}
                    </button>
                </div>
                <p v-if="ai.galat" class="mt-2 text-sm text-red-700">{{ ai.galat }}</p>
                <p v-if="ai.catatan" class="mt-3 rounded-lg bg-white px-3 py-2 text-sm text-violet-900">
                    <strong>Catatan AI:</strong> {{ ai.catatan }} Saran sudah ditambahkan ke bagian 3–5 dan ditandai <span class="font-semibold">AI</span>.
                </p>
            </section>

            <section class="panel">
                <JudulLangkah :nomor="3" judul="Identifikasi bahaya" keterangan="Centang bahaya yang ada di pekerjaan ini." :selesai="form.bahaya.length > 0 || !!form.bahaya_lain" />
                <div class="grid gap-2 sm:grid-cols-2">
                <label v-for="b in aturan.bahaya" :key="b" class="pilihan">
                    <input v-model="form.bahaya" type="checkbox" :value="b" />
                    <span>{{ b }} <span v-if="ai.disarankan.includes(b)" class="rounded bg-violet-100 px-1 text-xs font-semibold text-violet-800">AI</span></span>
                </label>
                </div>
                <label class="label mt-4" for="bahaya_lain">Bahaya lain</label>
                <textarea id="bahaya_lain" v-model="form.bahaya_lain" rows="3" class="masukan" />
            </section>

            <section class="panel">
                <JudulLangkah :nomor="4" judul="Pengendalian wajib" keterangan="Semua butir harus sudah dipastikan di lapangan sebelum izin dapat diajukan." :selesai="cek.pengendalian" />
                <div class="grid gap-2">
                    <label v-for="p in aturan.pengendalian" :key="p" class="pilihan">
                        <input v-model="form.pengendalian" type="checkbox" :value="p" />
                        <span>{{ p }}</span>
                    </label>
                </div>
                <p class="mt-2 text-xs font-semibold" :class="cek.pengendalian ? 'text-green-700' : 'text-slate-500'">
                    {{ form.pengendalian.length }} dari {{ aturan.pengendalian.length }} dipastikan
                </p>
                <label class="label mt-4" for="pengendalian_tambahan">Pengendalian tambahan</label>
                <textarea id="pengendalian_tambahan" v-model="form.pengendalian_tambahan" rows="3" class="masukan" />
            </section>

            <section class="panel">
                <JudulLangkah :nomor="5" judul="Alat pelindung diri (APD)" keterangan="Pilih semua APD yang wajib dipakai." :selesai="cek.apd" />
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <label v-for="a in katalog.apd" :key="a" class="pilihan">
                        <input v-model="form.apd" type="checkbox" :value="a" />
                        <span>{{ a }} <span v-if="ai.disarankan.includes(a)" class="rounded bg-violet-100 px-1 text-xs font-semibold text-violet-800">AI</span></span>
                    </label>
                </div>
            </section>

            <section v-if="aturan.uji_gas" class="panel">
                <JudulLangkah :nomor="6" judul="Uji gas" keterangan="Izin tidak dapat diajukan bila salah satu hasil di luar batas aman." :selesai="cek.gas" />
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(b, k) in katalog.uji_gas" :key="k">
                        <label class="label" :for="`gas_${k}`">{{ b.label }}</label>
                        <input
                            :id="`gas_${k}`"
                            v-model="form.uji_gas[k]"
                            type="number"
                            step="0.1"
                            min="0"
                            :class="['masukan', aman(k as string, form.uji_gas[k]) === false ? 'border-red-500 ring-1 ring-red-500' : '']"
                        />
                        <p :class="['mt-1 text-xs', aman(k as string, form.uji_gas[k]) === false ? 'font-semibold text-red-700' : 'text-slate-500']">
                            Aman: {{ b.min }}–{{ b.maks }} {{ b.satuan }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="uji_gas_oleh">Diuji oleh</label>
                        <input id="uji_gas_oleh" v-model="form.uji_gas_oleh" class="masukan" />
                    </div>
                    <div>
                        <label class="label" for="uji_gas_at">Waktu pengujian</label>
                        <input id="uji_gas_at" v-model="form.uji_gas_at" type="datetime-local" class="masukan" />
                    </div>
                </div>
            </section>

            <section class="panel">
                <JudulLangkah
                    :nomor="aturan.uji_gas ? 7 : 6"
                    judul="Dokumen pendukung"
                    :keterangan="`1 berkas PDF atau Word per dokumen, maksimal ${katalog.dokumen_maks_kb / 1024} MB. Ketiganya wajib.`"
                    :selesai="cek.dokumen"
                />
                <div
                    v-for="(label, k) in katalog.dokumen"
                    :key="k"
                    :class="[
                        'mb-2 flex flex-wrap items-center justify-between gap-3 rounded-xl border px-4 py-3 last:mb-0',
                        form.dokumen[k] || dokumenAda[k] ? 'border-green-200 bg-green-50/50' : 'border-dashed border-slate-300',
                    ]"
                >
                    <div>
                        <div class="font-semibold">{{ label }}</div>
                        <div class="text-sm">
                            <template v-if="form.dokumen[k]">
                                <span class="font-semibold text-blue-700">Akan diunggah: {{ form.dokumen[k]!.name }}</span>
                                <span class="text-slate-500"> ({{ Math.max(1, Math.round(form.dokumen[k]!.size / 1024)) }} KB)</span>
                            </template>
                            <template v-else-if="dokumenAda[k]">
                                <span class="text-green-700">✓</span>
                                <a :href="dokumenAda[k].url" class="text-merek-700 hover:underline">{{ dokumenAda[k].nama_asli }}</a>
                                <span class="text-slate-500"> ({{ dokumenAda[k].ukuran }}). Pilih berkas baru untuk mengganti.</span>
                            </template>
                            <span v-else class="text-slate-500">Belum diunggah</span>
                        </div>
                        <InputError :message="(form.errors as Record<string, string>)[`dokumen.${k}`]" />
                    </div>
                    <label class="tombol-sekunder cursor-pointer px-3 py-2 focus-within:ring-2 focus-within:ring-merek-500">
                        <Ikon nama="unduh" kelas="h-4 w-4 rotate-180" />{{ form.dokumen[k] || dokumenAda[k] ? 'Ganti berkas' : 'Pilih berkas' }}
                        <input
                            type="file"
                            class="sr-only"
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            @input="form.dokumen[k] = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                    </label>
                </div>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mt-2 w-full">{{ form.progress.percentage }}%</progress>
            </section>

            <div class="sticky bottom-20 z-20 rounded-2xl border border-slate-200 bg-white/95 px-3 py-2.5 shadow-angkat backdrop-blur sm:p-4 lg:bottom-4">
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span :class="siap ? 'text-green-700' : 'text-slate-600'">{{ siap ? 'Siap diajukan' : `Lengkap ${jumlahLengkap}/${daftarCek.length}` }}</span>
                            <span v-if="!siap" class="hidden truncate pl-2 text-slate-400 sm:inline">Kurang: {{ daftarCek.filter((c) => !c.ok).map((c) => c.label).join(', ') }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div :class="['h-full rounded-full transition-all duration-500', siap ? 'bg-green-500' : 'bg-merek-500']" :style="{ width: `${(jumlahLengkap / daftarCek.length) * 100}%` }" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="tombol-sekunder px-3" :disabled="form.processing" @click="simpan(false)">
                            <span class="sm:hidden">Draf</span><span class="hidden sm:inline">Simpan draf</span>
                        </button>
                        <button type="submit" class="tombol px-3 sm:px-4" :disabled="form.processing">
                            <Ikon nama="kirim" kelas="h-4 w-4" /><span class="sm:hidden">Ajukan</span><span class="hidden sm:inline">Simpan & ajukan</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
