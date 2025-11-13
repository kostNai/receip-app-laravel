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
            'step_number' => 'required',
            'step_text' => 'required|string',
        ], [
            'step_number.required' => 'Введіть номер кроку',
            'step_number.text' => 'Введіть текст кроку',
            'step_number.string' => 'Текст кроку має бути текстом',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
