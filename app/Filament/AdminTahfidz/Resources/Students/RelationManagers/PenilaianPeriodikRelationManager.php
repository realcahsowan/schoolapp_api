<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PenilaianPeriodikRelationManager extends RelationManager
{
    protected static string $relationship = 'penilaianPeriodik';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->required()
                                    ->maxLength(255),
                TextInput::make('score')
                    ->numeric()
                    ->step(0.01)
                    ->label('Score'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('tanggal', 'desc')
            ->columns([
                // TextColumn::make('id')
                //     ->searchable(),
                TextColumn::make('tanggal'),
                TextColumn::make('kehadiran'),
                TextColumn::make('score')
                    ->formatStateUsing(fn($state) => number_format($state, 2)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
