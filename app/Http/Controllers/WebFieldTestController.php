<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FieldTest;
use App\Exports\FieldTestExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;

class WebFieldTestController extends Controller
{
    /**
     * Display a listing of the field tests with filters.
     */
    public function index(Request $request)
    {
        $query = FieldTest::with('user');

        // Filter Waktu
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }

        // Filter Lokasi / Petugas
        if ($request->filled('location')) {
            $loc = $request->location;
            $query->where(function ($q) use ($loc) {
                $q->where('latitude', 'ilike', "%{$loc}%")
                  ->orWhere('longitude', 'ilike', "%{$loc}%")
                  ->orWhereHas('user', function ($qu) use ($loc) {
                      $qu->where('name', 'ilike', "%{$loc}%");
                  });
            });
        }

        // Filter Logam Berat
        if ($request->filled('metal')) {
            $metal = $request->metal;
            if ($metal === 'cr') {
                $query->whereNotNull('cr_estimated');
            } elseif ($metal === 'ni') {
                $query->whereNotNull('ni_estimated');
            }
        }

        // Get all testing data descending, paginated
        $tests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('pengujian.index', compact('tests'));
    }

    /**
     * Export field tests to Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        
        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Export Laporan Pengujian',
            'details' => 'User mengekspor laporan pengujian lapangan dalam format ' . strtoupper($format)
        ]);

        $filename = 'HERA_Pengujian_Lapangan_' . date('Ymd_His') . '.' . $format;
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        return Excel::download(new FieldTestExport($request), $filename, $exportFormat);
    }

    /**
     * Export field tests to PDF
     */
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $query = FieldTest::with('user');

        // Filter Waktu
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }

        // Filter Lokasi / Petugas
        if ($request->filled('location')) {
            $loc = $request->location;
            $query->where(function ($q) use ($loc) {
                $q->where('latitude', 'ilike', "%{$loc}%")
                  ->orWhere('longitude', 'ilike', "%{$loc}%")
                  ->orWhereHas('user', function ($qu) use ($loc) {
                      $qu->where('name', 'ilike', "%{$loc}%");
                  });
            });
        }

        // Filter Logam Berat
        if ($request->filled('metal')) {
            $metal = $request->metal;
            if ($metal === 'cr') {
                $query->whereNotNull('cr_estimated');
            } elseif ($metal === 'ni') {
                $query->whereNotNull('ni_estimated');
            }
        }

        $tests = $query->orderBy('created_at', 'desc')->take(500)->get();

        $pdf = Pdf::loadView('pengujian.pdf', compact('tests'))->setPaper('a4', 'landscape');
        
        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Export Laporan Pengujian',
            'details' => 'User mengekspor laporan pengujian lapangan dalam format PDF'
        ]);

        $filename = 'HERA_Pengujian_Lapangan_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
