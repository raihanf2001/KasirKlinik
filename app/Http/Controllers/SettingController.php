<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate(['id' => 1], [
            'app_name' => 'Kasir Pintar',
            'theme_color' => '#4361ee',
            'sidebar_color' => '#111827', // Default warna gelap
            'phone' => '0812-3456-7890',
            'address' => 'Jl. Alamat Toko Anda No. 1'
        ]);

        return view('admin.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required',
            'theme_color' => 'required',
            'sidebar_color' => 'required',
            'phone' => 'nullable',
            'address' => 'nullable',
            'logo' => 'nullable|image|max:2048'
        ]);

        $setting = Setting::first();
        $setting->app_name = $request->app_name;
        $setting->theme_color = $request->theme_color;
        $setting->sidebar_color = $request->sidebar_color;
        $setting->phone = $request->phone;
        $setting->address = $request->address;

        // --- FITUR AUTO REMOVE BACKGROUND (AI) ---
        if ($request->hasFile('logo')) {
            $imagePath = $request->file('logo')->getPathname();
            $apiKey = 'MASUKKAN_API_KEY_KAMU_DI_SINI'; // Ganti dengan API Key Remove.bg
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.remove.bg/v1.0/removebg');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image_file' => new \CURLFile($imagePath),
                'size' => 'auto'
            ]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Api-Key: ' . $apiKey]);
            
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpcode == 200) {
                $filename = time() . '_logo_transparent.png';
                if(!\Illuminate\Support\Facades\File::isDirectory(public_path('img'))) {
                    \Illuminate\Support\Facades\File::makeDirectory(public_path('img'), 0777, true, true);
                }
                \Illuminate\Support\Facades\File::put(public_path('img/' . $filename), $result);
                $setting->logo_path = 'img/' . $filename;
            } else {
                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img'), $filename);
                $setting->logo_path = 'img/' . $filename;
            }
        }

        $setting->save();
        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
