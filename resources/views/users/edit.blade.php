@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')
<div class="card" style="max-width:520px;">
    <div class="card-header"><i class="fas fa-user-edit me-2"></i>Edit Pengguna</div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password Baru <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }} required>
                    <option value="kasir" {{ old('role', $user->role) === 'kasir' ? 'selected' : '' }}>Kasir — hanya akses POS</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin — akses penuh</option>
                </select>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="admin">
                    <div class="form-text text-muted">Tidak dapat mengubah role akun sendiri.</div>
                @endif
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <label class="form-check-label" for="isActive">Akun Aktif</label>
                </div>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
