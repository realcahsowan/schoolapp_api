<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;

class DummyGuardiansExport implements FromCollection, ShouldAutoSize, WithStyles, WithColumnFormatting, WithHeadings, WithMapping
{
    public $guardians;

    public function __construct($number)
    {
        $this->guardians = collect(range(1, 25))->map(function ($i) {
            $gender = fake()->randomElement(['male', 'female']);
            $nama = fake()->firstName($gender) . ' ' . fake()->lastName('male');
            $slug = \Illuminate\Support\Str::slug($nama, '_');

            return (object) [
                'nama' => $nama,
                'gender' => $gender,
                'tempat_lahir' => fake()->city(),
                'tanggal_lahir' => fake()->date(),
                'alamat' => fake()->address(),
                'telepon' => '08' . fake()->numerify('##########'),
                'is_alive' => fake()->randomElement([0, 1]),
                'relation_status' => fake()->randomElement(['kandung', 'tiri', 'angkat']),
                'pekerjaan' => fake()->jobTitle(),
                'email' => "{$slug}@example.org",
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'Gender', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Telepon', 'Hidup', 'Status', 'Pekerjaan', 'Email'];
    }

    public function map($guardian): array
    {
        return [
            $guardian->nama,
            $guardian->gender,
            $guardian->tempat_lahir,
            Date::dateTimeToExcel(\Carbon\Carbon::parse($guardian->tanggal_lahir)),
            $guardian->alamat,
            $guardian->telepon,
            (int) $guardian->is_alive,
            $guardian->relation_status,
            $guardian->pekerjaan,
            $guardian->email,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_YYYYMMDD,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return $this->guardians;
    }
}