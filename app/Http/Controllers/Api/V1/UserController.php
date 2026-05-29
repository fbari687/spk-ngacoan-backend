<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse; // Pastikan kamu pakai trait respons bauranmu
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    // 1. READ: Tampilkan semua pengguna yang memiliki role 'pengelola'
    public function index(): JsonResponse
    {
        $staffs = User::where('role', 'pengelola')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($staffs, 'Daftar pengelola berhasil diambil.');
    }

    // 2. CREATE: Tambah pengelola baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $staff = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'pengelola', // Kunci otomatis sebagai pengelola
        ]);

        return $this->successResponse($staff, 'Akun pengelola berhasil dibuat.', 201);
    }

    // 3. UPDATE: Ubah data pengelola
    public function update(Request $request, $id): JsonResponse
    {
        $staff = User::where('role', 'pengelola')->find($id);

        if (!$staff) {
            return $this->errorResponse('Data pengelola tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8', // Nullable agar tidak wajib ganti password saat edit
        ]);

        $staff->name = $validated['name'];
        $staff->username = $validated['username'];
        $staff->email = $validated['email'];

        // Pengaman: Ganti password hanya jika kolom password diisi di form
        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        $staff->save();

        return $this->successResponse($staff, 'Data pengelola berhasil diperbarui.');
    }

    // 4. DELETE: Hapus akun pengelola
    public function destroy($id): JsonResponse
    {
        $staff = User::where('role', 'pengelola')->find($id);

        if (!$staff) {
            return $this->errorResponse('Data pengelola tidak ditemukan atau Anda tidak memiliki akses.', 404);
        }

        $staff->delete();

        return $this->successResponse(null, 'Akun pengelola berhasil dihapus.');
    }
}
