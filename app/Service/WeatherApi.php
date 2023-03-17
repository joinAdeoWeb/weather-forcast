<?php
namespace App\Service;

use Illuminate\Support\Facades\Http;

class WeatherApi
{
    public static function getCityNames()
    {
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
            return response()->json($cityNames);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.',], 500);
        }
    }

    public static function getCityWeather($city)
    {
        try {
            $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
            $response = Http::get($url);
            $weatherData = $response->json();
            if (count($weatherData) === 0) {
                return response()->json(['error' => "City not found in the weather data"], 404);
            }
            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.',], 500);
        }
    }
}