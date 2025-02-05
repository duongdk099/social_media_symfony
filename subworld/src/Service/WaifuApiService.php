<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WaifuApiService
{
    private HttpClientInterface $client;
    private string $apiUrl = 'https://api.waifu.im/search';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchWaifu(array $params = []): array
    {
        $queryParams = http_build_query($this->prepareParams($params));

        $requestUrl = "{$this->apiUrl}?{$queryParams}";

        try {
            $response = $this->client->request('GET', $requestUrl);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Request failed with status code: " . $response->getStatusCode());
            }

            return $response->toArray();
        } catch (\Exception $e) {
            return ['error' => 'An error occurred', 'message' => $e->getMessage()];
        }
    }

    private function prepareParams(array $params): array
    {
        $query = [];

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $query[$key][] = $val; // Keep multiple values
                }
            } else {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}
