<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeysAuthService
{
    /**
     * Authenticate user and generate token
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact administrator.'],
            ]);
        }

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        // Create new token with 24 hour expiration
        $token = $user->createToken(
            'auth-token',
            ['*'],
            now()->addHours(24)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400, // 24 hours in seconds
        ];
    }

    /**
     * Logout user by revoking current token
     */
    public function logout(User $user, $currentToken = null): bool
    {
        if ($currentToken) {
            // Delete only the current token
            $currentToken->delete();
        } else {
            // Delete all tokens
            $user->tokens()->delete();
        }

        return true;
    }

    /**
     * Refresh user token
     */
    public function refreshToken(User $user, $currentToken): array
    {
        // Delete the old token
        $currentToken->delete();

        // Create new token with 24 hour expiration
        $token = $user->createToken(
            'auth-token',
            ['*'],
            now()->addHours(24)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
        ];
    }

    /**
     * Get current authenticated user profile
     */
    public function getCurrentUser(User $user): User
    {
        return $user->load([]);
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    /**
     * Reset user password
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Revoke all tokens after password reset for security
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }
}