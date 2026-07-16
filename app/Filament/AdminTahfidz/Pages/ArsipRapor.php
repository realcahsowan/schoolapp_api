<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Classroom;
use App\Models\Student;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use UnitEnum;
use ZipArchive;

class ArsipRapor extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Arsip Rapor';

    protected static ?int $navigationSort = 31;

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian Akhir Semester';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentArrowDown;

    protected string $view = 'filament.admin-tahfidz.pages.arsip-rapor';

    public function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->id;
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;

        return $table
            ->query(
                Classroom::query()
                    ->when(
                        $tenantId,
                        fn (Builder $query) => $query->where('school_id', $tenantId),
                        fn (Builder $query) => $query->whereRaw('1 = 0')
                    )
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->with('employee')
                    ->orderBy('nama')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.nama')
                    ->label('Wali Kelas')
                    ->formatStateUsing(fn (?string $state) => $state ?: '-')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('unduhSemester1')
                    ->label('Unduh Rapor Semester 1')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn (Classroom $record) => $this->downloadSemesterRapors($record, 1)),
                Action::make('unduhSemester2')
                    ->label('Unduh Rapor Semester 2')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn (Classroom $record) => $this->downloadSemesterRapors($record, 2)),
            ])
            ->defaultSort('nama');
    }

    protected function downloadSemesterRapors(Classroom $classroom, int $semester)
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);

        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            Notification::make()
                ->danger()
                ->title('Tenant tidak ditemukan')
                ->body('Tidak bisa membuat arsip rapor tanpa school aktif.')
                ->send();

            return null;
        }

        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;

        $students = Student::query()
            ->where('classroom_id', $classroom->id)
            ->with([
                'classroom.school',
                'rapors' => fn ($query) => $query
                    ->withoutGlobalScope(\App\Scopes\CurrentYearSemesterScope::class)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester),
            ])
            ->orderBy('nama')
            ->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('Data siswa kosong')
                ->body('Tidak ada siswa ditemukan pada kelas terpilih.')
                ->send();

            return null;
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'arsip_rapor_');

        if ($zipPath === false) {
            Notification::make()
                ->danger()
                ->title('Gagal menyiapkan file arsip')
                ->body('Tidak dapat membuat file sementara untuk ZIP.')
                ->send();

            return null;
        }

        $finalZipPath = $zipPath . '.zip';
        @unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($finalZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Notification::make()
                ->danger()
                ->title('Gagal membuat ZIP')
                ->body('Tidak dapat membuka file ZIP sementara.')
                ->send();

            return null;
        }

        $added = 0;
        $skippedMissingFile = 0;
        $skippedMissingRapor = 0;

        foreach ($students as $student) {
            $rapor = $student->rapors->first();

            if (! $rapor) {
                $skippedMissingRapor++;
                continue;
            }

            $pdfStoragePath = $this->buildStoragePath($classroom, $semester, $student);

            if (! Storage::disk('public')->exists($pdfStoragePath)) {
                $skippedMissingFile++;
                continue;
            }

            $zip->addFile(
                Storage::disk('public')->path($pdfStoragePath),
                $this->buildZipEntryName($classroom, $semester, $student)
            );

            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($finalZipPath);

            Notification::make()
                ->warning()
                ->title('File rapor belum tersedia')
                ->body('Tidak ada file PDF rapor yang sudah tergenerate untuk semester ini.')
                ->send();

            return null;
        }

        if ($skippedMissingFile > 0 || $skippedMissingRapor > 0) {
            Notification::make()
                ->warning()
                ->title('Sebagian rapor dilewati')
                ->body("{$skippedMissingRapor} siswa tanpa rapor dan {$skippedMissingFile} file PDF belum tersedia.")
                ->send();
        }

        return response()->download(
            $finalZipPath,
            $this->buildDownloadFileName($classroom, $semester)
        )->deleteFileAfterSend(true);
    }

    protected function buildStoragePath(Classroom $classroom, int $semester, Student $student): string
    {
        $tahunAjaran = $this->normalizeFolderName((string) app(GeneralSettings::class)->tahun_ajaran);
        $semesterFolder = 'semester-' . $semester;
        $classroomName = $this->normalizeFolderName($classroom->nama ?: 'kelas-' . $classroom->id);
        $studentName = $this->normalizeFileName($student->nama ?: 'siswa');

        return sprintf(
            'rapor-tahfidz/school-%d/%s/%s/%s/rapor-tahfidz-%s-%d-semester-%d.pdf',
            $classroom->school_id,
            $tahunAjaran,
            $semesterFolder,
            $classroomName,
            $studentName,
            $student->id,
            $semester,
        );
    }

    protected function buildZipEntryName(Classroom $classroom, int $semester, Student $student): string
    {
        $classroomSlug = Str::slug($classroom->nama) ?: 'kelas';
        $studentSlug = Str::slug($student->nama) ?: 'siswa';

        return "Semester-{$semester}/{$classroomSlug}-{$studentSlug}-{$student->id}.pdf";
    }

    protected function buildDownloadFileName(Classroom $classroom, int $semester): string
    {
        $classroomSlug = Str::slug($classroom->nama) ?: 'kelas';

        return "arsip-rapor-{$classroomSlug}-semester-{$semester}.zip";
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
