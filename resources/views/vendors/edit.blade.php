@extends('layouts.app')
@section('title', 'Edit Vendor')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header">Edit Vendor</div>
    <div class="card-body">
        <form method="POST" action="{{ route('vendors.update', $vendor) }}">
            @csrf @method('PUT')
            @include('vendors._form', ['vendor' => $vendor])
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
