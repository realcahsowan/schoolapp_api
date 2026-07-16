<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use App\Settings\GeneralSettings;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengujisRelationManager extends RelationManager
{
    protected static string $relationship = 'pengujis';
    protected static ?string $title = 'Penguji';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('tahun_ajaran'),
                TextColumn::make('semester'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                DetachAction::make()
                    ->hidden(function ($record) {
                        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
                        $semester = app(GeneralSettings::class)->semester;
                        return $record->tahun_ajaran !== $tahunAjaran || $record->semester !== $semester;
                    }),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
