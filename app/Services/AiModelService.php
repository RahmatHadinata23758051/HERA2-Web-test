<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class AiModelService
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl   = config('services.ai.url', env('AI_SERVICE_URL', 'http://localhost:8001'));
        $this->secretKey = config('services.ai.secret', env('INTERNAL_AI_SECRET', 'hera-internal-secret-key-2026'));
    }

    /**
     * Ambil metadata status model yang sedang aktif di FastAPI.
     */
    public function getModelsInfo(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/api/v2/models/info");

            if ($response->successful()) {
                return [
                    'online' => true,
                    'models' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Gagal terhubung ke AI Service /models/info: " . $e->getMessage());
        }

        return [
            'online' => false,
            'models' => [
                'chromium' => ['exists' => false, 'name' => 'Offline / Unknown', 'size_kb' => 0, 'last_modified' => 'N/A'],
                'nickel'   => ['exists' => false, 'name' => 'Offline / Unknown', 'size_kb' => 0, 'last_modified' => 'N/A']
            ]
        ];
    }

    /**
     * Tembak endpoint Hot-Reload FastAPI dengan file .pkl/.joblib multipart.
     */
    public function reloadModel(string $target, UploadedFile $file): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Internal-Key' => $this->secretKey,
                ])
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post("{$this->baseUrl}/api/v2/reload-model", [
                    'target' => $target,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            $json = $response->json();
            $errorMsg = $json['detail'] ?? 'Terjadi kesalahan saat memproses file model di AI Service.';

            return [
                'success' => false,
                'error'   => $errorMsg,
            ];
        } catch (\Exception $e) {
            Log::error("Exception reloadModel: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => 'Gagal terhubung ke AI Service: ' . $e->getMessage(),
            ];
        }
    }
}
