<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StepController extends Controller
{

    public function index()
    {
        try {
            $steps = Step::all();

            return response()->json([
                'status' => true,
                'steps' => $steps
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
            'steps' => 'required|array',
            'steps.*.step_number' => 'required|integer',
            'steps.*.step_text' => 'required|string',
            'steps.*.receip_id' => 'required|integer|exists:receips,id',
        ], [
            'steps.required' => 'Потрібно передати масив кроків',
            'steps.array' => 'Кроки мають бути у форматі масиву',

            'steps.*.step_number.required' => 'Введіть номер кроку',
            'steps.*.step_number.integer' => 'Номер кроку має бути числом',

            'steps.*.step_text.required' => 'Введіть текст кроку',
            'steps.*.step_text.string' => 'Текст кроку має бути текстовим',

            'steps.*.receip_id.required' => 'Вкажіть ID рецепту',
            'steps.*.receip_id.integer' => 'ID рецепту має бути числом',
            'steps.*.receip_id.exists' => 'Рецепт з таким ID не знайдено',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }
        try {
            $steps = $request->input('steps');

            foreach ($steps as $step) {
                Step::create([
                    'step_number' => $step['step_number'],
                    'step_text' => $step['step_text'],
                    'receip_id' => $step['receip_id'],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Створено успішно'
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
            $step = Step::where('id', $id)->first();

            if (!$step) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не знайдено'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'step' => $step
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
        $step = Step::where('id', $id)->first();

        if (!$step) {
            return response()->json([
                'status' => false,
                'message' => 'Не знайдено'
            ], 404);
        }

        try {
            $step->update($request->all(), [
                'step_number',
                'step_text',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Успішно змінено'
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
        $step = Step::where('id', $id)->first();

        if (!$step) {
            return response()->json([
                'status' => false,
                'message' => 'Не знайдено'
            ], 404);
        }

        try {
            $step->delete();

            return response()->json([
                'status' => true,
                'message' => 'Успішно видалено'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
