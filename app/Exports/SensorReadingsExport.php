<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Repositories\InfluxSensorRepository;

class SensorReadingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $repo = app(InfluxSensorRepository::class);
        $from = $this->request->filled('from_date') ? $this->request->from_date . ' 00:00:00' : null;
        $to = $this->request->filled('to_date') ? $this->request->to_date . ' 23:59:59' : null;
        $status = $this->request->input('status', 'Semua');

        return $repo->getReportData($from, $to, $status);
    }

    public function headings(): array
    {
        return [
            'Tanggal & Waktu',
            'Tegangan (V)',
            'Suhu Air (°C)',
            'Suhu Lingkungan (°C)',
            'Kelembapan (%)',
            'TDS (mg/L)',
            'EC (µS/cm)',
            'pH',
            'Cr Estimated (mg/L)',
            'Status'
        ];
    }

    public function map($reading): array
    {
        return [
            $reading->created_at->format('Y-m-d H:i:s'),
            $reading->tegangan,
            $reading->suhu_air,
            $reading->suhu_lingkungan,
            $reading->kelembapan,
            $reading->tds,
            $reading->ec,
            $reading->ph,
            $reading->cr_estimated,
            strtoupper($reading->status)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
