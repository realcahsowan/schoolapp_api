<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataCheckController extends Controller
{
    // public function __construct()
    // {
    //     if (!app()->environment('local')) {
    //         abort(404);
    //     }
    // }

    public function index()
    {
        return view('data-check');
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:users,students,employees,guardians',
            'items' => 'required|string',
        ]);

        $columnMap = [
            'users'     => 'name',
            'students'  => 'nama',
            'employees' => 'nama',
            'guardians' => 'nama',
        ];

        $jenis  = $validated['jenis'];
        $table  = $jenis;
        $column = $columnMap[$jenis];

        $lines  = array_filter(array_map('trim', explode("\n", $validated['items'])), fn ($l) => $l !== '');
        $unique = array_values(array_unique($lines));

        $found = collect(DB::select(
            "SELECT DISTINCT `{$column}` FROM `{$table}` WHERE `{$column}` IN (" . implode(',', array_fill(0, count($unique), '?')) . ')',
            $unique
        ))->pluck($column)->toArray();

        $results = [];
        foreach ($lines as $item) {
            $results[] = [
                'item'   => $item,
                'exists' => in_array($item, $found),
            ];
        }

        $foundCount   = count(array_filter($results, fn ($r) => $r['exists']));
        $missingCount = count($results) - $foundCount;

        return view('data-check', compact('results', 'foundCount', 'missingCount', 'jenis'));
    }
}
