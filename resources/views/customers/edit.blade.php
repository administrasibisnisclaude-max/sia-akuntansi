@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header">Edit Pelanggan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf @method('PUT')
            @include('customers._form', ['customer' => $customer])
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
