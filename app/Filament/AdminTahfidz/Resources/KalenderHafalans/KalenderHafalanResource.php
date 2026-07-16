<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans;

use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages\CreateKalenderHafalan;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages\EditKalenderHafalan;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages\ListKalenderHafalans;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages\ViewKalenderHafalan;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Schemas\KalenderHafalanForm;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Schemas\KalenderHafalanInfolist;
use App\Filament\AdminTahfidz\Resources\KalenderHafalans\Tables\KalenderHafalansTable;
use App\Models\Tahfidz\KalenderHafalan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KalenderHafalanResource extends Resource
{
    protected static ?string $model = KalenderHafalan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;
    protected static ?int $navigationSort = 1;
    protected static string|UnitEnum|null $navigationGroup = 'Utama';

    protected static ?string $recordTitleAttribute = 'tanggal';

    public static function form(Schema $schema): Schema
    {
        return KalenderHafalanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KalenderHafalanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KalenderHafalansTable::configure($table);
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
            'index' => ListKalenderHafalans::route('/'),
            'create' => CreateKalenderHafalan::route('/create'),
            'view' => ViewKalenderHafalan::route('/{record}'),
            'edit' => EditKalenderHafalan::route('/{record}/edit'),
        ];
    }
}
