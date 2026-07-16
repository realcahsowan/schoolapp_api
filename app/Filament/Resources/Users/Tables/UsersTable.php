<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Models\ImpersonationToken;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('role', '!=', 'superuser');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                // TextColumn::make('email_verified_at')
                //     ->label('Email Terverifikasi')
                //     ->dateTime()
                //     ->sortable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->searchable(),
                // TextColumn::make('employee_id')
                //     ->label('Pegawai')
                //     ->searchable(),
                // TextColumn::make('student_id')
                //     ->label('Siswa')
                //     ->searchable(),
                // TextColumn::make('guardian_id')
                //     ->label('Wali')
                //     ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'employee' => 'Pegawai',
                        'student' => 'Santri',
                        'guardian' => 'Wali Santri',
                        // Sesuaikan peran lainnya sesuai kebutuhan
                    ]),
                // ->searchable(),
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Actions\Action::make('Buat Link Login Sekali Pakai')
                        ->label('Impersonasi')
                        ->icon('heroicon-o-user')
                        ->modalHeading('Token Impersonasi')
                        ->modalSubmitActionLabel('Tutup')
                        ->modalCancelAction(false)
                        ->schema([
                            Forms\Components\Textarea::make('impersonation_url')
                                ->label('Link Impersonasi')
                                ->default(function (User $record) {
                                    $token = ImpersonationToken::generate($record);

                                    return route('impersonate.token', $token->token);
                                })
                                ->rows(2)
                                ->readOnly()
                                ->helperText('Salin dan buka di browser lain. Berlaku 15 menit.'),
                        ])
                        ->visible(fn() => Auth::user()->role === 'superuser'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
