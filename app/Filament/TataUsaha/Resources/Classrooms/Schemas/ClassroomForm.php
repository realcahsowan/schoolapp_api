<?php

namespace App\Filament\TataUsaha\Resources\Classrooms\Schemas;

use App\Models\Position;
use App\Settings\GeneralSettings;
use App\Traits\SekolahTrait;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClassroomForm
{
    use SekolahTrait;

    public static function configure(Schema $schema): Schema
    {
        $school = Filament::getTenant();

        return $schema
            ->components([
                Hidden::make('school_id')
                    ->default($school?->id),
                Hidden::make('tahun_ajaran')
                    ->default(app(GeneralSettings::class)->tahun_ajaran),

                TextInput::make('nama')->label('Nama Kelas')->required(),

                Select::make('level')
                    ->label('Tingkat')
                    ->options(fn () => static::getLevelOptions($school?->id))
                    ->required()
                    ->disabledOn('edit'),

                Select::make('rombel')
                    ->label('Rombel')
                    ->options(static::getRombelOptions())
                    ->required()
                    ->disabledOn('edit'),

                Select::make('jurusan_id')
                    ->label('Jurusan')
                    ->options(static::getJurusanOptions())
                    ->required()
                    ->disabledOn('edit'),

                Select::make('employee_id')
                    ->label('Wali Kelas')
                    ->options(fn () => static::getPejabats($school?->id))
                    ->required(),

                Select::make('kurikulum_id')
                    ->label('Jenis Kurikulum')
                    ->options(static::getKurikulumOptions())
                    ->required(),
            ]);
    }

    protected static function getLevelOptions($sekolahId): array
    {
        $sekolah = \App\Models\School::find($sekolahId);
        $levels = static::getKelasLevelOptions($sekolah?->jenjang ?? '');
        $options = array_combine($levels, $levels);

        if ($sekolah?->jenjang === 'atas') {
            $options['idad'] = 'IDAD';
        }

        return $options;
    }

    protected static function getJurusanOptions(): array
    {
        return collect(app(GeneralSettings::class)->jurusan)->pluck('nama', 'id')->toArray();
    }

    protected static function getRombelOptions(): array
    {
        return array_combine(range('A', 'Z'), range('A', 'Z'));
    }

    protected static function getKurikulumOptions(): array
    {
        return collect(app(GeneralSettings::class)->kurikulum)->pluck('nama', 'id')->toArray();
    }

    protected static function getPejabats($schoolId): array
    {
        return Position::query()
            ->where('active', true)
            ->where('nama', 'wali-kelas')
            ->where('school_id', $schoolId)
            ->with('employee')
            ->get()
            ->pluck('employee.nama', 'employee_id')
            ->toArray();
    }
}
