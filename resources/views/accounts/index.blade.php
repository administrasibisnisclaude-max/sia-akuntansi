@extends('layouts.app')
@section('title', 'Bagan Akun (CoA)')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-columns me-2"></i>Bagan Akun (Chart of Accounts)</span>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Tambah Akun</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th>Saldo Normal</th><th>Induk</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($accounts as $account)
                <tr>
                    <td><code>{{ $account->account_code }}</code></td>
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->type }}</td>
                    <td>{{ ucfirst($account->normal_balance) }}</td>
                    <td>{{ $account->parent?->name ?? '-' }}</td>
                    <td>
                        @if($account->is_active)
                            <span class="badge bg-success-subtle text-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-xs btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada akun</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $accounts->links() }}</div>
</div>
@endsection
