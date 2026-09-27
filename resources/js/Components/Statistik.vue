<script setup lang="ts">
defineProps<{ angka: number | string | null; label: string; bahaya?: boolean }>();
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div :class="['text-3xl leading-tight font-bold', bahaya ? 'text-red-700' : 'text-slate-900']">{{ angka ?? '—' }}</div>
        <div class="text-sm text-slate-600">{{ label }}</div>
    </div>
</template>
