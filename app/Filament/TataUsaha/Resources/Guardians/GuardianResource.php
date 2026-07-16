<?php

namespace App\Filament\TataUsaha\Resources\Guardians;

use App\Filament\TataUsaha\Resources\Guardians\Pages\CreateGuardian;
use App\Filament\TataUsaha\Resources\Guardians\Pages\EditGuardian;
use App\Filament\TataUsaha\Resources\Guardians\Pages\ListGuardians;
use App\Filament\TataUsaha\Resources\Guardians\Pages\ViewGuardian;
use App\Filament\TataUsaha\Resources\Guardians\Schemas\GuardianForm;
use App\Filament\TataUsaha\Resources\Guardians\Schemas\GuardianInfolist;
use App\Filament\TataUsaha\Resources\Guardians\Tables\GuardiansTable;
use App\Models\Guardian;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GuardianResource extends Resource
{
    protected static ?string $model = Guardian::class;

    protected static ?string $tenantOwnershipRelationshipName = 'schools';

    protected static string|UnitEnum|null $navigationGroup = 'Civitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getLabel(): string
    {
        return 'Wali';
    }

    public static function getPluralLabel(): string
    {
        return 'Daftar Wali Santri';
    }

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return GuardianForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GuardianInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuardiansTable::configure($table);
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
            'index' => ListGuardians::route('/'),
            'create' => CreateGuardian::route('/create'),
            'view' => ViewGuardian::route('/{record}'),
            'edit' => EditGuardian::route('/{record}/edit'),
        ];
    }
}
