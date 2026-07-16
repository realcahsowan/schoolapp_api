<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Schemas;

use App\Settings\GeneralSettings;
use App\Traits\CalendarTrait;
use App\Traits\QuranTrait;
use App\Traits\SekolahTrait;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Fieldset;

class KalenderHafalanForm
{
    use SekolahTrait;
    use CalendarTrait;
    use QuranTrait;

    public static function configure(Schema $schema): Schema
    {
        $school = filament()->getTenant();
        return $schema
            ->components([
                Fieldset::make('Hari & Tanggal')
                     ->schema([
                         Forms\Components\Hidden::make('school_id')
                             ->default($school->id),
                         Forms\Components\Hidden::make('tahun_ajaran')
                             ->default(app(GeneralSettings::class)->tahun_ajaran),
                         Forms\Components\Hidden::make('semester')
                             ->default(app(GeneralSettings::class)->semester),
                         Forms\Components\Hidden::make('year'),
                         Forms\Components\Select::make('month')
                             ->options(fn() => static::getIndonesianMonths())
                             ->required()
                             ->label('Bulan'),
                         Forms\Components\TextInput::make('week')
                             ->numeric()
                             ->required()
                             ->label('Pekan'),
                         Forms\Components\TextInput::make('day')
                             ->numeric()
                             ->required()
                             ->label('Hari'),
                         Forms\Components\DatePicker::make('tanggal')
                             ->required()
                             ->afterStateUpdated(fn($state, Set $set) => $set('year', (new Carbon($state))->format('Y'))),
                         Forms\Components\Toggle::make('is_hp_only')
                             ->label('Hafalan Pagi Saja?')
                             ->live()
                             ->columnSpan(2),
                         Forms\Components\Toggle::make('is_weekly_examination')
                             ->label('Ujian Pekanan')
                             ->live(),
                     ])
                     ->columns(4)
                     ->columnSpanFull(),
                Tabs::make('target')
                    ->tabs(fn() => static::getDetails($school))
                    ->contained(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function getDetails($school)
    {
        $levels = static::getKelasLevelOptions($school->jenjang);

        if ($school->jenjang === 'atas') {
            array_unshift($levels, 'idad');
        }

        $components = [];

        foreach ($levels as $level) {
            $tab = Tabs\Tab::make('kelas_' . $level)
                ->label('Kelas ' . Str::upper($level))
                ->schema([
                    Section::make('Hafalan Pagi')
                        ->hidden(fn(Get $get) => $get('weekly_examination') === true)
                        ->schema([
                            Forms\Components\Repeater::make('hp_summary.' . $level)
                                ->label('')
                                ->schema([
                                    Forms\Components\Select::make('jenis')
                                        ->required()
                                        ->options([
                                            'tlw' => 'Tilawah',
                                            'hb' => 'Hafalan Baru',
                                            'hm' => 'Hafalan Murojaah',
                                            'up' => 'Ujian Pekanan',
                                        ])
                                        ->searchable()
                                        ->label('Jenis Hafalan'),
                                    Forms\Components\Select::make('surat')
                                        ->required()
                                        ->options(fn() => static::getSurahNames())
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $ayatData = static::getAyatsOfSurat($state);
                                            $set('awal', Arr::get($ayatData, 'awal'));
                                            $set('akhir', Arr::get($ayatData, 'akhir'));
                                        })
                                        ->label('Nama Surat'),
                                    Forms\Components\TextInput::make('awal')
                                        ->required()
                                        ->numeric()
                                        ->label('Awal (Nomor Ayat)'),
                                    Forms\Components\TextInput::make('akhir')
                                        ->required()
                                        ->numeric()
                                        ->label('Akhir (Nomor Ayat)'),

                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->compact(),
                    Section::make(function (Get $get) {
                        return $get('weekly_examination') === true ? 'Halaman Ujian Pekanan' : 'Hafalan Sore';
                    })
                        ->schema([
                            Forms\Components\Repeater::make('hs_summary.' . $level)
                                ->label('')
                                ->schema([
                                    Forms\Components\Select::make('jenis')
                                        ->required()
                                        ->options([
                                            'tlw' => 'Tilawah',
                                            'hb' => 'Hafalan Baru',
                                            'hm' => 'Hafalan Murojaah',
                                            'up' => 'Ujian Pekanan',
                                        ])
                                        ->searchable()
                                        ->label('Jenis Hafalan'),
                                    Forms\Components\Select::make('surat')
                                        ->required()
                                        ->options(fn() => static::getSurahNames())
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $ayatData = static::getAyatsOfSurat($state);
                                            $set('awal', Arr::get($ayatData, 'awal'));
                                            $set('akhir', Arr::get($ayatData, 'akhir'));
                                        })
                                        ->label('Nama Surat'),
                                    Forms\Components\TextInput::make('awal')
                                        ->required()
                                        ->numeric()
                                        ->label('Awal (Nomor Ayat)'),
                                    Forms\Components\TextInput::make('akhir')
                                        ->required()
                                        ->numeric()
                                        ->label('Akhir (Nomor Ayat)'),

                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->compact()
                        ->hidden(fn(Get $get) => $get('hp_only') === true),
                ]);
            array_push($components, $tab);
        }

        return $components;
    }
}
