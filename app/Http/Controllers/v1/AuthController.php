<?php

namespace App\Http\Controllers\v1;

use App\Http\Requests\v1\AuthRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller implements HasMiddleware
{
    public static function middleware(){
        return [
            new Middleware("auth:sanctum", only: ["logout"]),
            new Middleware("throttle:3,1", only: ["logout", "login"])
        ];
    }

    /**
     * @OA\Post(
     *     tags={"/v1/auth"},
     *     path="/v1/logout",
     *     summary="Logout",
     *     description="Invalidar token de autenticação",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=204,
     *         description="Sucesso, sem corpo de resposta",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([], 204);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/auth"},
     *     path="/v1/auth",
     *     summary="Login",
     *     description="Fazer autenticação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", description="e-mail"),
     *             @OA\Property(property="password", type="string", format="password", description="Senha"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", description="Token para autenticações"),
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="email", type="string", format="email", description="E-mail"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Login incorreto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=420,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", pattern="O campo email é obrigatório. (+ 1 erros)"),
     *             @OA\Property(property="errors", type="object", description="Campos com erro de validação e lista dos erros",
     *                 example={"campo_x": {"O campo email é obrigatório."}, 
     *                          "campo_y": {"O campo password é obrigatório."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function login(AuthRequest $request){
        $data = $request->validated();
        if(Auth::attempt($data)){
            $token = $request->user()->createToken("auth", ["*"])->plainTextToken;
            $name = $request->user()->first_name;
            $email = $request->user()->email;
            return response()->json(["token" => $token, "name" => $name, "email" => $email], 200);
        }
        return response()->json(["message" => trans("auth.failed")], 403);
    }
}
