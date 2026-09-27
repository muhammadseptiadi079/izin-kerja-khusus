<script setup lang="ts">
// Isian evaluasi pasca pekerjaan: dipakai saat pemohon mengajukan penutupan
// (lengkap) dan saat penyetuju menghentikan pekerjaan (cukup bagian insiden).
import InputError from '@/Components/InputError.vue';
import type { InertiaForm } from '@inertiajs/vue3';

type FormEvaluasi = {
    ada_insiden: boolean | null;
    kategori_insiden: string;
    uraian_insiden: string;
    tindakan_insiden: string;
    mulai_aktual_at: string;
    selesai_aktual_at: string;
    pemeriksaan_penutupan: string[];
};

defineProps<{
    form: InertiaForm<FormEvaluasi & { aksi: string; catatan: string }>;
    mode: 'ajukan_penutupan' | 'hentikan';
    insiden: Record<string, string>;
    pemeriksaan: string[];
    jadwal: { mulai: string; selesai: string };
}>();
</script>

<template>
    <div class="space-y-5">
        <div v-if="mode === 'ajukan_penutupan'">
            <h3 class="font-bold">Jam kerja sebenarnya</h3>
            <p class="mb-3 text-sm text-slate-500">Diajukan: {{ jadwal.mulai }} s.d. {{ jadwal.selesai }}. Isi jam yang sebenarnya terjadi di lapangan.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mulai_aktual">Mulai bekerja</label>
                    <input id="mulai_aktual" v-model="form.mulai_aktual_at" type="datetime-local" required class="masukan" />
                    <InputError :message="form.errors.mulai_aktual_at" />
                </div>
                <div>
                    <label class="label" for="selesai_aktual">Selesai bekerja</label>
                    <input id="selesai_aktual" v-model="form.selesai_aktual_at" type="datetime-local" required class="masukan" />
                    <InputError :message="form.errors.selesai_aktual_at" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-bold">Apakah terjadi insiden selama pekerjaan?</h3>
            <p class="mb-3 text-sm text-slate-500">Termasuk nyaris celaka. Laporan jujur membantu mencegah kejadian berikutnya.</p>
            <div class="grid grid-cols-2 gap-2 sm:max-w-md">
                <label class="pilihan">
                    <input v-model="form.ada_insiden" type="radio" :value="false" name="ada_insiden" class="mt-0.5 h-4.5 w-4.5 border-slate-300 text-green-600 focus:ring-green-500" />
                    <span class="font-semibold">Tidak ada</span>
                </label>
                <label class="pilihan has-checked:border-red-500 has-checked:bg-red-50 has-checked:ring-red-500/30">
                    <input v-model="form.ada_insiden" type="radio" :value="true" name="ada_insiden" class="mt-0.5 h-4.5 w-4.5 border-slate-300 text-red-600 focus:ring-red-500" />
                    <span class="font-semibold">Ada insiden</span>
                </label>
            </div>
            <InputError :message="form.errors.ada_insiden" />

            <div v-if="form.ada_insiden" class="mt-4 space-y-4 rounded-xl border border-red-200 bg-red-50/50 p-4">
                <div>
                    <label class="label" for="kategori_insiden">Kategori insiden</label>
                    <select id="kategori_insiden" v-model="form.kategori_insiden" required class="masukan">
                        <option value="" disabled>Pilih</option>
                        <option v-for="(label, k) in insiden" :key="k" :value="k">{{ label }}</option>
                    </select>
                    <InputError :message="form.errors.kategori_insiden" />
                </div>
                <div>
                    <label class="label" for="uraian_insiden">Kronologi singkat</label>
                    <textarea id="uraian_insiden" v-model="form.uraian_insiden" rows="3" class="masukan" placeholder="Apa yang terjadi, di mana, siapa yang terlibat" />
                    <InputError :message="form.errors.uraian_insiden" />
                </div>
                <div>
                    <label class="label" for="tindakan_insiden">Tindakan yang sudah diambil</label>
                    <textarea id="tindakan_insiden" v-model="form.tindakan_insiden" rows="2" class="masukan" placeholder="Pertolongan, pengamanan area, perbaikan" />
                    <InputError :message="form.errors.tindakan_insiden" />
                </div>
            </div>
        </div>

        <div v-if="mode === 'ajukan_penutupan'">
            <h3 class="font-bold">Kondisi area setelah pekerjaan</h3>
            <p class="mb-3 text-sm text-slate-500">Semua butir harus dipastikan sebelum izin ditutup.</p>
            <div class="grid gap-2">
                <label v-for="p in pemeriksaan" :key="p" class="pilihan">
                    <input v-model="form.pemeriksaan_penutupan" type="checkbox" :value="p" />
                    <span>{{ p }}</span>
                </label>
            </div>
            <InputError :message="form.errors.pemeriksaan_penutupan" />
        </div>
    </div>
</template>
