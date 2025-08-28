<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InformationApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppInfoController extends Controller
{
    /**
     * GET /api/app-info
     * Retorna la información de la aplicación (términos, políticas, etc.)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $appInfo = InformationApp::first();

            if (!$appInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Información de la aplicación no encontrada'
                ], 404);
            }

            // Construir URLs completas para los archivos
            $baseUrl = config('app.url');
            
            $data = [
                'terms_and_conditions' => [
                    'url' => $appInfo->url_terminos_y_condiciones ? $baseUrl . '/storage/' . $appInfo->url_terminos_y_condiciones : null,
                    'filename' => $appInfo->url_terminos_y_condiciones
                ],
                'privacy_policy' => [
                    'url' => $appInfo->url_politica_de_privacidad ? $baseUrl . '/storage/' . $appInfo->url_politica_de_privacidad : null,
                    'filename' => $appInfo->url_politica_de_privacidad
                ],
                'app_version' => config('app.version', '1.0.0'),
                'updated_at' => $appInfo->updated_at
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de la aplicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
