<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware(AuthMiddleware::class, only: ['store', 'update', 'destroy'])
        ];
    }
    public function index()
    {
        try {
            $categories = Category::all();

            return response()->json([
                'status' => true,
                'categories' => $categories->load('receips')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ], [
            'name.required' => 'Введіть назву категорії'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }

        if (Category::where('name', $request->name)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Така категорія вже існує'
            ], 500);
        }

        try {
            $new_category = Category::create(['name' => $request->name]);

            return response()->json([
                'status' => true,
                'new_category' => $new_category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $category = Category::where('id', $id)->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Такої категорії не існує'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'category' => $category
        ]);
    }
    public function update(Request $request, string $id)
    {
        $category = Category::where('id', $id)->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Такої категорії не існує'
            ], 404);
        }
        try {

            $category->update($request->all(), [
                "name" => $request->name
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Змінено успішно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(string $id)
    {
        $category = Category::where('id', $id)->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Такої категорії не існує'
            ], 404);
        }
        try {

            $category->delete();

            return response()->json([
                'status' => true,
                'message' => 'Видалено успішно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
