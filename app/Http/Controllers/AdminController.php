<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // همه‌ی صفحه‌های پنل؛ شمار گزارش‌های باز برای عدد قرمز تب
    private function page(array $data)
    {
        return view('admin', $data + ['openReports' => Report::whereNull('resolved_at')->count()]);
    }

    public function index()
    {
        $stats = [
            'users'    => User::count(),
            'online'   => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
            'admins'   => User::where('is_admin', true)->count(),
            'banned'   => User::where('is_banned', true)->count(),
            'posts'    => Post::published()->count(),
            'drafts'   => Post::whereNull('published_at')->count(),
            'hidden'   => Post::whereNotNull('hidden_at')->count(),
            'tasks'    => DB::table('tasks')->count(),
            'habits'   => DB::table('habits')->count(),
            'messages' => ChatMessage::count(),
            'reports'  => Report::whereNull('resolved_at')->count(),
        ];

        return $this->page([
            'tab'         => 'overview',
            'stats'       => $stats,
            'recentUsers' => User::latest('id')->take(5)->get(),
            'recentPosts' => Post::with('user:id,name')->latest()->take(5)->get(),
        ]);
    }

    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $filter = $request->query('filter');

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->when($filter === 'admins', fn ($query) => $query->where('is_admin', true))
            ->when($filter === 'banned', fn ($query) => $query->where('is_banned', true))
            ->when($filter === 'online', fn ($query) => $query->where('last_seen_at', '>=', now()->subMinutes(5)))
            ->orderByDesc('id')
            ->simplePaginate(20)
            ->withQueryString();

        $ids = $users->pluck('id');

        $taskCounts = DB::table('tasks')->whereIn('user_id', $ids)
            ->selectRaw('user_id, COUNT(*) as c')->groupBy('user_id')->pluck('c', 'user_id');

        $postCounts = DB::table('posts')->whereIn('user_id', $ids)
            ->selectRaw('user_id, COUNT(*) as c')->groupBy('user_id')->pluck('c', 'user_id');

        return $this->page([
            'tab' => 'users', 'q' => $q, 'filter' => $filter, 'users' => $users,
            'taskCounts' => $taskCounts, 'postCounts' => $postCounts,
        ]);
    }

    public function toggleBan(Request $request, User $user)
    {
        // نه خودت رو، نه ادمین دیگه‌ای رو
        abort_if($user->id === $request->user()->id || $user->is_admin, 403);

        $user->forceFill(['is_banned' => ! $user->is_banned])->save();

        return back()->with('status', $user->is_banned ? 'کاربر مسدود شد 🚫' : 'مسدودی کاربر برداشته شد ✅');
    }

    // ادمین کردن یا برداشتن دسترسی (نه برای خودت)
    public function toggleAdmin(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403);

        if (! $user->is_admin && $user->is_banned) {
            return back()->withErrors(['admin' => 'اول مسدودی این کاربر رو بردار.']);
        }

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        return back()->with('status', $user->is_admin
            ? "{$user->name} حالا مدیره 👑"
            : "دسترسی مدیریت {$user->name} برداشته شد");
    }

    public function posts(Request $request)
    {
        $filter = $request->query('filter');

        $posts = Post::with('user:id,name')
            ->when($filter === 'hidden', fn ($q) => $q->whereNotNull('hidden_at'))
            ->when($filter === 'draft', fn ($q) => $q->whereNull('published_at'))
            ->latest()
            ->simplePaginate(20)
            ->withQueryString();

        return $this->page(['tab' => 'posts', 'posts' => $posts, 'filter' => $filter]);
    }

    public function toggleHide(Post $post)
    {
        $post->hidden_at = $post->hidden_at ? null : now();
        $post->save();

        return back()->with('status', $post->hidden_at ? 'نوشته پنهان شد 🙈' : 'نوشته دوباره نمایش داده می‌شه ✅');
    }

    public function destroyPost(Post $post)
    {
        $post->delete();

        return back()->with('status', 'نوشته حذف شد 🗑️');
    }

    public function chat(Request $request)
    {
        $room = (int) $request->query('room', 0);

        $messages = ChatMessage::with('user:id,name,avatar')
            ->when(in_array($room, [1, 2, 3], true), fn ($q) => $q->where('room', $room))
            ->orderByDesc('id')
            ->simplePaginate(30)
            ->withQueryString();

        return $this->page(['tab' => 'chat', 'messages' => $messages, 'room' => $room]);
    }

    public function deleteMessage(Request $request, ChatMessage $message)
    {
        $message->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('status', 'پیام حذف شد');
    }

    // ---------- گزارش‌ها ----------
    public function reports(Request $request)
    {
        $state = $request->query('state') === 'resolved' ? 'resolved' : 'open';

        $reports = Report::with('reporter:id,name')
            ->when(
                $state === 'open',
                fn ($q) => $q->whereNull('resolved_at'),
                fn ($q) => $q->whereNotNull('resolved_at')
            )
            ->latest('id')
            ->simplePaginate(20)
            ->withQueryString();

        $items = $reports->getCollection();

        $posts = Post::with('user:id,name,is_admin')
            ->whereIn('id', $items->where('type', 'post')->pluck('target_id')->unique())
            ->get()->keyBy('id');

        $msgs = ChatMessage::with('user:id,name,is_admin')
            ->whereIn('id', $items->where('type', 'message')->pluck('target_id')->unique())
            ->get()->keyBy('id');

        $counts = Report::whereNull('resolved_at')
            ->selectRaw('type, target_id, COUNT(*) as c')
            ->groupBy('type', 'target_id')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->type . ':' . $r->target_id => $r->c]);

        return $this->page([
            'tab' => 'reports', 'state' => $state, 'reports' => $reports,
            'posts' => $posts, 'msgs' => $msgs, 'counts' => $counts,
        ]);
    }

    public function reportAct(Request $request, Report $report)
    {
        $action = $request->validate(['action' => 'required|in:dismiss,hide,delete,ban'])['action'];

        $post = $report->type === 'post' ? Post::find($report->target_id) : null;
        $msg = $report->type === 'message' ? ChatMessage::find($report->target_id) : null;
        $authorId = $post?->user_id ?? $msg?->user_id;

        if ($action === 'hide' && $post) {
            $post->hidden_at = now();
            $post->save();
        }

        if ($action === 'delete' || $action === 'ban') {
            if ($action === 'ban' && $authorId) {
                $author = User::find($authorId);

                if ($author && ! $author->is_admin && $author->id !== $request->user()->id) {
                    $author->forceFill(['is_banned' => true])->save();
                }
            }

            // با مسدودسازی، نوشته پنهان و پیام حذف می‌شه؛ با «حذف»، هر دو پاک می‌شن
            if ($action === 'ban' && $post) {
                $post->hidden_at = now();
                $post->save();
            } else {
                $post?->delete();
            }

            $msg?->delete();
        }

        // همه‌ی گزارش‌های باز همین محتوا بسته می‌شن
        Report::whereNull('resolved_at')
            ->where('type', $report->type)
            ->where('target_id', $report->target_id)
            ->update(['resolved_at' => now()]);

        return back()->with('status', match ($action) {
            'hide'  => 'نوشته پنهان و گزارش بسته شد 🙈',
            'delete' => 'محتوا حذف و گزارش بسته شد 🗑️',
            'ban'   => 'نویسنده مسدود، محتوا برداشته و گزارش بسته شد 🚫',
            default => 'گزارش رد شد ✅',
        });
    }
}