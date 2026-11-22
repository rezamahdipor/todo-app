<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    private const MAX_POSTS = 100;

    public function index(Request $request)
    {
        $posts = Post::where('user_id', $request->user()->id)->latest()->get();

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.edit', ['post' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (Post::where('user_id', $request->user()->id)->count() >= self::MAX_POSTS) {
            throw ValidationException::withMessages([
                'title' => 'حداکثر ۱۰۰ نوشته می‌تونی داشته باشی.',
            ]);
        }

        $post = new Post($data);
        $post->user_id = $request->user()->id;
        $post->slug = Post::makeSlug($data['title']);
        $post->published_at = $request->boolean('publish') ? now() : null;
        $post->save();

        return redirect()->route('posts.index')
            ->with('status', $post->published_at ? 'نوشته منتشر شد 🎉' : 'پیش‌نویس ذخیره شد ✅');
    }

    public function edit(Request $request, Post $post)
    {
        $this->own($request, $post);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->own($request, $post);

        $post->fill($this->validated($request));
        $post->slug = Post::makeSlug($post->title);

        if ($request->boolean('publish')) {
            if (! $post->published_at) {
                $post->published_at = now();
            }
        } else {
            $post->published_at = null;
        }

        $post->save();

        return redirect()->route('posts.index')->with('status', 'نوشته ذخیره شد ✅');
    }

    public function destroy(Request $request, Post $post)
    {
        $this->own($request, $post);

        $post->delete();

        return redirect()->route('posts.index')->with('status', 'نوشته حذف شد 🗑️');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:160',
            'body'        => 'required|string|min:20|max:20000',
        ]);
    }

    private function own(Request $request, Post $post): void
    {
        abort_unless($post->user_id === $request->user()->id, 403);
    }
}