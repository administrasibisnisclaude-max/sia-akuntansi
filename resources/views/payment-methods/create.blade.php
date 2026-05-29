@extends('layouts.app')
@section('title', 'Tambah Metode Bayar')
@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">Tambah Metode Pembayaran</div>
    <div class="card-body">
        <form method="POST" action="{{ route('payment-methods.store') }}">
            @csrf
            @include('payment-methods._form')
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
