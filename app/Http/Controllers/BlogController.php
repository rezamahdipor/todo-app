<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with('user:id,name,avatar')
            ->latest('published_at')
            ->simplePaginate(9);

        return view('blog.index', ['posts' => $posts, 'author' => null]);
    }

    public function author(User $user)
    {
        $posts = Post::published()
            ->where('user_id', $user->id)
            ->with('user:id,name,avatar')
            ->latest('published_at')
            ->simplePaginate(9);

        return view('blog.index', ['posts' => $posts, 'author' => $user]);
    }

    public function show(Request $request, Post $post, ?string $slug = null)
    {
        $owner = $request->user()?->id === $post->user_id;

        // پیش‌نویس فقط برای صاحبش دیده می‌شه
        abort_unless($post->isPublished() || $owner, 404);

        // آدرس درست (کانونیکال)
        if ($slug !== $post->slug) {
            return redirect()->route('blog.show', [$post, $post->slug], 301);
        }

        $post->load('user:id,name,avatar');

        $more = Post::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get(['id', 'title', 'slug']);

        return view('blog.show', compact('post', 'more'));
    }

    public function sitemap()
    {
        $urls = [[route('home'), null], [route('blog.index'), null], [route('privacy'), null], [route('terms'), null], [route('contact'), null]];

        $posts = Post::published()
            ->latest('published_at')
            ->limit(5000)
            ->get(['id', 'slug', 'updated_at']);

        foreach ($posts as $p) {
            $urls[] = [route('blog.show', [$p, $p->slug]), $p->updated_at->toAtomString()];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as [$loc, $mod]) {
            $xml .= '<url><loc>' . e($loc) . '</loc>' . ($mod ? "<lastmod>{$mod}</lastmod>" : '') . '</url>';
        }

        return response($xml . '</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}