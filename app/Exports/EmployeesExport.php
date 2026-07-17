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

class EmployeesExport implements FromCollection, ShouldAutoSize, WithStyles, WithColumnFormatting, WithHeadings, WithMapping
{
    public $employees;

    public function __construct($number)
    {
        $this->employees = collect(range(1, 25))->map(function ($i) {
            $gender = fake()->randomElement(['male', 'female']);
            $nama = fake()->firstName($gender) . ' ' . fake()->lastName('male');
            $slug = \Illuminate\Support\Str::slug($nama, '_');

            return (object) [
                'nama' => $nama,
                'nik' => fake()->numerify('################'),
                'nip' => fake()->numberBetween(111111, 999999),
                'gender' => $gender,
                'tempat_lahir' => fake()->city(),
                'tanggal_lahir' => fake()->date(),
                'alamat' => fake()->address(),
                'telepon' => '08' . fake()->numerify('##########'),
                'email' => "{$slug}@example.org",
                'nuptk' => fake()->numerify('############'),
                'peg_id' => fake()->numerify('########'),
                'golongan_darah' => fake()->randomElement(['A', 'B', 'AB', 'O']),
                'tanggal_mulai_bekerja' => fake()->date(),
                'status_perkawinan' => fake()->randomElement(['Belum Kawin', 'Kawin']),
                'nama_ayah' => fake()->name('male'),
                'nama_ibu' => fake()->name('female'),
                'nama_pasangan' => fake()->name($gender === 'male' ? 'female' : 'male'),
                'jumlah_anak' => fake()->numberBetween(0, 5),
                'pendidikan_terakhir' => fake()->randomElement(['S1', 'S2', 'S3']),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'nama',
            'nik',
            'nip',
            'gender',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'telepon',
            'email',
            'nuptk',
            'peg_id',
            'golongan_darah',
            'tanggal_mulai_bekerja',
            'status_perkawinan',
            'nama_ayah',
            'nama_ibu',
            'nama_pasangan',
            'jumlah_anak',
            'pendidikan_terakhir',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->nama,
            $employee->nik,
            $employee->nip,
            $employee->gender,
            $employee->tempat_lahir,
            Date::dateTimeToExcel(\Carbon\Carbon::parse($employee->tanggal_lahir)),
            $employee->alamat,
            $employee->telepon,
            $employee->email,
            $employee->nuptk,
            $employee->peg_id,
            $employee->golongan_darah,
            Date::dateTimeToExcel(\Carbon\Carbon::parse($employee->tanggal_mulai_bekerja)),
            $employee->status_perkawinan,
            $employee->nama_ayah,
            $employee->nama_ibu,
            $employee->nama_pasangan,
            $employee->jumlah_anak,
            $employee->pendidikan_terakhir,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'M' => NumberFormat::FORMAT_DATE_YYYYMMDD,
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
        return $this->employees;
    }
}