<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ departemen: string[] }>();

const form = useForm({
    name: '',
    nik: '',
    nomor_wa: '',
    departemen: '',
    jabatan: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <GuestLayout>
        <Head title="Buat Akun" />
        <h1 class="text-lg font-bold">Buat akun pekerja</h1>
        <p class="mb-4 text-sm text-slate-600">Akun dipakai untuk mengajukan izin dan memantau status persetujuannya.</p>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="label" for="name">Nama / <em>Your name</em></label>
                <input id="name" v-model="form.name" required autofocus autocomplete="name" class="masukan" />
                <InputError :message="form.errors.name" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="nik">NIK / <em>Your ID</em></label>
                    <input id="nik" v-model="form.nik" required class="masukan" />
                    <InputError :message="form.errors.nik" />
                </div>
                <div>
                    <label class="label" for="nomor_wa">Nomor WA</label>
                    <input id="nomor_wa" v-model="form.nomor_wa" required inputmode="tel" placeholder="08xxxxxxxxxx" class="masukan" />
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
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" v-model="form.email" type="email" required autocomplete="username" class="masukan" />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">Kata sandi</label>
                    <input id="password" v-model="form.password" type="password" required autocomplete="new-password" class="masukan" />
                    <InputError :message="form.errors.password" />
                </div>
                <div>
                    <label class="label" for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="masukan" />
                </div>
            </div>
            <button type="submit" class="tombol w-full" :disabled="form.processing">Buat akun</button>
            <p class="text-center text-sm">Sudah punya akun? <Link :href="route('login')" class="font-semibold text-merek-700 hover:underline">Masuk</Link></p>
        </form>
    </GuestLayout>
</template>
