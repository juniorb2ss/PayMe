<?php

namespace App\Services;

use App\Services\Exception\UnexpectedCurrencyLayerResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class CurrencyLayerServiceAPI
{
    /**
     * @var string
     */
    const API_URI = 'http://apilayer.net/';

    /**
     * @var string
     */
    const API_ROUTE = '/api/live';

    /**
     * @var int
     */
    const DUMP_FORMAT = 1;

    /**
     * @var string
     */
    protected $accessKey;

    /**
     * @var ClientInterface|null
     */
    protected $httpClient;

    /**
     * @param  string  $accessKey
     * @param  ClientInterface|null  $httpClient
     */
    public function __construct(string $accessKey, ClientInterface $httpClient = null)
    {
        $this->accessKey = $accessKey;
        $this->httpClient = $httpClient;
    }

    /**
     * @param  string  $currencySource
     * @param  array  $currencies
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws UnexpectedCurrencyLayerResponseException
     */
    public function dump(string $currencySource, array $currencies): array
    {
        $reverse_rates = [];

        $response = $this
                    ->getHttpClient()
                    ->request(
                        'GET',
                        self::API_ROUTE,
                        [
                            'query' => [
                                'access_key' => $this->accessKey,
                                'source' => $currencySource,
                                'currencies' => implode(',', $currencies),
                                'format' => self::DUMP_FORMAT,
                            ]
                        ]
                    );

        $dump = json_decode($response->getBody()->getContents(), true);

        if(!empty($dump['success']) && $dump['success'] === true){
            $reverse_rates = $this->reverseRates($dump);
        } else {
            throw new UnexpectedCurrencyLayerResponseException(
                $dump['error']['info'],
                $dump['error']['code']
            );
        }

        return $reverse_rates;
    }

    /**
     * @param  array  $rates
     * @return array
     */
    protected function reverseRates(array $rates): array
    {
        $reverse_rates = [];

        foreach ($rates['quotes'] as $pair => $rate) {
            $pair = strtolower(substr($pair, 3));
            $reverse_rates[$pair] = (1 / $rate);
        }

        return $reverse_rates;
    }

    /**
     * @return ClientInterface|null
     */
    protected function getHttpClient(): ?ClientInterface
    {
        if(empty($this->httpClient)) {
            $this->httpClient = new Client([
                'base_uri' => self::API_URI
            ]);
        }

        return $this->httpClient;
    }
}
