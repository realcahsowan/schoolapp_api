<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\RelationManagers;

use App\Settings\GeneralSettings;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make()
                    ->label('Sandingkan')
                    ->modalHeading('Sandingkan Siswa Diuji')
                    // ->modalSubmitActionLabel('Sandingkan')
                    // ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereBelongsTo($school)->whereHas('achievement')->whereHas('murobbis', fn ($q) => $q->where('active', true)))
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect()->autofocus(),
                        Hidden::make('tahun_ajaran')->default(app(GeneralSettings::class)->tahun_ajaran),
                        Hidden::make('semester')->default(app(GeneralSettings::class)->semester),
                        Hidden::make('periode')->default('pas'),
                    ])
                    ->attachAnother(true),

            ])
            ->recordActions([
                // EditAction::make(),
                DetachAction::make(),
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
