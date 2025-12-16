<?php
require_once __DIR__ . '/config.php';

class WeatherClient {
  private $baseUrl = 'https://api.weatherapi.com/v1';

    private function httpGetJson(string $url): array {
      $ch = curl_init();
      curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
          'Accept: application/json',
          'User-Agent: WeatherAPI-PHP-Native/1.0'
        ],
      ]);

      $body = curl_exec($ch);
      $err = curl_error($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($err) {
        return ['_error' => "cURL error: $err", '_status' => $code];
      }

      $data = json_decode($body, true);
      if ($data === null) {
        return [
          '_error' => 'Gagal parse JSON',
          '_status' => $code,
          '_raw' => $body
        ];
      }

      if (isset($data['error'])) {
        $msg = $data['error']['message'] ?? 'Unknown API error';
        return ['_error' => $msg, '_status' => $code];
      }

      $data['_status'] = $code;
      return $data;
    }

    public function getForecast(
      string $q,
      int $days = 3,
      string $aqi = 'no',
      string $alerts = 'no'
    ): array {
      $apiKey = $_ENV['WEATHERAPI_KEY'] ?? '';
      if (!$apiKey) {
        return ['_error' => 'API Key belum diset.'];
      }

      $query = http_build_query([
        'key' => $apiKey,
        'q' => $q,
        'days' => $days,
        'aqi' => $aqi,
        'alerts' => $alerts,
        'lang' => 'id'
      ]);

      $url = $this->baseUrl . '/forecast.json?' . $query;
      return $this->httpGetJson($url);
    }
}
