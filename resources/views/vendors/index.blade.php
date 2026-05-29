@extends('layouts.app')
@section('title', 'Vendor')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-building me-2"></i>Daftar Vendor</span>
        <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Tambah</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($vendors as $v)
                <tr>
                    <td><code>{{ $v->vendor_code }}</code></td><td>{{ $v->name }}</td>
                    <td>{{ $v->email ?? '-' }}</td><td>{{ $v->phone ?? '-' }}</td>
                    <td>@if($v->is_active)<span class="badge bg-success-subtle text-success">Aktif</span>@else<span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>@endif</td>
                    <td class="text-end">
                        <a href="{{ route('vendors.edit', $v) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('vendors.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada vendor</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $vendors->links() }}</div>
</div>
@endsection
