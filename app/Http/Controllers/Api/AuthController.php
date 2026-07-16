<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        $user->load(['employee.murobbis', 'guardian.students']);

        $token = $user->createToken('react-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
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
            'guardian' => $user->guardian ? [
                'id' => $user->guardian->id,
                'nama' => $user->guardian->nama,
                'nik' => $user->guardian->nik,
                'tempat_lahir' => $user->guardian->tempat_lahir,
                'tanggal_lahir' => $user->guardian->tanggal_lahir,
                'gender' => $user->guardian->gender,
                'alamat' => $user->guardian->alamat,
                'telepon' => $user->guardian->telepon,
                'file_foto' => $user->guardian->file_foto,
                'agama' => $user->guardian->agama,
                'pendidikan' => $user->guardian->pendidikan,
                'pekerjaan' => $user->guardian->pekerjaan,
                'relation_type' => $user->guardian->relation_type,
                'relation_status' => $user->guardian->relation_status,
            ] : null,
            'students' => $user->guardian?->students ?? [],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['employee.murobbis', 'guardian.students']);
        
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
            'guardian' => $user->guardian ? [
                'id' => $user->guardian->id,
                'nama' => $user->guardian->nama,
                'nik' => $user->guardian->nik,
                'tempat_lahir' => $user->guardian->tempat_lahir,
                'tanggal_lahir' => $user->guardian->tanggal_lahir,
                'gender' => $user->guardian->gender,
                'alamat' => $user->guardian->alamat,
                'telepon' => $user->guardian->telepon,
                'file_foto' => $user->guardian->file_foto,
                'agama' => $user->guardian->agama,
                'pendidikan' => $user->guardian->pendidikan,
                'pekerjaan' => $user->guardian->pekerjaan,
                'relation_type' => $user->guardian->relation_type,
                'relation_status' => $user->guardian->relation_status,
            ] : null,
            'students' => $user->guardian?->students ?? [],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
