<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import type { PageProps } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps<{ mustVerifyEmail?: boolean; status?: string; departemen: string[] }>();

const user = usePage<PageProps>().props.auth.user!;

const form = useForm({
    name: user.name,
    email: user.email,
    nik: user.nik ?? '',
    nomor_wa: user.nomor_wa ?? '',
    departemen: user.departemen ?? '',
    jabatan: user.jabatan ?? '',
});
</script>

<template>
    <section>
        <h2 class="judul-bagian">Data diri</h2>
        <p class="mb-4 text-sm text-slate-600">Data ini otomatis mengisi formulir registrasi izin. Peran Anda: <strong>{{ user.label_peran }}</strong>.</p>

        <form class="space-y-4" @submit.prevent="form.patch(route('profile.update'))">
            <div>
                <label class="label" for="name">Nama</label>
                <input id="name" v-model="form.name" required autocomplete="name" class="masukan" />
                <InputError :message="form.errors.name" />
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" v-model="form.email" type="email" required autocomplete="username" class="masukan" />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="nik">NIK</label>
                    <input id="nik" v-model="form.nik" required class="masukan" />
                    <InputError :message="form.errors.nik" />
                </div>
                <div>
                    <label class="label" for="nomor_wa">Nomor WA</label>
                    <input id="nomor_wa" v-model="form.nomor_wa" required inputmode="tel" class="masukan" />
                    <InputError :message="form.errors.nomor_wa" />
                </div>
                <div>
                    <label class="label" for="departemen">Departemen</label>
                    <select id="departemen" v-model="form.departemen" required class="masukan">
                        <option value="" disabled>Pilih</option>
                        <option v-for="d in departemen" :key="d">{{ d }}</option>
                    </select>
                    <InputError :message="form.errors.departemen" />
                </div>
                <div>
                    <label class="label" for="jabatan">Jabatan</label>
                    <input id="jabatan" v-model="form.jabatan" class="masukan" />
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="tombol" :disabled="form.processing">Simpan</button>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">Tersimpan.</span>
            </div>
        </form>
    </section>
</template>
