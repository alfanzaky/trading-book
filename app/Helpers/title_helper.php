<?php

if (!function_exists('page_title')) {
    function page_title(?string $customTitle = null, string $siteName = 'Trading Book'): string
    {
        if ($customTitle) {
            return $siteName . " | $customTitle";
        }

        $uri = service('uri');
        $segment = $uri->getSegment(1); // ambil segment pertama (misal 'jurnal')

        // Mapping route → judul yang ingin ditampilkan
        $titles = [
            'jurnal' => 'Jurnal',
            'accounts' => 'Akun',
            'transactions' => 'Transaksi',
            'dashboard' => 'Dashboard',
            // tambah route lain sesuai kebutuhan
        ];

        $title = $titles[$segment] ?? ucfirst($segment ?? 'Dashboard');

        return $siteName . " | $title";
    }
}

