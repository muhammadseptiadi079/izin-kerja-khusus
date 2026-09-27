<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
import Ikon from '@/Components/Ikon.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{ judul?: string }>();

type ItemMenu = { label: string; rute: string; hash?: string; ikon?: string; aktif: string[]; utama?: boolean; lencana?: boolean };

const halaman = usePage<PageProps>();
const user = computed(() => halaman.props.auth.user);
const jumlah = computed(() => halaman.props.jumlahTindakan ?? 0);
const angka = computed(() => (jumlah.value > 99 ? '99+' : String(jumlah.value)));

// Setelah login, "Beranda" adalah dasbor pribadi; halaman penjelasan jenis izin tetap di "/".
const ruteBeranda = computed(() => (user.value ? 'dasbor' : 'beranda'));
const aktif = (...pola: string[]) => pola.some((p) => route().current(p));

const menuAtas = computed<ItemMenu[]>(() =>
    user.value
        ? [
              { label: 'Beranda', rute: 'dasbor', aktif: ['dasbor'] },
              { label: 'Izin', rute: 'izin.index', aktif: ['izin.index', 'izin.show', 'izin.edit'] },
              { label: 'Monitoring', rute: 'monitoring', aktif: ['monitoring'] },
              { label: 'Jenis Izin', rute: 'beranda', aktif: ['beranda', 'jenis.show'] },
          ]
        : [
              { label: 'Beranda', rute: 'beranda', aktif: ['beranda'] },
              { label: 'Jenis Izin', rute: 'beranda', hash: '#jenis', aktif: ['jenis.show'] },
          ],
);

const menuBawah = computed<ItemMenu[]>(() =>
    user.value
        ? [
              { label: 'Beranda', rute: 'dasbor', ikon: 'rumah', aktif: ['dasbor', 'beranda', 'jenis.show'] },
              { label: 'Izin', rute: 'izin.index', ikon: 'daftar', aktif: ['izin.index', 'izin.show', 'izin.edit'] },
              { label: 'Ajukan', rute: 'izin.create', ikon: 'tambah', utama: true, aktif: ['izin.create'] },
              { label: 'Tindakan', rute: 'tindakan', ikon: 'lonceng', lencana: true, aktif: ['tindakan'] },
              { label: 'Akun', rute: 'akun', ikon: 'profil', aktif: ['akun', 'profile.edit', 'monitoring', 'pengguna.*'] },
          ]
        : [
              { label: 'Beranda', rute: 'beranda', ikon: 'rumah', aktif: ['beranda'] },
              { label: 'Jenis Izin', rute: 'beranda', hash: '#jenis', ikon: 'buku', aktif: ['jenis.show'] },
              { label: 'Ajukan', rute: 'izin.create', ikon: 'tambah', utama: true, aktif: [] },
              { label: 'Daftar', rute: 'register', ikon: 'formulir', aktif: ['register'] },
              { label: 'Masuk', rute: 'login', ikon: 'masuk', aktif: ['login'] },
          ],
);
</script>

<template>
    <Head :title="judul" />
    <div class="min-h-screen">
        <!-- Bilah atas: lengkap di laptop, ringkas di HP -->
        <header class="sticky top-0 z-30 bg-arang text-white shadow">
            <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-2.5">
                <Link :href="route(ruteBeranda)" class="flex shrink-0 items-center gap-2.5 font-bold">
                    <img src="/img/logo.png" alt="" class="h-9 w-9 rounded-md" />
                    <span>Izin Kerja Khusus</span>
                </Link>

                <nav class="ml-4 hidden items-center gap-1 lg:flex">
                    <Link
                        v-for="m in menuAtas"
                        :key="m.label"
                        :href="route(m.rute) + (m.hash ?? '')"
                        :class="['rounded-md px-3 py-1.5 text-sm transition', aktif(...m.aktif) ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white']"
                    >
                        {{ m.label }}
                    </Link>
                </nav>

                <div class="ml-auto hidden items-center gap-2 lg:flex">
                    <template v-if="user">
                        <Link :href="route('izin.create')" class="tombol py-1.5"><Ikon nama="tambah" kelas="h-4 w-4" />Ajukan izin</Link>
                        <Link
                            :href="route('tindakan')"
                            :class="['relative rounded-md p-2 transition hover:bg-white/10', aktif('tindakan') ? 'bg-white/10' : '']"
                            :aria-label="`Tindakan, ${jumlah} menunggu`"
                            title="Perlu tindakan"
                        >
                            <Ikon nama="lonceng" />
                            <span v-if="jumlah" class="absolute -top-0.5 -right-0.5 min-w-5 rounded-full bg-red-600 px-1 text-center text-[11px] leading-5 font-bold">{{ angka }}</span>
                        </Link>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 rounded-md py-1 pr-1.5 pl-2 text-left hover:bg-white/10">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-merek-600 text-sm font-bold">{{ user.name.charAt(0) }}</span>
                                    <span class="text-xs leading-tight">
                                        <span class="block font-semibold">{{ user.name }}</span>
                                        <span class="text-slate-400">{{ user.label_peran }}</span>
                                    </span>
                                    <Ikon nama="bawah" kelas="h-4 w-4 text-slate-400" />
                                </button>
                            </template>
                            <template #content>
                                <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Profil</Link>
                                <Link v-if="user.peran === 'admin'" :href="route('pengguna.index')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kelola pengguna</Link>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-slate-100">Keluar</Link>
                            </template>
                        </Dropdown>
                    </template>
                    <template v-else>
                        <Link :href="route('register')" class="rounded-md px-3 py-1.5 text-sm text-slate-300 hover:text-white">Daftar</Link>
                        <Link :href="route('login')" class="tombol py-1.5">Masuk</Link>
                    </template>
                </div>

                <!-- HP: hanya nama peran di kanan atas, menu ada di bawah -->
                <span v-if="user" class="ml-auto truncate text-right text-xs text-slate-400 lg:hidden">{{ user.label_peran }}</span>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 pt-6 pb-28 lg:pb-16">
            <div v-if="halaman.props.pesan" class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ halaman.props.pesan }}</div>
            <slot />
        </main>

        <!-- Bilah menu bawah untuk HP: terjangkau jempol, tanpa membuka menu -->
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(0,0,0,.06)] backdrop-blur lg:hidden">
            <ul class="mx-auto grid max-w-md grid-cols-5">
                <li v-for="m in menuBawah" :key="m.label">
                    <Link
                        :href="route(m.rute) + (m.hash ?? '')"
                        :class="[
                            'relative flex flex-col items-center gap-0.5 px-1 pt-2 pb-1.5 text-[11px] font-semibold',
                            m.utama ? 'text-merek-700' : aktif(...m.aktif) ? 'text-merek-700' : 'text-slate-500',
                        ]"
                        :aria-current="aktif(...m.aktif) ? 'page' : undefined"
                    >
                        <span v-if="m.utama" class="-mt-6 flex h-13 w-13 items-center justify-center rounded-full bg-merek-600 text-white shadow-lg ring-4 ring-white">
                            <Ikon :nama="m.ikon!" kelas="h-7 w-7" />
                        </span>
                        <span v-else class="relative">
                            <Ikon :nama="m.ikon!" kelas="h-6 w-6" />
                            <span
                                v-if="m.lencana && jumlah"
                                class="absolute -top-1.5 -right-2.5 min-w-4.5 rounded-full bg-red-600 px-1 text-center text-[10px] leading-4.5 font-bold text-white ring-2 ring-white"
                            >
                                {{ angka }}
                            </span>
                        </span>
                        {{ m.label }}
                        <span v-if="!m.utama && aktif(...m.aktif)" class="absolute top-0 h-0.5 w-8 rounded-full bg-merek-600" />
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
