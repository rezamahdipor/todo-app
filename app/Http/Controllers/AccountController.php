<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}