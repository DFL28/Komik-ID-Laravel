@extends('layouts.app', ['isAdmin' => true])

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-header__title">Manage Manga</h1>
        <p class="admin-header__subtitle">Kelola data komik, edit info, dan hapus data yang tidak diperlukan.</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">
            <div class="alert__content">
                <div class="alert__title">Sukses</div>
                <div class="alert__message">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="admin-section">
        <div class="admin-section__header">
            <div>
                <h2 class="admin-section__title">Daftar Manga</h2>
                <p class="admin-section__subtitle">Total: {{ $manga->total() }} judul</p>
            </div>
            <a href="{{ route('admin.manga.create') }}" class="btn btn--admin-primary">Tambah Manga</a>
        </div>

        <form class="admin-filter" method="get" action="{{ route('admin.manga.index') }}">
            <div class="admin-filter__row">
                <div class="filter-group">
                    <label for="filterSearch">Search</label>
                    <input id="filterSearch" type="text" name="q" value="{{ $search }}" placeholder="Cari judul...">
                </div>
                <div class="filter-group">
                    <label for="filterStatus">Status</label>
                    <select id="filterStatus" name="status">
                        <option value="">All</option>
                        <option value="ongoing" {{ $status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterType">Type</label>
                    <select id="filterType" name="type">
                        <option value="">All</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-filter__actions">
                    <button type="submit" class="btn btn--primary">Filter</button>
                    <a href="{{ route('admin.manga.index') }}" class="btn btn--ghost">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Chapters</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manga as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->title }}</strong>
                                <div class="text-muted">{{ $item->slug }}</div>
                            </td>
                            <td>{{ ucfirst($item->status ?? 'unknown') }}</td>
                            <td>{{ $item->type ?? '-' }}</td>
                            <td>{{ $item->chapters_count }}</td>
                            <td>{{ $item->updated_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('manga.detail', $item->slug) }}" class="table-action-btn table-action-btn--view">View</a>
                                    <a href="{{ route('admin.manga.edit', $item) }}" class="table-action-btn table-action-btn--edit">Edit</a>
                                    <form method="post" action="{{ route('admin.manga.destroy', $item) }}" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-action-btn table-action-btn--delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada data manga.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $manga->links() }}
        </div>
    </div>
</div>
@endsection
