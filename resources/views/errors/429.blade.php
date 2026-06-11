@include('errors.error-page', [
    'kode' => 429,
    'judul' => 'Terlalu Banyak Permintaan',
    'pesan' => 'Anda mengirim permintaan terlalu sering. Tunggu sebentar (sekitar satu menit) lalu coba lagi.',
])
