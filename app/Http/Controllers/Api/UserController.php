<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();

        try {
            return response()->json([
                'status' => true,
                'users' => $users
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
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:4'
        ], [
            "name.required" => "Введіть ім'я",
            'email.required' => 'Введіть email',
            'email.email' => 'Email має бути формату example@mail.com',
            'password.required' => 'Введіть пароль',
            'password.min' => 'Пароль має бути не менше 4 символів'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }
        if (User::where('email', $request->email)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Такий email вже існує'
            ], 500);
        }

        $img = '';
        $path = '';
        if ($request->hasFile('user_img')) {

            $image = $request->file('user_img');
            $path = Storage::disk('s3')->put("uploads/users/{$request->email}", $image);

            $img = Storage::disk('s3')->url($path);
        }
        try {
            $new_user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ? $request->role : 'user',
                'image_url' => $img,
                'image_path' => $path
            ]);

            return response()->json([
                'status' => true,
                'new_user' => $new_user
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
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'user' => $user
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
        $img = null;
        $path = null;

        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Такого користувача не існує'
                ], 404);
            }

            if ($request->hasFile('user_img')) {

                $path = $user->image_path;
                Storage::disk('s3')->delete($path);
                $image = $request->file('user_img');
                $path = Storage::disk('s3')->put("uploads/users/{$user->email}", $image);

                $img = Storage::disk('s3')->url($path);
            }

            $user->update($request->all([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'image_url' => $request->user_img ? $img : $user->image_url,
                'image_path' => $request->user_img ? $path : $user->image_path,
                'role_id' => $request->role ? $request->role : $user->role
            ]));

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
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            $user->delete();
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
