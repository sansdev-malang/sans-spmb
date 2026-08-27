<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the admin's profile edit form.
     */
    public function editAdminProfile(Request $request): \Illuminate\View\View
    {
        return view('admin.profile-edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the admin's profile information.
     */
    public function updateAdminProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Display the admin's password change form.
     */
    public function editAdminPassword(Request $request): \Illuminate\View\View
    {
        return view('admin.profile-password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the admin's password.
     */
    public function updateAdminPassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}
