<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Validator;
use App\Service\WeatherFilter;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;

class WeatherController extends Controller
{

    public function getCityNames()
    {
        $cacheCity = 'city_names';

        // 5min cahce
        $cacheTime = 5 * 60;

        // Check if the city names is in the cache
        if (Cache::has($cacheCity)) {
            return Cache::get($cacheCity);
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
            return $cityNames;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return array();
        }
    }

    public function index(): View
    {
        $recommendation = [];
        $cityNames = $this->getCityNames();
        return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
    }

    public function processForm(Request $request): View|JsonResponse
    {
        $validatet = Validator::make($request->all(), ['city' => 'required|string|max:25']);

        // 5min cache
        $cacheTime = 5 * 60;

        if ($validatet->fails()) {
            return response()
                ->json(["error" => "Text input is not correct"], 400);
        } else {
            $city = strtolower($request->input('city'));
            $weatherData = [];
            $cacheWeather = 'weather_data_' . $city;

            // Check if the city weather is in the cache
            if (Cache::has($cacheWeather)) {
                $weatherData = Cache::get($cacheWeather);
            } else {
                try {
                    $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
                    $response = Http::get($url);
                    $weatherData = $response->json();
                    if (count($weatherData) === 0) {
                        return response()->json(['error' => "City not found in the weather data"], 404);
                    }
                    Cache::put($cacheWeather, $weatherData, $cacheTime);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    return response()->json(['error' => 'An unexpected error occurred. Please try again later.',], 500);
                }
            }

            // Fill ter weather to 3 next days with 2 clothes recomendations for each day
            $recommendation = WeatherFilter::filter($weatherData);
            $cityNames = $this->getCityNames();
            return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
        }
    }
}