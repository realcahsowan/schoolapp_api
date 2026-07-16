<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Tahfidz\Configuration;
use App\Settings\GeneralSettings;
use App\Traits\HalaqohTrait;
use App\Traits\SekolahTrait;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PengaturanPas extends Page
{
    protected static ?int $navigationSort = 11;
    protected static ?string $title = 'Pengaturan PAS';
    protected static string|UnitEnum|null $navigationGroup = 'Penilaian Akhir Semester';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsVertical;

    protected string $view = 'filament.admin-tahfidz.pages.pengaturan-pas';


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
        // dd($this->data['bobotAspekPas']);
    }


    public function form(Schema $form): Schema
    {
        $semester = app(GeneralSettings::class)->semester;
        return $form
            ->schema([
                Tabs::make('Tabs')->tabs([
                    Tabs\Tab::make('Aspek & Kriteria')
                        ->schema([
                            Section::make('Bobot Aspek Ujian')
                                ->schema([
                                    Forms\Components\KeyValue::make('bobotAspekPas')
                                        ->label(''),
                                ]),
                            Section::make('Poin Kriteria')
                                ->schema([
                                    Forms\Components\Repeater::make('poinKriteriaPas')
                                        ->label('')
                                        ->schema([
                                            Forms\Components\TextInput::make('aspek')
                                                ->readOnly(),
                                            Forms\Components\Repeater::make('bobot')
                                                ->label('Bobot Kriteria')
                                                ->schema([
                                                    Forms\Components\TextInput::make('label')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn(string $state, Set $set) => $set('kriteria', Str::slug($state, "_")))
                                                        ->required(),
                                                    Forms\Components\TextInput::make('kriteria')
                                                        ->readOnly()
                                                        ->required(),
                                                    Forms\Components\TextInput::make('poin')
                                                        ->numeric()
                                                        ->required(),
                                                ])
                                                ->addable(true)
                                                ->grid(3),
                                        ])
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->grid(1),
                                ]),
                        ]),
                    Tabs\Tab::make('Juz Ujian')
                        ->schema([
                            Section::make('Pengaturan Juz Ujian PAS')
                                ->schema([
                                    Forms\Components\Repeater::make('juzUjian')
                                        // ->view('forms.compact-repeater')
                                        ->label('')
                                        ->columns(1)
                                        ->schema([
                                            Section::make(fn($state) => 'Semester ' . $state['semester'])
                                                ->collapsible()
                                                ->collapsed(fn($state) => $state['semester'] !== $semester)
                                                ->schema([
                                                    Forms\Components\Repeater::make('detail')
                                                        ->label('')
                                                        ->schema([
                                                            // here the detail: program, juz_map, etc...
                                                            Forms\Components\Select::make('program')
                                                                ->options($this->getTahfidzProgramOptions())
                                                                ->required(),
                                                            Forms\Components\Repeater::make('juz_map')
                                                                ->label('Peta Juz')
                                                                ->grid(3)
                                                                ->schema([
                                                                    Forms\Components\Select::make('grade')
                                                                        ->options($this->getGradeOptions())
                                                                        ->required(),
                                                                    Forms\Components\TagsInput::make('juz')
                                                                        ->placeholder('Type juz number then enter')
                                                                        ->required()
                                                                        ->nestedRecursiveRules([
                                                                            'min:1',
                                                                            'max:30',
                                                                        ]),
                                                                ])
                                                                ->addActionLabel('Add Peta Juz'),

                                                        ])
                                                        ->addActionLabel('Add Juz Ujian'),
                                                ]),
                                            // Forms\Components\TextInput::make('semester')
                                            //     ->numeric()
                                            //     ->readOnly(),
                                        ])
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->contained(false),

                // action button
                // Forms\Components\Actions::make([
                Action::make('submit')
                    ->action(fn() => $this->update()),
                // ])
            ])
            ->statePath('data');
    }

    // Kelas (Grade)
    public function getGradeOptions()
    {
        $sekolah = filament()->getTenant();
        $levels = static::getKelasLevelOptions($sekolah->jenjang);

        $options = array_combine($levels, $levels);
        if ($sekolah->jenjang === 'atas') {
            $options['idad'] = 'IDAD';
        }

        return $options;
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

    public static function getKelasLevelOptions(string $jenjang): array
    {
        $levels = [
            'dasar' => range(1, 6),
            'menengah' => range(7, 9),
            'atas' => range(10, 12),
        ];

        return Arr::get($levels, $jenjang, []);
    }

    public function getTahfidzProgramOptions(): array
    {
        $school = filament()->getTenant();
        $configuration = Configuration::where(fn($query) => $query->where('school_id', $school->id)->where('name', 'programs'))->first();

        if (is_null($configuration)) {
            return [];
        }

        return collect($configuration->payload)
            ->where('active', true)
            ->pluck('nama', 'slug')
            ->toArray();
    }

}
