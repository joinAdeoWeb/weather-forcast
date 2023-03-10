<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Validator;
use DateTime;
use DateTimeZone;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index()
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
            return view('welcome')->with('cityNames', $cityNames);
        }
        catch(Exception $e)
        {
            return response()->json(["error" => $e->getMessage() ], 500);
        }
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
            $results = [];

            try
            {
                $url = "https://api.meteo.lt/v1/places/$city/forecasts/long-term";
                $response = Http::get($url);
                $weather = $response->json();
                $currentDate = new DateTime('now', new DateTimeZone('UTC'));
                $lastDate = null;

                if (isset($weather['forecastTimestamps']))
                {
                    // Loop through each day in the forecast
                    foreach ($weather['forecastTimestamps'] as $forecast)
                    {
                        $forecastDate = new DateTime($forecast['forecastTimeUtc']);
                        $diff = $forecastDate->diff($currentDate);

                        // Check if the forecast is within the next 3 days
                        if ($diff->days <= 3)
                        {
                            $dateString = $forecastDate->format('Y-m-d');
                            if ($dateString !== $lastDate)
                            {
                                $dayResults = ['name' => $weather['place']['name'], 'forecastTimeUtc' => $forecast['forecastTimeUtc']];

                                // Loop through each forecast for this day and add condition codes to array
                                foreach ($weather['forecastTimestamps'] as $forecastOfDay)
                                {
                                    $daysConditionCount['conditionCodes'][] = $forecastOfDay['conditionCode'];
                                }

                                // Get the most common condition code for that day
                                $conditionCounts = array_count_values($daysConditionCount['conditionCodes']);
                                $mostCommonConditionCode = key($conditionCounts);

                                // Add the most common condition code to the day's results
                                $dayResults['conditionCode'] = $mostCommonConditionCode;
                                $results[] = $dayResults;

                                $lastDate = $dateString;
                            }
                        }
                    }

                    // Return only the next 3 days (excluding the current day)
                    $responseResults = array_slice($results, 1, 3);
                    dd($responseResults);
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
        }

        return view('welcome');
    }
}