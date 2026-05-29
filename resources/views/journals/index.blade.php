@extends('layouts.app')
@section('title', 'Jurnal Umum')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text me-2"></i>Jurnal Umum</span>
        <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Buat Jurnal</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>No. Jurnal</th><th>Tanggal</th><th>Keterangan</th><th>Referensi</th><th>Status</th><th>Dibuat</th><th></th></tr></thead>
            <tbody>
            @forelse($journals as $j)
                <tr>
                    <td><a href="{{ route('journals.show', $j) }}">{{ $j->journal_number }}</a></td>
                    <td>{{ $j->date->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($j->description, 40) }}</td>
                    <td>{{ $j->reference ?? '-' }}</td>
                    <td><span class="badge badge-{{ $j->status }}">{{ ucfirst($j->status) }}</span></td>
                    <td>{{ $j->createdBy->name }}</td>
                    <td class="text-end">
                        <a href="{{ route('journals.show', $j) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        @if($j->status == 'draft')
                            <a href="{{ route('journals.edit', $j) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada jurnal</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $journals->links() }}</div>
</div>
@endsection
