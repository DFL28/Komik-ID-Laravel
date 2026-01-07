@extends('layouts.app', ['isAdmin' => true])

@section('content')
<div class="admin-container">
    <h1>Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Manga</h3>
            <p class="stat-number">{{ $stats['total_manga'] }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Chapter</h3>
            <p class="stat-number">{{ $stats['total_chapters'] }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="stat-number">{{ $stats['total_users'] }}</p>
        </div>
    </div>

    <div class="admin-section">
        <h2>Manga Terbaru</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Chapters</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['recent_manga'] as $manga)
                    <tr>
                        <td><a href="{{ route('manga.detail', $manga->slug) }}">{{ $manga->title }}</a></td>
                        <td>{{ $manga->status }}</td>
                        <td>{{ $manga->chapters_count ?? 0 }}</td>
                        <td>{{ timeAgo($manga->updated_at) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="admin-actions">
        <a href="{{ route('admin.manga.index') }}" class="btn btn-primary">Kelola Manga</a>
        <a href="{{ route('admin.scraper') }}" class="btn btn-primary">Kelola Scraper</a>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Kelola Users</a>
    </div>
</div>
@endsection
