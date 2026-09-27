<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ canResetPassword?: boolean; status?: string }>();

const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />
        <p class="mb-4 text-center text-sm text-slate-600">Masuk untuk mengajukan atau menyetujui izin kerja berisiko tinggi.</p>
        <div v-if="status" class="mb-4 text-sm font-medium text-green-700">{{ status }}</div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" class="masukan" />
                <InputError :message="form.errors.email" />
            </div>
            <div>
                <label class="label" for="password">Kata sandi</label>
                <input id="password" v-model="form.password" type="password" required autocomplete="current-password" class="masukan" />
                <InputError :message="form.errors.password" />
            </div>
            <label class="flex items-center gap-2 text-sm"><input v-model="form.remember" type="checkbox" class="rounded text-merek-600" /> Ingat saya</label>
            <button type="submit" class="tombol w-full" :disabled="form.processing">Masuk</button>
            <div class="flex flex-wrap justify-between gap-2 text-sm">
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-slate-600 underline hover:text-slate-900">Lupa kata sandi?</Link>
                <Link :href="route('register')" class="font-semibold text-merek-700 hover:underline">Buat akun pekerja</Link>
            </div>
        </form>
    </GuestLayout>
</template>
