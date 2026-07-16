<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $user->load('employee');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'nama' => $user->employee->nama,
                'nik' => $user->employee->nik,
                'nip' => $user->employee->nip,
                'tempat_lahir' => $user->employee->tempat_lahir,
                'tanggal_lahir' => $user->employee->tanggal_lahir,
                'gender' => $user->employee->gender,
                'alamat' => $user->employee->alamat,
                'telepon' => $user->employee->telepon,
                'file_foto' => $user->employee->file_foto,
                'file_signature' => $user->employee->file_signature,
                'pendidikan_terakhir' => $user->employee->pendidikan_terakhir,
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        // Update employee name if provided
        $employee = $user->employee;
        if ($employee && $request->has('employee_nama')) {
            $employee->update(['nama' => $request->employee_nama]);
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $path = $request->file('signature')->store('signatures', 'public');
        $employee->update(['file_signature' => $path]);

        return response()->json([
            'message' => 'Tanda tangan berhasil diupload',
            'file_signature' => $path,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui',
        ]);
    }
}
