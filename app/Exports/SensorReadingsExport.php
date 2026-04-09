<?php

namespace App\Exports;

use App\Models\SensorReading;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SensorReadingsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = SensorReading::query();

        if ($this->request->filled('from_date') && $this->request->filled('to_date')) {
            $from = $this->request->from_date . ' 00:00:00';
            $to = $this->request->to_date . ' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($this->request->filled('status') && $this->request->status !== 'Semua') {
            $query->where('status', $this->request->status);
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal & Waktu',
            'Tegangan (V)',
            'Suhu Air (°C)',
            'Suhu Lingkungan (°C)',
            'Kelembapan (%)',
            'TDS (mg/L)',
            'EC (µS/cm)',
            'pH',
            'Cr Estimated (µg/L)',
            'Status'
        ];
    }

    public function map($reading): array
    {
        return [
            $reading->id,
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
            // Style the first row as bold text
            1    => ['font' => ['bold' => true]],
        ];
    }
}
