<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use App\Settings\GeneralSettings;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ExaminationsRelationManager extends RelationManager
{
    protected static string $relationship = 'examinations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('juz')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('juz')
            ->columns([
                TextColumn::make('juz'),
                TextColumn::make('score'),
                TextColumn::make('tahun_ajaran')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('semester')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_nulled')
                    ->boolean()
                    ->label('Tidak Disetor?'),
                IconColumn::make('is_locked')
                    ->boolean()
                    ->label('Dikunci?'),
                TextColumn::make('penguji.nama'),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(function () {
                        $years = app(GeneralSettings::class)->years ?? [];

                        return $years ? array_combine($years, $years) : [];
                    })
                    ->default(fn () => app(GeneralSettings::class)->tahun_ajaran)
                    ->searchable()
                    ->placeholder('Semua Tahun Ajaran'),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        1 => 'Ganjil',
                        2 => 'Genap',
                    ])
                    ->default(fn () => app(GeneralSettings::class)->semester)
                    ->placeholder('Semua Semester'),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ViewAction::make('view')
                    ->url(fn($record) => \App\Filament\AdminTahfidz\Resources\Examinations\Pages\ViewExamination::getUrl(['record' => $record]))
                    ->label('View Examination'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
