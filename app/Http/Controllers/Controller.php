<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Notes Management System",
    description: "REST API untuk Notes Management System dengan framework Laravel dan JWT"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local development"
)]
#[OA\Server(
    url: "https://notes-api-production-4a37.up.railway.app/",
    description: "Production"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Schema(
    schema: "SuccessResponse",
    properties: [
        new OA\Property(property: "status", type: "string", example: "Success"),
        new OA\Property(property: "message", type: "string"),
        new OA\Property(property: "data", type: "object"),
    ],
    type: "object"
)]
#[OA\Schema(
    schema: "ErrorResponse",
    properties: [
        new OA\Property(property: "status", type: "string", example: "Error"),
        new OA\Property(property: "message", type: "string"),
        new OA\Property(property: "data", type: "object", nullable: true),
    ],
    type: "object"
)]

abstract class Controller
{
    //
}
