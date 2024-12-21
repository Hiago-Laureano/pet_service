<?php

namespace App\Http\Controllers;

/**
 * @OA\Server(url="http://localhost/api"),
 * @OA\Info(
 *     version="1.0.0",
 *     title="Documentação da API",
 *     description="Documentação apresentando todos endpoints desta API. Contém informações de autenticação, corpo da requisição e respostas.",
 *     @OA\Contact(
 *         email="hiagolaureano@hotmail.com"
 *     )
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth"
 * )
 */
abstract class Controller
{
    //
}
