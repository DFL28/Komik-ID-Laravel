@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Lupa Password</h1>

        <p class="text-muted mb-2">Masukkan email akun kamu untuk menerima tautan reset.</p>

        @if(session('status'))
            <div class="alert">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Kirim Link Reset</button>
        </form>

        <p class="auth-footer">
            <a href="{{ route('login') }}">Kembali ke login</a>
        </p>
    </div>
</div>
@endsection
