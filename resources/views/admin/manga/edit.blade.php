@extends('layouts.app', ['isAdmin' => true])

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-header__title">Edit Manga</h1>
        <p class="admin-header__subtitle">Perbarui informasi manga yang sudah ada.</p>
    </div>

    <div class="admin-section">
        <form method="post" action="{{ route('admin.manga.update', $manga) }}">
            @csrf
            @method('PUT')
            @include('admin.manga._form')

            <div class="admin-form-actions">
                <button type="submit" class="btn btn--admin-primary">Update</button>
                <a href="{{ route('admin.manga.index') }}" class="btn btn--ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
