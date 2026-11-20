<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');

            return response()->json([
                'status' => 'ok',
                'database' => 'ok',
            ]);
        } catch (\Throwable) {
            return response()->json([
                'status' => 'degraded',
                'database' => 'error',
            ], 503);
        }
    }
}
