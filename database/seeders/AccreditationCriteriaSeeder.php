<?php

namespace Database\Seeders;

use App\Models\AccreditationCriterion;
use Illuminate\Database\Seeder;

class AccreditationCriteriaSeeder extends Seeder
{
    /**
     * 9 kriteria instrumen akreditasi LAMEMBA.
     * Bisa disunting admin lewat menu Kriteria Akreditasi.
     */
    public function run(): void
    {
        $criteria = [
            'K1' => 'Visi, Misi, Tujuan, dan Strategi',
            'K2' => 'Tata Pamong, Tata Kelola, dan Kerja Sama',
            'K3' => 'Mahasiswa',
            'K4' => 'Sumber Daya Manusia',
            'K5' => 'Keuangan, Sarana, dan Prasarana',
            'K6' => 'Pendidikan',
            'K7' => 'Penelitian',
            'K8' => 'Pengabdian kepada Masyarakat',
            'K9' => 'Luaran dan Capaian Tridharma',
        ];

        $order = 1;

        foreach ($criteria as $code => $name) {
            AccreditationCriterion::updateOrCreate(
                ['instrument' => 'LAMEMBA', 'code' => $code],
                ['name' => $name, 'sort_order' => $order++],
            );
        }
    }
}
