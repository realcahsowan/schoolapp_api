<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Tahfidz\Configuration;
use Filament\Forms;
use Filament\Schemas\Components\Tabs;
// use Filament\Forms\Set;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;

class Pengaturan extends Page
{
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Pengaturan Umum';
    protected static string|UnitEnum|null $navigationGroup = 'Utama';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected string $view = 'filament.admin-tahfidz.pages.pengaturan';

    public array $data = [];
    public int $schoolId;
    public Collection $settings;
    public function mount()
    {
        $school = filament()->getTenant();
        $settings = Configuration::where('school_id', $school->id)->get();
        $this->settings = $settings;
        $this->data = $settings->pluck('payload', 'name')->toArray();
        $this->schoolId = $school->id;
        $this->form->fill($this->data);
        // dd($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')->tabs([
                    Tabs\Tab::make('Programs')
                        ->schema([
                            Forms\Components\Repeater::make('programs')
                                ->label('')
                                ->schema([
                                    // Forms\Components\Hidden::make('id'),
                                    Forms\Components\Hidden::make('slug')
                                        ->required(),
                                    Forms\Components\TextInput::make('nama')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state)))
                                        ->required(),
                                    // Forms\Components\TextInput::make('slug')
                                    //     ->readOnly()
                                    //     ->required(),
                                    Forms\Components\Toggle::make('active')
                                        ->required(),
                                ])
                                ->addable(true)
                                ->grid(2)
                                ->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Categories')
                        ->schema([
                            Forms\Components\Repeater::make('categories')
                                ->label('')
                                ->schema([
                                    // Forms\Components\Hidden::make('id'),
                                    Forms\Components\Hidden::make('slug')
                                        ->required(),
                                    Forms\Components\TextInput::make('nama')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state)))
                                        ->required(),
                                    // Forms\Components\TextInput::make('slug')
                                    //     ->readOnly()
                                    //     ->required(),
                                    // Forms\Components\Toggle::make('active')
                                    //     ->required(),
                                ])
                                ->addable(true)
                                ->grid(2)
                                ->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Mutabaah Harian')
                        ->schema([
                            Section::make('Buka Akses')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\TimePicker::make('aksesMutabaahPagi'),
                                    Forms\Components\TimePicker::make('aksesMutabaahSore'),
                                ])
                                ->columns(2),
                            Section::make('Libur Mutabaah Sore')
                                ->schema([
                                    Forms\Components\Repeater::make('liburMutabaahSore')
                                        ->label('')
                                        ->schema([
                                            Forms\Components\Select::make('day')
                                                ->label('Hari')
                                                ->options([
                                                    'Monday' => 'Senin',
                                                    'Tuesday' => 'Selasa',
                                                    'Wednesday' => 'Rabu',
                                                    'Thursday' => 'Kamis',
                                                    'Friday' => 'Jumat',
                                                    'Saturday' => 'Sabtu',
                                                ]),
                                            Forms\Components\TagsInput::make('categories')
                                                ->label('Kategori Halaqoh')
                                                ->placeholder('Masukkan Kategori Halaqoh')
                                        ])
                                        ->grid(2)
                                ]),

                            Section::make('Waktu Pengingat')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\TimePicker::make('reminderMutabaahTime.pagi'),
                                    Forms\Components\TimePicker::make('reminderMutabaahTime.sore'),
                                ])
                                ->columns(2),

                            Section::make('Pesan Pengingat')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\Textarea::make('reminderMutabaahMessage.pagi')
                                        ->autosize(),
                                    Forms\Components\Textarea::make('reminderMutabaahMessage.sore')
                                        ->autosize(),
                                ])
                                ->columns(2),

                            // Section::make('Jenis Hafalan Pagi')
                            //     ->collapsible()
                            //     ->schema(fn () => $this->getJenisHafalanComponents('pagi'))
                            //     ->columns(count($this->getJenisHafalanComponents('pagi'))),
                            // Section::make('Jenis Hafalan Sore')
                            //     ->collapsible()
                            //     ->schema(fn () => $this->getJenisHafalanComponents('sore'))
                            //     ->columns(count($this->getJenisHafalanComponents('sore'))),
                            Section::make('Poin Pelanggaran')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\Repeater::make('pelanggaran')
                                        ->label('')
                                        ->schema([
                                            // Forms\Components\Hidden::make('id'),
                                            Forms\Components\Hidden::make('slug')
                                                ->required(),
                                            Forms\Components\TextInput::make('jenis')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state)))
                                                ->required(),
                                            // Forms\Components\TextInput::make('slug')
                                            //     ->readOnly()
                                            //     ->required(),
                                            Forms\Components\TextInput::make('point')
                                                ->numeric()
                                                ->required(),
                                        ])
                                        ->addable(true)
                                        ->grid(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tabs\Tab::make('Aspek Penilaian')
                        ->schema([
                            Forms\Components\TextInput::make('kkm')
                                ->label('KKM')
                                ->numeric()
                                ->columnSpanFull(),
                            // Forms\Components\TagsInput::make('aspekPenilaian')
                            //     ->label('Aspek Penilaian')
                            //     ->placeholder('')
                            //     ->disabled()
                            //     ->columnSpanFull()
                            Forms\Components\KeyValue::make('aspekPenilaian')
                                ->keyLabel('Key')
                                ->editableKeys(false)
                                ->valueLabel('Aspek')
                                ->columnSpanFull()
                        ]),
                    Tabs\Tab::make('Penilaian Periodik')
                        ->schema([
                            Forms\Components\TextInput::make('jumlahPelaksanaanPeriodik')
                                ->numeric()
                                ->columnSpanFull(),
                            // Forms\Components\TextInput::make('jumlahSoalPeriodik')
                            //     ->numeric(),
                            Forms\Components\KeyValue::make('jumlahSoalPeriodik')
                                ->default($this->getDefaultJumlahSoalPeriodik())
                                ->keyLabel('Kategori')
                                ->valueLabel('Jumlah Soal')
                                ->editableKeys(false)
                                ->columnSpanFull(),
                            Forms\Components\KeyValue::make('bobotAspekPeriodik')
                                ->keyLabel('Aspek')
                                ->valueLabel('Bobot')
                                ->editableKeys(false)
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('templateLaporanPeriodik')
                                ->columnSpanFull()
                                ->autosize()
                        ]),
                    Tabs\Tab::make('Rapor')
                        ->schema([
                            Forms\Components\TextInput::make('lokasiRapor')
                                ->required(),
                            Forms\Components\DatePicker::make('tanggalRapor')
                                ->date()
                                ->required(),
                            Forms\Components\KeyValue::make('bobotRapor')
                                ->keyLabel('Aspek')
                                ->valueLabel('Bobot')
                                ->editableKeys(false)
                                ->columnSpanFull(),
                            Forms\Components\Repeater::make('petaPredikat')
                                ->label('Peta Predikat')
                                ->schema([
                                    // Forms\Components\Select::make('predikat')
                                    //     ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E'])
                                    //     ->required(),
                                    Forms\Components\TextInput::make('predikat')
                                        ->readOnly()
                                        ->required(),
                                    Forms\Components\TextInput::make('min')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('max')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\Textarea::make('deskripsi')
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columns(3)
                                // ->grid(3)
                                ->columnSpanFull()
                        ])

                ])
                    ->columns(2)
                    ->columnSpanFull(),

                // action button
                // Actions::make([
                    Action::make('submit')
                        ->label('Simpan')
                        ->action(fn () => $this->update()),
                // ])
            ])
            ->statePath('data');
    }

    public function update(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $this->settings->where('name', $key)->first()?->update(['payload' => $value]);
        }

        Notification::make()
            ->title('Settings updated successfully')
            ->success()
            ->seconds(2)
            ->send();
    }

    public function getDefaultJumlahSoalPeriodik()
    {
        $arr = [];
        foreach ($this->settings->where('name', 'categories')->first()->payload as $category) {
            $arr[$category['slug']] = 0;
        }
        return $arr;
    }

    public function getJenisHafalanComponents($waktu)
    {
        $school = filament()->getTenant();
        $name = 'jenisHafalan' . Str::title($waktu);
        $configuration = Configuration::where('school_id', $school->id)->where('name', $name)->first();
        $types = Arr::get($configuration, 'payload', []);
        $components = [];

        foreach (array_keys($types) as $level) {
            array_push(
                $components,
                Forms\Components\Select::make($name . '.' . $level)
                    ->label('Kelas ' . $level)
                    ->options([
                        'hb' => 'Hafalan Baru',
                        'hm' => 'Hafalan Murojaah',
                    ])
            );
        }

        return $components;
    }
}
