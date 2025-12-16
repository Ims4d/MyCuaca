<?php
require_once __DIR__ . '/api/weatherclient.php';

$q = $_GET['q'] ?? '';
$days = $_GET['days'] ?? 3;

$client = new WeatherClient();
$response = null;
$errorMsg = null;

if ($q !== '') {
  $response = $client->getForecast($q, (int)$days);
  if (isset($response['_error'])) {
    $errorMsg = $response['_error'];
  }
}

include __DIR__ . '/views/header.php';
include __DIR__ . '/views/weather.php';
include __DIR__ . '/views/footer.php';
