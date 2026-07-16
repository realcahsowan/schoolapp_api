<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettingsPage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    public function getTitle(): string
    {
        return 'Pengaturan Umum';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Umum';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun_ajaran')
                    ->required(),
                TextInput::make('semester')
                    ->numeric()
                    ->integer()
                    ->required(),
                TextInput::make('years')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),

                TextInput::make('guardianapp_url')
                    ->label('Guardian App URL')
                    ->url()
                    ->required(),

                TextInput::make('mentorapp_url')
                    ->label('Mentor App URL')
                    ->url()
                    ->required(),

                // TextInput::make('tingkat')
                //     ->required(),
                // TextInput::make('jurusan')
                //     ->required(),
                \Filament\Forms\Components\KeyValue::make('roles')
                    ->label('Roles')
                    ->deletable(false)
                    ->addable(false)
                    ->editableKeys(false)
                    ->editableValues(false)
                    ->required()
                    ->columnSpanFull(),

                \Filament\Forms\Components\KeyValue::make('jabatans')
                    ->label('Jabatan')
                    ->deletable(false)
                    ->addable(false)
                    ->editableKeys(false)
                    ->editableValues(false)
                    ->required()
                    ->columnSpanFull(),

                // TextInput::make('achievement_conversion')
                //     ->required(),

                \Filament\Forms\Components\KeyValue::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->deletable(false)
                    ->addable(false)
                    ->editableKeys(false)
                    ->editableValues(false)
                    ->required()
                    ->columnSpanFull(),

                \Filament\Forms\Components\Repeater::make('kurikulum')
                    ->label('Kurikulum')
                    ->grid(2)
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('nama')
                            ->label('Nama Kurikulum')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('alias')
                            ->label('Alias')
                            ->required(),
                    ])
                    ->required(),
            ]);
    }
}
