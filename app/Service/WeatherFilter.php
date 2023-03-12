<?php
namespace App\Service;

use DateTime;
use DateTimeZone;
use App\Models\Product;


class WeatherFilter
{
    public static function filter($weatherData): array
    {
        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
        $lastDate = null;
        $combinedData = [];
        if (isset($weatherData['forecastTimestamps'])) {
            // Loop through each day in the forecast
            foreach ($weatherData['forecastTimestamps'] as $forecast) {
                $forecastDate = new DateTime($forecast['forecastTimeUtc']);
                $diff = $forecastDate->diff($currentDate);
                // Check if the forecast is within the next 3 days
                if ($diff->days <= 3) {
                    $dateString = $forecastDate->format('Y-m-d');
                    if ($dateString !== $lastDate) {
                        $dayResults = ['name' => $weatherData['place']['name'], 'forecastTimeUtc' => date('Y-m-d', strtotime($forecast['forecastTimeUtc']))];

                        // Loop through each forecast for this day and add condition codes to array
                        foreach ($weatherData['forecastTimestamps'] as $forecastOfDay) {
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
            return $recommendate;
        }
        return [];
    }

    public static function recommendProduct($fillteredData): array
    {
        if (!is_array($fillteredData)) {
            $fillteredData = json_decode($fillteredData, true);
        }
        $allProducts = Product::all()->toArray();
        $recommendation = [];

        foreach ($fillteredData as $condition) {
            $matchingProducts = [];
            foreach ($allProducts as $product) {

                // if weather in product maches with weather in perticular day add to matchingProduct
                if ($product['ocasion'] === $condition['conditionCode']) {
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
        return $recommendation;
    }
}