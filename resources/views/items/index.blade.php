@extends('layouts.app')
@section('title', 'Item')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2"></i>Daftar Item</span>
        <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Tambah</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Satuan</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th></th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><code>{{ $item->item_code }}</code></td><td>{{ $item->name }}</td>
                    <td>{{ $item->type == 'product' ? 'Produk' : 'Jasa' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">Rp {{ number_format($item->buy_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $item->type == 'product' ? number_format($item->stock, 2, ',', '.') : '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('items.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Belum ada item</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $items->links() }}</div>
</div>
@endsection
