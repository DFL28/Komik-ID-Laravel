<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'manga_id' => 'required|exists:manga,id',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
        
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'manga_id' => $validated['manga_id'],
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);
        
        $comment->load('user');
        
        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $comment = Comment::findOrFail($id);
        $comment->delete();
        
        return response()->json(['success' => true]);
    }
}
