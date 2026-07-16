<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\RelationManagers;

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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalSummariesRelationManager extends RelationManager
{
    protected static string $relationship = 'journalSummaries';
    protected static ?string $title = 'Ikhtisar Mutabaah';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tanggal')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal')
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    // ->searchable()
                    ->date(),
                IconColumn::make('hp_only')
                    ->label('Hafalan pagi saja?')
                    ->boolean(),
                TextColumn::make('target'),
                TextColumn::make('terisi'),
                IconColumn::make('completed')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Data Mutabaah')
                    ->modalContent(function ($record) {
                        // Ambil data Journal dengan tanggal dan murobbi sama
                        $journals = \App\Models\Tahfidz\Journal::where('murobbi_id', $record->murobbi_id)
                            ->whereDate('tanggal', $record->tanggal)
                            ->get();

                        return view(
                            'filament.admin-tahfidz.components.journal-list',
                            [
                                'journals' => $journals,
                                'tanggal' => $record->tanggal->format('d F Y'),
                            ]
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('3xl'),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
