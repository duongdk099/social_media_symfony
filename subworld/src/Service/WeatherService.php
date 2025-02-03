<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $baseUrl, string $username, string $password)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    public function getWeather(string $latitude, string $longitude): array
    {
        $date = new \DateTime();
        $formattedDate = $date->format('Y-m-d\TH:i:s\Z'); // Format exigé par Meteomatics

        $url = "{$this->baseUrl}/{$formattedDate}/t_2m:C/{$latitude},{$longitude}/json";

        $response = $this->httpClient->request('GET', $url, [
            'auth_basic' => [$this->username, $this->password],
        ]);

        return $response->toArray();
    }
}
