<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis;

use App\Filament\AdminTahfidz\Resources\Murobbis\Pages\CreateMurobbi;
use App\Filament\AdminTahfidz\Resources\Murobbis\Pages\EditMurobbi;
use App\Filament\AdminTahfidz\Resources\Murobbis\Pages\ListMurobbis;
use App\Filament\AdminTahfidz\Resources\Murobbis\Pages\ViewMurobbi;
use App\Filament\AdminTahfidz\Resources\Murobbis\Schemas\MurobbiForm;
use App\Filament\AdminTahfidz\Resources\Murobbis\Schemas\MurobbiInfolist;
use App\Filament\AdminTahfidz\Resources\Murobbis\Tables\MurobbisTable;
use App\Models\Murobbi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MurobbiResource extends Resource
{
    protected static ?string $model = Murobbi::class;

    protected static ?int $navigationSort = 2;
    protected static string|UnitEnum|null $navigationGroup = 'Utama';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'Daftar Murobbi';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return MurobbiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MurobbiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MurobbisTable::configure($table);
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
            'index' => ListMurobbis::route('/'),
            'create' => CreateMurobbi::route('/create'),
            'view' => ViewMurobbi::route('/{record}'),
            'edit' => EditMurobbi::route('/{record}/edit'),
        ];
    }
}
