<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseTrait;
use App\Models\User;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    use ResponseTrait;

    /**
     * POST ユーザー登録
     *
     * @group Auth
     * @bodyParam name string required 名前 Example: 山田花子
     * @bodyParam email string required メールアドレス Example: user1@example.com
     * @bodyParam password string required パスワード Example: password
     * @response 200 {
     *     "message": "User registration completed",
     *     "data": {
     *         "id": 15,
     *         "name": "山田花子",
     *         "email": "user1@example.com"
     *     }
     * }
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'User registration failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = User::create([
            'name' =>  $request->name,
            'email' => $request->email,
            'point' => 10000,
            'password' => Hash::make($request->password),
        ]);

        return $this->getResponse(
            [
                'id' => $user->id,
                'name' =>  $user->name,
                'email' => $user->email,
            ],
            'User registration completed'
        );
    }

    /**
     * POST ログイン
     *
     * @group Auth
     * @bodyParam email string required メールアドレス Example: user1@example.com
     * @bodyParam password string required パスワード Example: password
     * @response 200 {
     *     "message": "Success",
     *     "data": {
     *         "access_token": "xxxxxxxxxxxxxxxxxxxx",
     *         "token_type": "Bearer",
     *     }
     * }
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return $this->getErrorResponse('User Not Found.');
        }

        $user = User::whereEmail($request->email)->first();
        $user->tokens()->delete();
        $token = $user->createToken("login:user{$user->id}")->plainTextToken;

        return $this->getResponse(
            [
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
        );
    }
}
