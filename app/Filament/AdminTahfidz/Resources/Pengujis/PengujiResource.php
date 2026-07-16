<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis;

use App\Filament\AdminTahfidz\Resources\Pengujis\Pages\CreatePenguji;
use App\Filament\AdminTahfidz\Resources\Pengujis\Pages\EditPenguji;
use App\Filament\AdminTahfidz\Resources\Pengujis\Pages\ListPengujis;
use App\Filament\AdminTahfidz\Resources\Pengujis\Pages\ViewPenguji;
use App\Filament\AdminTahfidz\Resources\Pengujis\Schemas\PengujiForm;
use App\Filament\AdminTahfidz\Resources\Pengujis\Schemas\PengujiInfolist;
use App\Filament\AdminTahfidz\Resources\Pengujis\Tables\PengujisTable;
use App\Models\Tahfidz\Penguji;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PengujiResource extends Resource
{
    protected static ?string $model = Penguji::class;
    protected static ?int $navigationSort = 12;
    protected static string|UnitEnum|null $navigationGroup = 'Penilaian Akhir Semester';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    protected static ?string $navigationLabel = 'Daftar Penguji';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return PengujiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PengujiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengujisTable::configure($table);
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
            'index' => ListPengujis::route('/'),
            // 'create' => CreatePenguji::route('/create'),
            'view' => ViewPenguji::route('/{record}'),
            // 'edit' => EditPenguji::route('/{record}/edit'),
        ];
    }
}
