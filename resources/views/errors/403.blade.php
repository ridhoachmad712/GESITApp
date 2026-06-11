@include('errors.error-page', [
    'kode' => 403,
    'judul' => 'Akses Ditolak',
    'pesan' => $exception?->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman atau dokumen ini. Masuk dengan akun yang sesuai, atau hubungi admin prodi.',
])
