<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Filament\Schemas\Schema;
use UnitEnum;

class KirimPesanWhatsapp extends Page
{
    use InteractsWithForms;

    protected static ?int $navigationSort = 20;
    protected static string|UnitEnum|null $navigationGroup = 'Utilitas';
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedChatBubbleBottomCenterText;
    protected string $view = 'filament.pages.kirim-pesan-whatsapp';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('sender')
                    ->label('Sender')
                    ->options([
                        'sender1' => 'Sender 1 (Pegawai)',
                        'sender2' => 'Sender 2 (Wali Murid)',
                    ])
                    ->required(),
                TextInput::make('receiver')
                    ->label('Penerima (Nomor)')
                    ->numeric()
                    ->required(),
                Textarea::make('message')
                    ->label('Pesan')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function sendWhatsapp(): void
    {
        $data = $this->form->getState();
        $token = $url = null;
        if (($data['sender'] ?? null) === 'sender1') {
            $token = env('ONESENDER1_TOKEN');
            $url = env('ONESENDER1_URL');
        } elseif (($data['sender'] ?? null) === 'sender2') {
            $token = env('ONESENDER2_TOKEN');
            $url = env('ONESENDER2_URL');
        }
        try {
            $payload = [
                'recipient_type' => 'individual',
                'to' => (string) $data['receiver'],
                'type' => 'text',
                'text' => ['body' => $data['message']],
            ];
            // \Log::info([$token, $url, $payload]);
            Http::withToken($token)->post($url, $payload);
            Notification::make()
                ->success()
                ->title('Pesan berhasil dikirim!')
                ->send();
            $this->form->fill([]);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal mengirim pesan')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('Kirim')
                ->label('Kirim')
                ->action('sendWhatsapp')
                ->requiresConfirmation(),
        ];
    }
}
