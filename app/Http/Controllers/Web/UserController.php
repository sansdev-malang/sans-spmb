<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                ?? \App\Models\SpmbPeriod::value('id');
        });

        $admins = User::with('spmbUnit')->whereIn('role', ['admin', 'super_admin'])->latest()->paginate(10, ['*'], 'admins_page');
        
        $candidates = User::where('role', 'candidate')
            ->whereHas('registrations', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            })
            ->latest()
            ->paginate(10, ['*'], 'candidates_page');
            
        $units = \App\Models\SpmbUnit::all();

        return view('admin.users', compact('admins', 'candidates', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|in:admin,candidate,super_admin',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'spmb_unit_id' => $request->role === 'admin' ? $request->spmb_unit_id : null,
        ]);



        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,candidate,super_admin',
            'spmb_unit_id' => 'nullable|exists:spmb_units,id',
        ]);

        // Prevent admin from changing their own role
        if ($user->id === Auth::id() && $request->role !== $user->role) {
            return redirect()->back()->with('error', 'Gagal: Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'spmb_unit_id' => $request->role === 'admin' ? $request->spmb_unit_id : null,
        ]);

        return redirect()->back()->with('success', 'Informasi user berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent self destruction
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Gagal: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password user "' . $user->name . '" berhasil direset.');
    }

    public function quickRegister(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'admission_level' => 'required|in:PAUD,SD,SMP',
        ]);

        // Check if user already exists
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return redirect()->route('login')->with('error', 'Email ini sudah terdaftar. Silakan login terlebih dahulu untuk melanjutkan pendaftaran.');
        }

        // Create user with WhatsApp as password
        $user = User::create([
            'name' => $request->candidate_name . ' (Wali)',
            'email' => $request->email,
            'password' => Hash::make($request->parent_phone),
            'role' => 'candidate',
        ]);

        // Determine unit
        $unitName = $request->admission_level;
        $unit = \App\Models\SpmbUnit::where('name', 'like', '%' . $unitName . '%')->first();

        // Get active configs
        $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
        $activeWave = \App\Models\SpmbWave::where('is_active', true)->first();
        $activeType = \App\Models\SpmbType::where('is_active', true)->first();

        // Create dynamic registration
        $registration = Registration::create([
            'user_id' => $user->id,
            'candidate_name' => $request->candidate_name,
            'parent_phone' => $request->parent_phone,
            'admission_level' => $request->admission_level,
            'spmb_unit_id' => $unit?->id,
            'spmb_period_id' => $activePeriod?->id,
            'spmb_wave_id' => $activeWave?->id,
            'spmb_type_id' => $activeType?->id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard.detail', $registration->id)->with('success', 'Akun berhasil dibuat dan Anda telah masuk secara otomatis! Sandi akun Anda adalah nomor WhatsApp Anda. Silakan lengkapi formulir.');
    }
}
