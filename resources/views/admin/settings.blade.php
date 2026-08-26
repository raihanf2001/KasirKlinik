@extends('layout')
@section('container')
<div class="content-area" style="padding: 30px;">
    <h2>Pengaturan Aplikasi</h2>
    
    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form action="/admin/settings" method="POST" enctype="multipart/form-data" style="background: white; padding: 30px; border-radius: 15px; max-width: 700px; border: 1px solid #e5e7eb;">
        @csrf
        
        <h4 style="margin-bottom: 15px; color: var(--primary); border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Profil Toko</h4>
        
        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">Nama Toko / Aplikasi</label>
            <input type="text" name="app_name" value="{{ $setting->app_name }}" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 8px;">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ $setting->phone }}" placeholder="Contoh: 0812-3456-7890" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">Alamat Toko</label>
            <textarea name="address" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">{{ $setting->address }}</textarea>
            <small style="color: #6b7280;">Alamat ini akan dicetak di kertas struk kasir.</small>
        </div>

        <h4 style="margin-bottom: 15px; color: var(--primary); border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Tema & Logo</h4>

        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 8px;">Warna Utama</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="theme_color" value="{{ $setting->theme_color }}" style="width: 50px; height: 50px; border: none; cursor: pointer; padding: 0;">
                    <span style="color: #6b7280; font-size: 0.8rem;">Warna tombol & teks.</span>
                </div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 8px;">Warna Sidebar</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="sidebar_color" value="{{ $setting->sidebar_color }}" style="width: 50px; height: 50px; border: none; cursor: pointer; padding: 0;">
                    <span style="color: #6b7280; font-size: 0.8rem;">Warna menu samping.</span>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">Logo Aplikasi (Auto Hapus Background)</label>
            @if($setting->logo_path)
                <div style="margin-bottom: 10px; padding: 15px; border-radius: 8px; display: inline-block; background-color: #fff; background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e7eb 75%), linear-gradient(-45deg, transparent 75%, #e5e7eb 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; border: 1px solid #d1d5db;">
                    <img src="{{ asset($setting->logo_path) }}" alt="Logo" style="max-height: 80px; object-fit: contain;">
                </div>
            @endif
            <input type="file" name="logo" accept="image/*" style="width: 100%; padding: 10px; border: 1px dashed #ccc; border-radius: 8px; background: #f9fafb;">
        </div>

        <button type="submit" style="width: 100%; padding: 12px; background: {{ $setting->theme_color }}; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1.1rem;">
            <i class="fa-solid fa-save"></i> Simpan Pengaturan
        </button>
    </form>
</div>
@endsection