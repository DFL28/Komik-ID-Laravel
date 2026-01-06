@extends('layouts.app')

@section('content')
<div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header__content">
            <div class="profile-header__avatar-wrapper">
                <img src="{{ resolveMedia($user->avatar_path, '/images/avatar-placeholder.png') }}" 
                     alt="{{ $user->username }}" 
                     class="profile-header__avatar">
                <div class="profile-header__badge">
                    @if($user->is_admin)
                        <span class="badge badge--primary">👑 Admin</span>
                    @else
                        <span class="badge badge--success">✓ Member</span>
                    @endif
                </div>
            </div>
            <div class="profile-header__info">
                <h1 class="profile-header__name">{{ $user->username }}</h1>
                <p class="profile-header__email">{{ $user->email }}</p>
                <p class="profile-header__joined">Bergabung {{ $user->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert--success">
            <div class="alert__icon">✓</div>
            <div class="alert__content">
                <div class="alert__title">Berhasil!</div>
                <div class="alert__message">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert--danger">
            <div class="alert__icon">⚠</div>
            <div class="alert__content">
                <div class="alert__title">Terjadi Kesalahan</div>
                <ul class="alert__list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Profile Settings Grid -->
    <div class="profile-grid">
        
        <!-- Personal Information Card -->
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">
                    <span class="card__icon">👤</span>
                    Informasi Pribadi
                </h2>
            </div>
            <form method="POST" action="{{ route('profile') }}" class="card__body">
                @csrf
                
                <div class="form-group">
                    <label for="username" class="form-label form-label--required">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username', $user->username) }}" 
                           class="form-input"
                           placeholder="Masukkan username"
                           required>
                    <span class="form-hint">Username unik untuk identitas Anda</span>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label form-label--required">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           class="form-input"
                           placeholder="your@email.com"
                           required>
                    <span class="form-hint">Email untuk notifikasi dan login</span>
                </div>

                <div class="card__footer">
                    <button type="submit" class="btn btn--primary btn--block">
                        <span>💾</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">
                    <span class="card__icon">🔐</span>
                    Keamanan Akun
                </h2>
            </div>
            <form method="POST" action="{{ url('/profile/password') }}" class="card__body">
                @csrf
                
                <div class="form-group">
                    <label for="current_password" class="form-label form-label--required">Password Saat Ini</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="form-input"
                           placeholder="••••••••"
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label form-label--required">Password Baru</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input"
                           placeholder="••••••••"
                           minlength="6"
                           required>
                    <span class="form-hint">Minimal 6 karakter</span>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label form-label--required">Konfirmasi Password Baru</label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="form-input"
                           placeholder="••••••••"
                           required>
                </div>

                <div class="card__footer">
                    <button type="submit" class="btn btn--warning btn--block">
                        <span>🔒</span>
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Avatar Upload Card -->
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">
                    <span class="card__icon">🖼️</span>
                    Foto Profil
                </h2>
            </div>
            <form method="POST" action="{{ url('/profile/avatar') }}" enctype="multipart/form-data" class="card__body">
                @csrf
                
                <div class="form-group">
                    <div class="avatar-upload">
                        <div class="avatar-upload__preview">
                            <img src="{{ resolveMedia($user->avatar_path, '/images/avatar-placeholder.png') }}" 
                                 alt="Preview" 
                                 id="avatarPreview"
                                 class="avatar-upload__img">
                        </div>
                        <div class="avatar-upload__info">
                            <h4 class="avatar-upload__title">Upload Foto Baru</h4>
                            <p class="avatar-upload__hint">
                                JPG, PNG atau GIF. Maksimal 2MB.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="avatar" class="form-label">Pilih File</label>
                    <input type="file" 
                           id="avatar" 
                           name="avatar" 
                           accept="image/jpeg,image/png,image/gif" 
                           class="form-input"
                           onchange="previewAvatar(event)"
                           required>
                </div>

                <div class="card__footer">
                    <button type="submit" class="btn btn--primary btn--block">
                        <span>📤</span>
                        Upload Avatar
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Statistics Card -->
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">
                    <span class="card__icon">📊</span>
                    Statistik Akun
                </h2>
            </div>
            <div class="card__body">
                <div class="stat-list">
                    <div class="stat-item">
                        <div class="stat-item__icon stat-item__icon--primary">📚</div>
                        <div class="stat-item__content">
                            <div class="stat-item__label">Total Bookmark</div>
                            <div class="stat-item__value">0</div>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-item__icon stat-item__icon--success">📖</div>
                        <div class="stat-item__content">
                            <div class="stat-item__label">Manga Dibaca</div>
                            <div class="stat-item__value">0</div>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-item__icon stat-item__icon--warning">💬</div>
                        <div class="stat-item__content">
                            <div class="stat-item__label">Komentar</div>
                            <div class="stat-item__value">0</div>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-item__icon stat-item__icon--info">⏱️</div>
                        <div class="stat-item__content">
                            <div class="stat-item__label">Waktu Membaca</div>
                            <div class="stat-item__value">0 jam</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
