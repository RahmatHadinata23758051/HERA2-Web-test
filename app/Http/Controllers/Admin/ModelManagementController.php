<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiModelService;
use Illuminate\Http\Request;

class ModelManagementController extends Controller
{
    protected AiModelService $aiService;

    public function __construct(AiModelService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Tampilkan halaman status dan manajemen model ML.
     */
    public function index()
    {
        $info = $this->aiService->getModelsInfo();

        return view('admin.models.index', [
            'online' => $info['online'],
            'models' => $info['models'],
        ]);
    }

    /**
     * Upload dan pemicu Hot-Reload model ML baru ke FastAPI.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'target'     => 'required|in:chromium,nickel',
            'model_file' => 'required|file|max:51200', // maks 50 MB
        ], [
            'target.required'     => 'Target parameter logam berat wajib dipilih.',
            'target.in'           => 'Target logam berat harus Chromium atau Nickel.',
            'model_file.required' => 'File model wajib diunggah.',
            'model_file.max'      => 'Ukuran file model tidak boleh melebihi 50 MB.',
        ]);

        $target = $request->input('target');
        $file   = $request->file('model_file');

        $result = $this->aiService->reloadModel($target, $file);

        if ($result['success']) {
            $msg = $result['data']['message'] ?? 'Model berhasil diperbarui!';
            $detail = $result['data']['detail'] ?? '';

            return redirect()->back()->with('success', "{$msg} ({$detail})");
        }

        return redirect()->back()->with('error', $result['error']);
    }
}
