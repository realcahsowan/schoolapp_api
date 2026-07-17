<?php

namespace App\Exports;

use App\Models\Guardian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuardiansExport implements FromCollection, ShouldAutoSize, WithStyles, WithColumnFormatting, WithHeadings, WithMapping
{
    public $guardians;

    public function __construct($school)
    {
        $this->guardians = Guardian::whereHas('students', function ($query) use ($school) {
            $query->whereHas('classroom', fn ($q) => $q->where('school_id', $school->id));
        })->with(['user', 'students.classroom'])->get();
    }

    public function headings(): array
    {
        return ['Nama Wali Santri', 'Nama Santri', 'Gender', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Telepon', 'Hidup', 'Status', 'Pendidikan', 'Pekerjaan', 'Email'];
    }

    public function map($guardian): array
    {
        return [
            $guardian->nama,
            $guardian->students->pluck('nama')->implode(', '),
            $guardian->gender,
            $guardian->tempat_lahir,
            $guardian->tanggal_lahir ? Date::dateTimeToExcel(\Carbon\Carbon::parse($guardian->tanggal_lahir)) : null,
            $guardian->alamat,
            $guardian->telepon,
            (int) $guardian->is_alive,
            $guardian->relation_status,
            $guardian->pendidikan,
            $guardian->pekerjaan,
            $guardian->user->email ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_YYYYMMDD,
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