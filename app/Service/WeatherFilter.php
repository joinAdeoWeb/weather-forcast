<?php
namespace App\Service;

use DateTime;
use DateTimeZone;
use App\Models\Product;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeatherFilter
{
    public static function filter(object $weatherData): JsonResponse
    {
        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
        $lastDate = null;
        $combinedData = [];

        $weatherData = get_object_vars($weatherData);

        if (isset($weatherData['original']['forecastTimestamps'])) {
            // Loop through each day in the forecast
            foreach ($weatherData['original']['forecastTimestamps'] as $forecast) {
                $forecastDate = new DateTime($forecast['forecastTimeUtc']);
                $diff = $forecastDate->diff($currentDate);
                // Check if the forecast is within the next 3 days
                if ($diff->days <= 3) {
                    $dateString = $forecastDate->format('Y-m-d');
                    if ($dateString !== $lastDate) {
                        $dayResults = ['name' => $weatherData['original']['place']['name'], 'forecastTimeUtc' => date('Y-m-d', strtotime($forecast['forecastTimeUtc']))];

                        // Loop through each forecast for this day and add condition codes to array
                        foreach ($weatherData['original']['forecastTimestamps'] as $forecastOfDay) {
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
            $recommendate = self::recommendProduct($fillteredData);

            return response()->json($recommendate, 200);
        }
        return response()
            ->json(['message' => 'No city with recommendations found'], 400);
    }

    public static function recommendProduct(array $filteredData): JsonResponse
    {
        try {
            $allProducts = Product::with('weathers')->get()->toArray();
            $recommendation = [];
            foreach ($filteredData as $condition) {
                $matchingProducts = [];
                foreach ($allProducts as $product) {

                    // if weather in product maches with weather in perticular day add to matchingProduct
                    if ($product['weathers'][0]['weather'] === $condition['conditionCode']) {
                        $matchingProducts[] = ['name' => $product['name'], 'sku' => $product['sku'], 'price' => $product['price']];
                    }

                    // break out of the inner loop when  maching products are found
                    if (count($matchingProducts) >= 2) {
                        break;
                    }
                }
                $condition['recommendations'] = $matchingProducts;
                $recommendation[] = $condition;
            }
            return response()->json($recommendation, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Theres no recomendations for this city.'], 500);
        }
    }
}