{{-- Tailwind-styled table for missing murobbis with gender tabs and search --}}
<div x-data="{ tab: 'male', search: '' }" class="min-w-[320px]">
    <div class="flex gap-2 p-2 pb-0">
        <div x-on:click.stop="tab = 'male'" x-bind:class="tab === 'male' ? 'bg-blue-100 text-blue-600 font-semibold' : 'bg-white text-gray-500'" class="px-4 py-2 rounded-t border border-b-0 cursor-pointer select-none">Laki-laki</div>
                <div x-on:click.stop="tab = 'female'" x-bind:class="tab === 'female' ? 'bg-pink-100 text-pink-600 font-semibold' : 'bg-white text-gray-500'" class="px-4 py-2 rounded-t border border-b-0 cursor-pointer select-none">Perempuan</div>
    </div>

    <div class="p-3 bg-white border rounded-b shadow-sm">
        <input x-model="search" type="text" placeholder="Cari nama..." class="mb-4 w-full px-3 py-2 rounded border text-sm focus:ring focus:border-blue-300" />

        <template x-if="tab === 'male'">
            <div>
                @if($maleMurobbis->isEmpty())
                    <div class="flex items-center gap-3 px-2 py-4 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                        </svg>
                        <div>Tidak ada. Semua murobbi laki-laki sudah input hari ini.</div>
                    </div>
                @else
                    <div class="max-h-[50vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @php $no = 1; @endphp
                                @foreach($maleMurobbis as $murobbi)
                                    <tr x-show="search === '' || '{{ strtolower($murobbi->nama) }}'.includes(search.toLowerCase())" class="hover:bg-gray-50 border-b border-gray-200">
                                        <td class="px-4 py-3 whitespace-nowrap text-left text-sm text-gray-700">{{ $no++ }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-left text-sm font-medium text-gray-900">{{ $murobbi->nama }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </template>

        <template x-if="tab === 'female'">
            <div>
                @if($femaleMurobbis->isEmpty())
                    <div class="flex items-center gap-3 px-2 py-4 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                        </svg>
                        <div>Tidak ada. Semua murobbi perempuan sudah input hari ini.</div>
                    </div>
                @else
                    <div class="max-h-[50vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @php $no = 1; @endphp
                                @foreach($femaleMurobbis as $murobbi)
                                    <tr x-show="search === '' || '{{ strtolower($murobbi->nama) }}'.includes(search.toLowerCase())" class="hover:bg-gray-50 border-b border-gray-200">
                                        <td class="px-4 py-3 whitespace-nowrap text-left text-sm text-gray-700">{{ $no++ }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-left text-sm font-medium text-gray-900">{{ $murobbi->nama }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </template>
    </div>
</div>
