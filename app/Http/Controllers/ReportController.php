<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Http\Request;
use App\Exports\SensorReadingsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = SensorReading::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = $request->from_date . ' 00:00:00';
            $to = $request->to_date . ' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        $readings = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();

        return view('laporan.index', compact('readings'));
    }

    public function exportExcel(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Export Laporan',
            'details' => 'User mengekspor laporan dalam format ' . strtoupper($format)
        ]);

        $filename = 'HERA_Laporan_' . date('Ymd_His') . '.' . $format;
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        return Excel::download(new SensorReadingsExport($request), $filename, $exportFormat);
    }

    public function exportPdf(Request $request)
    {
        // Increase memory limit dynamically because DOMPDF consumes a lot of RAM parsing tables
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $query = SensorReading::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = $request->from_date . ' 00:00:00';
            $to = $request->to_date . ' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        // Limit data for PDF to prevent memory exhaustion (DOMPDF is heavy)
        $readings = $query->orderBy('id', 'desc')->take(500)->get();

        $pdf = Pdf::loadView('laporan.pdf', compact('readings'))->setPaper('a4', 'landscape');
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Export Laporan',
            'details' => 'User mengekspor laporan dalam format PDF'
        ]);

        $filename = 'HERA_Laporan_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
