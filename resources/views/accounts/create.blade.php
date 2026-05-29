@extends('layouts.app')
@section('title', 'Tambah Akun')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Tambah Akun</div>
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            @include('accounts._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
