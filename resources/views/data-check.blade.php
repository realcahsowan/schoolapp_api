<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Check</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f9fafb; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 2rem; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: .375rem; }
        select, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: .5rem .75rem; font-size: .875rem; outline: none; }
        select:focus, textarea:focus { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.2); }
        textarea { height: 200px; resize: vertical; font-family: ui-monospace, monospace; }
        button { background: #f59e0b; color: #fff; border: none; padding: .625rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:hover { background: #d97706; }
        .summary { margin-top: 1.5rem; padding: 1rem; border-radius: 8px; font-size: .875rem; display: flex; gap: 1.5rem; }
        .summary.all { background: #f0f9ff; color: #0369a1; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: .875rem; }
        th { text-align: left; padding: .5rem .75rem; border-bottom: 2px solid #e5e7eb; font-weight: 600; }
        td { padding: .5rem .75rem; border-bottom: 1px solid #f3f4f6; }
        tr.exists { background: #ecfdf5; }
        tr.not-exists { background: #fef2f2; }
        .badge { display: inline-block; padding: .125rem .5rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
        .badge.yes { background: #bbf7d0; color: #166534; }
        .badge.no { background: #fecaca; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data Check</h1>

        <form method="POST" action="{{ route('data-check.post') }}">
            @csrf
            <div class="form-group">
                <label for="jenis">Jenis Data</label>
                <select name="jenis" id="jenis">
                    <option value="">-- Pilih --</option>
                    @php $selected = old('jenis', $jenis ?? ''); @endphp
                    <option value="users" {{ $selected === 'users' ? 'selected' : '' }}>Users</option>
                    <option value="students" {{ $selected === 'students' ? 'selected' : '' }}>Students</option>
                    <option value="employees" {{ $selected === 'employees' ? 'selected' : '' }}>Employees</option>
                    <option value="guardians" {{ $selected === 'guardians' ? 'selected' : '' }}>Guardians</option>
                </select>
            </div>

            <div class="form-group">
                <label for="items">Items (satu per baris)</label>
                <textarea name="items" id="items" placeholder="Ahmad&#10;Fatimah&#10;Budi">{{ old('items') }}</textarea>
            </div>

            <button type="submit">Cek Data</button>
        </form>

        @if($errors->any())
            <div style="margin-top:1rem;padding:1rem;background:#fef2f2;border-radius:8px;color:#991b1b;font-size:.875rem;">
                {{ $errors->first() }}
            </div>
        @endif

        @if(isset($results))
            <div class="summary all">
                <span>Total: {{ count($results) }}</span>
                <span>Ditemukan: {{ $foundCount }}</span>
                <span>Tidak Ditemukan: {{ $missingCount }}</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Item</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $i => $row)
                        <tr class="{{ $row['exists'] ? 'exists' : 'not-exists' }}">
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['item'] }}</td>
                            <td>
                                <span class="badge {{ $row['exists'] ? 'yes' : 'no' }}">
                                    {{ $row['exists'] ? 'Ditemukan' : 'Tidak Ditemukan' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
