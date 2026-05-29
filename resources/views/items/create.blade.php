@extends('layouts.app')
@section('title', 'Tambah Item')
@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">Tambah Item</div>
    <div class="card-body">
        <form method="POST" action="{{ route('items.store') }}">
            @csrf
            @include('items._form')
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
