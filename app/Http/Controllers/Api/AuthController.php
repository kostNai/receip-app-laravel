<?php

namespace App\Http\Controllers\Api;


use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
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
        try {
            $new_user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
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

    public function login(Request $request)
    {
        $user = null;

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:4',
            'email' => 'required|email'
        ], [
            'password.required' => 'Введіть пароль',
            'password.min' => 'Пароль має бути довшим 4 символів',
            'email.required' => 'Введіть email!',
            'email.email' => 'Email має бути у форматі email. Наприклад example@nail.com'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 500);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Помилка входу. Невірні дані'
            ], 409);
        }

        $old_token = RefreshToken::where('user_id', $user->id)->first();
        if ($old_token) $old_token->delete();

        $payload = ['email' => $user->email, 'name' => $user->name, 'role' => $user->role];

        function getClaims(string $type, array $payload)
        {
            return array_merge(['type' => $type], $payload);
        }

        $access_claims = getClaims('success', $payload);
        $refresh_claims = getClaims('refresh', $payload);


        $refresh_token = Auth::setTTL(60 * 24 * 60)->claims($refresh_claims)->fromUser($user);

        $access_token = Auth::setTTL(60)->claims($access_claims)->fromUser($user);

        $encrypted_refresh_token = Crypt::encrypt($refresh_token);


        $token = RefreshToken::create([
            'refresh_token' => $encrypted_refresh_token,
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token
        ]);
    }
    public function refresh(Request $request)
    {
        try {
            $refresh_token = $request->bearerToken();


            $payload = Auth::setToken($refresh_token)->getPayload();


            if ($payload['type'] !== 'refresh') {
                return response()->json(['error' => 'Invalid token type'], 401);
            }
            $user = auth()->user();

            $old_token = RefreshToken::where('user_id', $user->id)->first();
            $new_accessToken = Auth::setTTL(60)->claims(['type' => 'access'], $payload)->fromUser(auth()->user());
            $new_refreshToken = Auth::setTTL(60 * 24 * 60)->claims(['type' => 'refresh'], $payload)->fromUser(auth()->user());

            $encrypted_refresh_token = Crypt::encrypt($new_refreshToken);
            $old_token->delete();

            $token = RefreshToken::create([
                'refresh_token' => $encrypted_refresh_token,
                'user_id' => $user->id
            ]);

            return response()->json([
                'access_token' => $new_accessToken,
                'refresh_token' => $new_refreshToken,
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token refresh failed'], 401);
        }
    }
}
