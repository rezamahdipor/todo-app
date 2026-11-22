<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $stats = [
            'tasks'      => $user->tasks()->count(),
            'done'       => $user->tasks()->where('is_done', true)->count(),
            'habits'     => Habit::where('user_id', $user->id)->count(),
            'categories' => $user->categories()->count(),
        ];

        return view('account', compact('user', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validateWithBag('profile', [
            'name' => 'required|string|max:60',
        ]);

        $request->user()->forceFill($data)->save();

        return back()->with('status', 'نام ذخیره شد ✅');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('status', 'رمز عبور عوض شد 🔒');
    }

    public function updateAvatar(Request $request)
    {
        $request->validateWithBag('avatar', [
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100'],
        ]);

        $user = $request->user();
        $file = $request->file('avatar');
        $name = Str::random(40) . '.' . $file->extension();

        $file->move(public_path('uploads/avatars'), $name);

        $this->deleteAvatarFile($user->avatar);
        $user->forceFill(['avatar' => $name])->save();

        return back()->with('status', 'عکس پروفایل ذخیره شد 📸');
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        $this->deleteAvatarFile($user->avatar);
        $user->forceFill(['avatar' => null])->save();

        return back()->with('status', 'عکس پروفایل حذف شد');
    }

    private function deleteAvatarFile(?string $name): void
    {
        if (! $name) {
            return;
        }

        $path = public_path('uploads/avatars/' . basename($name));

        if (is_file($path)) {
            @unlink($path);
        }
    }
}