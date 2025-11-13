<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller implements HasMiddleware
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
            $ingredients = Ingredient::all();

            return response()->json([
                'status' => true,
                'ingredients' => $ingredients
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
        $validator = Validator::make($request->all(), [
            'ingredient' => 'required'
        ], [
            'ingredient.required' => 'Введіть назву інгредієнту'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->messages()->all()
            ], 500);
        }
        if (Ingredient::where('ingredient', $request->ingredient)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Такий інгредієнт вже існує'
            ], 500);
        }
        try {
            $new_ingredient = Ingredient::create([
                'ingredient' => $request->ingredient
            ]);

            return response()->json([
                'status' => true,
                'new_ingredient' => $new_ingredient
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
        try {
            $ingredient = Ingredient::where('id', $id)->first();

            if (!$ingredient) {
                return response()->json([
                    'status' => false,
                    'message' => 'Такого інгредієнту не існує'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'ingredient' => $ingredient
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, string $id)
    {
        $ingredient = Ingredient::where('id', $id)->first();

        if (!$ingredient) {
            return response()->json([
                'status' => false,
                'message' => 'Такого інгредієнту не існує'
            ], 404);
        }
        $validator = Validator::make(
            $request->all(),
            ['ingredient' => 'required'],
            ['ingredient.required' => 'Введіть назву інгредієнту']
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }
        if (Ingredient::where('ingredient', $request->ingredient)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Такий інгредієнт вже існує'
            ]);
        }

        try {
            $ingredient->update($request->all(), ['ingredient']);

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
        $ingredient = Ingredient::where('id', $id)->first();

        if (!$ingredient) {
            return response()->json([
                'status' => false,
                'message' => 'Такого інгредієнту не існує'
            ], 404);
        }
        try {
            $ingredient->delete();

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
