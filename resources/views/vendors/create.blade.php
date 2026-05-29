@extends('layouts.app')
@section('title', 'Tambah Vendor')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header">Tambah Vendor</div>
    <div class="card-body">
        <form method="POST" action="{{ route('vendors.store') }}">
            @csrf
            @include('vendors._form')
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
