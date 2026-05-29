@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header">Tambah Pelanggan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            @include('customers._form')
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
