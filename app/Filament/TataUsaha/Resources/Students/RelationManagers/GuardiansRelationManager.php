<?php

namespace App\Filament\TataUsaha\Resources\Students\RelationManagers;

use App\Filament\TataUsaha\Resources\Guardians\Pages\ViewGuardian;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
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

class GuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';
    public static ?string $title = 'Wali Santri';

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
                TextColumn::make('nama'),
                TextColumn::make('user.email')
                    ->label('Email'),
                TextColumn::make('telepon'),
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
                // DeleteAction::make(),
                ViewAction::make('view')
                    ->label('Lihat')
                    ->url(fn($record) => ViewGuardian::getUrl(['record' => $record->id]))
                    ->openUrlInNewTab(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
