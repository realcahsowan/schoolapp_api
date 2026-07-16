<?php

namespace App\Jobs\Tahfidz;

use App\Models\Tahfidz\Rapor;
use App\Scopes\CurrentYearSemesterScope;
use App\Services\RaporTahfidzService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateRaporPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $raporId)
    {
    }

    public function handle(RaporTahfidzService $service): void
    {
        $rapor = Rapor::withoutGlobalScope(CurrentYearSemesterScope::class)
            ->with(['student.classroom.school', 'student.dormitories'])
            ->find($this->raporId);

        if (! $rapor || ! $rapor->student || ! $rapor->student->classroom) {
            return;
        }

        $data = $service->generateRaporData($rapor->id);
        $pdfContent = Pdf::loadView('tahfidz.rapor', $data)
            ->setPaper('a4', 'portrait')
            ->output();

        $storagePath = $this->buildStoragePath($rapor);
        Storage::disk('public')->makeDirectory(dirname($storagePath));
        Storage::disk('public')->put($storagePath, $pdfContent);
    }

    protected function buildStoragePath(Rapor $rapor): string
    {
        $student = $rapor->student;
        $classroom = $student->classroom;

        $tahunAjaran = $this->normalizeFolderName((string) $rapor->tahun_ajaran);
        $semester = 'semester-' . $rapor->semester;
        $classroomName = $this->normalizeFolderName($classroom->nama ?: 'kelas-' . $classroom->id);
        $studentName = $this->normalizeFileName($student->nama ?: 'siswa');

        return sprintf(
            'rapor-tahfidz/school-%d/%s/%s/%s/rapor-tahfidz-%s-%d-semester-%d.pdf',
            $classroom->school_id,
            $tahunAjaran,
            $semester,
            $classroomName,
            $studentName,
            $student->id,
            $rapor->semester,
        );
    }

    protected function normalizeFolderName(string $value): string
    {
        $value = trim($value);
        $value = Str::of($value)->replace(['/', '\\'], '-')->toString();

        return Str::slug($value, '-') ?: 'folder';
    }

    protected function normalizeFileName(string $value): string
    {
        $value = trim($value);
        $value = Str::of($value)->replace(['/', '\\'], '-')->toString();

        return Str::slug($value, '-') ?: 'file';
    }
}
