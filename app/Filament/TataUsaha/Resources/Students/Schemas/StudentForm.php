<?php

namespace App\Filament\TataUsaha\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        $tenant = filament()->getTenant();
        // dd($tenant->classrooms()->currentYear()->pluck('nama', 'id')->sort()->toArray());
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('nis')
                    ->label('NIS'),
                TextInput::make('nisn')
                    ->label('NISN'),
                // TextInput::make('nik')
                //     ->label('NIK'),
                TextInput::make('tempat_lahir'),
                DatePicker::make('tanggal_lahir'),
                Select::make('gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                TextInput::make('telepon')
                    ->tel(),
                TextInput::make('anak_ke')
                    ->numeric(),
                TextInput::make('jumlah_saudara')
                    ->numeric(),
                // TextInput::make('sekolah_asal'),
                // TextInput::make('nomor_ijazah'),
                // TextInput::make('riwayat_kelas'),
                // Toggle::make('is_graduated')
                //     ->required(),
                // Toggle::make('is_beasiswa')
                //     ->required(),
                // Toggle::make('is_active')
                //     ->required(),
                Toggle::make('has_siblings')
                    ->required(),
                Select::make('classroom_id')
                    ->relationship(
                        name: 'classroom',
                        titleAttribute: 'nama',
                        modifyQueryUsing: fn(Builder $query) => $query->currentYear(),
                    ),
                // ->options($tenant->classrooms()->currentYear()->pluck('nama', 'id')->sort()->toArray()),
                // TextInput::make('virtual_account'),
                // TextInput::make('agama'),
                FileUpload::make('file_foto')->columnSpanFull(),
                // TextInput::make('pendidikan'),
                // TextInput::make('kode_emis'),
                // TextInput::make('propinsi'),
                // TextInput::make('kabupaten_kota'),
                // TextInput::make('kecamatan'),
                // TextInput::make('kelurahan'),
                // TextInput::make('kodepos'),
                // TextInput::make('tingkat_id')
                //     ->numeric(),
                // TextInput::make('classroom_id')
                //     ->numeric(),
            ]);
    }
}
