<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    pengguna: { id: number; name: string; email: string; nik: string | null; jabatan: string | null; departemen: string | null; aktif: boolean; label_peran: string }[];
}>();
</script>

<template>
    <AppLayout judul="Pengguna">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Pengguna</h1>
                <p class="text-slate-600">Peran menentukan tahap persetujuan yang dapat diputuskan setiap orang.</p>
            </div>
            <Link :href="route('pengguna.create')" class="tombol">+ Tambah pengguna</Link>
        </div>
        <div class="panel overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs tracking-wide text-slate-500 uppercase">
                        <th class="px-2 py-2">Nama</th>
                        <th class="px-2 py-2">NIK</th>
                        <th class="px-2 py-2">Email</th>
                        <th class="px-2 py-2">Peran</th>
                        <th class="px-2 py-2">Jabatan / Departemen</th>
                        <th class="px-2 py-2">Status</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in pengguna" :key="p.id" class="border-b border-slate-100 last:border-0">
                        <td class="px-2 py-2.5 font-semibold">{{ p.name }}</td>
                        <td class="px-2 py-2.5">{{ p.nik || '—' }}</td>
                        <td class="px-2 py-2.5">{{ p.email }}</td>
                        <td class="px-2 py-2.5">{{ p.label_peran }}</td>
                        <td class="px-2 py-2.5">{{ [p.jabatan, p.departemen].filter(Boolean).join(' · ') || '—' }}</td>
                        <td class="px-2 py-2.5">
                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', p.aktif ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600']">
                                {{ p.aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-right"><Link :href="route('pengguna.edit', p.id)" class="tombol-sekunder px-3 py-1 text-xs">Ubah</Link></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
