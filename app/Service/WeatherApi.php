<?php
namespace App\Service;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WeatherApi
{
    public static function getCityNames(): JsonResponse
    {
        $cacheCity = 'city_names';
        // 5min cahce
        $cacheTime = 5 * 60;

        // Check if the city names is in the cache
        if (Cache::has($cacheCity)) {
            $cityNames = Cache::get($cacheCity);
            return response()->json($cityNames, 200);
        }

        try {
            $url = "https://api.meteo.lt/v1/places";
            $response = Http::get($url);
            $places = $response->json();
            $cityNames = array();

            // Takes only unque city names
            foreach ($places as $place) {
                if (!in_array($place['name'], $cityNames)) {
                    $cityNames[] = $place['name'];
                }
            }
            Cache::put($cacheCity, $cityNames, $cacheTime);
            return response()->json($cityNames);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

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