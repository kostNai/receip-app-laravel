<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatController extends Controller
{
    public function index()
    {
        try {
            $stats = Stat::all();

            return response()->json([
                'status' => true,
                'stats' => $stats
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
            'prep_time' => "required",
            'cooking_time' => "required",
            'yields' => "required",
        ], [
            'prep_time.required' => 'Введіть час на підготовку',
            'prep_time.required' => 'Введіть час на приготування',
            'prep_time.required' => 'Введіть кількість порцій',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        try {
            $new_stat = Stat::create($request->all(), [
                'prep_time',
                'cooking_time',
                'yields',
                'receip_id'
            ]);

            return response()->json([
                'status' => true,
                'new_stat' => $new_stat
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
            $stat = Stat::where('id', $id)->first();

            if (!$stat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не знайдено'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'stat' => $stat
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
        $stat = Stat::where('id', $id)->first();

        if (!$stat) {
            return response()->json([
                'status' => false,
                'message' => 'Не знайдено'
            ], 404);
        }

        try {
            $stat::update($request->all(), [
                'prep_time',
                'cooking_time',
                'yields',
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
        $stat = Stat::where('id', $id)->first();

        if (!$stat) {
            return response()->json([
                'status' => false,
                'message' => 'Не знайдено'
            ], 404);
        }

        try {
            $stat::delete();

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
