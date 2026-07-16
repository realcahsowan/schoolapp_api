<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendWhatsappActivation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $telepon, public $kode)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = env('ONESENDER2_TOKEN');
        $url = env('ONESENDER2_URL');

        $message = $this->getActivationMessage($this->kode);

        $data = $this->composeReminder($this->telepon, $message);
        Http::withToken($token)->post($url, $data);
    }

    public function getActivationMessage($kode)
    {
        return "Assalamualaikum\n"
            . 'Berikut kode aktivasi telepon wali santri di web ' . config('app.name') . "\n"
            . $kode;
    }

    public function composeReminder($telepon, $message)
    {
        return [
            'recipient_type' => 'individual',
            'to' => (string) $telepon,
            'type' => 'text',
            'text' => ['body' => $message],
        ];
    }
}
