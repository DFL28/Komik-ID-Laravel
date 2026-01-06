@extends('layouts.app')

@section('content')
<section class="manga-grid-section">
    <div class="container">
        <h1>Hasil Pencarian: "{{ $query }}"</h1>
        
        <div class="manga-grid">
            @forelse($manga as $item)
                <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title">{{ $item->title }}</h3>
                    </div>
                </a>
            @empty
                <p class="empty-state">Tidak ada manga yang ditemukan untuk "{{ $query }}"</p>
            @endforelse
        </div>

        <div class="pagination">
            {{ $manga->appends(['q' => $query])->links() }}
        </div>
    </div>
</section>
@endsection
