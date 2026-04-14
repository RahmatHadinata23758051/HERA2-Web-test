<?php

namespace App\Imports;

use App\Models\RqAnalysis;
use App\Services\RQCalculationService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class RqAnalysisImport implements ToCollection, WithHeadingRow
{
    protected string $pollutantType;
    protected RQCalculationService $rqService;

    public function __construct(string $pollutantType, RQCalculationService $rqService)
    {
        $this->pollutantType = $pollutantType;
        $this->rqService = $rqService;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Abaikan row kosong
            if (!isset($row['nama'])) {
                continue;
            }

            // Normalisasi huruf field dari heading excel
            try {
                $umur = (float) ($row['umur'] ?? 0);
                $wb   = (float) ($row['wb'] ?? 0);
                $f    = (float) ($row['f'] ?? 0);
                $c    = (float) ($row['c'] ?? 0);
                $r    = (float) ($row['r'] ?? 0);
                $rfd  = (float) ($row['rfd'] ?? RqAnalysis::$rfdDefaults[$this->pollutantType] ?? 1);
                $tavg = (float) ($row['tavg'] ?? 0);
                $dt   = (float) ($row['dt_realtime'] ?? $row['dt'] ?? 0);

                if ($wb <= 0 || $tavg <= 0) {
                    continue; // Mencegah division by zero
                }

                $validated = [
                    'c' => $c,
                    'r' => $r,
                    'f' => $f,
                    'wb' => $wb,
                    'rfd' => $rfd,
                    'tavg' => $tavg,
                    'dt_input' => $dt,
                ];

                $calculations = $this->rqService->calculate($validated);

                RqAnalysis::create(array_merge([
                    'pollutant_type' => $this->pollutantType,
                    'user_id'        => auth()->id(),
                    'source'         => 'import',
                    'no_responden'   => $row['no'] ?? null,
                    'nama'           => $row['nama'],
                    'umur'           => $umur,
                    'wb'             => $wb,
                    'f'              => $f,
                    'c'              => $c,
                    'r'              => $r,
                    'rfd'            => $rfd,
                    'tavg'           => $tavg,
                    'dt_input'       => $dt,
                ], $calculations));
            } catch (Exception $e) {
                // Lewati baris jika data tidak valid / header tidak match
                continue;
            }
        }
    }
}
