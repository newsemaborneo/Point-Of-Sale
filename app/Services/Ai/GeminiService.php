<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $maxRetries = 2;
    protected int $retryDelay = 500; // milliseconds

    public function __construct()
    {
        $this->apiKey = config('services.ai.gemini_api_key');
        $this->model = config('services.ai.gemini_model', 'gemini-2.5-flash');
    }

    /**
     * Check if Gemini API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate content from prompt using Gemini API with retry logic.
     */
    public function generate(string $prompt, string $systemInstruction = null): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('Gemini API key is not configured. Falling back to local rules-based engine.');
            return null;
        }

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = $this->callGeminiApi($prompt, $systemInstruction);

                if ($response !== null) {
                    return $response;
                }
                
                // If it returns null without exception, it's a blocked response or empty (non-retriable)
                break;
                
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $status = $e->response->status();
                Log::warning("Gemini API attempt " . ($attempt + 1) . " failed HTTP {$status}: " . $e->response->body());

                // Only retry for rate limit (429) or server errors (5xx)
                if ($status === 429 || $status >= 500) {
                    if ($attempt < $this->maxRetries) {
                        $delayMs = $this->retryDelay * pow(2, $attempt);
                        usleep($delayMs * 1000);
                        continue;
                    }
                } else {
                    // Do not retry for 4xx errors (e.g., 400 Bad Request, 401 Unauthorized)
                    break;
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                Log::warning("Gemini API attempt " . ($attempt + 1) . " connection error: {$errorMessage}");

                if ($attempt < $this->maxRetries) {
                    $delayMs = $this->retryDelay * pow(2, $attempt);
                    usleep($delayMs * 1000);
                    continue;
                }
            }
        }

        Log::error('Gemini API failed after ' . ($this->maxRetries + 1) . ' attempts.');
        return null;
    }

    /**
     * Call Gemini API and return response text.
     */
    protected function callGeminiApi(string $prompt, string $systemInstruction = null): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2, // Lowered temperature for consistent, strict data
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey
        ])->timeout(30)->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();

            // Handle blocked responses
            if (isset($data['promptFeedback']['blockReason'])) {
                Log::warning('Gemini API blocked response: ' . $data['promptFeedback']['blockReason']);
                return null;
            }

            // Extract text from response
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($text)) {
                Log::warning('Gemini API returned empty response', ['response' => $data]);
                return null;
            }

            return $text;
        }

        // Throw an exception for non-2xx responses so it can be handled by retry logic
        $response->throw();
        
        return null;
    }
}
