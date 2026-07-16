<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\RelationManagers;

use App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource;
use App\Filament\AdminTahfidz\Resources\Students\Pages\ViewStudent;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $relatedResource = MurobbiResource::class;
    protected static ?string $title = 'Daftar Siswa/i';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $tahunAjaran = $this->getOwnerRecord()->tahun_ajaran;
        $semester = $this->getOwnerRecord()->semester;

        return $table
            ->modifyQueryUsing(function ($query) use ($tahunAjaran, $semester) {
                $query->whereHas('murobbis', function ($q) use ($tahunAjaran, $semester) {
                    $q->where('tahun_ajaran', $tahunAjaran)
                      ->where('semester', $semester);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama'),
                Tables\Columns\TextColumn::make('classroom.nama')->label('Kelas'),
            ])
            ->defaultSort('nama', 'asc')
            ->recordActions([
                ViewAction::make()
                    ->url(fn($record) => ViewStudent::getUrl(['record' => $record->id])),
                DetachAction::make(),
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make()
                    ->form([
                        \Filament\Forms\Components\Select::make('recordId')
                            ->label('Siswa')
                            ->options(function () {
                                $schoolId = $this->getOwnerRecord()->school_id;
                                $attachedStudentIds = $this->getOwnerRecord()->students()->pluck('students.id')->toArray();
                                return \App\Models\Student::whereHas('classroom', function ($query) use ($schoolId) {
                                    $query->where('school_id', $schoolId);
                                })
                                ->whereNotIn('id', $attachedStudentIds)
                                ->pluck('nama', 'students.id');
                            })
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options(function () {
                                $schoolId = $this->getOwnerRecord()->school_id;
                                $config = \App\Models\Tahfidz\Configuration::where('school_id', $schoolId)
                                    ->where('name', 'categories')
                                    ->first();
                                return $config ? collect($config->payload)->pluck('nama', 'slug')->toArray() : [];
                            })
                            ->required(),
                        \Filament\Forms\Components\Select::make('program')
                            ->label('Program')
                            ->options(function () {
                                $schoolId = $this->getOwnerRecord()->school_id;
                                $config = \App\Models\Tahfidz\Configuration::where('school_id', $schoolId)
                                    ->where('name', 'programs')
                                    ->first();
                                return $config ? collect($config->payload)->where('active', true)->pluck('nama', 'slug')->toArray() : [];
                            })
                            ->required(),

                        // \Filament\Forms\Components\TextInput::make('program_pivot')->label('Program')->required(),
                    ]),
            ]);
    }
}
