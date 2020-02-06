<?php

namespace Tests\Unit\Services;

use App\Services\CurrencyLayerServiceAPI;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class CurrencyLayerServiceAPITest extends TestCase
{
    public function testRetrievingCurrenciesRatesFromService(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json; Charset=UTF-8'
                ],
                json_encode([
                    'success' => true,
                    'quotes' => [
                        'USDUSD' => 1,
                        'USDEUR' => 0.90901,
                        'USDCAD' => 1.32949,
                        'USDAUD' => 1.48194,
                    ]
                ])
            ),
        ]);

        $httpClient = new Client(
            [
                'handler' => HandlerStack::create($mock)
            ]
        );

        $service = new CurrencyLayerServiceAPI('', $httpClient);

        $dump = $service->dump(
            'usd',
            [
                'USD',
                'EUR',
                'CAD',
                'AUD'
            ]
        );

        $this->assertSame(
            [
                'usd' => 1,
                'eur' => 1.1000979087138756,
                'cad' => 0.7521681246192149,
                'aud' => 0.6747911521384131,
            ],
            $dump
        );
    }
}
