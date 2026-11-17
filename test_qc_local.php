<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FieldTest;
use App\Models\Threshold;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\MobileTestController;
use App\Http\Controllers\WebFieldTestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "       HERA SYSTEM - COMPREHENSIVE LOCAL QC RUNNER      \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertQc($label, $condition, $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        if ($details) echo "         -> {$details}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$label}\n";
        if ($details) echo "         -> ERROR: {$details}\n";
        $failCount++;
    }
}

// 1. Database Connection & Thresholds
echo "--- [1] DATABASE & THRESHOLD VALIDATION ---\n";
try {
    $crThresholds = Threshold::getCrThresholds();
    $niThresholds = Threshold::getNiThresholds();
    
    assertQc("Threshold Cr loaded from DB/Cache", isset($crThresholds['cr_normal_max']));
    assertQc("Threshold Ni loaded from DB/Cache", isset($niThresholds['ni_normal_max']));
    assertQc("Threshold Cr(VI) Normal Max is 50 µg/L", (float)$crThresholds['cr_normal_max'] === 50.0, "Value: {$crThresholds['cr_normal_max']}");
    assertQc("Threshold Cr(VI) Warning Max is 100 µg/L", (float)$crThresholds['cr_warning_max'] === 100.0, "Value: {$crThresholds['cr_warning_max']}");
    assertQc("Threshold Ni Normal Max is 20 µg/L", (float)$niThresholds['ni_normal_max'] === 20.0, "Value: {$niThresholds['ni_normal_max']}");
    assertQc("Threshold Ni Warning Max is 70 µg/L", (float)$niThresholds['ni_warning_max'] === 70.0, "Value: {$niThresholds['ni_warning_max']}");

    $thresholdRow = Threshold::first();
    assertQc("Threshold DB Unit is µg/L", ($thresholdRow->unit ?? '') === 'µg/L', "DB Unit: " . ($thresholdRow->unit ?? 'NONE'));

} catch (\Throwable $e) {
    assertQc("Database & Threshold", false, $e->getMessage());
}

// 2. User & Authentication
echo "\n--- [2] AUTHENTICATION & USER CONTEXT ---\n";
$user = User::first();
if (!$user) {
    $user = User::create([
        'name' => 'QC Field Officer',
        'email' => 'officer@hera.test',
        'role' => 'direksi',
        'password' => bcrypt('password123'),
    ]);
}
assertQc("User retrieved/created", $user !== null, "User: {$user->name} ({$user->email})");
Auth::login($user);

// 3. Simulating Mobile Ingestion (POST /api/mobile/testing/location)
echo "\n--- [3] TESTING MOBILE DATA INGESTION (FIELD TEST) ---\n";
try {
    $initialCount = FieldTest::count();
    $controller = app()->make(MobileTestController::class);

    // Test Case A: Payload with complete sensor data
    $testPayloadA = [
        'latitude' => -6.914744,
        'longitude' => 107.609810,
        'altitude' => 768.2,
        'ph' => 7.15,
        'tds' => 180.5,
        'ec' => 360.0,
        'suhu_air' => 26.4,
        'suhu_lingkungan' => 29.8,
        'kelembapan' => 68.0,
        'tegangan' => 12.1,
    ];

    $requestA = Request::create('/api/mobile/testing/location', 'POST', $testPayloadA);
    $requestA->setUserResolver(fn() => $user);

    $responseA = $controller->store($requestA);
    $responseDataA = json_decode($responseA->getContent(), true);

    assertQc("Mobile Test API (Full Sensors) HTTP 201", $responseA->getStatusCode() === 201);
    assertQc("Mobile Test API Response Status true", ($responseDataA['status'] ?? false) === true);
    
    // Check in Database
    $newCount = FieldTest::count();
    assertQc("Record inserted into 'field_tests' table", $newCount > $initialCount, "Total records: {$newCount}");

    $latestRecordA = FieldTest::latest('id')->first();
    assertQc("FieldTest Lat & Lng match payload", 
        round((float)$latestRecordA->latitude, 5) === round($testPayloadA['latitude'], 5) &&
        round((float)$latestRecordA->longitude, 5) === round($testPayloadA['longitude'], 5)
    );
    assertQc("FieldTest Cr(VI) is estimated", $latestRecordA->cr_estimated !== null, "Cr(VI) Est: {$latestRecordA->cr_estimated} µg/L");
    assertQc("FieldTest Ni is estimated", $latestRecordA->ni_estimated !== null, "Ni Est: {$latestRecordA->ni_estimated} µg/L");

    // Test Case B: Payload without sensor data (fallback test to ensure no crash)
    $testPayloadB = [
        'latitude' => -6.920000,
        'longitude' => 107.610000,
    ];
    $requestB = Request::create('/api/mobile/testing/location', 'POST', $testPayloadB);
    $requestB->setUserResolver(fn() => $user);

    $responseB = $controller->store($requestB);
    $responseDataB = json_decode($responseB->getContent(), true);
    assertQc("Mobile Test API (Empty Sensors Fallback) HTTP 201", $responseB->getStatusCode() === 201);
    assertQc("Fallback record saved in PostgreSQL", FieldTest::count() > $newCount);

} catch (\Throwable $e) {
    assertQc("Mobile Ingestion Execution", false, $e->getMessage() . "\n" . $e->getTraceAsString());
}

// 4. Web Data Pengujian Controller & Views (GET /laporan/pengujian)
echo "\n--- [4] TESTING WEB PENGUJIAN CONTROLLER & FILTERS ---\n";
try {
    $webPengujian = app()->make(WebFieldTestController::class);

    // Normal view without filter
    $reqIndex = Request::create('/laporan/pengujian', 'GET');
    $viewIndex = $webPengujian->index($reqIndex);
    assertQc("WebFieldTestController::index returns view", $viewIndex instanceof \Illuminate\View\View);
    $renderedIndex = $viewIndex->render();
    assertQc("HTML contains 'Cr(VI) Est. (µg/L)'", strpos($renderedIndex, 'Cr(VI) Est. (µg/L)') !== false);
    assertQc("HTML contains 'Ni Est. (µg/L)'", strpos($renderedIndex, 'Ni Est. (µg/L)') !== false);

    // Filter by decimal location (checking fix for PostgreSQL numeric ILIKE)
    $reqFilterLoc = Request::create('/laporan/pengujian', 'GET', ['location' => '107.60']);
    $viewFilterLoc = $webPengujian->index($reqFilterLoc);
    $renderedFilter = $viewFilterLoc->render();
    assertQc("PostgreSQL numeric location filter (107.60) executes without crash", strlen($renderedFilter) > 0);

    // Filter by heavy metal parameter: chromium & nikel
    $reqFilterCr = Request::create('/laporan/pengujian', 'GET', ['heavy_metal' => 'chromium']);
    $webPengujian->index($reqFilterCr);
    assertQc("Filter by Chromium executes successfully", true);

    $reqFilterNi = Request::create('/laporan/pengujian', 'GET', ['heavy_metal' => 'nikel']);
    $webPengujian->index($reqFilterNi);
    assertQc("Filter by Nikel executes successfully", true);

    // PDF Export
    $reqPdf = Request::create('/laporan/pengujian/export/pdf', 'GET');
    $responsePdf = $webPengujian->exportPdf($reqPdf);
    assertQc("Pengujian PDF Export returns binary response", $responsePdf->getStatusCode() === 200, "Content-Type: " . $responsePdf->headers->get('content-type'));

    // Excel Export
    $reqExcel = Request::create('/laporan/pengujian/export/excel', 'GET');
    $responseExcel = $webPengujian->exportExcel($reqExcel);
    assertQc("Pengujian Excel Export returns download response", $responseExcel->getStatusCode() === 200);

} catch (\Throwable $e) {
    assertQc("Web Pengujian Controller", false, $e->getMessage() . "\n" . $e->getTraceAsString());
}

// 5. Web Laporan & Dashboard Views
echo "\n--- [5] TESTING WEB LAPORAN & DASHBOARD CONTROLLERS ---\n";
try {
    $reportController = app()->make(ReportController::class);
    $reqReport = Request::create('/laporan', 'GET');
    $viewReport = $reportController->index($reqReport);
    $renderedReport = $viewReport->render();
    assertQc("HTML Laporan contains 'Cr(VI) (µg/L)'", strpos($renderedReport, 'Cr(VI) (µg/L)') !== false);
    assertQc("HTML Laporan contains 'Ni (µg/L)'", strpos($renderedReport, 'Ni (µg/L)') !== false);

    $dashboardController = app()->make(DashboardController::class);
    $viewDashboard = app()->call([$dashboardController, 'index']);
    $renderedDashboard = $viewDashboard->render();
    assertQc("Dashboard contains 'Chromium Heksavalen : Cr(VI)'", strpos($renderedDashboard, 'Chromium Heksavalen : Cr(VI)') !== false);
    assertQc("Dashboard contains 'Nikel : Ni'", strpos($renderedDashboard, 'Nikel : Ni') !== false);
    assertQc("Dashboard contains unit 'µg/L'", strpos($renderedDashboard, 'µg/L') !== false);

} catch (\Throwable $e) {
    assertQc("Laporan & Dashboard Controllers", false, $e->getMessage() . "\n" . $e->getTraceAsString());
}

// 6. Threshold Evaluation Logic
echo "\n--- [6] TESTING THRESHOLD CLASSIFICATION LOGIC ---\n";
try {
    $normalCr = 30.0;   // <= 50 -> Aman
    $warningCr = 75.0;  // 51 - 100 -> Waspada
    $dangerCr = 120.0;  // > 100 -> Bahaya

    $normalNi = 15.0;   // <= 20 -> Aman
    $warningNi = 55.0;  // 21 - 70 -> Waspada
    $dangerNi = 95.0;   // > 70 -> Bahaya

    assertQc("Cr(VI) 30 µg/L is NORMAL", Threshold::classifyCr($normalCr) === 'normal');
    assertQc("Cr(VI) 75 µg/L is WARNING", Threshold::classifyCr($warningCr) === 'warning');
    assertQc("Cr(VI) 120 µg/L is DANGER", Threshold::classifyCr($dangerCr) === 'danger');

    assertQc("Ni 15 µg/L is NORMAL", Threshold::classifyNi($normalNi) === 'normal');
    assertQc("Ni 55 µg/L is WARNING", Threshold::classifyNi($warningNi) === 'warning');
    assertQc("Ni 95 µg/L is DANGER", Threshold::classifyNi($dangerNi) === 'danger');

} catch (\Throwable $e) {
    assertQc("Threshold Classification", false, $e->getMessage());
}

echo "\n========================================================\n";
echo "QC RESULT SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
