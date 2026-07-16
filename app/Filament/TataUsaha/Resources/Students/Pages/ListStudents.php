<?php

namespace App\Filament\TataUsaha\Resources\Students\Pages;

use App\Exports\TataUsaha\StudentsDataTemplate;
use App\Exports\TataUsaha\StudentsExport;
use App\Filament\TataUsaha\Resources\Students\StudentResource;
use App\Imports\TataUsaha\StudentsImport;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ActionGroup::make([
                Action::make('panduan-template')
                    ->label('Panduan Template Data')
                    ->icon('heroicon-o-question-mark-circle')
                    ->modalHeading('Panduan Pengisian Template Data Siswa')
                    ->modalContent(view('filament.tata-usaha.components.panduan-template'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('download-template')
                    ->label('Unduh Template Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $fileName = 'template-data-siswa-' . now()->format('Y-m-d_His') . '.xlsx';

                        return Excel::download(new StudentsDataTemplate(), $fileName);
                    }),
                Action::make('upload-student-data')
                    ->label('Upload Data Siswa')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Select::make('classroom_id')
                            ->label('Kelas')
                            ->options(function () {
                                $tenant = Filament::getTenant();

                                return Classroom::query()
                                    ->where('school_id', $tenant?->id)
                                    ->currentYear()
                                    ->orderBy('nama')
                                    ->pluck('nama', 'id');
                            })
                            ->required(),
                        FileUpload::make('file')
                            ->label('Pilih File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $rows = Excel::toCollection(new StudentsImport(), $data['file']);

                        $seenNis = [];
                        $seenEmails = [];
                        $imported = 0;

                        foreach ($rows[0] as $row) {
                            $nis = $row['nis'] ?? null;
                            $email = $row['email'] ?? null;

                            if ($nis && (in_array($nis, $seenNis) || Student::where('nis', $nis)->exists())) {
                                continue;
                            }

                            if ($email && (in_array($email, $seenEmails) || User::where('email', $email)->exists())) {
                                continue;
                            }

                            $seenNis[] = $nis;
                            $seenEmails[] = $email;

                            $student = new Student();
                            $student->forceFill([
                                'classroom_id' => $data['classroom_id'],
                                'nama' => $row['nama'],
                                'nisn' => $row['nisn'] ?? null,
                                'nis' => $nis,
                                'gender' => static::normalizeGender($row['gender'] ?? null),
                                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                                'tanggal_lahir' => static::parseDate($row['tanggal_lahir'] ?? null),
                                'alamat' => $row['alamat'] ?? null,
                                'telepon' => $row['telepon'] ?? null,
                            ]);
                            $student->saveQuietly();

                            DB::table('classroom_student')->insertOrIgnore([
                                'student_id' => $student->id,
                                'classroom_id' => $data['classroom_id'],
                                'is_active' => true,
                            ]);

                            if ($email) {
                                User::create([
                                    'student_id' => $student->id,
                                    'name' => $row['nama'],
                                    'email' => $email,
                                    'password' => app(GeneralSettings::class)->password,
                                    'role' => 'student',
                                ]);
                            }

                            $imported++;
                        }

                        Notification::make()
                            ->success()
                            ->title("{$imported} data siswa berhasil diimpor.")
                            ->send();
                    }),
                Action::make('export-student-data')
                    ->label('Export Student Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $fileName = 'student-data-' . now()->format('Y-m-d_His') . '.xlsx';

                        return Excel::download(new StudentsExport(), $fileName);
                    }),
            ])
                ->label('Data Siswa')
                ->button(),
        ];
    }

    protected static function normalizeGender(?string $gender): ?string
    {
        return match ($gender) {
            'Laki-laki', 'laki-laki', 'male', 'Male' => 'male',
            'Perempuan', 'perempuan', 'female', 'Female' => 'female',
            default => $gender,
        };
    }

    protected static function parseDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return \Carbon\Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)
            )->format('Y-m-d');
        }

        return $value;
    }
}
