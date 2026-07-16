<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans;

use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages\CreateKurikulumHafalan;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages\EditKurikulumHafalan;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages\ListKurikulumHafalans;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages\ViewKurikulumHafalan;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Schemas\KurikulumHafalanForm;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Schemas\KurikulumHafalanInfolist;
use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Tables\KurikulumHafalansTable;
use App\Models\Tahfidz\KurikulumHafalan;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KurikulumHafalanResource extends Resource
{
    protected static ?string $model = KurikulumHafalan::class;

    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string|UnitEnum|null $navigationGroup = 'Utama';

    public static function form(Schema $schema): Schema
    {
        return KurikulumHafalanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KurikulumHafalanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KurikulumHafalansTable::configure($table);
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
            'index' => ListKurikulumHafalans::route('/'),
            // 'create' => CreateKurikulumHafalan::route('/create'),
            'view' => ViewKurikulumHafalan::route('/{record}'),
            // 'edit' => EditKurikulumHafalan::route('/{record}/edit'),
        ];
    }
}
