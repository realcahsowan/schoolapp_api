<?php

namespace App\Filament\AdminTahfidz\Resources\Students;

use App\Filament\AdminTahfidz\Resources\Students\Pages\CreateStudent;
use App\Filament\AdminTahfidz\Resources\Students\Pages\EditStudent;
use App\Filament\AdminTahfidz\Resources\Students\Pages\ListStudents;
use App\Filament\AdminTahfidz\Resources\Students\Pages\ViewStudent;
use App\Filament\AdminTahfidz\Resources\Students\Schemas\StudentForm;
use App\Filament\AdminTahfidz\Resources\Students\Schemas\StudentInfolist;
use App\Filament\AdminTahfidz\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?int $navigationSort = 3;
    protected static string|UnitEnum|null $navigationGroup = 'Utama';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;
    protected static ?string $label = 'Siswa';
    protected static ?string $pluralLabel = 'Daftar Siswa';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
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
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view' => ViewStudent::route('/{record}'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
