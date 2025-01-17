<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreUpdatePetRequest;
use App\Http\Resources\v1\PetResource;
use App\Models\Pet;
use App\Services\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PetController extends Controller implements HasMiddleware
{
    const PAGINATION = 15;

    public static function middleware(){
        return [
            new Middleware("auth:sanctum", only: ["index", "show", "store", "update", "destroy"]),
            new Middleware("throttle:8,1", only: ["index", "show", "store", "update", "destroy"])
        ];
    }

    /**
     * @OA\Get(
     *     tags={"/v1/pets"},
     *     path="/v1/pets",
     *     summary="Obter lista de pets",
     *     description="Retorna uma lista com todos pets",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da paginação dos dados. São 15 registros por paginação",
     *         @OA\Schema(type="integer"),
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
     *                 @OA\items(ref="#/components/schemas/Pet"),
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
        return PetResource::collection(Pet::where("active", "=", "1")->paginate(self::PAGINATION));
    }

    /**
     * @OA\Get(
     *     tags={"/v1/pets"},
     *     path="/v1/pets/{id}",
     *     summary="Obter pet específico",
     *     description="Retorna um pet específico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do pet",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
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
        if(! Permissions::IsSuperuserOrMyPet($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $pets = Pet::where("active", "=", "1");
        $pet = $pets->findOrFail($id);
        return new PetResource($pet);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/pets"},
     *     path="/v1/pets",
     *     summary="Registrar novo pet",
     *     description="Cria um novo pet",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "species", "breed", "weight", "gender", "agressive"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono, se não for fornecido, será atribuído o usuário logado"),
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="species", type="string", description="Espécie"),
     *             @OA\Property(property="breed", type="string", description="Raça"),
     *             @OA\Property(property="weight", type="numeric", description="Peso"),
     *             @OA\Property(property="age", type="integer", description="Idade"),
     *             @OA\Property(property="gender", type="string", description="Sexo, 'M' para macho e 'F' para fêmea"),
     *             @OA\Property(property="agressive", type="boolean", description="Se é agressivo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Novo registro criado",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
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
    public function store(StoreUpdatePetRequest $request){
        $data = $request->validated();
        if(! isset($data["user_id"])){
            $data = array_merge($data, ["user_id" => $request->user()->id]);
        }elseif(! $request->user()->is_superuser){
            $data["user_id"] == $request->user()->id;
        }
        if(!($request->user()->is_superuser) || !(isset($data["user_id"]))){
            $data["user_id"] == $request->user()->id;
        }
        $pet = Pet::create($data);
        return new PetResource($pet);
    }

    /**
     * @OA\Put(
     *     tags={"/v1/pets"},
     *     path="/v1/pets{id}",
     *     summary="Atualizar pet",
     *     description="Atualiza todos os dados de um pet",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do pet",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "species", "breed", "weight", "gender", "agressive"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono, se não for fornecido, será atribuído o usuário logado"),
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="species", type="string", description="Espécie"),
     *             @OA\Property(property="breed", type="string", description="Raça"),
     *             @OA\Property(property="weight", type="numeric", description="Peso"),
     *             @OA\Property(property="age", type="integer", description="Idade"),
     *             @OA\Property(property="gender", type="string", description="Sexo, 'M' para macho e 'F' para fêmea"),
     *             @OA\Property(property="agressive", type="boolean", description="Se é agressivo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
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
     *     tags={"/v1/pets"},
     *     path="/v1/pets{id}",
     *     summary="Atualizar parcialmente pet",
     *     description="Atualiza parcialmente os dados de um pet",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do pet",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono, se não for fornecido, será atribuído o usuário logado"),
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="species", type="string", description="Espécie"),
     *             @OA\Property(property="breed", type="string", description="Raça"),
     *             @OA\Property(property="weight", type="numeric", description="Peso"),
     *             @OA\Property(property="age", type="integer", description="Idade"),
     *             @OA\Property(property="gender", type="string", description="Sexo, 'M' para macho e 'F' para fêmea"),
     *             @OA\Property(property="agressive", type="boolean", description="Se é agressivo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
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
    public function update(StoreUpdatePetRequest $request, int $id){
        if(! Permissions::IsSuperuserOrMyPet($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        if(! isset($data["user_id"])){
            if(($request->method() === "PATCH" && !($request->user()->is_superuser)) || $request->method() === "PUT"){
                $data = array_merge($data, ["user_id" => $request->user()->id]);
            }
        }elseif(! $request->user()->is_superuser){
            $data["user_id"] == $request->user()->id;
        }
        $pets = Pet::where("active", "=", "1");
        $pet = $pets->findOrFail($id);
        $pet->update($data);
        return new PetResource($pet);
    }

    /**
     * @OA\Delete(
     *     tags={"/v1/pets"},
     *     path="/v1/pets/{id}",
     *     summary="Deletar pet",
     *     description="Deleta um pet",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do pet",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
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
        if(! Permissions::IsSuperuserOrMyPet($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $pets = Pet::where("active", "=", "1");
        $pet = $pets->findOrFail($id);
        $pet->active = false;
        $pet->save();
        return response()->json([], 204);
    }
}
