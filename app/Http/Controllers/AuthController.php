<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
     public function index()
    {
        return view ('auth.login',[
            'title' => 'Login'
        ]);
    }
    
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' =>'required',
            'password' => 'required',
        ]);

        // Auth::attempt sudah menangani pengecekan user dan password sekaligus
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // Jika login gagal, kembalikan ke halaman login dengan pesan error
        // dan pertahankan input email agar user tidak perlu mengetik ulang
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}
