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

        // Fallback lokal membaca file disk langsung jika FastAPI Service offline/restarting
        $crPath = base_path('fastapi-model/models/best_model_chromium_v2.pkl');
        $niPath = base_path('fastapi-model/models/best_model_nickel_v2.pkl');

        $getFallbackInfo = function($path, $defaultName, $defaultFilename) {
            if (file_exists($path)) {
                return [
                    'exists'        => true,
                    'name'          => $defaultName,
                    'filename'      => $defaultFilename,
                    'size_kb'       => round(filesize($path) / 1024, 2),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($path))
                ];
            }
            return ['exists' => false, 'name' => 'File Not Found', 'filename' => 'N/A', 'size_kb' => 0, 'last_modified' => 'N/A'];
        };

        return [
            'online' => false,
            'models' => [
                'chromium' => $getFallbackInfo($crPath, 'XGBoost Regressor (Standby)', 'best_model_chromium_v2.pkl'),
                'nickel'   => $getFallbackInfo($niPath, 'Random Forest (Standby)', 'best_model_nickel_v2.pkl')
            ]
        ];
    }

    /**
     * Tembak endpoint Hot-Reload FastAPI dengan file .pkl/.joblib multipart.
     */
    public function reloadModel(string $target, UploadedFile $file, ?string $modelName = null): array
    {
        try {
            $payload = ['target' => $target];
            if (!empty($modelName)) {
                $payload['model_name'] = trim($modelName);
            }

            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Internal-Key' => $this->secretKey,
                ])
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post("{$this->baseUrl}/api/v2/reload-model", $payload);

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
