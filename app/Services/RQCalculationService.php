<?php

namespace App\Services;

use App\Models\RqAnalysis;

class RQCalculationService
{
    /**
     * Periode durasi pajanan yang dihitung (tahun)
     */
    protected array $periods = [5, 10, 15, 20, 25, 30];

    /**
     * Hitung Intake (mg/kg/hari)
     * Formula: Intake = (C × R × f × dt) / (Wb × tavg)
     *
     * @param float $c     Konsentrasi (mg/L)
     * @param float $r     Laju asupan (L/hari)
     * @param float $f     Frekuensi pajanan (hari/tahun)
     * @param float $dt    Durasi pajanan (tahun)
     * @param float $wb    Berat badan (kg)
     * @param float $tavg  Waktu rata-rata (hari)
     */
    public function calcIntake(float $c, float $r, float $f, float $dt, float $wb, float $tavg): float
    {
        if ($wb <= 0 || $tavg <= 0) return 0;
        return ($c * $r * $f * $dt) / ($wb * $tavg);
    }

    /**
     * Hitung Risk Quotient (RQ)
     * Formula: RQ = Intake / RfD
     *
     * @param float $intake Hasil kalkulasi intake
     * @param float $rfd    Reference Dose (mg/kg/hari)
     */
    public function calcRQ(float $intake, float $rfd): float
    {
        if ($rfd <= 0) return 0;
        return $intake / $rfd;
    }

    /**
     * Hitung seluruh periode dan kembalikan array siap simpan ke DB
     */
    public function calculate(array $data): array
    {
        $c    = (float) $data['c'];
        $r    = (float) $data['r'];
        $f    = (float) $data['f'];
        $wb   = (float) $data['wb'];
        $rfd  = (float) $data['rfd'];
        $tavg = (float) $data['tavg'];
        $dtInput = (float) $data['dt_input'];

        // Kalkulasi Realtime (menggunakan dt_input dari pengguna)
        $intakeRealtime = $this->calcIntake($c, $r, $f, $dtInput, $wb, $tavg);
        $rqRealtime     = $this->calcRQ($intakeRealtime, $rfd);

        $result = [
            'intake_realtime' => round($intakeRealtime, 6),
            'rq_realtime'     => round($rqRealtime, 6),
        ];

        // Kalkulasi per periode
        foreach ($this->periods as $year) {
            $intake = $this->calcIntake($c, $r, $f, $year, $wb, $tavg);
            $rq     = $this->calcRQ($intake, $rfd);

            $result["intake_{$year}th"] = round($intake, 6);
            $result["rq_{$year}th"]     = round($rq, 6);
        }

        return $result;
    }
}
