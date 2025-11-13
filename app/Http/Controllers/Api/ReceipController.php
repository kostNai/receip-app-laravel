<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Category;
use App\Models\Ingredient;
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
            'title' => 'required|string',
            'text' => 'required|string',
            'category' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.value' => 'required|string',
        ], [
            'title.required' => 'Введіть заголовок',
            'text.required' => 'Введіть опис',
            'category.required' => 'Оберіть категорію',
            'ingredients.required' => 'Оберіть хоча б один інгредієнт',
            'ingredients.*.id.required' => 'ID інгредієнта обовʼязковий',
            'ingredients.*.id.exists' => 'Інгредієнт не знайдено, спочатку додайте його',
            'ingredients.*.value.required' => 'Вкажіть кількість або значення інгредієнта',
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
        if (Receip::where('title', $request->title)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Рецепт з такой назвою вже існує '
            ], 500);
        }

        try {
            $new_receip = Receip::create([
                'title' => $request->title,
                'text' => $request->text,
                'rating' => 0,
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);

            foreach ($request->ingredients as $ingredient) {
                $new_receip->ingredients()->attach($ingredient['id'], [
                    'ingredients_value' => $ingredient['value']
                ]);
            }

            return response()->json([
                'status' => true,
                'new_receip' => $new_receip
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
