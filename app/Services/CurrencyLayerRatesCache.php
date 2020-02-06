<?php

namespace App\Services;

use App\Services\Exception\CurrencyLayerRatesEmptyCacheException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;

class CurrencyLayerRatesCache
{
    /**
     * @var string
     */
    const CACHE_PREFIX = 'currency-rates';

    /**
     * @var int
     */
    const CACHE_TTL = 1800;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param  Repository  $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws CurrencyLayerRatesEmptyCacheException
     */
    public function rates(): array
    {
        $cache = $this->repository->get(self::CACHE_PREFIX);

        if(empty($cache)) {
            throw new CurrencyLayerRatesEmptyCacheException();
        }

        return $cache;
    }

    /**
     * @param  array  $rates
     * @param  bool  $force
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function store(array $rates, bool $force = false): void
    {
        $cacheExists = $this->repository->get(self::CACHE_PREFIX);

        Log::debug(
            'Retrieving currency-layer rates cache',
            [
                'forcing' => $force,
                'currentCache' => $cacheExists,
            ]
        );

        if(!$cacheExists || $force) {
            Log::debug(
                'Saving currency-layer rates cache',
                [
                    'rates' => $rates,
                    'ttl' => self::CACHE_TTL,
                ]
            );

            $this
                ->repository
                ->set(
                    self::CACHE_PREFIX,
                    $rates,
                    self::CACHE_TTL
                );
        }
    }
}
