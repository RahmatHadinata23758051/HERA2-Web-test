<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RqMultiSheetImport implements WithMultipleSheets
{
    protected int $batchId;
    protected array $existingSheets;

    public function __construct(int $batchId, array $existingSheets)
    {
        $this->batchId = $batchId;
        $this->existingSheets = $existingSheets;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Pola pemetaan nama sheet ke logam berat
        $mappings = [
            'chromium' => ['rq chromium', 'chromium', 'rq_chromium', 'kromium'],
            'pb'       => ['rq timbal', 'timbal', 'rq_timbal', 'pb', 'lead'],
            'nickel'   => ['rq nikel', 'nikel', 'rq_nikel', 'ni', 'nickel'],
            'arsenic'  => ['rq arsen', 'arsen', 'rq_arsen', 'as', 'arsenic'],
            'cd'       => ['rq cadmium', 'cadmium', 'rq_cadmium', 'cd', 'kadmium'],
        ];

        foreach ($this->existingSheets as $sheetName) {
            $normalizedName = strtolower(trim($sheetName));

            foreach ($mappings as $pollutant => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($normalizedName === $pattern) {
                        $sheets[$sheetName] = new RqAnalysisImport($pollutant, $this->batchId);
                        break 2; // Jika cocok, lanjut ke sheet berikutnya
                    }
                }
            }
        }

        return $sheets;
    }
}
