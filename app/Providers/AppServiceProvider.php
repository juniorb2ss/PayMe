<?php

namespace App\Providers;

use App\Services\CurrencyLayerRatesCache;
use App\Services\CurrencyLayerServiceAPI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this
            ->app
            ->bind(CurrencyLayerRatesCache::class, function () {
                $cacheRepository = Cache::store('currency-rates');

                return new CurrencyLayerRatesCache($cacheRepository);
            });

        $this
            ->app
            ->singleton(CurrencyLayerServiceAPI::class, function () {
                $accessKey = config('currencylayer.apiKey');

                return new CurrencyLayerServiceAPI($accessKey);
            });
    }
}
