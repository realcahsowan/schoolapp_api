<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Exceptions\ImportDataException;
use App\Exports\EmployeesExport;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\Institution;
use App\Settings\GeneralSettings;
use DateTime;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;
    protected int $maxRows = 50;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('Template Pegawai')
                    ->icon('heroicon-m-document-arrow-down')
                    ->action(fn () => Excel::download(new EmployeesExport($this->maxRows), 'employee_template.xlsx')),
                Actions\Action::make('Unggah Pegawai')
                    ->form([
                        Forms\Components\Select::make('institution_id')
                            ->label('Institusi')
                            ->options(Institution::pluck('nama', 'id'))
                            ->required(),
                        Forms\Components\FileUpload::make('employee_data')
                            ->label('File Data Pegawai')
                            ->helperText('Maksimal 50 baris data.')
                            ->disk('local')
                            ->required()
                            ->directory('imports'),
                    ])
                    ->icon('heroicon-m-document-arrow-up')
                    ->action(function ($data) {
                        if ($data['employee_data'] !== null) {
                            $filePath = $data['employee_data'];
                            $array = Excel::toArray(new EmployeesImport, $filePath);
                            try {
                                $this->proccessImport($array[0], $data['institution_id']);
                                Notification::make()
                                    ->title('Data pegawai berhasil diunggah!')
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
            ])
                ->label('Impor Pegawai')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('primary')
                ->button()
        ];
    }

    public function proccessImport($rows, $institutionId)
    {
        $ref = [
            'nama',
            'nik',
            'nip',
            'gender',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'telepon',
            'email',
            'nuptk',
            'peg_id',
            'golongan_darah',
            'tanggal_mulai_bekerja',
            'status_perkawinan',
            'nama_ayah',
            'nama_ibu',
            'nama_pasangan',
            'jumlah_anak',
            'pendidikan_terakhir',
        ];

        $headings = array_shift($rows);

        if (count($rows) > $this->maxRows) {
            throw new ImportDataException('Maksimal baris yang diunggah adalah 50.');
        }

        if (count(array_diff($ref, $headings)) > 0) {
            throw new ImportDataException('Kolom header tidak sesuai dengan template pegawai.');
        }

        $items = [];
        $usersData = [];

        $defaultPassword = app(GeneralSettings::class)->password;
        foreach ($rows as $row) {
            $item = array_combine($headings, $row);

            $item['gender'] = trim($item['gender']);
            $item['tanggal_lahir'] = $this->convertToDateTime($item['tanggal_lahir']);
            $item['tanggal_mulai_bekerja'] = $this->convertToDateTime($item['tanggal_mulai_bekerja']);

            if (!$this->hasValidDate($item['tanggal_lahir'])) {
                throw new ImportDataException('Tanggal lahir ' . $item['nama'] . ' harus berformat tanggal.');
            }

            if (!$this->hasValidDate($item['tanggal_mulai_bekerja'])) {
                throw new ImportDataException('Tanggal mulai bekerja ' . $item['nama'] . ' harus berformat tanggal.');
            }

            if (is_null($item['telepon'])) {
                throw new ImportDataException('Telepon ' . $item['nama'] . ' kosong.');
            }

            if (Str::of($item['telepon'])->startsWith('08')) {
                $item['telepon'] = Str::replaceFirst('08', '628', $item['telepon']);
            }

            array_push($items, $item);
            array_push($usersData, [
                'name' => $item['nama'],
                'email' => $item['email'],
                'password' => bcrypt($defaultPassword),
                'role' => 'employee',
            ]);
        }

        $collection = collect($items);

        if ($collection->pluck('gender')->diff(['male', 'female'])->count() > 0) {
            throw new ImportDataException('Gender harus male atau female dengan huruf kecil.');
        }

        if ($collection->duplicates('nip')->count() > 0) {
            throw new ImportDataException('NIP duplikat ditemukan.');
        }

        if ($collection->duplicates('email')->count() > 0) {
            throw new ImportDataException('Email duplikat ditemukan.');
        }

        if ($collection->pluck('nip')->filter(fn ($nip) => is_null($nip))->count() > 0) {
            throw new ImportDataException('NIP kosong ditemukan.');
        }

        if ($collection->pluck('telepon')->filter(fn ($telp) => is_null($telp))->count() > 0) {
            throw new ImportDataException('Telepon kosong ditemukan.');
        }

        $nips = $collection->pluck('nip');
        $oldData = Employee::whereIn('nip', $nips)->with('user')->get();

        $extra = $collection->whereIn('nip', $oldData->pluck('nip'))->pluck('email');

        if (collect($usersData)->whereNotIn('email', $oldData->pluck('user.email')->merge($extra))->count() === 0) {
            throw new ImportDataException('Tidak ada data baru atau NIP duplikat.');
        }

        $newItems = collect($items)->whereNotIn('nip', $oldData->pluck('nip'));

        $employeesData = $newItems
            ->map(function ($item) use ($institutionId) {
                $item['institution_id'] = $institutionId;
                $item['tanggal_lahir'] = $item['tanggal_lahir']->format('Y-m-d');
                $item['tanggal_mulai_bekerja'] = $item['tanggal_mulai_bekerja']->format('Y-m-d');
                $item['jumlah_anak'] = gettype($item['jumlah_anak']) == 'string' ? 0 : $item['jumlah_anak'];
                $item['created_at'] = now();
                unset($item['email']);
                return $item;
            })
            ->toArray();

        DB::table('employees')->insert($employeesData);

        $employeeIds = DB::table('employees')
            ->whereIn('nip', $newItems->pluck('nip'))
            ->pluck('id', 'nip');

        $usersToInsert = collect($usersData)
            ->whereNotIn('email', $oldData->pluck('user.email')->merge($extra))
            ->map(function ($user) use ($newItems, $employeeIds) {
                $employee = $newItems->firstWhere('email', $user['email']);
                if ($employee) {
                    $user['employee_id'] = Arr::get($employeeIds, $employee['nip']);
                }
                return $user;
            })
            ->toArray();

        DB::table('users')->insert($usersToInsert);
    }

    public function convertToDateTime($input)
    {
        return gettype($input) === 'string' ? DateTime::createFromFormat("Y-m-d", $input) : Date::excelToDateTimeObject($input);
    }

    public function hasValidDate($obj)
    {
        return $obj instanceof DateTime;
    }
}
