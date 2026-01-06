<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        // Temporarily removed middleware due to autoload issue
        // $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return back()->with('success', 'Profil berhasil diupdate!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        
        $user = auth()->user();
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);
        
        return back()->with('success', 'Password berhasil diubah!');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
        
        $user = auth()->user();
        
        // Delete old avatar
        if ($user->avatar_path) {
            Storage::delete('public/' . $user->avatar_path);
        }
        
        // Store new avatar
        $path = $request->file('avatar')->store('users', 'public');
        
        $user->update(['avatar_path' => $path]);
        
        return back()->with('success', 'Avatar berhasil diupdate!');
    }
}
