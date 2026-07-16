<?php

namespace App\Filament\TataUsaha\Resources\Classrooms;

use App\Filament\TataUsaha\Resources\Classrooms\Pages\CreateClassroom;
use App\Filament\TataUsaha\Resources\Classrooms\Pages\EditClassroom;
use App\Filament\TataUsaha\Resources\Classrooms\Pages\ListClassrooms;
use App\Filament\TataUsaha\Resources\Classrooms\Schemas\ClassroomForm;
use App\Filament\TataUsaha\Resources\Classrooms\Tables\ClassroomsTable;
use App\Models\Classroom;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;
    protected static ?int $navigationSort = 1;
    protected static string|UnitEnum|null $navigationGroup = 'Akademik';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $label = 'Kelas';
    protected static ?string $pluralLabel = 'Daftar Kelas';
    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return ClassroomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassroomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClassrooms::route('/'),
            'create' => CreateClassroom::route('/create'),
            'edit' => EditClassroom::route('/{record}/edit'),
        ];
    }
}
