<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource;
use App\Filament\AdminTahfidz\Resources\Students\StudentResource;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MurobbisRelationManager extends RelationManager
{
    protected static string $relationship = 'murobbis';

    protected static ?string $title = 'Murobbi';

    protected static ?string $relatedResource = StudentResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->withoutGlobalScopes()->with('employee.dormitories');
            })
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama'),
                Tables\Columns\TextColumn::make('tahun_ajaran')->label('Tahun Ajaran'),
                Tables\Columns\TextColumn::make('semester')->label('Semester'),
                Tables\Columns\TextColumn::make('category')->label('Kategori')
                    ->formatStateUsing(function ($state) {
                        return Str::title(str_replace('-', ' ', $state));
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('program')->label('Program')
                    ->formatStateUsing(function ($state) {
                        return Str::title(str_replace('-', ' ', $state));
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('employee.dormitories.name')->label('Asrama')
                    ->getStateUsing(function ($record) {
                        if (! $record->is_active) {
                            return;
                        }

                        return $record->employee->dormitories->where('pivot.is_active', true)->first()?->name;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employee.dormitories.pivot.room')->label('Kamar')
                    ->getStateUsing(function ($record) {
                        if (! $record->is_active) {
                            return;
                        }

                        return $record->employee->dormitories->where('pivot.is_active', true)->first()?->pivot->room;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->headerActions([
                // CreateAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                DetachAction::make()
                    ->hidden(function ($record) use ($tahunAjaran, $semester) {
                        return $record->tahun_ajaran !== $tahunAjaran || $record->semester !== $semester;
                    }),
                // DeleteAction::make(),
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-s-check-circle')
                    ->action(fn ($record) => $record->students()->updateExistingPivot($record->pivot->student_id, ['is_active' => true]))
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->tahun_ajaran == $tahunAjaran && $record->semester == $semester && ! $record->pivot->is_active),
                ViewAction::make()
                    ->url(fn ($record) => MurobbiResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DetachBulkAction::make(),
                    // BulkAction::make('detach_murobbis')
                    //     ->label('Detach Selected Murobbi')
                    //     ->icon('heroicon-s-x-mark')
                    //     ->color('danger')
                    //     ->action(function ($records) use ($tahunAjaran, $semester) {
                    //         // $records->where(function() {
                    //         //     //
                    //         // })->each(fn($murobbi) => $murobbi->students()->detach());
                    //     })
                    // ->deselectRecordsAfterCompletion(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
