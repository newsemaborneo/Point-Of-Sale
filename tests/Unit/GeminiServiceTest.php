<?php

namespace Tests\Unit;

use App\Services\Ai\GeminiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class GeminiServiceTest extends BaseTestCase
{
    public function test_it_handles_missing_api_key_gracefully(): void
    {
        // Force API key to be null
        putenv('GEMINI_API_KEY=');
        
        $service = new GeminiService();
        $this->assertFalse($service->isConfigured());
        
        $result = $service->generate('Test prompt');
        $this->assertNull($result);
    }

    public function test_it_generates_content_with_api_key(): void
    {
        putenv('GEMINI_API_KEY=dummy_key');
        
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Ini adalah tanggapan mock dari Gemini.']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new GeminiService();
        $this->assertTrue($service->isConfigured());

        $result = $service->generate('Halo Gemini');
        $this->assertEquals('Ini adalah tanggapan mock dari Gemini.', $result);
    }
}
