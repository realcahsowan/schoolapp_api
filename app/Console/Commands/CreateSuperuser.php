<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateSuperuser extends Command
{
    protected $signature = 'user:superuser';
    protected $description = 'Membuat user dengan role superuser, email dan password ditentukan saat eksekusi.';

    public function handle()
    {
        $email = $this->ask('Masukkan email superuser', 'superboy@madrasah.app');
        $password = $this->secret('Masukkan password superuser');
        $exists = User::where('email', $email)->first();
        if ($exists) {
            $this->warn('User ' . $email . ' sudah ada!');
            return;
        }
        $user = User::create([
            'name' => 'Superuser',
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role' => 'superuser',
        ]);
        $this->info('User superuser berhasil dibuat!');
    }
}
