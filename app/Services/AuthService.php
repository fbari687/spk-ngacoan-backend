<?php
namespace App\Services;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function authenticate(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang Anda masukkan salah.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function getProfile($user)
    {
        return $user;
    }
}
