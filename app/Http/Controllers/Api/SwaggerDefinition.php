<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '2.0.0',
    description: 'API untuk integrasi aplikasi mobile HERA (Hydrology Environmental Real-time Analytics) dengan sistem monitoring Chromium berbasis IoT. Semua endpoint yang dilindungi memerlukan Bearer Token dari Sanctum.',
    title: 'HERA 2.0 Mobile API',
    contact: new OA\Contact(
        name: 'Tim HERA 2.0 — Makerindo Prima Solusi',
        email: 'admin@hera.ac.id'
    )
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'HERA 2.0 API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Masukkan Bearer Token dari endpoint /api/mobile/login atau /api/mobile/register',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
#[OA\Tag(name: 'Auth Mobile', description: 'Autentikasi untuk aplikasi mobile HERA')]
#[OA\Tag(name: 'Sensor', description: 'Data pembacaan sensor IoT real-time dari InfluxDB')]
#[OA\Tag(name: 'Field Test', description: 'Laporan pengujian lapangan yang dikirim dari mobile')]
class SwaggerDefinition
{
    // Kelas ini hanya untuk menyimpan anotasi global OpenAPI.
}
