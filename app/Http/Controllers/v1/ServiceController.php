<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreUpdateServiceRequest;
use App\Http\Resources\v1\ServiceResource;
use App\Models\Service;
use App\Services\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServiceController extends Controller implements HasMiddleware
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
     *     tags={"/v1/services"},
     *     path="/v1/services",
     *     summary="Obter lista de serviços",
     *     description="Retorna uma lista com todos serviços",
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
     *                 @OA\items(ref="#/components/schemas/Service"),
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
        return ServiceResource::collection(Service::where("active", "=", "1")->paginate(self::PAGINATION));
    }

    /**
     * @OA\Get(
     *     tags={"/v1/services"},
     *     path="/v1/services/{id}",
     *     summary="Obter serviço específico",
     *     description="Retorna um serviço específico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do serviço",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
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
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $service = Service::findOrFail($id);
        return new ServiceResource($service);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/services"},
     *     path="/v1/services",
     *     summary="Registrar novo serviço",
     *     description="Cria um novo serviço",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "price"},
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="price", type="numeric", description="Preço"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Novo registro criado",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
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
    public function store(StoreUpdateServiceRequest $request){
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        $service = Service::create($data);
        return new ServiceResource($service);
    }

    /**
     * @OA\Put(
     *     tags={"/v1/services"},
     *     path="/v1/services{id}",
     *     summary="Atualizar serviço",
     *     description="Atualiza todos os dados de um serviço",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do serviço",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "price"},
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="price", type="numeric", description="Preço"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
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
     *     tags={"/v1/services"},
     *     path="/v1/services{id}",
     *     summary="Atualizar parcialmente serviço",
     *     description="Atualiza parcialmente os dados de um serviço",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do serviço",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Nome"),
     *             @OA\Property(property="price", type="numeric", description="Preço"),
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
     *         description="Somente superusuários possuem essa permissão",
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
    public function update(StoreUpdateServiceRequest $request, int $id){
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        
        $service = Service::findOrFail($id);
        $service->update($data);
        return new ServiceResource($service);
    }

    /**
     * @OA\Delete(
     *     tags={"/v1/services"},
     *     path="/v1/services/{id}",
     *     summary="Deletar serviço",
     *     description="Deleta um serviço",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do serviço",
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
     *         description="Somente superusuários possuem essa permissão",
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
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $service = Service::findOrFail($id);
        $service->active = false;
        $service->save();
        return response()->json([], 204);
    }
}
