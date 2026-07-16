<?php

namespace App\Jobs\Tahfidz;

use App\Models\Tahfidz\Configuration;
use App\Traits\ReminderTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

// use Illuminate\Support\Facades\Log;

class SendReminderMutabaah implements ShouldQueue
{
    use Queueable;
    use ReminderTrait;

    /**
     * Create a new job instance.
     */
    public function __construct(public $penerima, public $waktu)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $kurir = env('MUTABAAH_REMINDER_KURIR', 'ONESENDER');
        $schoolId = $this->penerima['school_id'];
        $reminderConfig = Configuration::where('school_id', $schoolId)
            ->where('name', 'reminderMutabaahMessage')
            ->first();
        $message = Arr::get($reminderConfig, 'payload.' . $this->waktu);
        $message = Str::replace('<NAMA>', $this->penerima['nama'], $message);

        $message .= "\n\n*Berikut nama-nama santri yang belum diinput:*\n";

        foreach ($this->penerima['incompleted_members'] as $name) {
            $message .= "- " . $name . "\n";
        }

        $data = $this->composeReminder($this->penerima['telepon'], $message);

        if ($kurir === 'ONESENDER') {
            $token = env('ONESENDER1_TOKEN');
            $url = env('ONESENDER1_URL');
            Http::withToken($token)->post($url, $data);
        } elseif ($kurir === 'LOG') {
            \Log::info('Reminder mutabaah: ' . $message);
        }
    }
}
