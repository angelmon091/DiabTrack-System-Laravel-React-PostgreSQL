<?php

namespace Tests\Unit;

use App\Services\DailyTipSuggestionService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class DailyTipSuggestionServiceTest extends TestCase
{
    private DailyTipSuggestionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DailyTipSuggestionService;
    }

    #[DataProvider('providerCases')]
    public function test_gemini_provider_returns_structured_api_result(string $provider, string $model): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Camina 10 minutos después de comer.']]],
                ]],
                'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 8],
            ], 200),
        ]);

        $result = $this->service->generateGemini([], 'test-key', $model);

        $this->assertSame('Camina 10 minutos después de comer.', $result['tip']);
        $this->assertSame($provider, $result['provider']);
    }

    public function test_gemini_provider_includes_available_context_in_request(): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Consejo breve.']]]]],
        ], 200)]);

        $this->service->generateGemini(['nombre' => 'Ana', 'edad' => 42], 'test-key');

        Http::assertSent(fn ($request) => str_contains($request->body(), 'Ana')
            && str_contains($request->body(), '42'));
    }

    public function test_gemini_provider_accepts_incomplete_clinical_context(): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Registra tus datos de hoy.']]]]],
        ], 200)]);

        $result = $this->service->generateGemini([], 'test-key');

        $this->assertSame('Registra tus datos de hoy.', $result['tip']);
    }

    public function test_gemini_receives_prediabetes_condition_in_prompt(): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Consejo preventivo.']]]]],
        ], 200)]);

        $this->service->generateGemini(['tipo_diabetes' => 'Prediabetes'], 'test-key');

        Http::assertSent(fn ($request) => str_contains($request->body(), 'Prediabetes')
            && str_contains($request->body(), 'Condici'));
    }

    public function test_anthropic_receives_non_diabetic_condition_in_prompt(): void
    {
        Http::fake(['api.anthropic.com/v1/messages' => Http::response([
            'content' => [['text' => 'Consejo de bienestar preventivo.']],
        ], 200)]);

        $this->service->generateAnthropic(
            ['tipo_diabetes' => 'Sin diagnóstico de diabetes'],
            'test-key',
            'claude-haiku-4-5'
        );

        Http::assertSent(fn ($request) => str_contains($request->body(), 'Sin diagn')
            && str_contains($request->body(), 'Condici'));
    }

    public function test_anthropic_provider_returns_tip_from_api_response(): void
    {
        Http::fake([
            'api.anthropic.com/v1/messages' => Http::response([
                'content' => [
                    ['text' => 'Camina 10 minutos después de comer para apoyar tu glucosa.'],
                ],
            ], 200),
        ]);

        $result = $this->service->generateAnthropic([], 'test-key', 'claude-haiku-4-5');

        $this->assertSame('Camina 10 minutos después de comer para apoyar tu glucosa.', $result['tip']);
        $this->assertSame('anthropic', $result['provider']);
    }

    public function test_anthropic_provider_rejects_failed_api_response(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->expectException(RuntimeException::class);

        $this->service->generateAnthropic([], 'test-key', 'claude-haiku-4-5');
    }

    public function test_anthropic_provider_throws_when_api_key_is_missing(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->generateAnthropic([], null, 'claude-haiku-4-5');
    }

    public static function providerCases(): array
    {
        return [
            'default Gemini model' => ['gemini', 'gemini-2.5-flash'],
            'explicit Gemini model' => ['gemini', 'gemini-test-model'],
        ];
    }
}
