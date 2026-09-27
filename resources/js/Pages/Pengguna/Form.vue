<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    pengguna: { id: number | null; name?: string; email?: string; peran: string; nik?: string | null; nomor_wa?: string | null; jabatan?: string | null; departemen?: string | null; aktif: boolean };
    peranList: Record<string, string>;
    departemen: string[];
}>();

const form = useForm({
    name: props.pengguna.name ?? '',
    email: props.pengguna.email ?? '',
    peran: props.pengguna.peran,
    nik: props.pengguna.nik ?? '',
    nomor_wa: props.pengguna.nomor_wa ?? '',
    jabatan: props.pengguna.jabatan ?? '',
    departemen: props.pengguna.departemen ?? '',
    password: '',
    aktif: props.pengguna.aktif,
});

const simpan = () => (props.pengguna.id ? form.put(route('pengguna.update', props.pengguna.id)) : form.post(route('pengguna.store')));
</script>

<template>
    <AppLayout :judul="pengguna.id ? 'Ubah Pengguna' : 'Tambah Pengguna'">
        <h1 class="mb-5 text-2xl font-bold">{{ pengguna.id ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
        <form class="panel max-w-2xl space-y-4" @submit.prevent="simpan">
            <div><label class="label" for="name">Nama</label><input id="name" v-model="form.name" required class="masukan" /><InputError :message="form.errors.name" /></div>
            <div><label class="label" for="email">Email</label><input id="email" v-model="form.email" type="email" required class="masukan" /><InputError :message="form.errors.email" /></div>
            <div>
                <label class="label" for="peran">Peran</label>
                <select id="peran" v-model="form.peran" class="masukan">
                    <option v-for="(label, k) in peranList" :key="k" :value="k">{{ label }}</option>
                </select>
                <InputError :message="form.errors.peran" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label" for="nik">NIK</label><input id="nik" v-model="form.nik" class="masukan" /><InputError :message="form.errors.nik" /></div>
                <div><label class="label" for="wa">Nomor WA</label><input id="wa" v-model="form.nomor_wa" class="masukan" /><InputError :message="form.errors.nomor_wa" /></div>
                <div><label class="label" for="jabatan">Jabatan</label><input id="jabatan" v-model="form.jabatan" class="masukan" /></div>
                <div>
                    <label class="label" for="departemen">Departemen</label>
                    <select id="departemen" v-model="form.departemen" class="masukan">
                        <option value="">—</option>
                        <option v-for="d in departemen" :key="d">{{ d }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="label" for="password">Kata sandi</label>
                <input id="password" v-model="form.password" type="password" autocomplete="new-password" :required="!pengguna.id" class="masukan" />
                <p v-if="pengguna.id" class="mt-1 text-xs text-slate-500">Kosongkan bila tidak diganti.</p>
                <InputError :message="form.errors.password" />
            </div>
            <label class="flex items-center gap-2"><input v-model="form.aktif" type="checkbox" class="rounded text-merek-600" /> Akun aktif (dapat masuk)</label>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="tombol" :disabled="form.processing">Simpan</button>
                <Link :href="route('pengguna.index')" class="tombol-sekunder">Batal</Link>
            </div>
        </form>
    </AppLayout>
</template>
