<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    use ApiResponse;

    // Langkah 1: Kirim OTP ke "Email" (Log)
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Buat 6 digit angka acak
        $otp = rand(100000, 999999);

        // Simpan ke database (Timpa jika sudah pernah minta sebelumnya)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now()
            ]
        );

        // Kirim email (Karena MAIL_MAILER=log, ini akan masuk ke storage/logs/laravel.log)
        Mail::raw("Kode OTP Anda untuk mereset kata sandi SPK Ngacoan adalah: {$otp}\n\nKode ini bersifat rahasia. Jangan berikan kepada siapapun.", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Kode OTP Reset Password');
        });

        return $this->successResponse(null, 'Kode OTP berhasil dikirim. Silakan cek email Anda.');
    }

    // Langkah 2: Verifikasi OTP dan Ganti Password
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|numeric|digits:6',
            'password' => 'required|string|min:8',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // Cek apakah OTP benar dan valid (belum dihapus)
        if (!$record || !Hash::check($request->otp, $record->token)) {
            return $this->errorResponse('Kode OTP tidak valid atau sudah kedaluwarsa.', 400);
        }

        // Cek masa kedaluwarsa OTP (Misal: 15 Menit)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Kode OTP sudah kedaluwarsa. Silakan minta ulang.', 400);
        }

        // Ganti password user di database
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus jejak OTP agar tidak bisa dipakai 2 kali
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Kata sandi berhasil diubah! Silakan login dengan kata sandi baru.');
    }
}
