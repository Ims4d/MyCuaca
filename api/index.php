<?php
const WEATHER_API = 'https://api.weatherapi.com/v1/forecast.json';

function tanggalIndo(string $tanggal): string {
  $bulan = [
    1 => 'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
  ];
  $ts = strtotime($tanggal);
  return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function fetchJson(string $url): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
  ]);

  $body = curl_exec($ch);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($err) return ['error' => $err];

  $data = json_decode($body, true);
  if (!$data) return ['error' => 'Gagal membaca data cuaca'];
  if (isset($data['error'])) return ['error' => $data['error']['message']];

  return $data;
}

function getWeather(string $city, int $days): array {
  $key = getenv('WEATHERAPI_KEY');
  if (!$key) return ['error' => 'API Key belum diset'];

  $query = http_build_query([
    'key' => $key,
    'q' => $city,
    'days' => $days,
    'lang' => 'id',
    'aqi' => 'no',
    'alerts' => 'no'
  ]);

  return fetchJson(WEATHER_API . '?' . $query);
}

$q    = trim($_GET['q'] ?? '');
$days = (int)($_GET['days'] ?? 3);
$days = max(3, min(7, $days));

$data = null;
$error = null;

if ($q) {
  $data = getWeather($q, $days);
  if (isset($data['error'])) $error = $data['error'];
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Cuaca</title>

<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { darkMode: 'media' }
</script>

<style>
@keyframes fade {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade {
  animation: fade .4s ease-out;
}
</style>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100
transition-colors duration-300">

<div class="max-w-3xl mx-auto px-4 py-10">

<header class="mb-12">
<h1 class="text-3xl font-semibold tracking-tight">My Cuaca</h1>
<p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
Prakiraan cuaca sederhana
</p>
</header>

<!-- FORM -->
<form method="get"
id="weatherForm"
class="grid gap-6 sm:grid-cols-4 items-end mb-16">

<div class="sm:col-span-2">
<label class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
Kota
</label>
<input
name="q"
value="<?= htmlspecialchars($q ?: 'Bandung') ?>"
placeholder="Bandung"
required
class="w-full mt-2 bg-transparent border-b border-slate-300
dark:border-slate-600
focus:border-blue-600 dark:focus:border-blue-400
focus:outline-none py-2 transition"
>
</div>

<div>
<label class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
Hari
</label>
<select
name="days"
onchange="document.getElementById('weatherForm').submit()"
class="w-full mt-2 bg-transparent border-b border-slate-300
dark:border-slate-600
focus:border-blue-600 dark:focus:border-blue-400
focus:outline-none py-2 transition"
>
<?php for ($i = 3; $i <= 7; $i++): ?>
<option value="<?= $i ?>" <?= $days === $i ? 'selected' : '' ?>>
<?= $i ?> Hari
</option>
<?php endfor; ?>
</select>
</div>

<button class="text-blue-600 dark:text-blue-400 font-medium
hover:underline mt-6 sm:mt-0">
Cari
</button>
</form>

<?php if ($error): ?>
<p class="text-red-600 mb-10 animate-fade">
<?= htmlspecialchars($error) ?>
</p>
<?php endif; ?>

<?php if ($data && !$error):
$loc = $data['location'];
$cur = $data['current'];
$fc  = $data['forecast']['forecastday'];

$ts = strtotime($loc['localtime']);

$icon = match (true) {
  str_contains($cur['condition']['text'], 'Hujan') => '🌧️',
  str_contains($cur['condition']['text'], 'Cerah') => '☀️',
  str_contains($cur['condition']['text'], 'Berawan') => '☁️',
  default => '🌤️'
};
?>

<!-- CURRENT -->
<section class="mb-20 animate-fade">
<h2 class="text-3xl font-medium tracking-tight">
<?= $loc['name'] ?>
<span class="text-slate-400 dark:text-slate-500 text-lg font-normal">
, <?= $loc['country'] ?>
</span>
</h2>

<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
<?= tanggalIndo(date('Y-m-d', $ts)) ?> · <?= date('H:i', $ts) ?>
</p>

<div class="flex items-center gap-6 mt-8">
<div class="text-6xl font-light tracking-tight">
<?= $icon ?> <?= round($cur['temp_c']) ?>°
</div>
<div class="text-lg text-slate-600 dark:text-slate-300">
<?= $cur['condition']['text'] ?>
</div>
</div>
</section>

<!-- FORECAST -->
<section class="animate-fade">
<h3 class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">
Prakiraan <?= $days ?> Hari
</h3>

<ul class="space-y-5">
<?php foreach ($fc as $day): ?>
<li class="flex items-center justify-between border-b
border-slate-200 dark:border-slate-700 pb-3">
<div>
<div class="font-medium">
<?= tanggalIndo($day['date']) ?>
</div>
<div class="text-sm text-slate-500 dark:text-slate-400">
<?= $day['day']['condition']['text'] ?>
</div>
</div>

<div class="text-sm text-slate-600 dark:text-slate-300">
<?= round($day['day']['mintemp_c']) ?>° / <?= round($day['day']['maxtemp_c']) ?>°
</div>
</li>
<?php endforeach; ?>
</ul>
</section>

<?php endif; ?>

<footer class="mt-20 text-xs text-slate-400">
Powered by WeatherAPI · PHP Native
</footer>

</div>
</body>
</html>
