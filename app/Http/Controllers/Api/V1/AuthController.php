<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
            'password' => ['required', 'confirmed', 'min:6'],
            'jenis_kerja' => ['required', 'in:non_shift,shift'],
            'id_pos' => ['required', 'integer', 'exists:pos_lokasi,id'],
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'jenis_kerja' => $request->jenis_kerja,
            'status' => 'pending',
            'id_pos' => $request->id_pos,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Silakan hubungi admin untuk verifikasi akun Anda.',
            'data' => [
                'user' => $user
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
        ]);

        $user = User::with('pos')->where('nip', $request->nip)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'NIP atau Password salah.'
            ], 401);
        }

        if ($user->status !== 'verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun belum diverifikasi admin.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('pos')
        ]);
    }

    public function getPositions()
    {
        $positions = DB::table('pos_lokasi')->select('id', 'nama_pos')->get();
        return response()->json([
            'status' => 'success',
            'data' => $positions
        ]);
    }
}
