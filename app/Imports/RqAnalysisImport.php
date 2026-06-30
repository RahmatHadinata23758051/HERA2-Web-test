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
    protected int $batchId;
    protected RQCalculationService $rqService;

    public function __construct(string $pollutantType, int $batchId)
    {
        $this->pollutantType = $pollutantType;
        $this->batchId = $batchId;
        $this->rqService = app(RQCalculationService::class);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Abaikan row kosong, cari nama / responden
            $nama = $row['responden'] ?? $row['nama'] ?? $row['name'] ?? null;
            if ($nama === null) {
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

                // Baca koordinat secara opsional
                $latitude  = isset($row['latitude']) ? (float) $row['latitude'] : (isset($row['lat']) ? (float) $row['lat'] : null);
                $longitude = isset($row['longitude']) ? (float) $row['longitude'] : (isset($row['long']) ? (float) $row['long'] : (isset($row['lng']) ? (float) $row['lng'] : null));

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
                    'rq_batch_id'    => $this->batchId,
                    'pollutant_type' => $this->pollutantType,
                    'user_id'        => auth()->id(),
                    'source'         => 'import',
                    'no_responden'   => $row['no'] ?? null,
                    'nama'           => $nama,
                    'umur'           => $umur,
                    'wb'             => $wb,
                    'f'              => $f,
                    'c'              => $c,
                    'r'              => $r,
                    'rfd'            => $rfd,
                    'tavg'           => $tavg,
                    'dt_input'       => $dt,
                    'latitude'       => $latitude,
                    'longitude'      => $longitude,
                ], $calculations));
            } catch (Exception $e) {
                // Lewati baris jika data tidak valid / header tidak match
                continue;
            }
        }
    }
}
