<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Category;
use App\Models\Receip;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReceipController extends Controller implements HasMiddleware
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
            $receips = Receip::all();

            return response()->json([
                'status' => true,
                'receips' => $receips->load('category')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'text' => 'required',
            'category' => 'required'
        ], [
            'title.required' => 'Введіть заголовок',
            'text.required' => 'Введіть опис',
            'category.required' => 'Оберіть категорію',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }

        $category = Category::where('name', $request->category)->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Такої категорії не існує'
            ], 404);
        }

        try {
            $new_receip = Receip::create([
                'title' => $request->title,
                'text' => $request->text,
                'rating' => 0,
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);

            return response()->json([
                'status' => true,
                'new_receip' => $new_receip
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function show(string $id)
    {
        $receip = Receip::where('id', $id)->first();

        if (!$receip) {
            return response()->json([
                'status' => false,
                'message' => 'Такого рецепту не існує'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'receip' => $receip->load('category')
        ]);
    }

    public function update(Request $request, string $id)
    {
        $receip = Receip::where('id', $id)->first();

        if (!$receip) {
            return response()->json([
                'status' => false,
                'message' => 'Такого рецепту не існує'
            ], 404);
        }
        $category = null;
        try {
            $updateData = $request->only(['title', 'text', 'rating']);

            if ($request->has('category')) {
                $category = Category::where('name', $request->category)->first();

                if (!$category) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Такої категорії не існує'
                    ], 404);
                }

                $updateData['category_id'] = $category->id;
            }

            $receip->update($updateData);

            return response()->json([
                'status' => true,
                'message' => 'Змінено успішно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
    public function destroy(string $id)
    {
        $receip = Receip::where('id', $id)->first();

        if (!$receip) {
            return response()->json([
                'status' => false,
                'message' => 'Такого рецепту не існує'
            ], 404);
        }

        try {
            $receip->delete();

            return response()->json([
                'status' => true,
                'message' => 'Видалено успішно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
