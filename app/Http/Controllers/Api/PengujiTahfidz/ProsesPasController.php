<?php

namespace App\Http\Controllers\Api\PengujiTahfidz;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Configuration;
use App\Models\Tahfidz\Examination;
use App\Models\Tahfidz\Mistake;
use App\Traits\QuranTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProsesPasController extends Controller
{
    use QuranTrait;

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'rawScores' => 'required',
            'mistakes' => 'required',
            'nulleds' => 'required',
            'scores' => 'required',
            'score' => 'required',
            'lock' => 'required',
            'hash' => 'required',
        ]);

        $exam = Examination::where('hash', $data['hash'])
            ->with([
                'student' => fn ($q) => $q->with(['school', 'rapor'])
            ])
            ->firstOrFail();

        $mistakes = Mistake::where('examination_id', $exam->id)
            ->where(fn ($query) => $query->whereNull('is_disabled')->orWhere('is_disabled', false))
            ->get();

        foreach ($mistakes as $item) {
            $item->raw_score = $data['rawScores'][(string)$item->page] ?? $item->raw_score;
            $item->detail = $data['mistakes'][(string)$item->page] ?? $item->detail;
            $item->is_nulled = (int) (Arr::get($data['nulleds'], (string)$item->page, 0) ?? 0);
            $item->score = Arr::get($data['scores'], (string)$item->page, $item->score);
            $item->is_locked = (int) (Arr::get($data, 'lock', 0) ?? 0);
            $item->save();
        }

        $rawCollection = collect($data['rawScores']);
        $basePages = $this->generatePages($exam->juz);
        $configuration = Configuration::where('school_id', $exam->student->classroom->school->id)
            ->where('name', 'bobotAspekPas')
            ->first();

        $detail = [];
        if ($configuration && $configuration->payload) {
            foreach (array_keys((array)$configuration->payload) as $aspek) {
                $detail[$aspek] = $rawCollection->sum($aspek) / count($basePages);
            }
        }

        $exam->update([
            'score' => $data['score'],
            'detail' => $detail,
            'is_locked' => Arr::get($data, 'lock', false),
        ]);

        return response()->json([
            'message' => 'PAS berhasil diproses',
            'score' => $data['score'],
            'is_locked' => Arr::get($data, 'lock', false),
        ]);
    }
}
