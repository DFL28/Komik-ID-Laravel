@php
    $value = fn($key, $default = '') => old($key, $manga->{$key} ?? $default);
@endphp

<div class="form-grid">
    <div class="form-group">
        <label class="form-label form-label--required" for="title">Title</label>
        <input id="title" name="title" class="form-input" value="{{ $value('title') }}" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="slug">Slug</label>
        <input id="slug" name="slug" class="form-input" value="{{ $value('slug') }}" placeholder="Auto from title">
    </div>
    <div class="form-group">
        <label class="form-label" for="alternative_title">Alternative Title</label>
        <input id="alternative_title" name="alternative_title" class="form-input" value="{{ $value('alternative_title') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="cover_path">Cover URL</label>
        <input id="cover_path" name="cover_path" class="form-input" value="{{ $value('cover_path') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="author">Author</label>
        <input id="author" name="author" class="form-input" value="{{ $value('author') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="artist">Artist</label>
        <input id="artist" name="artist" class="form-input" value="{{ $value('artist') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="serialization">Serialization</label>
        <input id="serialization" name="serialization" class="form-input" value="{{ $value('serialization') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">Unknown</option>
            <option value="ongoing" {{ $value('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            <option value="completed" {{ $value('status') === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="type">Type</label>
        <select id="type" name="type" class="form-select">
            <option value="">Unknown</option>
            <option value="Manga" {{ $value('type') === 'Manga' ? 'selected' : '' }}>Manga</option>
            <option value="Manhwa" {{ $value('type') === 'Manhwa' ? 'selected' : '' }}>Manhwa</option>
            <option value="Manhua" {{ $value('type') === 'Manhua' ? 'selected' : '' }}>Manhua</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="content_type">Content Type</label>
        <input id="content_type" name="content_type" class="form-input" value="{{ $value('content_type') }}" placeholder="color, bw, etc.">
    </div>
    <div class="form-group">
        <label class="form-label" for="country">Country</label>
        <input id="country" name="country" class="form-input" value="{{ $value('country') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="rating">Rating</label>
        <input id="rating" name="rating" type="number" step="0.1" min="0" max="10" class="form-input" value="{{ $value('rating') }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="genres">Genres</label>
        <input id="genres" name="genres" class="form-input" value="{{ $value('genres') }}" placeholder="Action, Drama, Romance">
    </div>
</div>

<div class="form-group">
    <label class="form-label" for="description">Description</label>
    <textarea id="description" name="description" class="form-textarea">{{ $value('description') }}</textarea>
</div>
