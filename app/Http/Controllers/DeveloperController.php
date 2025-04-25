<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DeveloperController extends Controller
{
    public function showRegisterForm()
    {
        // Pastikan hanya developer yang bisa mengakses halaman ini
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk mengakses halaman ini.']);
        }

        return view('developer.register');
    }


    public function registerUser(Request $request)
    {
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk melakukan ini.']);
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:kantor,pabrik,owner'
        ]);
    
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
    
        return redirect()->route('developer.dashboard')->with('success', 'User berhasil ditambahkan.');
    }

    public function dashboard()
    {
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk mengakses halaman ini.']);
        }
    
        $users = User::all(); // Mengambil semua user dari database
        return view('developer', compact('users')); // Kirim data ke developer.blade.php
    }
    
    public function deleteUser($id)
    {
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk melakukan ini.']);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('developer.dashboard')->withErrors(['not_found' => 'User tidak ditemukan.']);
        }
    
        if ($user->role === 'developer') {
            return redirect()->route('developer.dashboard')->withErrors(['forbidden' => 'Tidak dapat menghapus akun developer.']);
        }
    
        $user->delete();
    
        return redirect()->route('developer.dashboard')->with('success', 'User berhasil dihapus.');
    }

    public function editUser($id)
    {
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk mengedit user.']);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('developer.dashboard')->withErrors(['not_found' => 'User tidak ditemukan.']);
        }
    
        return view('developer.edit-user', compact('user'));
    }
    
    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'developer') {
            return redirect('/')->withErrors(['unauthorized' => 'Anda tidak memiliki izin untuk melakukan ini.']);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('developer.dashboard')->withErrors(['not_found' => 'User tidak ditemukan.']);
        }
    
        if ($user->role === 'developer') {
            return redirect()->route('developer.dashboard')->withErrors(['forbidden' => 'Tidak dapat mengedit akun developer.']);
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:kantor,pabrik,owner',
            'password' => 'nullable|min:6'
        ]);
    
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
    
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
    
        $user->save();
    
        return redirect()->route('developer.dashboard')->with('success', 'User berhasil diperbarui.');
    }
    
    
    
}
