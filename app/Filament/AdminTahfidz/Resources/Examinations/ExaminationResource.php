<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations;

use App\Filament\AdminTahfidz\Resources\Examinations\Pages\CreateExamination;
use App\Filament\AdminTahfidz\Resources\Examinations\Pages\EditExamination;
use App\Filament\AdminTahfidz\Resources\Examinations\Pages\ListExaminations;
use App\Filament\AdminTahfidz\Resources\Examinations\Pages\ViewExamination;
use App\Filament\AdminTahfidz\Resources\Examinations\Schemas\ExaminationForm;
use App\Filament\AdminTahfidz\Resources\Examinations\Schemas\ExaminationInfolist;
use App\Filament\AdminTahfidz\Resources\Examinations\Tables\ExaminationsTable;
use App\Models\Tahfidz\Examination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExaminationResource extends Resource
{
    protected static ?string $model = Examination::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExaminationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExaminationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExaminationsTable::configure($table);
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
            'index' => ListExaminations::route('/'),
            'create' => CreateExamination::route('/create'),
            'view' => ViewExamination::route('/{record}'),
            'edit' => EditExamination::route('/{record}/edit'),
        ];
    }
}
