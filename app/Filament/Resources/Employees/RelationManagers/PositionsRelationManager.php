<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Traits\JabatanTrait;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionsRelationManager extends RelationManager
{
    use JabatanTrait;

    protected static string $relationship = 'positions';

    protected static ?string $title = 'Jabatan';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('nama')
                //     ->required()
                //     ->maxLength(255),

                Forms\Components\Select::make('nama')
                    ->options($this->getJabatanOptions())
                    ->required(),
                Forms\Components\TextInput::make('sk')
                    ->maxLength(400),
                Forms\Components\DatePicker::make('mulai')
                    ->required(),
                Forms\Components\DatePicker::make('selesai')
                    ->required(),
                Forms\Components\Select::make('school_id')
                    ->required()
                    ->relationship('school', 'nama'),
                // Forms\Components\Toggle::make('active')
                // ->label('Status Jabatan')
                // ->required(),
                Forms\Components\Radio::make('active')
                    ->label('Status Jabatan')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Non Aktif',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('school.nama'),
                TextColumn::make('mulai'),
                TextColumn::make('selesai'),
                IconColumn::make('active')
                    ->boolean()
                    ->label('Status'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
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
