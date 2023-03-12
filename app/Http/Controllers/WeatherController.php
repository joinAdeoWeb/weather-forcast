<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Validator;
use App\Service\WeatherFilter;
use \Illuminate\Contracts\View\View;

use Illuminate\Http\Request;

class WeatherController extends Controller
{

    public function getCityNames()
    {
        try {
            $url = "https://api.meteo.lt/v1/places";
            $response = Http::get($url);
            $places = $response->json();
            $cityNames = array();
            foreach ($places as $place) {
                if (!in_array($place['name'], $cityNames)) {
                    $cityNames[] = $place['name'];
                }
            }
            return $cityNames;
        } catch (\Exception $e) {
            // Handle exception
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function index(): View
    {
        $recommendation = [];
        $cityNames = $this->getCityNames();
        return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
    }

    public function processForm(Request $request)
    {
        $validatet = Validator::make($request->all(), ['city' => 'required|string|max:25']);

        if ($validatet->fails()) {
            return response()
                ->json(["error" => "Text input is not correct"], 422);
        } else {
            $city = strtolower($request->input('city'));
            $weatherData = [];
            try {
                $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
                $response = Http::get($url);
                $weatherData = $response->json();
                if (count($weatherData) === 0) {
                    return response()->json(['error' => "City not found in the weather data"], 404);
                }

                $recommendation = WeatherFilter::filter($weatherData);
                $cityNames = $this->getCityNames();
                return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'An unexpected error occurred. Please try again later.',], 500);
            }
        }
    }
}