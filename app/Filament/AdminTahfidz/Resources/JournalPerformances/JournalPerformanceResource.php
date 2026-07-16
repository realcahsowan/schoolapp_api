<?php

namespace App\Filament\AdminTahfidz\Resources\JournalPerformances;

use App\Filament\AdminTahfidz\Resources\JournalPerformances\Pages\CreateJournalPerformance;
use App\Filament\AdminTahfidz\Resources\JournalPerformances\Pages\EditJournalPerformance;
use App\Filament\AdminTahfidz\Resources\JournalPerformances\Pages\ListJournalPerformances;
use App\Filament\AdminTahfidz\Resources\JournalPerformances\Schemas\JournalPerformanceForm;
use App\Filament\AdminTahfidz\Resources\JournalPerformances\Tables\JournalPerformancesTable;
use App\Models\Tahfidz\JournalPerformance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JournalPerformanceResource extends Resource
{
    protected static ?string $model = JournalPerformance::class;
    protected static ?int $navigationSort = 20;
    protected static string|UnitEnum|null $navigationGroup = 'Monitoring Proses';

    protected static ?string $navigationLabel = 'Performa Murobbi';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function form(Schema $schema): Schema
    {
        return JournalPerformanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalPerformancesTable::configure($table);
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
            'index' => ListJournalPerformances::route('/'),
            // 'create' => CreateJournalPerformance::route('/create'),
            // 'edit' => EditJournalPerformance::route('/{record}/edit'),
        ];
    }
}
