<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use App\Models\SpmbActivityLog;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                ?? \App\Models\SpmbPeriod::value('id');
        });

        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $search = $request->search;
        $unitId = $request->unit_id;

        // 1. Admins query
        $adminsQuery = User::whereIn('role', ['admin', 'super_admin']);
        if (!$isSuperAdmin) {
            $adminsQuery->where('spmb_unit_id', auth()->user()->spmb_unit_id);
        } else {
            if ($request->filled('unit_id')) {
                $adminsQuery->where('spmb_unit_id', $unitId);
            }
        }
        if ($request->filled('search')) {
            $adminsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $adminsCount = (clone $adminsQuery)->count();
        $admins = $adminsQuery->latest()->paginate($request->integer('per_page', 10), ['*'], 'admins_page');

        // 2. Active Candidates (Sudah memilih unit & sudah lunas formulir pendaftaran)
        $candidatesQuery = User::where('role', 'candidate')
            ->whereHas('registrations', function($rq) use ($selectedPeriodId, $isSuperAdmin, $unitId) {
                if ($selectedPeriodId) {
                    $rq->where('spmb_period_id', $selectedPeriodId);
                }
                if (!$isSuperAdmin) {
                    $rq->where('spmb_unit_id', auth()->user()->spmb_unit_id);
                } elseif ($unitId) {
                    $rq->where('spmb_unit_id', $unitId);
                }
                // Sudah lunas formulir atau bukan draf
                $rq->where(function($sq) {
                    $sq->where('registration_status', '!=', 'draft')
                       ->orWhereHas('payments', function($pq) {
                           $pq->where('payment_type', 'registration_fee')->where('status', 'success');
                       });
                });
            });

        if ($request->filled('search')) {
            $candidatesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('registrations', function($rq) use ($search) {
                      $rq->where('candidate_name', 'like', '%' . $search . '%')
                         ->orWhere('parent_phone', 'like', '%' . $search . '%');
                  });
            });
        }
        $candidatesCount = (clone $candidatesQuery)->count();
        $candidates = $candidatesQuery->with(['registrations' => function($rq) use ($selectedPeriodId) {
            if ($selectedPeriodId) $rq->where('spmb_period_id', $selectedPeriodId);
            $rq->with(['unit', 'payments']);
        }])->latest()->paginate($request->integer('per_page', 10), ['*'], 'candidates_page');

        // 3. Unregistered / Unpaid Leads (Belum memilih unit ATAU belum bayar formulir)
        $unregisteredQuery = User::where('role', 'candidate')
            ->whereDoesntHave('registrations', function($rq) use ($selectedPeriodId) {
                if ($selectedPeriodId) {
                    $rq->where('spmb_period_id', $selectedPeriodId);
                }
                $rq->where(function($sq) {
                    $sq->where('registration_status', '!=', 'draft')
                       ->orWhereHas('payments', function($pq) {
                           $pq->where('payment_type', 'registration_fee')->where('status', 'success');
                       });
                });
            });

        if (!$isSuperAdmin) {
            $myUnitId = auth()->user()->spmb_unit_id;
            $unregisteredQuery->where(function($q) use ($myUnitId) {
                $q->doesntHave('registrations')
                  ->orWhereHas('registrations', function($rq) use ($myUnitId) {
                      $rq->where('spmb_unit_id', $myUnitId);
                  });
            });
        } elseif ($request->filled('unit_id')) {
            $unregisteredQuery->whereHas('registrations', function($rq) use ($unitId) {
                $rq->where('spmb_unit_id', $unitId);
            });
        }

        if ($request->filled('search')) {
            $unregisteredQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('registrations', function($rq) use ($search) {
                      $rq->where('candidate_name', 'like', '%' . $search . '%')
                         ->orWhere('parent_phone', 'like', '%' . $search . '%');
                  });
            });
        }
        $unregisteredCount = (clone $unregisteredQuery)->count();
        $unregistered = $unregisteredQuery->with(['registrations' => function($rq) use ($selectedPeriodId) {
            if ($selectedPeriodId) $rq->where('spmb_period_id', $selectedPeriodId);
            $rq->with(['unit', 'payments']);
        }])->latest()->paginate($request->integer('per_page', 10), ['*'], 'unregistered_page');
            
        $units = $isSuperAdmin ? \App\Models\SpmbUnit::all() : \App\Models\SpmbUnit::where('id', auth()->user()->spmb_unit_id)->get();

        return view('admin.users', compact('admins', 'candidates', 'unregistered', 'units', 'adminsCount', 'candidatesCount', 'unregisteredCount'));
    }

    public function store(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $allowedRoles = $isSuperAdmin ? 'admin,candidate,super_admin' : 'admin,candidate';

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|in:' . $allowedRoles,
            'spmb_unit_id' => $isSuperAdmin ? 'nullable|exists:spmb_units,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'user_create');
        }

        $unitId = $isSuperAdmin ? $request->spmb_unit_id : auth()->user()->spmb_unit_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'spmb_unit_id' => ($request->role === 'admin' || $request->role === 'candidate') ? $unitId : null,
        ]);

        SpmbActivityLog::log('CREATE_USER', "Membuat user baru: {$user->name} ({$user->email}) dengan role {$user->role}");

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if (!$isSuperAdmin) {
            if ($user->spmb_unit_id !== auth()->user()->spmb_unit_id || $user->role === 'super_admin') {
                abort(403, 'Unauthorized action.');
            }
        }

        $allowedRoles = $isSuperAdmin ? 'admin,candidate,super_admin' : 'admin,candidate';

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:' . $allowedRoles,
            'spmb_unit_id' => $isSuperAdmin ? 'nullable|exists:spmb_units,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('failed_modal', 'user_edit_' . $id);
        }

        // Prevent admin from changing their own role
        if ($user->id === Auth::id() && $request->role !== $user->role) {
            return redirect()->back()->with('error', 'Gagal: Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $unitId = $isSuperAdmin ? $request->spmb_unit_id : auth()->user()->spmb_unit_id;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'spmb_unit_id' => ($request->role === 'admin' || $request->role === 'candidate') ? $unitId : null,
        ]);

        SpmbActivityLog::log('UPDATE_USER', "Memperbarui informasi user: {$user->name} ({$user->email})");

        return redirect()->back()->with('success', 'Informasi user berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if (!$isSuperAdmin) {
            if ($user->spmb_unit_id !== auth()->user()->spmb_unit_id || $user->role === 'super_admin') {
                abort(403, 'Unauthorized action.');
            }
        }

        // Prevent self destruction
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Gagal: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Save detail for log before delete
        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        SpmbActivityLog::log('DELETE_USER', "Menghapus user: {$userName} ({$userEmail})");

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        if (!$isSuperAdmin) {
            if ($user->spmb_unit_id !== auth()->user()->spmb_unit_id || $user->role === 'super_admin') {
                abort(403, 'Unauthorized action.');
            }
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => ['required', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('failed_modal', 'user_reset_' . $id);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        SpmbActivityLog::log('RESET_PASSWORD_USER', "Mereset kata sandi user: {$user->name} ({$user->email})");

        return redirect()->back()->with('success', 'Password user "' . $user->name . '" berhasil direset.');
    }

    public function quickRegister(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'spmb_unit_id' => 'required|exists:spmb_units,id',
        ]);

        // Check if user already exists
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return redirect()->route('login')->with('error', 'Email ini sudah terdaftar. Silakan login terlebih dahulu untuk melanjutkan pendaftaran.');
        }

        // Create user with password input
        $user = User::create([
            'name' => $request->candidate_name . ' (Wali)',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'candidate',
        ]);

        // Resolve unit and default grade dynamically
        $unit = \App\Models\SpmbUnit::find($request->spmb_unit_id);
        $grade = \App\Models\SpmbGrade::where('spmb_unit_id', $unit->id)->where('is_active', true)->first();

        // Map admission level dynamically
        $admissionLevel = $grade ? $grade->name : null;

        // Get active configs
        $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
        $activeWave = \App\Models\SpmbWave::where('is_active', true)->first();
        $activeType = \App\Models\SpmbType::where('is_active', true)->first();

        // Create dynamic registration
        $registration = Registration::create([
            'user_id' => $user->id,
            'candidate_name' => $request->candidate_name,
            'parent_phone' => $request->parent_phone,
            'admission_level' => $admissionLevel,
            'spmb_unit_id' => $unit->id,
            'spmb_grade_id' => $grade->id,
            'spmb_period_id' => $activePeriod?->id,
            'spmb_wave_id' => $activeWave?->id,
            'spmb_type_id' => $activeType?->id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        // Trigger notification to all admins
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                'title' => 'Pendaftaran Akun Baru',
                'message' => 'Wali murid "' . $user->name . '" (' . $user->email . ') baru saja mendaftarkan akun baru.',
                'url' => route('admin.users') . '?search=' . urlencode($user->email),
                'type' => 'info',
            ]));
        } catch (\Exception $e) {
            // Ignore or log error
        }

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard.detail', $registration->id)->with('success', 'Akun berhasil dibuat dan Anda telah masuk secara otomatis! Silakan lengkapi formulir.');
    }
}
