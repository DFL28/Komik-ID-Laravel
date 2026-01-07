@extends('layouts.app', ['isAdmin' => true])

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-header__title">Tambah Manga</h1>
        <p class="admin-header__subtitle">Buat data manga baru untuk katalog.</p>
    </div>

    <div class="admin-section">
        <form method="post" action="{{ route('admin.manga.store') }}">
            @csrf
            @include('admin.manga._form')

            <div class="admin-form-actions">
                <button type="submit" class="btn btn--admin-primary">Simpan</button>
                <a href="{{ route('admin.manga.index') }}" class="btn btn--ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
