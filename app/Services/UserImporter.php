<?php

namespace App\Services;

use App\Models\User;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class UserImporter
{
    public const MAX_ROWS = 1000;

    /** Nama kolom yang diterima untuk tiap field (huruf kecil, tanpa spasi). */
    private const HEADER_ALIASES = [
        'name' => ['nama', 'name', 'nama_lengkap', 'namalengkap'],
        'email' => ['email', 'e_mail', 'surel'],
        'identity_number' => ['nim', 'nip', 'nidn', 'nomor_identitas', 'nomoridentitas', 'identity_number', 'no_identitas'],
    ];

    /**
     * Impor pengguna dari file CSV/XLSX.
     *
     * @param  'mahasiswa'|'dosen'  $role
     * @param  'identity'|'fixed'  $passwordMode  identity = nomor identitas jadi password awal
     * @return array{created: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(string $absolutePath, string $role, string $passwordMode, ?string $fixedPassword = null): array
    {
        $rows = $this->readRows($absolutePath);

        if (count($rows) < 2) {
            return ['created' => 0, 'skipped' => [['row' => 1, 'reason' => 'File kosong atau hanya berisi baris judul.']]];
        }

        $columns = $this->mapHeader($rows[0]);

        if ($columns === null) {
            return ['created' => 0, 'skipped' => [[
                'row' => 1,
                'reason' => 'Baris judul tidak dikenali. Kolom wajib: nama, email, dan nim/nip/nomor_identitas.',
            ]]];
        }

        $existingEmails = User::pluck('email')->map(fn (string $e): string => mb_strtolower($e))->flip()->all();
        $existingIdentities = User::whereNotNull('identity_number')->pluck('identity_number')->flip()->all();

        $created = 0;
        $skipped = [];

        foreach (array_slice($rows, 1, self::MAX_ROWS, preserve_keys: true) as $index => $row) {
            $rowNumber = $index + 1;

            $name = trim((string) ($row[$columns['name']] ?? ''));
            $email = mb_strtolower(trim((string) ($row[$columns['email']] ?? '')));
            $identity = trim((string) ($row[$columns['identity_number']] ?? ''));

            if ($name === '' && $email === '' && $identity === '') {
                continue; // baris kosong
            }

            $reason = match (true) {
                $name === '' => 'Nama kosong.',
                $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) => 'Email kosong atau tidak valid.',
                $identity === '' => 'Nomor identitas (NIM/NIP) kosong.',
                isset($existingEmails[$email]) => "Email {$email} sudah terdaftar.",
                isset($existingIdentities[$identity]) => "Nomor identitas {$identity} sudah terdaftar.",
                default => null,
            };

            if ($reason !== null) {
                $skipped[] = ['row' => $rowNumber, 'reason' => $reason];

                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'identity_number' => $identity,
                'password' => $passwordMode === 'identity' ? $identity : (string) $fixedPassword,
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $existingEmails[$email] = true;
            $existingIdentities[$identity] = true;
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readRows(string $path): array
    {
        $reader = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'xlsx'
            ? new XlsxReader
            : $this->csvReader($path);

        $reader->open($path);

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(
                    fn ($cell): string => trim((string) $cell),
                    $row->toArray(),
                );
            }

            break; // hanya sheet pertama
        }

        $reader->close();

        return $rows;
    }

    /**
     * Excel berbahasa Indonesia sering mengekspor CSV dengan pemisah ';'.
     */
    private function csvReader(string $path): CsvReader
    {
        $firstLine = (string) fgets(fopen($path, 'r') ?: throw new \RuntimeException('File tidak terbaca.'));

        $options = new CsvOptions;

        if (str_contains($firstLine, ';') && ! str_contains($firstLine, ',')) {
            $options->FIELD_DELIMITER = ';';
        }

        return new CsvReader($options);
    }

    /**
     * Petakan baris judul ke indeks kolom. Null bila kolom wajib tidak ada.
     *
     * @param  array<int, string>  $headerRow
     * @return array{name: int, email: int, identity_number: int}|null
     */
    private function mapHeader(array $headerRow): ?array
    {
        $normalized = array_map(
            fn (string $cell): string => str_replace([' ', '-'], '_', mb_strtolower(trim($cell))),
            $headerRow,
        );

        $columns = [];

        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($normalized as $index => $cell) {
                if (in_array($cell, $aliases, true)) {
                    $columns[$field] = $index;

                    break;
                }
            }
        }

        return count($columns) === 3 ? $columns : null;
    }
}
