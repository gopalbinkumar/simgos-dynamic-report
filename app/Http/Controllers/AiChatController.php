<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\AiDataService;
use App\Services\AiIntentRouter;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __invoke(
        Request $request,
        GeminiService $gemini,
        AiIntentRouter $router,
        AiDataService $dataService
    ) {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', 'in:user,assistant,model'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $route = $router->route($data['message']);

        if (!$route['allowed']) {
            return response()->json([
                'ok' => true,
                'answer' => $route['message'],
            ]);
        }

        try {
            $databaseContext = $dataService->context(
                $route['intent'],
                $data['message']
            );

            $answer = $gemini->ask(
                $data['message'],
                $data['history'] ?? [],
                $databaseContext
            );

            return response()->json([
                'ok' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'Data belum dapat dibaca atau AI sedang tidak dapat diakses.',
            ], 502);
        }
    }
}
