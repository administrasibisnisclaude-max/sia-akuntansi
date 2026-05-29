@extends('layouts.app')
@section('title', 'Metode Pembayaran')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-credit-card me-2"></i>Metode Pembayaran</span>
        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Tambah</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Tipe</th><th>Akun</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($methods as $m)
                <tr>
                    <td>{{ $m->name }}</td>
                    <td>{{ ucfirst($m->type) }}</td>
                    <td>{{ $m->account ? $m->account->account_code . ' - ' . $m->account->name : '-' }}</td>
                    <td>@if($m->is_active)<span class="badge bg-success-subtle text-success">Aktif</span>@else<span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>@endif</td>
                    <td class="text-end">
                        <a href="{{ route('payment-methods.edit', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('payment-methods.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada metode pembayaran</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $methods->links() }}</div>
</div>
@endsection
