<?php
namespace App\Service;

use DateTime;
use DateTimeZone;
use App\Models\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
                        $daysConditionCount = ['conditionCodes' => []];

                        // Loop through each forecast for this day and add condition codes to array
                        foreach ($weatherData['original']['forecastTimestamps'] as $forecastOfDay) {
                            $forecastOfDayDate = new DateTime($forecastOfDay['forecastTimeUtc']);
                            $forecastOfDayDateString = $forecastOfDayDate->format('Y-m-d');

                            if ($dateString == $forecastOfDayDateString) {
                                $daysConditionCount['conditionCodes'][] = $forecastOfDay['conditionCode'];
                            }
                        }

                        // Get the most common condition code for that day
                        $conditionCounts = array_count_values($daysConditionCount['conditionCodes']);
                        arsort($conditionCounts);
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

    public static function getProductsByWeather(string $conditionCode)
    {
        return Product::with('weathers')
            ->whereHas('weathers', function ($query) use ($conditionCode) {
                $query->where('weather', $conditionCode);
            })
            ->inRandomOrder()
            ->limit(2)
            ->get()
            ->toArray();
    }

    const CACHE_TIME = 300; // 5min

    public static function recommendProduct(array $filteredData): JsonResponse
    {
        $cacheRecommended = $filteredData[0]['name'];

        // Check if recomendations for that city is in the cache
        if (Cache::has($cacheRecommended)) {
            $recommendedProducts = Cache::get($cacheRecommended);
            return response()->json($recommendedProducts, 200);
        }

        try {
            $recommendation = [];
            foreach ($filteredData as $condition) {
                // Get up to 2 random products that have a specific weather condition.
                $allProducts = self::getProductsByWeather($condition['conditionCode']);

                $matchingProducts = [];
                foreach ($allProducts as $product) {
                    $cloudyData = array_filter($product['weathers'], fn($data) => $data["weather"] === $condition['conditionCode']);

                    // if weather in product maches with weather in perticular day add to matchingProduct
                    if ($cloudyData) {
                        $matchingProducts[] = ['name' => $product['name'], 'sku' => $product['sku'], 'price' => $product['price']];
                    }

                    // break out of the inner loop when  maching products are found
                    if (count($matchingProducts) >= 2) {
                        break;
                    }
                }
                $condition['recommendations'] = $matchingProducts;
                $recommendation[] = $condition;
                Cache::put($cacheRecommended, $recommendation, self::CACHE_TIME);
            }
            return response()->json($recommendation, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Theres no recomendations for this city.'], 500);
        }
    }
}