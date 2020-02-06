<?php

namespace Tests\Unit\Services;

use App\Services\CurrencyLayerRatesCache;
use App\Services\Exception\CurrencyLayerRatesEmptyCacheException;
use Illuminate\Contracts\Cache\Repository;
use Tests\TestCase;

class CurrencyLayerRatesCacheTest extends TestCase
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

    public function testStoringRatesCache(): void
    {
        $repository = $this->prophesize(Repository::class);

        $repository
            ->get(CurrencyLayerRatesCache::CACHE_PREFIX)
            ->shouldBeCalledOnce()
            ->willReturn(null);

        $repository
            ->set(
                CurrencyLayerRatesCache::CACHE_PREFIX,
                self::RATES,
                CurrencyLayerRatesCache::CACHE_TTL
            )
            ->shouldBeCalledOnce();


        $cache = new CurrencyLayerRatesCache($repository->reveal());

        $cache->store(self::RATES);
    }

    public function testRetrievingRatesFromCache(): void
    {
        $repository = $this->prophesize(Repository::class);

        $repository
            ->get(CurrencyLayerRatesCache::CACHE_PREFIX)
            ->shouldBeCalledOnce()
            ->willReturn(self::RATES);


        $cache = new CurrencyLayerRatesCache($repository->reveal());

        $rates = $cache->rates();

        $this->assertSame(self::RATES, $rates);
    }

    public function testRetrievingEmptyRatesCache(): void
    {
        $this->expectException(CurrencyLayerRatesEmptyCacheException::class);

        $repository = $this->prophesize(Repository::class);

        $repository
            ->get(CurrencyLayerRatesCache::CACHE_PREFIX)
            ->shouldBeCalledOnce()
            ->willReturn(null);


        $cache = new CurrencyLayerRatesCache($repository->reveal());

        $cache->rates();
    }

    public function testGettingRatesTable(): void
    {
        $repository = $this->prophesize(Repository::class);

        $repository
            ->get(CurrencyLayerRatesCache::CACHE_PREFIX)
            ->shouldBeCalledOnce()
            ->willReturn(self::RATES);

        $repository
            ->set(
                CurrencyLayerRatesCache::CACHE_PREFIX,
                self::RATES,
                CurrencyLayerRatesCache::CACHE_TTL
            )
            ->shouldNotBeCalled();


        $cache = new CurrencyLayerRatesCache($repository->reveal());

        $cache->store(self::RATES);
    }
}
