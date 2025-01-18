<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreUpdateMedicalRecordRequest;
use App\Http\Resources\v1\MedicalRecordResource;
use App\Jobs\MedicalRecordJob;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicalRecordController extends Controller implements HasMiddleware
{
    const PAGINATION = 15;

    public static function middleware(){
        return [
            new Middleware("auth:sanctum", only: ["index", "show", "store", "update", "destroy"]),
            new Middleware("throttle:8,1", only: ["index", "show", "store", "update", "destroy", "result"])
        ];
    }

    /**
     * @OA\Get(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords",
     *     summary="Obter lista de prontuários médicos",
     *     description="Retorna uma lista com todos prontuários médicos",
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
     *                 @OA\items(ref="#/components/schemas/MedicalRecord"),
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
        return MedicalRecordResource::collection(MedicalRecord::paginate(self::PAGINATION));
    }

    /**
     * @OA\Get(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords/{id}",
     *     summary="Obter prontuário médico específico",
     *     description="Retorna um prontuário médico específico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do prontuário médico",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/MedicalRecord")
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
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $mr = MedicalRecord::findOrFail($id);
        return new MedicalRecordResource($mr);
    }

    /**
     * @OA\Get(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords/result",
     *     summary="Obter prontuário médico através do código de acesso ao prontuário",
     *     description="Retorna um prontuário médico através do código de acesso ao prontuário",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Código de acesso ao prontuário",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/MedicalRecord")
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
    public function result(Request $request){
        $code = $request->get("code") ?? "0";
        $mr = MedicalRecord::where("access_code", "=", "{$code}")->firstOrFail();
        return new MedicalRecordResource($mr);
    }

    /**
     * @OA\Post(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords",
     *     summary="Registrar novo prontuário médico",
     *     description="Cria um novo prontuário médico",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id", "pet_id", "observation"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono do pet"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="observation", type="string", description="Observações"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Novo registro criado",
     *         @OA\JsonContent(ref="#/components/schemas/MedicalRecord")
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
    public function store(StoreUpdateMedicalRecordRequest $request){
        if(! Permissions::IsStaff($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        $data["access_code"] = fake()->unique()->regexify("[A-Za-z0-9]{60}");
        $mr = MedicalRecord::create($data);
        MedicalRecordJob::dispatch(user: User::find($mr->user_id), accessCode: $mr->access_code);
        return new MedicalRecordResource($mr);
    }
    
    /**
     * @OA\Put(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords{id}",
     *     summary="Atualizar prontuário médico",
     *     description="Atualiza todos os dados de um prontuário médico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do prontuário médico",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id", "pet_id", "observation"},
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono do pet"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="observation", type="string", description="Observações"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/MedicalRecord")
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
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords{id}",
     *     summary="Atualizar parcialmente prontuário médico",
     *     description="Atualiza parcialmente os dados de um prontuário médico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do prontuário médico",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", description="Id do usuário dono do pet"), 
     *             @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
     *             @OA\Property(property="observation", type="string", description="Observações"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/MedicalRecord")
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
    public function update(StoreUpdateMedicalRecordRequest $request, int $id){
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $data = $request->validated();
        $mr = MedicalRecord::findorFail($id);
        $mr->update($data);
        return new MedicalRecordResource($mr);
    }
    
    /**
     * @OA\Delete(
     *     tags={"/v1/medicalrecords"},
     *     path="/v1/medicalrecords/{id}",
     *     summary="Deletar prontuário médico",
     *     description="Deleta um prontuário médico",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id do prontuário médico",
     *         required=true,
     *         @OA\Schema(type="integer")
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
        if(! Permissions::IsSuperuser($request->user())){
            return response()->json(["message" => trans("auth.not_authorized")], 403);
        }
        $mr = MedicalRecord::findOrFail($id);
        $mr->delete();
        return response()->json([], 204);
    } 
}
