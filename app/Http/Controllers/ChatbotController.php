<?php

namespace App\Http\Controllers;

use App\Services\Chatbot\ChatbotIntentRouter;
use App\Services\Chatbot\ChatbotPeriodParser;
use App\Services\Chatbot\ChatbotResponseService;
use App\Services\Chatbot\Queries\ClaimsQuery;
use App\Services\Chatbot\Queries\DashboardQuery;
use App\Services\Chatbot\Queries\DiagnosisQuery;
use App\Services\Chatbot\Queries\FinanceQuery;
use App\Services\Chatbot\Queries\ServicesQuery;
use App\Services\Chatbot\Queries\StatisticsQuery;
use App\Services\Chatbot\Queries\VisitsQuery;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __invoke(
        Request $request,
        ChatbotIntentRouter $router,
        ChatbotPeriodParser $periodParser,
        ChatbotResponseService $responseService,
        DashboardQuery $dashboardQuery,
        DiagnosisQuery $diagnosisQuery,
        ClaimsQuery $claimsQuery,
        VisitsQuery $visitsQuery,
        ServicesQuery $servicesQuery,
        FinanceQuery $financeQuery,
        StatisticsQuery $statisticsQuery
    ) {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $route = $router->route($data['message']);

        if (!$route['allowed']) {
            return response()->json([
                'ok' => true,
                'answer' => $route['message'],
            ]);
        }

        try {
            $period = $periodParser->parse($data['message']);
            $result = match ($route['intent']) {
                'overview' => $dashboardQuery->summary($period),
                'diagnosis' => $diagnosisQuery->summary($period),
                'claims' => $claimsQuery->summary($period),
                'visits' => $visitsQuery->summary($period),
                'services' => $servicesQuery->summary($period),
                'finance' => $financeQuery->summary($period),
                'statistics' => $statisticsQuery->summary($period),
                default => [],
            };

            $answer = $responseService->build(
                $route['intent'],
                $result,
                $period,
                $data['message']
            );

            return response()->json([
                'ok' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'Data belum dapat dibaca dari database.',
            ], 503);
        }
    }
}
