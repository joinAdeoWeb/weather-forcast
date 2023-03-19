<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Validator;
use App\Service\WeatherFilter;
use App\Service\WeatherApi;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    const CACHE_TIME = 300; // 5min

    public function processForm(Request $request): JsonResponse
    {
        $validatet = Validator::make($request->all(), ['city' => 'required|string|max:25']);

        if ($validatet->fails()) {
            return response()
                ->json(["error" => "Text input is not correct"], 400);
        }

        $city = strtolower($request->input('city'));
        try {
            $weatherData = WeatherApi::getCityWeather($city);

            // Fill ter weather to 3 next days with 2 clothes recomendations for each day
            $recommendation = WeatherFilter::filter($weatherData);
            if (count($recommendation) === 0) {
                return response()->json(["message" => "no recommendatios found"], 200);
            }

            return response()->json($recommendation, 200);
        } catch (\Exception $e) {
            return response()->json(["error" => "request failed"], 500);
        }
    }
}