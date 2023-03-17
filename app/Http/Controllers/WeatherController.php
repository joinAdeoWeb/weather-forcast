<?php
namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Validator;
use App\Service\WeatherFilter;
use App\Service\WeatherApi;
use \Illuminate\Contracts\View\View;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(): View
    {
        $recommendation = [];
        $cityNames = WeatherApi::getCityNames();
        $recommendation = WeatherFilter::filter($recommendation);

        return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
    }

    public function processForm(Request $request): View|JsonResponse
    {
        $validatet = Validator::make($request->all(), ['city' => 'required|string|max:25']);

        if ($validatet->fails()) {
            return response()
                ->json(["error" => "Text input is not correct"], 400);
        } else {
            $city = strtolower($request->input('city'));
            $weatherData = [];
            $weatherData = WeatherApi::getCityWeather($city);

            // Fill ter weather to 3 next days with 2 clothes recomendations for each day
            $recommendation = WeatherFilter::filter($weatherData);
            $cityNames = $this->getCityNames();
            return view('welcome', ['cityNames' => $cityNames, 'recommendation' => $recommendation]);
        }
    }
}