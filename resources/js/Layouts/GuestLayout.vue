<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
        <Link href="/" class="flex flex-col items-center gap-3">
            <img src="/img/logo.png" alt="Logo" class="h-24 w-24 rounded-2xl" />
            <span class="text-xl font-bold">Izin Kerja Khusus</span>
        </Link>
        <div class="mt-6 w-full max-w-md rounded-xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <slot />
        </div>
    </div>
</template>
