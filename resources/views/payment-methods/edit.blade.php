@extends('layouts.app')
@section('title', 'Edit Metode Bayar')
@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">Edit Metode Pembayaran</div>
    <div class="card-body">
        <form method="POST" action="{{ route('payment-methods.update', $paymentMethod) }}">
            @csrf @method('PUT')
            @include('payment-methods._form', ['paymentMethod' => $paymentMethod])
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
