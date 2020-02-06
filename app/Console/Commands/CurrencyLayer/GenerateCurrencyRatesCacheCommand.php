<?php

namespace App\Console\Commands\CurrencyLayer;

use App\Services\CurrencyLayerRatesCache;
use App\Services\CurrencyLayerServiceAPI;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;

class GenerateCurrencyRatesCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencylayer:currency-rates-cache {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate currency rates cache layer.';

    /**
     * @var CurrencyLayerServiceAPI
     */
    protected $serviceAPI;

    /**
     * @var CurrencyLayerRatesCache
     */
    protected $cacheRepository;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @param  CurrencyLayerServiceAPI  $serviceAPI
     * @param  CurrencyLayerRatesCache  $cacheRepository
     * @param  ConfigRepository  $configRepository
     */
    public function __construct(
        CurrencyLayerServiceAPI $serviceAPI,
        CurrencyLayerRatesCache $cacheRepository,
        ConfigRepository $configRepository
    ) {
        $this->serviceAPI = $serviceAPI;
        $this->cacheRepository = $cacheRepository;
        $this->configRepository = $configRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $force = (bool) $this->option('force');

        $sourceCurrency = $this->getSourceCurrency();
        $currencies = $this->getAvailableCurrencies();

        Log::debug(
                'Fetching currencies rates',
                [
                    'source' => $sourceCurrency,
                    'currencies' => $currencies
                ]
            );

        $dump = $this->serviceAPI->dump($sourceCurrency, $currencies);

        $this
            ->cacheRepository
            ->store($dump, $force);

        $this->info('CurrencyLayer rates cache populated.');
    }

    /**
     * @return string
     */
    private function getSourceCurrency(): string
    {
        return $this->configRepository->get('currencies.source');
    }

    /**
     * @return array
     */
    private function getAvailableCurrencies(): array
    {
        return $this->configRepository->get('currencies.available');
    }
}
