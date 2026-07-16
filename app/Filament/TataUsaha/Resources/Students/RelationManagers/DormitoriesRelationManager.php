<?php

namespace App\Filament\TataUsaha\Resources\Students\RelationManagers;

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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DormitoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'dormitories';

    protected static ?string $title = 'Riwayat Asrama';

    public function mount(): void
    {
        \Log::info('Mount lifecycle called');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        \Log::info('Render lifecycle called');

        return parent::render();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(function ($query) {
            //     \Log::info('modifyQueryUsing called');
            //     \Log::info($query->toSql()); // Raw SQL query
            //     return $query->select(
            //         'dormitories.*',
            //         'dormitory_student.id as pivot_id',
            //         'dormitory_student.room',
            //         'dormitory_student.is_active',
            //         'dormitory_student.tahun_ajaran',
            //         'dormitory_student.semester',
            //         'dormitory_student.created_at',
            //         'dormitory_student.updated_at'
            //     );
            // })
            ->recordTitleAttribute('pivot.id')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('pivot.id'),
                TextColumn::make('pivot.room')
                    ->label('Room'),
                TextColumn::make('pivot.tahun_ajaran')
                    ->label('Tahun Ajaran'),
                TextColumn::make('pivot.semester')
                    ->label('Semester'),
                IconColumn::make('pivot.is_active')
                    ->boolean()
                    ->label('Active'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AttachAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                // DetachAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DetachBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
