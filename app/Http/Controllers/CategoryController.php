<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:40',
                Rule::unique('categories')->where('user_id', $request->user()->id),
            ],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category = $request->user()->categories()->create($data);

        return response()->json($category, 201);
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($category->user_id === $request->user()->id, 403);

        $category->delete();

        return response()->json(['deleted' => true]);
    }
}