<?php
namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Validator;
use App\Service\WeatherFilter;
use App\Service\WeatherApi;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function getCityNames(): JsonResponse
    {
        $cityNames = WeatherApi::getCityNames();
        return response()->json($cityNames, 200);
    }

    public function processForm(Request $request) //: JsonResponse
    {
        $validatet = Validator::make($request->all(), ['city' => 'required|string|max:25']);

        // 5min cache
        $cacheTime = 5 * 60;

        if ($validatet->fails()) {
            return response()
                ->json(["error" => "Text input is not correct"], 400);
        } else {
            $city = strtolower($request->input('city'));
            $cacheWeather = 'weather_data_' . $city;
            $weatherData = [];

            if (Cache::has($cacheWeather)) {
                $weatherData = Cache::get($cacheWeather);
            } else {
                $weatherData = WeatherApi::getCityWeather($city);
                Cache::put($cacheWeather, $weatherData, $cacheTime);
            }
        }

        // Fill ter weather to 3 next days with 2 clothes recomendations for each day
        $recommendation = WeatherFilter::filter($weatherData);

        return response()->json($recommendation, 200);
    }
}