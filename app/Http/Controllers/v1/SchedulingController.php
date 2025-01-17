<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreUpdateSchedulingRequest;
use App\Http\Resources\v1\SchedulingResource;
use App\Models\Scheduling;
use App\Services\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SchedulingController extends Controller implements HasMiddleware
{
    const PAGINATION = 15;

    public static function middleware(){
        return [
            new Middleware("auth:sanctum", only: ["index", "show", "store", "update", "destroy"]),
            new Middleware("throttle:6,1", only: ["index", "show", "store", "update", "destroy"])
        ];
    }

    /**
     * @OA\Get(
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings",
     *     summary="Obter lista de agendamentos",
     *     description="Retorna uma lista com todos agendamentos",
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
     *                 @OA\items(ref="#/components/schemas/Scheduling"),
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
        return SchedulingResource::collection(Scheduling::paginate(self::PAGINATION));
    }

    /**
     * @OA\Get(
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings/{id}",
     *     summary="Obter agendamento específico",
     *     description="Retorna um agendamento específico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Scheduling")
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
        if(! Permissions::IsSuperuserOrMyScheduling($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $scheduling = Scheduling::findOrFail($id);
        return new SchedulingResource($scheduling);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings",
     *     summary="Registrar novo agendamento",
     *     description="Cria um novo agendamento",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"pet_id", "service_id", "date", "finished"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="service_id", type="integer", description="Id do serviço"), 
     *             @OA\Property(property="date", type="string", pattern="2024/12/11 11:12:40", description="Data do agendamento"),
     *             @OA\Property(property="finished", type="boolean", description="Se o agendamento foi finalizado"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Novo registro criado",
     *         @OA\JsonContent(ref="#/components/schemas/Scheduling")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login necessário",
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
    public function store(StoreUpdateSchedulingRequest $request){
        $data = $request->validated();
        if(! isset($data["user_id"])){
            $data = array_merge($data, ["user_id" => $request->user()->id]);
        }elseif(! $request->user()->is_superuser){
            $data["user_id"] == $request->user()->id;
        }
        if(!($request->user()->is_superuser) || !(isset($data["user_id"]))){
            $data["user_id"] == $request->user()->id;
        }
        $scheduling = Scheduling::create($data);
        return new SchedulingResource($scheduling);
    }

    /**
     * @OA\Put(
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings{id}",
     *     summary="Atualizar agendamento",
     *     description="Atualiza todos os dados de um agendamento",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"pet_id", "service_id", "date", "finished"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="service_id", type="integer", description="Id do serviço"), 
     *             @OA\Property(property="date", type="string", pattern="2024/12/11 11:12:40", description="Data do agendamento"),
     *             @OA\Property(property="finished", type="boolean", description="Se o agendamento foi finalizado"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Scheduling")
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
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings{id}",
     *     summary="Atualizar parcialmente agendamento",
     *     description="Atualiza parcialmente os dados de um agendamento",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="service_id", type="integer", description="Id do serviço"), 
     *             @OA\Property(property="date", type="string", pattern="2024/12/11 11:12:40", description="Data do agendamento"),
     *             @OA\Property(property="finished", type="boolean", description="Se o agendamento foi finalizado"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Scheduling")
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
    public function update(StoreUpdateSchedulingRequest $request, int $id){
        if(! Permissions::IsSuperuserOrMyScheduling($request->user(), $id)){
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
        $scheduling = Scheduling::findOrFail($id);
        $scheduling->update($data);
        return new SchedulingResource($scheduling);
    }

    /**
     * @OA\Delete(
     *     tags={"/v1/schedulings"},
     *     path="/v1/schedulings/{id}",
     *     summary="Deletar agendamento",
     *     description="Deleta um agendamento",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do agendamento",
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
        if(! Permissions::IsSuperuserOrMyScheduling($request->user(), $id)){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $scheduling = Scheduling::findOrFail($id);
        $scheduling->delete();
        return response()->json([], 204);
    }
}
