<?php
$user = App\Models\User::first();
$controller = new App\Http\Controllers\Api\MobileTestController();

echo "\n--- 1. TEST VALIDASI ERROR (Data Korup / Tidak Lengkap) ---\n";
$badRequest = Illuminate\Http\Request::create('/api/mobile/testing/location', 'POST', [
    'latitude' => 'bukan-angka',
    'longitude' => null // Kosong / Invalid
]);
$badRequest->setUserResolver(function () use ($user) { return $user; });

try {
    $controller->store($badRequest);
} catch (\Illuminate\Validation\ValidationException $e) {
    echo json_encode($e->errors(), JSON_PRETTY_PRINT);
}

echo "\n\n--- 2. TEST PAYLOAD SUKSES (Valid API Simulation) ---\n";
$goodRequest = Illuminate\Http\Request::create('/api/mobile/testing/location', 'POST', [
    'latitude' => -6.914744,
    'longitude' => 107.609808,
    'suhu_air' => 28.5,
    'suhu_lingkungan' => 33.1,
    'kelembapan' => 81.0,
    'ec' => 41,
    'tds' => 230,
    'ph' => 4.5,
    'tegangan' => 3.9
]);
$goodRequest->setUserResolver(function () use ($user) { return $user; });

$response = $controller->store($goodRequest);
echo $response->getContent() . "\n";
