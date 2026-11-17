<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionApiController extends Controller
{
    /**
     * Check for the latest active mobile app version.
     *
     * Query Parameters:
     * - version: string (current app version, e.g. "1.0.0")
     * - build_number: int (current build number, e.g. 1)
     */
    public function latest(Request $request): JsonResponse
    {
        $latest = AppVersion::getLatestActive();

        if (!$latest) {
            return response()->json([
                'status' => true,
                'update_available' => false,
                'message' => 'No active app version found on server.',
            ]);
        }

        $clientBuild = $request->query('build_number');
        $clientVersion = $request->query('version');

        $updateAvailable = false;
        if ($clientBuild !== null && is_numeric($clientBuild)) {
            $updateAvailable = (int) $latest->build_number > (int) $clientBuild;
        } elseif ($clientVersion !== null) {
            $updateAvailable = version_compare($latest->version, $clientVersion, '>');
        }

        return response()->json([
            'status' => true,
            'update_available' => $updateAvailable,
            'current_client_version' => $clientVersion,
            'current_client_build' => $clientBuild !== null ? (int) $clientBuild : null,
            'latest_version' => $latest->version,
            'build_number' => (int) $latest->build_number,
            'download_url' => $latest->download_url,
            'changelog' => $latest->changelog ?? '',
            'file_size' => $latest->file_size,
            'is_force_update' => (bool) $latest->is_force_update,
            'released_at' => $latest->created_at ? $latest->created_at->toISOString() : null,
        ]);
    }
}
