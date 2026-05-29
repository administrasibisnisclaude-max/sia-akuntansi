@extends('layouts.app')
@section('title', 'Edit Akun')
@section('content')
<div class="card" style="max-width:600px">
    <div class="card-header"><i class="bi bi-pencil me-2"></i>Edit Akun</div>
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @csrf @method('PUT')
            @include('accounts._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
