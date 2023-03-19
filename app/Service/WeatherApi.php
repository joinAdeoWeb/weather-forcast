<?php
namespace App\Service;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WeatherApi
{
    public static function getCityWeather(string $city): JsonResponse
    {
        try {
            $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
            $response = Http::get($url);
            $weatherData = $response->json();
            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }
}