<?php

namespace App\Imports\TataUsaha;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows->map(function ($row) {
            return [
                'nama' => $row['nama'],
                'nisn' => $row['nisn'] ?? null,
                'nis' => $row['nis'] ?? null,
                'gender' => $this->formatGender($row['gender'] ?? null),
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                'alamat' => $row['alamat'] ?? null,
                'telepon' => $row['telepon'] ?? null,
                'email' => $row['email'] ?? null,
            ];
        });
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nisn' => 'nullable|string|max:20',
            'nis' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ];
    }

    protected function formatGender(?string $gender): ?string
    {
        return match ($gender) {
            'Laki-laki', 'laki-laki', 'male', 'Male' => 'male',
            'Perempuan', 'perempuan', 'female', 'Female' => 'female',
            default => $gender,
        };
    }
}
