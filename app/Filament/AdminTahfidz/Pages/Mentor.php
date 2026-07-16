<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Employee;
use App\Models\Murobbi;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class Mentor extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin-tahfidz.pages.mentor';

    protected static ?string $title = 'Mentor (Murobbi)';

    protected static string|UnitEnum|null $navigationGroup = 'Utama';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected function getHeaderActions(): array
    {
        $tenantId = Filament::getTenant()?->id;
        $generalSettings = app(GeneralSettings::class);

        return [
            Action::make('add_mentor')
                ->label('Add Mentor')
                ->icon('heroicon-o-user-plus')
                ->modalHeading('Add Mentor')
                ->modalWidth('lg')
                ->form([
                    Select::make('employee_id')
                        ->label('Pegawai')
                        ->options(function () use ($tenantId) {
                            if (! $tenantId) {
                                return [];
                            }

                            return Employee::query()
                                ->whereHas('positions', function (Builder $query) use ($tenantId) {
                                    $query->where('nama', 'Murobbi')
                                        ->where('school_id', $tenantId);
                                })
                                ->orderBy('nama')
                                ->pluck('nama', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Hidden::make('school_id')
                        ->default($tenantId),
                    Hidden::make('tahun_ajaran')
                        ->default($generalSettings->tahun_ajaran),
                    Hidden::make('semester')
                        ->default($generalSettings->semester),
                ])
                ->action(function (array $data) use ($tenantId, $generalSettings) {
                    if (! $tenantId) {
                        Notification::make()
                            ->danger()
                            ->title('Tenant belum dipilih')
                            ->body('Tidak bisa membuat mentor tanpa school aktif.')
                            ->send();

                        return;
                    }

                    $employee = Employee::query()
                        ->whereKey($data['employee_id'])
                        ->whereHas('positions', function (Builder $query) use ($tenantId) {
                            $query->where('nama', 'Murobbi')
                                ->where('school_id', $tenantId);
                        })
                        ->first();

                    if (! $employee) {
                        Notification::make()
                            ->danger()
                            ->title('Pegawai tidak valid')
                            ->body('Pegawai yang dipilih tidak ditemukan atau tidak memiliki jabatan Murobbi pada school aktif.')
                            ->send();

                        return;
                    }

                    $murobbi = Murobbi::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'school_id' => $data['school_id'],
                            'tahun_ajaran' => $data['tahun_ajaran'],
                            'semester' => $data['semester'],
                        ],
                        [
                            'nama' => $employee->nama,
                            'gender' => $employee->gender,
                        ],
                    );

                    Notification::make()
                        ->success()
                        ->title($murobbi->wasRecentlyCreated ? 'Mentor berhasil dibuat' : 'Mentor berhasil diperbarui')
                        ->body("Data mentor untuk {$employee->nama} sudah disimpan.")
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->id;

        return $table
            ->query(
                Murobbi::query()
                    ->when($tenantId, fn(Builder $query) => $query->where('school_id', $tenantId))
                    ->withCount('students')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Jumlah Anggota')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->placeholder('Semua'),
                TernaryFilter::make('whereHasStudents')
                    ->label('Punya Anggota')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHasStudents(),
                        false: fn(Builder $query) => $query->doesntHave('students'),
                    ),
            ])
            ->actions([
                Action::make('view_detail')
                    ->label('View Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Murobbi $record) => \App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource::getUrl('view', [
                        'record' => $record,
                    ])),
            ])
            ->defaultSort('nama');
    }
}
