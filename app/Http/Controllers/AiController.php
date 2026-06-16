<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return Inertia::render('Ai/Chat', [
            'availableModels' => $this->aiService->tags(),
            'initialMessages' => [
                [
                    'role' => 'assistant',
                    'content' => 'مرحباً بك! أنا مساعدك الذكي. يمكنك الآن الاختيار من بين الموديلات المتوفرة لديك في Ollama. إذا وجدت أن الموديل بطيء، جرب اختيار موديل أصغر (مثل 1B).'
                ]
            ]
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|string',
            'messages.*.content' => 'required|string',
            'model' => 'nullable|string',
        ]);

        $response = $this->aiService->chat($request->messages, $request->model);

        return response()->json([
            'message' => [
                'role' => 'assistant',
                'content' => $response
            ]
        ]);
    }
}
