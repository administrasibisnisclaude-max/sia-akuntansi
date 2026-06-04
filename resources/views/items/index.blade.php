@extends('layouts.app')
@section('title', 'Item / Produk')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2"></i>Daftar Item / Produk</span>
        <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Tambah Item</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Stok</th>
                    <th class="text-center" title="Data Lingkungan terkonfigurasi">
                        <i class="fas fa-leaf text-success" title="Status Lingkungan"></i>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><code>{{ $item->item_code }}</code></td>
                    <td>{{ $item->name }}</td>
                    <td><span class="badge {{ $item->type == 'product' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                        {{ $item->type == 'product' ? 'Produk' : 'Jasa' }}
                    </span></td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">Rp {{ number_format($item->buy_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                    <td class="text-end">
                        {{ $item->type == 'product' ? number_format($item->stock, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-center">
                        @if(($item->waste_per_unit > 0) || ($item->carbon_per_unit > 0))
                            <span title="Limbah: {{ $item->waste_per_unit }} kg/unit | Karbon: {{ $item->carbon_per_unit }} kg CO₂e/unit">
                                <i class="fas fa-leaf text-success"></i>
                            </span>
                        @else
                            <span class="text-muted" title="Belum dikonfigurasi data lingkungan">
                                <i class="fas fa-leaf" style="opacity:.2"></i>
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('items.destroy', $item) }}" class="d-inline"
                              onsubmit="return confirm('Hapus item {{ addslashes($item->name) }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-box-seam me-2"></i>Belum ada item.
                        <a href="{{ route('items.create') }}">Tambah sekarang</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            <i class="fas fa-leaf text-success me-1"></i> = Data lingkungan sudah dikonfigurasi
        </small>
        {{ $items->links() }}
    </div>
</div>
@endsection
