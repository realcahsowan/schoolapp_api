<?php

namespace App\Filament\TataUsaha\Resources\Guardians\Pages;

use App\Exceptions\ImportDataException;
use App\Exports\DummyGuardiansExport;
use App\Exports\GuardiansExport;
use App\Filament\TataUsaha\Resources\Guardians\GuardianResource;
use App\Imports\GuardiansImport;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ListGuardians extends ListRecords
{
    protected static string $resource = GuardianResource::class;
    protected int $maxRows = 50;

    protected function getHeaderActions(): array
    {
        $school = Filament::getTenant();
        return [
            Actions\CreateAction::make()
                ->label('Tambah Wali'),
            Actions\ActionGroup::make([
                Actions\Action::make('Template Orang Tua')
                    ->icon('heroicon-m-document-arrow-down')
                    ->action(fn () => Excel::download(new DummyGuardiansExport($this->maxRows), 'orangtua_template.xlsx')),
                Actions\Action::make('Unggah Data Orang Tua')
                    ->form([
                        Forms\Components\FileUpload::make('data_orangtua')
                            ->label('File Data Orang Tua')
                            ->helperText('Maksimal 50 baris data.')
                            ->disk('local')
                            ->required()
                            ->directory('imports'),
                    ])
                    ->icon('heroicon-m-document-arrow-up')
                    ->action(function ($data) {
                        if ($data['data_orangtua'] !== null) {
                            $filePath = $data['data_orangtua'];
                            $array = Excel::toArray(new GuardiansImport(), $filePath);
                            try {
                                $this->proccessImport($array[0]);
                                Notification::make()
                                    ->title('Data orang tua berhasil diunggah!')
                                    ->success()
                                    ->color('success')
                                    ->send();
                            } catch (ImportDataException $e) {
                                Notification::make()
                                    ->title($e->getMessage())
                                    ->danger()
                                    ->color('danger')
                                    ->persistent()
                                    ->send();
                            }
                        }
                    }),
                Actions\Action::make('Unduh Data Wali Santri')
                    ->icon('heroicon-m-document-arrow-down')
                    ->action(fn () => Excel::download(new GuardiansExport($school), 'data-wali-santri-' . ($school->alias ?? $school->id) . '.xlsx')),
            ])
                ->label('Kelola Data Orang Tua (Wali)')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('primary')
                ->button()
        ];
    }

    public function proccessImport($rows)
    {
        $ref = ['nama', 'gender', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'telepon', 'hidup', 'status', 'pekerjaan', 'email'];
        $headings = collect(array_shift($rows))->map(fn ($heading) => Str::slug($heading, '_'));

        if (count($rows) > $this->maxRows) {
            throw new ImportDataException('Maksimal baris yang diunggah adalah 50.');
        }

        if ($headings->diff($ref)->count() > 0) {
            throw new ImportDataException('Kolom header tidak sesuai dengan template orang tua.');
        }

        $items = [];
        $usersData = [];

        $defaultPassword = app(GeneralSettings::class)->password;
        foreach ($rows as $row) {
            $item = array_combine($ref, $row);
            $item['tanggal_lahir'] = $this->convertToDateTime($item['tanggal_lahir']);
            array_push($items, $item);
        }

        $collection = collect($items);

        if ($collection->duplicates('email')->count() > 0) {
            throw new ImportDataException('Email duplikat ditemukan.');
        }

        foreach ($collection as $item) {
            array_push($usersData, [
                'name' => $item['nama'],
                'email' => $item['email'],
                'password' => bcrypt($defaultPassword),
                'role' => 'parent',
            ]);
        }

        DB::transaction(function () use ($items, $usersData) {
            User::whereIn('email', collect($items)->pluck('email'))->delete();

            $guardiansData = collect($items)->map(function ($item) {
                $item['tanggal_lahir'] = $item['tanggal_lahir'] instanceof \DateTime
                    ? $item['tanggal_lahir']->format('Y-m-d')
                    : $item['tanggal_lahir'];
                $item['created_at'] = now();
                $item['relation_type'] = match ($item['gender']) {
                    'male' => 'ayah',
                    'female' => 'ibu',
                };
                $item['relation_status'] = $item['status'];
                $item['is_alive'] = $item['hidup'] ?? true;
                unset($item['email'], $item['hidup'], $item['status']);
                return $item;
            });

            DB::table('guardians')->insert($guardiansData->toArray());

            $guardianIds = DB::table('guardians')
                ->whereIn('nama', $guardiansData->pluck('nama'))
                ->pluck('id', 'nama');

            $usersToInsert = collect($usersData)->map(function ($user) use ($items, $guardianIds) {
                $item = collect($items)->firstWhere('email', $user['email']);
                if ($item) {
                    $user['guardian_id'] = Arr::get($guardianIds, $item['nama']);
                }
                return $user;
            })->toArray();

            DB::table('users')->insert($usersToInsert);

            $school = Filament::getTenant();
            $guardianSchoolRelations = $guardianIds->map(fn ($id) => [
                'guardian_id' => $id,
                'school_id' => $school->id,
            ])->toArray();

            if (!empty($guardianSchoolRelations)) {
                DB::table('guardian_school')->insert($guardianSchoolRelations);
            }
        });
    }

    public function convertToDateTime($input)
    {
        if ($input instanceof \DateTime) {
            return $input;
        }
        return gettype($input) === 'string'
            ? \DateTime::createFromFormat('Y-m-d', $input)
            : Date::excelToDateTimeObject($input);
    }
}
