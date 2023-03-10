<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Weather Forecast Recomendations</title>
   </head>
   <body>
      <form method="POST" action="{{ route('processForm') }}">
         @csrf
         <label for="city">Enter your city:</label>
         <input type="text" id="city" name="city" list="cities">
         <datalist id="cities">
            @foreach ($cityNames as $city)
            <option value={{ $city }}>{{$city}}
            @endforeach
         </datalist>
         <button type="submit">Submit</button>
      </form>
   </body>
</html>