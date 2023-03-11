<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Validator;
use DateTime;
use DateTimeZone;
use App\Models\Product;

use Illuminate\Http\Request;

class WeatherController extends Controller
{

    public function getCityNames()
    {
        try
        {
            $url = "https://api.meteo.lt/v1/places";
            $response = Http::get($url);
            $places = $response->json();
            $cityNames = array();
            foreach ($places as $place)
            {
                if (!in_array($place['name'], $cityNames))
                {
                    $cityNames[] = $place['name'];
                }
            }
            return $cityNames;
        }
        catch(Exception $e)
        {
            // Handle exception
            return response()->json(["error" => $e->getMessage() ], 500);
        }
    }

    public function index()
    {
        $cityNames = $this->getCityNames();
        return view('welcome', ['cityNames' => $cityNames]);
    }

    public function processForm(Request $request)
    {

        $validatet = Validator::make($request->all() , ['city' => 'required|string|max:25']);

        if ($validatet->fails())
        {
            return response()
                ->json(["error" => "Text input is not correct"], 422);
        }
        else
        {
            $city = $request->input('city');
            $city = strtolower($city);
            $weatherData = [];

            try
            {
                $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
                $response = Http::get($url);
                $weatherData = $response->json();
                if (count($weatherData) > 0)
                {
                    $results = $weatherData;
                }
                else
                {
                    return response()->json(['error' => "City not found in the weather data"], 404);
                }
            }
            catch(Exception $e)
            {
                return response()->json(["error" => $e->getMessage() ], 500);
            }

            $this->filterWeather($weatherData);
            $cityNames = $this->getCityNames();
            return view('welcome', ['cityNames' => $cityNames]);
        }
    }

    public function filterWeather($weatherData)
    {
        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
        $lastDate = null;
        $combinedData = [];

        if (isset($weatherData['forecastTimestamps']))
        {
            // Loop through each day in the forecast
            foreach ($weatherData['forecastTimestamps'] as $forecast)
            {
                $forecastDate = new DateTime($forecast['forecastTimeUtc']);
                $diff = $forecastDate->diff($currentDate);

                // Check if the forecast is within the next 3 days
                if ($diff->days <= 3)
                {
                    $dateString = $forecastDate->format('Y-m-d');
                    if ($dateString !== $lastDate)
                    {
                        $dayResults = ['name' => $weatherData['place']['name'], 'forecastTimeUtc' => $forecast['forecastTimeUtc']];

                        // Loop through each forecast for this day and add condition codes to array
                        foreach ($weatherData['forecastTimestamps'] as $forecastOfDay)
                        {
                            $daysConditionCount['conditionCodes'][] = $forecastOfDay['conditionCode'];
                        }

                        // Get the most common condition code for that day
                        $conditionCounts = array_count_values($daysConditionCount['conditionCodes']);
                        $mostCommonConditionCode = key($conditionCounts);

                        // Add the most common condition code to the day's results
                        $dayResults['conditionCode'] = $mostCommonConditionCode;
                        $combinedData[] = $dayResults;
                        $lastDate = $dateString;
                    }

                }
            }

            // Return only the next 3 days (excluding the current day)
            $fillteredData = array_slice($combinedData, 1, 3);
            $this->recomendProduct($fillteredData);
        }
    }

    public function recomendProduct($fillteredData)
    {

        try
        {
            if (!is_array($fillteredData))
            {
                $fillteredData = json_decode($fillteredData, true);
            }
            $allProducts = Product::all()->toArray();
            $recomendation = [];
            foreach ($fillteredData as $condition)
            {
                $matchingProducts = [];
                foreach ($allProducts as $product) dd($condition);
                {
                    // if weather in product maches with weather in perticular day add to matchingProduct
                    if ($product['ocasion'] === $condition['conditionCode'])
                    {
                        $matchingProducts[] = ['name' => $product['name'], 'sku' => $product['sku'], 'price' => $product['price']];
                    }

                    // break out of the inner loop when  maching products are found
                    if (count($matchingProducts) >= 2)
                    {
                        break;
                    }
                }

                $condition['recommendations'] = $matchingProducts;
                $recomendation[] = $condition;
            }

            return response()->json(['recomendation' => $recomendation]);
        }
        catch(\Exception $e)
        {
            return response()->json(['error' => 'Could not retrieve recommendations'], 500);
        }
    }

}

