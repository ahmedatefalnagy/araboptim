<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected $baseUrl;
    protected $model;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434/api');
        $this->model = config('services.ollama.model', 'deepseek-coder:6.7b');
    }

    public function tags()
    {
        try {
            $response = Http::get("{$this->baseUrl}/tags");
            return $response->json()['models'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function chat(array $messages, $model = null)
    {
        $selectedModel = $model ?: $this->model;
        
        $systemMessage = [
            'role' => 'system',
            'content' => 'أنت خبير ومساعد ذكي لنظام "التفاؤل العربية" (Arab Optem) لإدارة الموارد. مهمتك هي مساعدة المستخدم في إدارة الحسابات، الموارد البشرية، والعمليات. كن مهنياً، دقيقاً، وودوداً. إذا سألك المستخدم عن كيفية القيام بشيء، اشرح له الخطوات ووجهه للقسم المناسب في القائمة الجانبية.'
        ];

        $finalMessages = array_merge([$systemMessage], $messages);
        
        try {
            $response = Http::timeout(300)->post("{$this->baseUrl}/chat", [
                'model' => $selectedModel,
                'messages' => $finalMessages,
                'stream' => false,
            ]);

            if ($response->successful()) {
                return $response->json()['message']['content'];
            }

            Log::error("Ollama Error: " . $response->body());
            return "عذراً، حدث خطأ أثناء الاتصال بالموديل ($selectedModel). تأكد من تحميله بشكل صحيح.";
        } catch (\Exception $e) {
            Log::error("Ollama Exception: " . $e->getMessage());
            return "عذراً، يبدو أن خدمة الذكاء الاصطناعي استغرقت وقتاً أطول من اللازم أو أنها غير مفعلة حالياً.";
        }
    }
}
