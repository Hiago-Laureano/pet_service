<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreUpdateUserRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Services\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    const PAGINATION = 15;

    public static function middleware(){
        return [
            new Middleware("auth:sanctum", only: ["index", "show", "update", "destroy"]),
            new Middleware("throttle:8,1", only: ["index", "show", "store", "update", "destroy"])
        ];
    }

    /**
     * @OA\Get(
     *     tags={"/v1/users"},
     *     path="/v1/users",
     *     summary="Obter lista de usuários",
     *     description="Retorna uma lista com todos usuários",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da paginação dos dados. São 15 registros por paginação",
     *         @OA\Schema(type="int"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Dados da requisição",
     *                 @OA\items(ref="#/components/schemas/User"),
     *             ),
     *             @OA\Property(property="links", type="object", description="Links da paginação",
     *                 example={"first": "http:localhost/api/v1/item?page=1", 
     *                          "last": "http:localhost/api/v1/item?page=3",
     *                          "prev": "http:localhost/api/v1/item?page=1",
     *                          "next": "http:localhost/api/v1/item?page=3"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request){
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        return UserResource::collection(User::where("active", "=", "1")->paginate(self::PAGINATION));
    }

    /**
     * @OA\Get(
     *     tags={"/v1/users"},
     *     path="/v1/users/{id}",
     *     summary="Obter usuário específico",
     *     description="Retorna um usuário específico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Id do usuário",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente o propretário do registro e superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Request $request, int $id){
        if(! Permissions::IsSuperuserOrMe($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $users = User::where("active", "=", "1");
        $user = $users->findOrFail($id);
        return new UserResource($user);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/users"},
     *     path="/v1/users",
     *     summary="Registrar novo usuário",
     *     description="Cria um novo usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"first_name", "last_name", "phone", "email", "password"},
     *             @OA\Property(property="first_name", type="string", description="Nome"),
     *             @OA\Property(property="last_name", type="string", description="Sobrenome"),
     *             @OA\Property(property="phone", type="string", pattern="5544999998888", description="Número de telefone (Somente números, código do país + DDD + número)"),
     *             @OA\Property(property="email", type="string", format="email", description="E-mail"),
     *             @OA\Property(property="password", type="string", format="password", description="Senha"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Novo registro criado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente o propretário do registro e superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=420,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", pattern="O campo x é obrigatório. (+ 3 erros)"),
     *             @OA\Property(property="errors", type="object", description="Campos com erro de validação e lista dos erros",
     *                 example={"campo_x": {"O campo x é obrigatório."}, 
     *                          "campo_y": {"O campo y deve ser um inteiro."},
     *                          "campo_z": {"O campo z deve ter no máximo 30 caracteres."},
     *                          "campo_w": {"O campo w é obrigatório."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreUpdateUserRequest $request){
        $data = $request->validated();
        $user = User::create($data);
        return new UserResource($user);
    }

    /**
     * @OA\Put(
     *     tags={"/v1/users"},
     *     path="/v1/users{id}",
     *     summary="Atualizar usuário",
     *     description="Atualiza todos os dados de um usuário",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Id do usuário",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"first_name", "last_name", "phone", "email", "password"},
     *             @OA\Property(property="first_name", type="string", description="Nome"),
     *             @OA\Property(property="last_name", type="string", description="Sobrenome"),
     *             @OA\Property(property="phone", type="string", pattern="5544999998888", description="Número de telefone (Somente números, código do país + DDD + número)"),
     *             @OA\Property(property="email", type="string", format="email", description="E-mail"),
     *             @OA\Property(property="password", type="string", format="password", description="Senha"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente o propretário do registro e superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=420,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", pattern="O campo x é obrigatório. (+ 3 erros)"),
     *             @OA\Property(property="errors", type="object", description="Campos com erro de validação e lista dos erros",
     *                 example={"campo_x": {"O campo x é obrigatório."}, 
     *                          "campo_y": {"O campo y deve ser um inteiro."},
     *                          "campo_z": {"O campo z deve ter no máximo 30 caracteres."},
     *                          "campo_w": {"O campo w é obrigatório."}
     *                 }
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Patch(
     *     tags={"/v1/users"},
     *     path="/v1/users{id}",
     *     summary="Atualizar parcialmente usuário",
     *     description="Atualiza parcialmente os dados de um usuário",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Id do usuário",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", description="Nome"),
     *             @OA\Property(property="last_name", type="string", description="Sobrenome"),
     *             @OA\Property(property="phone", type="string", pattern="5544999998888", description="Número de telefone (Somente números, código do país + DDD + número)"),
     *             @OA\Property(property="email", type="string", format="email", description="E-mail"),
     *             @OA\Property(property="password", type="string", format="password", description="Senha"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente o propretário do registro e superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=420,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", pattern="O campo x deve ser um inteiro. (+ 2 erros)"),
     *             @OA\Property(property="errors", type="object", description="Campos com erro de validação e lista dos erros",
     *                 example={"campo_x": {"O campo x deve ser um inteiro."}, 
     *                          "campo_y": {"O campo y deve ser um inteiro."},
     *                          "campo_z": {"O campo z deve ter no máximo 30 caracteres."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function update(StoreUpdateUserRequest $request, int $id){
        if(! Permissions::IsSuperuserOrMe($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        $users = User::where("active", "=", "1");
        $user = $users->findOrFail($id);
        $user->update($data);
        return new UserResource($user);
    }

    /**
     * @OA\Delete(
     *     tags={"/v1/users"},
     *     path="/v1/users/{id}",
     *     summary="Deletar usuário",
     *     description="Deleta um usuário",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Id do usuário",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Sucesso, sem corpo de resposta"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Somente o propretário do registro e superusuários possuem essa permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, int $id){
        if(! Permissions::IsSuperuserOrMe($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $users = User::where("active", "=", "1");
        $user = $users->findOrFail($id);
        $user->active = false;
        $user->save();
        return response()->json([], 204);
    }
}
