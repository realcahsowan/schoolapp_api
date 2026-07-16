<?php

namespace App\Exports\TataUsaha;

use App\Models\Student;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query(): Builder
    {
        $schoolId = Filament::getTenant()?->id;

        abort_unless($schoolId, 403);

        return Student::query()
            ->whereHas('classroom', fn (Builder $query) => $query->where('school_id', $schoolId))
            ->with([
                'user',
                'classroom.school',
                'murobbis' => fn (BelongsToMany $query) => $query->wherePivot('is_active', true),
            ])
            ->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'nama',
            'email',
            'nis',
            'nisn',
            'gender',
            'tempat_lahir',
            'tanggal_lahir',
            'sekolah',
            'kelas',
            'murobbi',
            'program (tahfidz)',
            'alamat',
        ];
    }

    public function map($student): array
    {
        return [
            $student->nama,
            $student->user?->email ?? $student->email,
            $student->nis,
            $student->nisn,
            $this->formatGender($student->gender),
            $student->tempat_lahir,
            $this->formatDate($student->tanggal_lahir),
            $student->classroom?->school?->nama,
            $student->classroom?->nama,
            $student->murobbi?->nama,
            $student->murobbi?->pivot?->program,
            $student->alamat,
        ];
    }

    protected function formatGender(?string $gender): string
    {
        return match ($gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => $gender ?? '',
        };
    }

    protected function formatDate(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        return Carbon::parse($value)->format('Y-m-d');
    }
}
