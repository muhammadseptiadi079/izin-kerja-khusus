<script setup lang="ts">
// Ikon SVG native (garis 24x24), tanpa pustaka ikon eksternal.
const jalur: Record<string, string[]> = {
    menu: ['M4 6h16', 'M4 12h16', 'M4 18h16'],
    tutup: ['M6 6l12 12', 'M18 6L6 18'],
    rumah: ['M3 11l9-7 9 7', 'M5 10v10h14V10', 'M10 20v-6h4v6'],
    formulir: ['M9 4h6v3H9z', 'M7 5H5v16h14V5h-2', 'M8 11h8', 'M8 15h8', 'M8 19h5'],
    grafik: ['M4 20V4', 'M4 20h16', 'M8 16v-5', 'M12 16V8', 'M16 16v-8'],
    dasbor: ['M4 4h7v7H4z', 'M13 4h7v4h-7z', 'M13 10h7v10h-7z', 'M4 13h7v7H4z'],
    daftar: ['M9 6h11', 'M9 12h11', 'M9 18h11', 'M4 6h.01', 'M4 12h.01', 'M4 18h.01'],
    pengguna: ['M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1', 'M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7', 'M21 19v-1a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
    keluar: ['M15 4h4v16h-4', 'M10 16l4-4-4-4', 'M14 12H4'],
    profil: ['M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M4 21a8 8 0 0 1 16 0'],
    unduh: ['M12 4v11', 'M7 10l5 5 5-5', 'M5 20h14'],
    cetak: ['M7 8V3h10v5', 'M7 17H4v-7h16v7h-3', 'M7 14h10v7H7z'],
    ai: ['M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z', 'M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z'],
    centang: ['M5 12l5 5L20 7'],
    peringatan: ['M12 3l10 18H2z', 'M12 10v4', 'M12 17.5h.01'],
    jam: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18', 'M12 7v5l3 2'],
    berkas: ['M14 3H6v18h12V7z', 'M14 3v4h4'],
    panah: ['M5 12h14', 'M13 6l6 6-6 6'],
    kembali: ['M19 12H5', 'M11 6l-6 6 6 6'],
    whatsapp: ['M4 20l1.5-4.5A8 8 0 1 1 9 19z', 'M9 9.5c.5 2 2.5 4 4.5 4.5l1.2-1.2 2 1-.6 1.7c-3.5.3-7.5-3.7-7.2-7.2L10.6 7.7l1 2z'],
};

defineProps<{ nama: keyof typeof jalur | string; kelas?: string }>();
</script>

<template>
    <svg
        :class="kelas ?? 'h-5 w-5'"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path v-for="(d, i) in jalur[nama] ?? []" :key="i" :d="d" />
    </svg>
</template>
