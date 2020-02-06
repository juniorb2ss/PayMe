<?php

namespace Tests\Unit\Services;

use App\Console\Commands\CurrencyLayer\GenerateCurrencyRatesCacheCommand;
use App\Services\CurrencyLayerRatesCache;
use App\Services\CurrencyLayerServiceAPI;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Tests\TestCase;

class GenerateCurrencyRatesCacheCommandTest extends TestCase
{
    /**
     * @var array
     */
    const RATES = [
        'usd' => 1,
        'eur' => 1.1000979087138756,
        'cad' => 0.7521681246192149,
        'aud' => 0.6747911521384131,
    ];

    public function testGeneratingCurrencyRatesCache(): void
    {
        $serviceAPI = $this->prophesize(CurrencyLayerServiceAPI::class);
        $serviceAPI
            ->dump(
                'USD',
                [
                    'USD',
                    'EUR',
                    'CAD',
                    'AUD'
                ]
            )
            ->shouldBeCalledOnce()
            ->willReturn(self::RATES);

        $cacheRepository = $this->prophesize(CurrencyLayerRatesCache::class);
        $cacheRepository
            ->store(
                self::RATES,
                false
            )
            ->shouldBeCalledOnce();

        $configRepository = $this->prophesize(ConfigRepository::class);
        $configRepository
            ->get('currencies.source')
            ->shouldBeCalledOnce()
            ->willReturn('USD');

        $configRepository
            ->get('currencies.available')
            ->shouldBeCalledOnce()
            ->willReturn(
                [
                    'USD',
                    'EUR',
                    'CAD',
                    'AUD'
                ]
            );

        $this->app->instance(CurrencyLayerServiceAPI::class, $serviceAPI->reveal());
        $this->app->instance(CurrencyLayerRatesCache::class, $cacheRepository->reveal());
        $this->app->instance(ConfigRepository::class, $configRepository->reveal());

        $this->artisan('currencylayer:currency-rates-cache');
    }
}
