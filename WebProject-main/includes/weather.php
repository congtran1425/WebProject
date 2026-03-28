<?php
function get_current_weather($latitude, $longitude, $timezone = "Asia/Ho_Chi_Minh")
{
    $cache_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "weather_hcm.json";
    $cache_ttl = 600;

    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
        $cached = json_decode(file_get_contents($cache_file), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $query = http_build_query([
        "latitude" => $latitude,
        "longitude" => $longitude,
        "current_weather" => "true",
        "temperature_unit" => "celsius",
        "timezone" => $timezone,
    ]);

    $url = "https://api.open-meteo.com/v1/forecast?" . $query;
    $context = stream_context_create([
        "http" => [
            "timeout" => 3,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || !isset($data["current_weather"])) {
        return null;
    }

    file_put_contents($cache_file, json_encode($data["current_weather"]));
    return $data["current_weather"];
}

function map_weather_icon($weather_code)
{
    if ($weather_code === 0) {
        return "bi-sun";
    }
    if ($weather_code >= 1 && $weather_code <= 3) {
        return "bi-cloud-sun";
    }
    if ($weather_code >= 45 && $weather_code <= 48) {
        return "bi-cloud-fog";
    }
    if ($weather_code >= 51 && $weather_code <= 67) {
        return "bi-cloud-rain";
    }
    if ($weather_code >= 71 && $weather_code <= 77) {
        return "bi-cloud-snow";
    }
    if ($weather_code >= 80 && $weather_code <= 82) {
        return "bi-cloud-rain-heavy";
    }
    if ($weather_code >= 95 && $weather_code <= 99) {
        return "bi-cloud-lightning-rain";
    }
    return "bi-cloud";
}
