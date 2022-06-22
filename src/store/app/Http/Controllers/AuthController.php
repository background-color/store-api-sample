<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{

    /**
     * ユーザ登録
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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

        return response()->json([
            'message' => 'User registration completed',
        ], Response::HTTP_OK);
    }

    /**
     * ログイン
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = User::whereEmail($request->email)->first();

            $user->tokens()->delete();
            $token = $user->createToken("login:user{$user->id}")->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'User Not Found.'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
