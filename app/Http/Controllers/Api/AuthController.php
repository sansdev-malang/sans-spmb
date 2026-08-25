<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
        $activeWave = \App\Models\SpmbWave::where('is_active', true)->first();
        $activeType = \App\Models\SpmbType::where('is_active', true)->first();

        // Automatically initialize draft registration for the user
        $registration = Registration::create([
            'user_id' => $user->id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid',
            'spmb_period_id' => $activePeriod?->id,
            'spmb_wave_id' => $activeWave?->id,
            'spmb_type_id' => $activeType?->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'registration_id' => $registration->id,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Ensure user has at least one registration record
        $registration = Registration::firstOrCreate(
            ['user_id' => $user->id],
            [
                'registration_status' => 'draft',
                'payment_status' => 'unpaid'
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'registration_id' => $registration->id,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
