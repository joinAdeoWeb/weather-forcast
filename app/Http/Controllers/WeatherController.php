<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Validator;


use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index()
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
            return view('welcome')->with('cityNames', $cityNames);
        } catch(Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
       
        
    }

    public function processForm(Request $request)
    {

        $validatet = Validator::make($request->all(),[
            'city' => 'required|string|max:25',
        ]);

        if ($validatet->fails()) {
            return response()->json(["error" => "Text input is not correct"], 422);
        } else {
            $city = $request->input('city');
            $city = strtolower($city);
            $results = [];
        }
        
        for ($i = 0;$i < 3;$i++)
        {
            try
            {
                $date = date('Y-m-d', strtotime("-{$i} day"));
                $url = "https://api.meteo.lt/v1/stations/$city-ams/observations/$date";
                $response = Http::get($url);
                $weather = $response->json();
                $results[] = $weather;
            }
            catch(Exception $e)
            {
                return response()->json(["error" => $e->getMessage()], 500);
            }
        }

        if (empty($results)) {
            return response()->json(["error" => "Failed to fetch weather data"], 500);
        } else {
            dd($results);
        }
        
        return view('welcome');
    }
}
