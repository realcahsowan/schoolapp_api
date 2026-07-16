<?php
namespace App\Jobs\Tahfidz;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GenerateJournalPerformanceService;

class GenerateJournalPerformanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $murobbiId;
    public string $periode;
    public ?int $number;

    public function __construct(int $murobbiId, string $periode, ?int $number = null)
    {
        $this->murobbiId = $murobbiId;
        $this->periode = $periode;
        $this->number = $number;
    }

    public function handle(GenerateJournalPerformanceService $service)
    {
        $service->execute($this->murobbiId, $this->periode, $this->number);
    }
}