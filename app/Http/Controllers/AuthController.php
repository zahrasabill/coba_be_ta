<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Dedoc\Scramble\Annotations\Description;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication management endpoints",
 *     x={"order": 2}
 * )
 */

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/v1/login",
     *     summary="Login User",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kode_akses", "password"},
     *             @OA\Property(property="kode_akses", type="string", format="string", example="ERS-736521"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login success"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    #[Description("Login User")]
    public function login(Request $request)
    {
        $request->validate([
            'kode_akses' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Cek kredensial berdasarkan kode_akses dan password
        $credentials = $request->only('kode_akses', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Kode Akses atau Password Salah!'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/v1/logout",
     *     summary="Logout User",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logged out successfully")
     * )
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/v1/refresh",
     *     summary="Refresh JWT Token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Token refreshed")
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
