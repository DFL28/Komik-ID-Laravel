<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MangaController extends Controller
{
    public function index(Request $request)
    {
        $query = Manga::query()->withCount('chapters');

        $search = $request->get('q');
        $status = $request->get('status');
        $type = $request->get('type');

        if ($search) {
            $query->search($search);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $manga = $query->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $types = Manga::query()
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();

        return view('admin.manga.index', compact('manga', 'search', 'status', 'type', 'types'));
    }

    public function create()
    {
        $manga = new Manga();
        return view('admin.manga.create', compact('manga'));
    }

    public function store(Request $request)
    {
        $data = $this->validateManga($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        Manga::create($data);

        return redirect()->route('admin.manga.index')
            ->with('success', 'Manga berhasil dibuat.');
    }

    public function edit(Manga $manga)
    {
        return view('admin.manga.edit', compact('manga'));
    }

    public function update(Request $request, Manga $manga)
    {
        $data = $this->validateManga($request, $manga->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $manga->update($data);

        return redirect()->route('admin.manga.index')
            ->with('success', 'Manga berhasil diperbarui.');
    }

    public function destroy(Manga $manga)
    {
        DB::transaction(function () use ($manga) {
            $manga->chapters()->delete();
            $manga->comments()->delete();
            $manga->bookmarks()->delete();
            $manga->readingHistory()->delete();
            $manga->delete();
        });

        return redirect()->route('admin.manga.index')
            ->with('success', 'Manga berhasil dihapus.');
    }

    private function validateManga(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('manga', 'slug')->ignore($ignoreId),
            ],
            'alternative_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_path' => ['nullable', 'string', 'max:2048'],
            'author' => ['nullable', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'serialization' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:32'],
            'type' => ['nullable', 'string', 'max:64'],
            'genres' => ['nullable', 'string', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'country' => ['nullable', 'string', 'max:10'],
            'content_type' => ['nullable', 'string', 'max:64'],
        ]);
    }
}
