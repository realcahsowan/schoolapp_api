<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ExaminationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.nama')->label('Student Name')->sortable()->searchable(),
                TextColumn::make('juz')->label('Juz')->sortable(),
                TextColumn::make('score')->label('Score')->sortable(),
                IconColumn::make('is_nulled')->label('Tidak Disetor')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
