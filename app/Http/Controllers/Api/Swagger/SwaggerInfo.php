<?php

namespace App\Http\Controllers\Api\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Inventory System API",
 *     description="Documentación de la API del sistema de inventarios",
 *     @OA\Contact(email="dev@example.com")
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Servidor local"
 * )
 */
class SwaggerInfo
{
    // Clase vacía usada sólo para contener anotaciones de documentación
}
