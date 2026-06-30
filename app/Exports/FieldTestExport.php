<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\FieldTest;

class FieldTestExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = FieldTest::with('user');

        // Filter Waktu
        if ($this->request->filled('from_date')) {
            $query->where('created_at', '>=', $this->request->from_date . ' 00:00:00');
        }
        if ($this->request->filled('to_date')) {
            $query->where('created_at', '<=', $this->request->to_date . ' 23:59:59');
        }

        // Filter Lokasi / Petugas
        if ($this->request->filled('location')) {
            $loc = $this->request->location;
            $query->where(function ($q) use ($loc) {
                $q->where('latitude', 'like', "%{$loc}%")
                  ->orWhere('longitude', 'like', "%{$loc}%")
                  ->orWhereHas('user', function ($qu) use ($loc) {
                      $qu->where('name', 'like', "%{$loc}%");
                  });
            });
        }

        // Filter Logam Berat
        if ($this->request->filled('metal')) {
            $metal = $this->request->metal;
            if ($metal === 'cr') {
                $query->whereNotNull('cr_estimated');
            } elseif ($metal === 'ni') {
                $query->whereNotNull('ni_estimated');
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal & Waktu',
            'Nama Petugas',
            'Latitude',
            'Longitude',
            'Altitude (m)',
            'pH',
            'TDS (ppm)',
            'EC (mS)',
            'Suhu Air (°C)',
            'Suhu Udara (°C)',
            'Kelembapan (%)',
            'Tegangan (V)',
            'Cr Estimated (mg/L)',
            'Ni Estimated (mg/L)'
        ];
    }

    public function map($test): array
    {
        return [
            $test->created_at ? $test->created_at->format('Y-m-d H:i:s') : '',
            optional($test->user)->name ?? 'Unknown',
            $test->latitude,
            $test->longitude,
            $test->altitude,
            $test->ph,
            $test->tds,
            $test->ec,
            $test->suhu_air,
            $test->suhu_lingkungan,
            $test->kelembapan,
            $test->tegangan,
            $test->cr_estimated,
            $test->ni_estimated
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
