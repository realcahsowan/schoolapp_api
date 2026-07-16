<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class MistakesRelationManager extends RelationManager
{
    protected static string $relationship = 'mistakes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('page')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->recordTitleAttribute('page')
            ->columns([
                TextColumn::make('page')
                    ->sortable(),
                TextColumn::make('score'),
                IconColumn::make('is_nulled')
                    ->label('Nilai Halaman Nol')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('detail')
                    ->label('Details')
                    ->modalHeading('Detail Penilaian Halaman')
                    ->modalSubmitAction(false)
                    ->modalContent(
                        function ($record) {
                            $details = $record->detail;
                            return view('mistake-details', [
                                'details' => $details,
                            ]);
                        }
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
