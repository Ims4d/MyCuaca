<?php
require_once __DIR__ . '/weatherclient.php';

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
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Weather App</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-3xl mx-auto p-4">
<header class="flex items-center justify-between mb-6">
<h1 class="text-2xl font-bold">🌤️ Weather App</h1>
<a
href="https://www.weatherapi.com/"
target="_blank"
class="text-sm text-blue-600 hover:underline"
>
WeatherAPI.com
</a>
</header>

<?php
function tanggalIndo(string $tanggal): string {
  $bulanIndo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  $ts = strtotime($tanggal);
  return date('j', $ts) . ' ' . $bulanIndo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
?>

<!-- FORM -->
<section class="bg-white rounded-2xl shadow-sm p-5 md:p-6 mb-6">
<form method="get" class="grid gap-4 md:grid-cols-6 items-end">
<div class="md:col-span-4">
<label class="block text-sm font-medium text-slate-700 mb-1">
Nama Kota
</label>
<input
type="text"
name="q"
placeholder="Contoh: Bandung"
value="<?= htmlspecialchars($q ?: 'Bandung') ?>"
class="w-full rounded-xl border border-slate-300 px-3 py-2
focus:outline-none focus:ring-2 focus:ring-blue-500"
required
/>
</div>

<div>
<label class="block text-sm font-medium text-slate-700 mb-1">Hari</label>
<input
type="number"
name="days"
min="1"
max="10"
value="<?= (int)$days ?>"
class="w-full rounded-xl border border-slate-300 px-3 py-2
focus:outline-none focus:ring-2 focus:ring-blue-500"
/>
</div>

<div class="md:col-span-6 flex gap-3">
<button
type="submit"
class="flex-1 md:flex-none px-4 py-2 rounded-xl
bg-blue-600 text-white font-medium
hover:bg-blue-700 transition"
>
🔍 Cari
</button>
<a
href="?"
class="flex-1 md:flex-none px-4 py-2 rounded-xl
bg-slate-100 text-slate-700 text-center
hover:bg-slate-200 transition"
>
Reset
</a>
</div>
</form>
</section>

<?php if ($errorMsg): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-300 text-red-800">
<strong>Terjadi kesalahan:</strong>
<?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<?php if ($response && !isset($response['_error'])): ?>
<?php
$loc = $response['location'];
$cur = $response['current'];
$fc  = $response['forecast']['forecastday'];

$datetime = strtotime($loc['localtime']);
$tanggalLokal = tanggalIndo(date('Y-m-d', $datetime));
$jamLokal = date('H:i', $datetime);
?>

<!-- CURRENT WEATHER -->
<section class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
<h2 class="text-xl font-semibold">
<?= $loc['name'] ?>, <?= $loc['country'] ?>
</h2>
<p class="text-sm text-slate-500 mb-4">
🕒 <?= $tanggalLokal ?> · <?= $jamLokal ?>
</p>

<div class="flex items-center gap-4">
<div class="text-5xl font-bold">
<?= round($cur['temp_c']) ?>°C
</div>
<div class="text-slate-600">
<?= $cur['condition']['text'] ?>
</div>
</div>
</section>

<!-- FORECAST -->
<section class="mt-6 bg-white rounded-2xl shadow-sm p-5 md:p-6">
<h3 class="text-lg font-semibold mb-4">📅 Prakiraan</h3>
<div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
<?php foreach ($fc as $day): ?>
<div class="p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
<div class="text-sm font-medium">
<?= tanggalIndo($day['date']) ?>
</div>
<div class="text-slate-600 text-sm mb-2">
<?= $day['day']['condition']['text'] ?>
</div>
<div class="text-sm">
🌡️ Max: <?= round($day['day']['maxtemp_c']) ?>°C
</div>
<div class="text-sm">
❄️ Min: <?= round($day['day']['mintemp_c']) ?>°C
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

<footer class="text-center text-xs text-slate-500 mt-10">
Dibuat dengan PHP + WeatherAPI <br>
Copyright by Kelompok 5.
</footer>
</div>
</body>
</html>
