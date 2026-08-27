@extends('layout')
@section('container')
<div class="content-area" style="padding: 30px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="page-title" style="margin: 0; color: #f1f5f9">Kelola Pengguna (Kasir & Admin)</h2>
        <span style="background: #e0e7ff; color: #4361ee; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: flex; align-items: center; gap: 6px;">
            <span style="width: 8px; height: 8px; background: #4361ee; border-radius: 50%; display: inline-block; animation: blink 1s infinite;"></span> Status Live
        </span>
    </div>

    @if(session('success'))
        <div id="success-alert" style="background: #dcfce7; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0; transition: opacity 0.5s ease;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <ul style="margin-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 300px; background: white; padding: 25px; border-radius: 15px; border: 1px solid #e5e7eb; height: fit-content;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 id="formTitle" style="font-size: 1.1rem; margin: 0;">Tambah Pengguna Baru</h3>
                <button type="button" id="btnCancel" onclick="resetForm()" style="display: none; padding: 5px 10px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem;">
                    Batal Edit
                </button>
            </div>

            <form id="userForm" action="/admin/users" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Nama Kasir / Admin" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Username</label>
                    <input type="text" id="username" name="username" placeholder="username" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                    <small id="passwordHelp" style="color: #6b7280; display: block; margin-top: 5px;">*Wajib diisi untuk pengguna baru.</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Hak Akses (Role)</label>
                    <select id="is_admin" name="is_admin" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="0">Kasir (Staff)</option>
                        <option value="1">Admin (Pemilik)</option>
                    </select>
                </div>

                <button type="submit" id="formSubmitBtn" style="width: 100%; padding: 12px; background: #4361ee; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    + Simpan Pengguna
                </button>
            </form>
        </div>

        <div style="flex: 2; min-width: 400px; background: white; padding: 25px; border-radius: 15px; border: 1px solid #e5e7eb; overflow-x: auto;">
            <h3 style="margin-bottom: 15px; font-size: 1.1rem;">Daftar Pengguna</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama & Status</th>
                        <th style="padding: 12px 15px;">Role</th>
                        <th style="padding: 12px 15px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    @forelse($users as $user)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px 15px; color: #6b7280;">{{ $loop->iteration }}</td>
                        <td style="padding: 12px 15px;">
                            <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">{{ $user->name }}</div>
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px;">{{ $user->username }}</div>
                            @if($user->is_online)
                                <span style="background: #dcfce7; color: #16a34a; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-right: 3px; vertical-align: middle;"></i> Online
                                </span>
                            @else
                                <span style="background: #f1f5f9; color: #64748b; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-right: 3px; vertical-align: middle;"></i> Offline
                                </span>
                            @endif

                        </td>
                        <td style="padding: 12px 15px;">
                            @if($user->is_admin == 1)
                                <span style="background: #fef9c3; color: #ca8a04; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Admin</span>
                            @else
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Kasir</span>
                            @endif
                        </td>
                        <td style="padding: 12px 15px; display: flex; justify-content: center; gap: 8px;">
                            <button type="button" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->username) }}', {{ $user->is_admin }})" style="padding: 6px 10px; background: #fef9c3; color: #ca8a04; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" {{ auth()->id() == $user->id ? 'disabled' : '' }} style="padding: 6px 10px; background: {{ auth()->id() == $user->id ? '#f3f4f6' : '#fee2e2' }}; color: {{ auth()->id() == $user->id ? '#9ca3af' : '#ef4444' }}; border: none; border-radius: 6px; cursor: {{ auth()->id() == $user->id ? 'not-allowed' : 'pointer' }}; font-size: 0.9rem;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #6b7280;">Belum ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0; }
        100% { opacity: 1; }
    }
</style>

<script>
    // Live Auto-Refresh Script (Hanya menyegarkan tabel)
    setInterval(() => {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                let doc = new DOMParser().parseFromString(html, 'text/html');
                let newTable = doc.getElementById('user-table-body');
                let currentTable = document.getElementById('user-table-body');
                
                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }
            })
            .catch(error => console.error('Gagal mengambil status live:', error));
    }, 5000); // Cek status setiap 5 detik

    // Alert Auto-hide
    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        setTimeout(function() {
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000); 
    }

    // Form Dinamis
    function editUser(id, name, username, isAdmin) {
        document.getElementById('formTitle').innerText = 'Edit Pengguna';
        document.getElementById('userForm').action = '/admin/users/' + id;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('name').value = name;
        document.getElementById('username').value = username;
        document.getElementById('is_admin').value = isAdmin;
        
        const passInput = document.getElementById('password');
        passInput.required = false;
        passInput.value = '';
        document.getElementById('passwordHelp').innerText = '*Biarkan kosong jika tidak ingin mengganti password.';
        document.getElementById('passwordHelp').style.color = '#ca8a04';
        
        document.getElementById('formSubmitBtn').innerHTML = '<i class="fa-solid fa-save"></i> Simpan Perubahan';
        document.getElementById('btnCancel').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerText = 'Tambah Pengguna Baru';
        document.getElementById('userForm').action = '/admin/users';
        document.getElementById('formMethod').value = 'POST';
        
        document.getElementById('name').value = '';
        document.getElementById('username').value = '';
        document.getElementById('is_admin').value = '0';
        
        const passInput = document.getElementById('password');
        passInput.required = true;
        passInput.value = '';
        document.getElementById('passwordHelp').innerText = '*Wajib diisi untuk pengguna baru.';
        document.getElementById('passwordHelp').style.color = '#6b7280';
        
        document.getElementById('formSubmitBtn').innerHTML = '+ Simpan Pengguna';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>
@endsection