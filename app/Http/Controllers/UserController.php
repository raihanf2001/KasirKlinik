<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username'    => 'required|unique:users,username,',
            'password' => 'required|string|min:6',
            'is_admin' => 'required|in:0,1',
        ]);

        User::create([
            'name'     => $request->name,
            'username'    => $request->username,
            'password' => Hash::make($request->password), // Password wajib di-hash (enkripsi)
            'is_admin' => $request->is_admin,
        ]);

        return back()->with('success', 'Pengguna baru berhasil ditambahkan!');
    }

    // Mengupdate data pengguna
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username'    => 'required|unique:users,username,' . $user->id,
            'is_admin' => 'required|in:0,1',
        ]);

        $data = [
            'name'     => $request->name,
            'username'    => $request->username,
            'is_admin' => $request->is_admin,
        ];

        // Jika form password diisi, berarti mau ganti password. Jika kosong, abaikan.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect('/users')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    // Menghapus pengguna
    public function destroy(User $user)
    {
        if (Auth::user()->id == $user->id) {
            return back()->withErrors(['error' => 'Gagal: Anda tidak bisa menghapus akun Anda sendiri!']);
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus!');
    }
}
