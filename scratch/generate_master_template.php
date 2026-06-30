<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

// Definisikan tab dan data contoh
$sheets = [
    [
        'name' => 'Chromium',
        'rfd' => 0.003,
        'c1' => 0.0044,
        'c2' => 0.0002,
    ],
    [
        'name' => 'Pb',
        'rfd' => 0.0014,
        'c1' => 0.0012,
        'c2' => 0.0001,
    ],
    [
        'name' => 'Nikel',
        'rfd' => 0.02,
        'c1' => 0.015,
        'c2' => 0.002,
    ],
    [
        'name' => 'Arsenik',
        'rfd' => 0.0003,
        'c1' => 0.0005,
        'c2' => 0.00001,
    ],
    [
        'name' => 'Cadmium',
        'rfd' => 0.001,
        'c1' => 0.0008,
        'c2' => 0.0001,
    ]
];

$first = true;

foreach ($sheets as $s) {
    if ($first) {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($s['name']);
        $first = false;
    } else {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($s['name']);
    }

    // Set header
    $headers = ['No', 'Responden', 'Umur', 'Wb', 'f', 'C', 'R', 'RfD', 'tavg', 'Dt (realtime)', 'Latitude', 'Longitude'];
    foreach ($headers as $colIndex => $header) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($colLetter . '1', $header);
    }

    // Tulis baris contoh 1
    $row1 = [1, 'Sal', 20, 53, 365, $s['c1'], 900, $s['rfd'], 10950, 20, -5.4012, 105.2601];
    foreach ($row1 as $colIndex => $val) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($colLetter . '2', $val);
    }

    // Tulis baris contoh 2
    $row2 = [2, 'Nrf', 25, 68, 365, $s['c2'], 1500, $s['rfd'], 10950, 22, -5.3978, 105.2667];
    foreach ($row2 as $colIndex => $val) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($colLetter . '3', $val);
    }
}

// Simpan ke folder public/templates/
$destDir = __DIR__ . '/../public/templates';
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

$destPath = $destDir . '/template_master_all_metals.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($destPath);

echo "Template Master Excel berhasil digenerate di: $destPath\n";
