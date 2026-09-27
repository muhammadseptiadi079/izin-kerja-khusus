<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{ mustVerifyEmail?: boolean; status?: string; departemen: string[] }>();
</script>

<template>
    <AppLayout judul="Profil">
        <h1 class="judul-halaman mb-5">Profil</h1>
        <div class="max-w-2xl space-y-5">
            <div class="panel">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" :departemen="departemen" />
            </div>
            <div class="panel">
                <UpdatePasswordForm />
            </div>
            <p class="text-sm text-slate-500">
                Akun tidak bisa dihapus sendiri karena riwayat izin harus tetap tercatat. Hubungi administrator untuk menonaktifkan akun.
            </p>
        </div>
    </AppLayout>
</template>
