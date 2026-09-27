<script setup lang="ts">
import Ikon from '@/Components/Ikon.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineProps<{ judul?: string }>();

const halaman = usePage<PageProps>();
const user = computed(() => halaman.props.auth.user);
const menuTerbuka = ref(false);

watch(() => halaman.url, () => (menuTerbuka.value = false));

const menu = computed(() => {
    const daftar = [
        { label: 'Home', rute: 'beranda', ikon: 'rumah', aktif: ['beranda', 'jenis.show'] },
        { label: 'Registrasi', rute: 'izin.create', ikon: 'formulir', aktif: ['izin.create'] },
        { label: 'Monitoring & Evaluasi', rute: 'monitoring', ikon: 'grafik', aktif: ['monitoring'] },
    ];
    if (user.value) {
        daftar.push(
            { label: 'Dasbor', rute: 'dasbor', ikon: 'dasbor', aktif: ['dasbor'] },
            { label: 'Daftar Izin', rute: 'izin.index', ikon: 'daftar', aktif: ['izin.index', 'izin.show', 'izin.edit'] },
        );
        if (user.value.peran === 'admin') {
            daftar.push({ label: 'Pengguna', rute: 'pengguna.index', ikon: 'pengguna', aktif: ['pengguna.*'] });
        }
    }
    return daftar;
});

const aktif = (pola: string[]) => pola.some((p) => route().current(p));
</script>

<template>
    <Head :title="judul" />
    <div class="min-h-screen">
        <header class="sticky top-0 z-30 bg-arang text-white shadow">
            <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-2.5">
                <Link :href="route('beranda')" class="flex shrink-0 items-center gap-2.5 font-bold">
                    <img src="/img/logo.png" alt="" class="h-9 w-9 rounded-md" />
                    <span>Izin Kerja Khusus</span>
                </Link>

                <nav class="hidden flex-1 items-center gap-1 lg:flex">
                    <Link
                        v-for="m in menu"
                        :key="m.rute"
                        :href="route(m.rute)"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm transition',
                            aktif(m.aktif) ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white',
                        ]"
                    >
                        {{ m.label }}
                    </Link>
                </nav>

                <div class="ml-auto hidden items-center gap-3 lg:flex">
                    <template v-if="user">
                        <Link :href="route('profile.edit')" class="text-right text-xs leading-tight text-slate-300 hover:text-white">
                            <div class="font-semibold text-white">{{ user.name }}</div>
                            {{ user.label_peran }}
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="rounded-md border border-slate-600 px-2.5 py-1 text-sm hover:bg-white/10">
                            Keluar
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="tombol py-1.5">Masuk</Link>
                    </template>
                </div>

                <button class="ml-auto rounded-md p-1.5 hover:bg-white/10 lg:hidden" :aria-expanded="menuTerbuka" aria-label="Menu" @click="menuTerbuka = !menuTerbuka">
                    <Ikon :nama="menuTerbuka ? 'tutup' : 'menu'" kelas="h-6 w-6" />
                </button>
            </div>

            <nav v-if="menuTerbuka" class="border-t border-white/10 px-4 pb-3 lg:hidden">
                <Link
                    v-for="m in menu"
                    :key="m.rute"
                    :href="route(m.rute)"
                    :class="['flex items-center gap-3 rounded-md px-3 py-2.5', aktif(m.aktif) ? 'bg-white/10' : 'text-slate-300']"
                >
                    <Ikon :nama="m.ikon" />{{ m.label }}
                </Link>
                <div class="mt-2 border-t border-white/10 pt-2">
                    <template v-if="user">
                        <Link :href="route('profile.edit')" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-slate-300">
                            <Ikon nama="profil" />{{ user.name }} · {{ user.label_peran }}
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-slate-300">
                            <Ikon nama="keluar" />Keluar
                        </Link>
                    </template>
                    <Link v-else :href="route('login')" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-slate-300">
                        <Ikon nama="profil" />Masuk
                    </Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 pt-6 pb-16">
            <div v-if="halaman.props.pesan" class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ halaman.props.pesan }}</div>
            <slot />
        </main>
    </div>
</template>
