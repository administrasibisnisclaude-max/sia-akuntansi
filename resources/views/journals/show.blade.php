@extends('layouts.app')
@section('title', 'Detail Jurnal')
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal me-2"></i>{{ $journal->journal_number }}</span>
                <span class="badge badge-{{ $journal->status }} fs-6">{{ ucfirst($journal->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col"><strong>Tanggal:</strong> {{ $journal->date->format('d F Y') }}</div>
                    <div class="col"><strong>Referensi:</strong> {{ $journal->reference ?? '-' }}</div>
                </div>
                <p><strong>Keterangan:</strong> {{ $journal->description }}</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Akun</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead>
                        <tbody>
                        @foreach($journal->lines as $line)
                            <tr>
                                <td><code>{{ $line->account->account_code }}</code> {{ $line->account->name }}</td>
                                <td>{{ $line->description }}</td>
                                <td class="text-end">{{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '' }}</td>
                                <td class="text-end">{{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total</td>
                                <td class="text-end">Rp {{ number_format($journal->totalDebit(), 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($journal->totalCredit(), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <small class="text-muted">Dibuat oleh: {{ $journal->createdBy->name }}
                    @if($journal->postedBy) | Diposting oleh: {{ $journal->postedBy->name }} pada {{ $journal->posted_at->format('d/m/Y H:i') }} @endif
                </small>
            </div>
            <div class="card-footer d-flex gap-2 no-print">
                @if($journal->status == 'draft')
                    <form method="POST" action="{{ route('journals.post', $journal) }}">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Posting</button>
                    </form>
                    <a href="{{ route('journals.edit', $journal) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                @elseif($journal->status == 'posted' && !$journal->reference_type)
                    <form method="POST" action="{{ route('journals.unpost', $journal) }}">
                        @csrf
                        <button class="btn btn-warning btn-sm">Unpost</button>
                    </form>
                @endif
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Cetak</button>
                <a href="{{ route('journals.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
