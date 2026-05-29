@extends('layouts.app')
@section('title', 'Edit Item')
@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">Edit Item</div>
    <div class="card-body">
        <form method="POST" action="{{ route('items.update', $item) }}">
            @csrf @method('PUT')
            @include('items._form', ['item' => $item])
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
