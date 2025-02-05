<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/')]
class WeatherController extends AbstractController
{
    #[Route('/api/weather', methods: ['GET'])]
    public function getWeather(WeatherService $weatherService): JsonResponse
    {
        $latitude = '48.8566'; // Paris
        $longitude = '2.3522';

        $weatherData = $weatherService->getWeather($latitude, $longitude);

        return $this->json($weatherData['data'] ?? []);
    }

    #[Route('/weather', name: 'app_weather')]
    public function showWeather(WeatherService $weatherService): Response
    {
        $latitude = '48.8566'; // Paris
        $longitude = '2.3522';
        $weatherData = $weatherService->getWeather($latitude, $longitude);

        return $this->render('weather/index.html.twig', [
            'weather' => $weatherData['data'] ?? [],
        ]);
    }

}
