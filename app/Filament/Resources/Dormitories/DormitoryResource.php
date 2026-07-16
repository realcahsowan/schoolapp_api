<?php

namespace App\Filament\Resources\Dormitories;

use App\Filament\Resources\Dormitories\Pages\CreateDormitory;
use App\Filament\Resources\Dormitories\Pages\EditDormitory;
use App\Filament\Resources\Dormitories\Pages\ListDormitories;
use App\Filament\Resources\Dormitories\Pages\ViewDormitory;
use App\Filament\Resources\Dormitories\Schemas\DormitoryForm;
use App\Filament\Resources\Dormitories\Schemas\DormitoryInfolist;
use App\Filament\Resources\Dormitories\Tables\DormitoriesTable;
use App\Models\Dormitory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DormitoryResource extends Resource
{
    protected static ?string $model = Dormitory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;
    public static function getLabel(): string
    {
        return 'Asrama';
    }

    public static function getPluralLabel(): string
    {
        return 'Daftar Asrama';
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DormitoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DormitoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DormitoriesTable::configure($table);
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
            'index' => ListDormitories::route('/'),
            'create' => CreateDormitory::route('/create'),
            'view' => ViewDormitory::route('/{record}'),
            'edit' => EditDormitory::route('/{record}/edit'),
        ];
    }
}
