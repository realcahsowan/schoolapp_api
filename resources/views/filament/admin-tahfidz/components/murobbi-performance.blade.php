<div
    x-data="{ activeTab: 'semester' }"
    x-init="(() => {
        const tabs = $el.querySelectorAll('[role=tab]');
        tabs.forEach(t => {
            t.addEventListener('click', () => {
                activeTab = t.textContent.trim().toLowerCase();
            });
        });
        const init = Array.from(tabs).find(t => t.getAttribute('aria-selected') === 'true');
        if (init) activeTab = init.textContent.trim().toLowerCase();
    })()"
>
    <x-filament::tabs label="Performance">
        <x-filament::tabs.item
            alpine-active="activeTab === 'semester'"
            x-on:click="activeTab = 'semester'"
        >
            Semester
        </x-filament::tabs.item>

        <x-filament::tabs.item
            alpine-active="activeTab === 'bulanan'"
            x-on:click="activeTab = 'bulanan'"
        >
            Bulanan
        </x-filament::tabs.item>

        <x-filament::tabs.item
            alpine-active="activeTab === 'pekanan'"
            x-on:click="activeTab = 'pekanan'"
        >
            Pekanan
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- summary area that adapts to active tab using Alpine --}}
    <div class="mt-4 p-4 bg-white border rounded shadow-sm">
        <template x-if="activeTab === 'semester'">
            <div>
                <div class="text-sm text-gray-600 mb-2">Ringkasan Semester</div>
                @php
                    $totalTarget = $semesterly->sum('target');
                    $totalRealisasi = $semesterly->sum('realisasi');
                    if ($totalTarget > 0) {
                        $totalPercent = round($totalRealisasi / $totalTarget * 100, 2);
                    } else {
                        $totalPercent = $totalRealisasi > 0 ? 100.00 : 0.00;
                    }
                    $percentClass = $totalPercent >= 100 ? 'text-green-600' : ($totalPercent >= 50 ? 'text-yellow-600' : 'text-red-600');
                @endphp
                <div class="flex gap-6 items-center">
                    <div class="text-sm">
                        <div class="text-xs text-gray-500">Total Target</div>
                        <div class="text-lg font-medium text-gray-800">{{ number_format($totalTarget) }}</div>
                    </div>
                    <div class="text-sm">
                        <div class="text-xs text-gray-500">Total Realisasi</div>
                        <div class="text-lg font-medium text-gray-800">{{ number_format($totalRealisasi) }}</div>
                    </div>
                    <div class="text-sm ml-4">
                        <div class="text-xs text-gray-500">Persentase</div>
                        <div class="text-lg font-medium {{ $percentClass }}">{{ $totalPercent }}%</div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="activeTab === 'bulanan'">
            <div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Periode</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Target</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Realisasi</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($monthly as $index => $item)
                                @php
                                    $t = (int) ($item->target ?? 0);
                                    $r = (int) ($item->realisasi ?? 0);
                                    if ($t > 0) {
                                        $p = round($r / $t * 100, 2);
                                    } else {
                                        $p = $r > 0 ? 100.00 : 0.00;
                                    }
                                    $pClass = $p >= 100 ? 'text-green-600' : ($p >= 50 ? 'text-yellow-600' : 'text-red-600');
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 text-gray-700">Bulan {{ $item->angka_periode }}</td>
                                    <td class="px-3 py-2 text-gray-800">{{ number_format($t) }}</td>
                                    <td class="px-3 py-2 text-gray-800">{{ number_format($r) }}</td>
                                    <td class="px-3 py-2"><span class="font-medium {{ $pClass }}">{{ $p }}%</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <template x-if="activeTab === 'pekanan'">
            <div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Periode</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Target</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Realisasi</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($weekly as $index => $item)
                                @php
                                    $t = (int) ($item->target ?? 0);
                                    $r = (int) ($item->realisasi ?? 0);
                                    if ($t > 0) {
                                        $p = round($r / $t * 100, 2);
                                    } else {
                                        $p = $r > 0 ? 100.00 : 0.00;
                                    }
                                    $pClass = $p >= 100 ? 'text-green-600' : ($p >= 50 ? 'text-yellow-600' : 'text-red-600');
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 text-gray-700">Pekan {{ $item->angka_periode }}</td>
                                    <td class="px-3 py-2 text-gray-800">{{ number_format($t) }}</td>
                                    <td class="px-3 py-2 text-gray-800">{{ number_format($r) }}</td>
                                    <td class="px-3 py-2"><span class="font-medium {{ $pClass }}">{{ $p }}%</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</div>