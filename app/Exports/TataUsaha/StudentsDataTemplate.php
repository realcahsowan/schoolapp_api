<?php

namespace App\Exports\TataUsaha;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsDataTemplate implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    public function array(): array
    {
        return [
            [
                'nama' => 'Ahmad Fauzi',
                'nisn' => '0034567891',
                'nis' => '24101',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2007-03-15',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'telepon' => '08123456701',
                'email' => 'ahmad.fauzi@email.com',
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'nisn' => '0034567892',
                'nis' => '24102',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2008-07-22',
                'alamat' => 'Jl. Diponegoro No. 22, Bandung',
                'telepon' => '08123456702',
                'email' => 'siti.nurhaliza@email.com',
            ],
            [
                'nama' => 'Budi Santoso',
                'nisn' => '0034567893',
                'nis' => '24103',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '2007-11-08',
                'alamat' => 'Jl. Pahlawan No. 5, Surabaya',
                'telepon' => '08123456703',
                'email' => 'budi.santoso@email.com',
            ],
            [
                'nama' => 'Dewi Lestari',
                'nisn' => '0034567894',
                'nis' => '24104',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '2008-01-30',
                'alamat' => 'Jl. Malioboro No. 12, Yogyakarta',
                'telepon' => '08123456704',
                'email' => 'dewi.lestari@email.com',
            ],
            [
                'nama' => 'Rizky Pratama',
                'nisn' => '0034567895',
                'nis' => '24105',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Medan',
                'tanggal_lahir' => '2007-05-19',
                'alamat' => 'Jl. Sumatra No. 8, Medan',
                'telepon' => '08123456705',
                'email' => 'rizky.pratama@email.com',
            ],
            [
                'nama' => 'Rina Wijaya',
                'nisn' => '0034567896',
                'nis' => '24106',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Semarang',
                'tanggal_lahir' => '2008-09-12',
                'alamat' => 'Jl. Pandanaran No. 15, Semarang',
                'telepon' => '08123456706',
                'email' => 'rina.wijaya@email.com',
            ],
            [
                'nama' => 'Doni Saputra',
                'nisn' => '0034567897',
                'nis' => '24107',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Palembang',
                'tanggal_lahir' => '2007-02-25',
                'alamat' => 'Jl. Sriwijaya No. 3, Palembang',
                'telepon' => '08123456707',
                'email' => 'doni.saputra@email.com',
            ],
            [
                'nama' => 'Maya Anggraini',
                'nisn' => '0034567898',
                'nis' => '24108',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Makassar',
                'tanggal_lahir' => '2008-06-14',
                'alamat' => 'Jl. Hasanuddin No. 7, Makassar',
                'telepon' => '08123456708',
                'email' => 'maya.anggraini@email.com',
            ],
            [
                'nama' => 'Hendra Gunawan',
                'nisn' => '0034567899',
                'nis' => '24109',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Bogor',
                'tanggal_lahir' => '2007-08-20',
                'alamat' => 'Jl. Raya Puncak No. 18, Bogor',
                'telepon' => '08123456709',
                'email' => 'hendra.gunawan@email.com',
            ],
            [
                'nama' => 'Fitri Handayani',
                'nisn' => '0034567801',
                'nis' => '24110',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Malang',
                'tanggal_lahir' => '2008-04-02',
                'alamat' => 'Jl. Ijen No. 9, Malang',
                'telepon' => '08123456710',
                'email' => 'fitri.handayani@email.com',
            ],
            [
                'nama' => 'Agus Setiawan',
                'nisn' => '0034567802',
                'nis' => '24111',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Depok',
                'tanggal_lahir' => '2007-10-11',
                'alamat' => 'Jl. Margonda No. 45, Depok',
                'telepon' => '08123456711',
                'email' => 'agus.setiawan@email.com',
            ],
            [
                'nama' => 'Nadia Kusuma',
                'nisn' => '0034567803',
                'nis' => '24112',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Tangerang',
                'tanggal_lahir' => '2008-12-05',
                'alamat' => 'Jl. BSD Raya No. 20, Tangerang',
                'telepon' => '08123456712',
                'email' => 'nadia.kusuma@email.com',
            ],
            [
                'nama' => 'Andi Firmansyah',
                'nisn' => '0034567804',
                'nis' => '24113',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Bekasi',
                'tanggal_lahir' => '2007-01-28',
                'alamat' => 'Jl. Kalimalang No. 33, Bekasi',
                'telepon' => '08123456713',
                'email' => 'andi.firmansyah@email.com',
            ],
            [
                'nama' => 'Lina Marlina',
                'nisn' => '0034567805',
                'nis' => '24114',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Aceh',
                'tanggal_lahir' => '2008-03-17',
                'alamat' => 'Jl. Syiah Kuala No. 6, Banda Aceh',
                'telepon' => '08123456714',
                'email' => 'lina.marlina@email.com',
            ],
            [
                'nama' => 'Candra Wijaya',
                'nisn' => '0034567806',
                'nis' => '24115',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Lampung',
                'tanggal_lahir' => '2007-06-09',
                'alamat' => 'Jl. Teuku Umar No. 14, Bandar Lampung',
                'telepon' => '08123456715',
                'email' => 'candra.wijaya@email.com',
            ],
            [
                'nama' => 'Putri Ayuningtyas',
                'nisn' => '0034567807',
                'nis' => '24116',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Bali',
                'tanggal_lahir' => '2008-08-23',
                'alamat' => 'Jl. Sunset Road No. 27, Denpasar',
                'telepon' => '08123456716',
                'email' => 'putri.ayuningtyas@email.com',
            ],
            [
                'nama' => 'Eko Prasetyo',
                'nisn' => '0034567808',
                'nis' => '24117',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Padang',
                'tanggal_lahir' => '2007-04-04',
                'alamat' => 'Jl. Sutan Syahrir No. 11, Padang',
                'telepon' => '08123456717',
                'email' => 'eko.prasetyo@email.com',
            ],
            [
                'nama' => 'Sari Dewi',
                'nisn' => '0034567809',
                'nis' => '24118',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Pontianak',
                'tanggal_lahir' => '2008-10-29',
                'alamat' => 'Jl. Tanjungpura No. 5, Pontianak',
                'telepon' => '08123456718',
                'email' => 'sari.dewi@email.com',
            ],
            [
                'nama' => 'Fajar Ramadhan',
                'nisn' => '0034567810',
                'nis' => '24119',
                'gender' => 'Laki-laki',
                'tempat_lahir' => 'Samarinda',
                'tanggal_lahir' => '2007-09-16',
                'alamat' => 'Jl. Mulawarman No. 21, Samarinda',
                'telepon' => '08123456719',
                'email' => 'fajar.ramadhan@email.com',
            ],
            [
                'nama' => 'Intan Permata Sari',
                'nisn' => '0034567811',
                'nis' => '24120',
                'gender' => 'Perempuan',
                'tempat_lahir' => 'Manado',
                'tanggal_lahir' => '2008-02-10',
                'alamat' => 'Jl. Sam Ratulangi No. 4, Manado',
                'telepon' => '08123456720',
                'email' => 'intan.permata@email.com',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NISN',
            'NIS',
            'Gender',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Telepon',
            'Email',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama'],
            $row['nisn'],
            $row['nis'],
            $row['gender'],
            $row['tempat_lahir'],
            Date::dateTimeToExcel(\Carbon\Carbon::parse($row['tanggal_lahir'])),
            $row['alamat'],
            $row['telepon'],
            $row['email'],
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    }
}
