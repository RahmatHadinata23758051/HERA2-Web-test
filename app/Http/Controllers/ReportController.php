<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SensorReadingsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;
use App\Repositories\InfluxSensorRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ReportController extends Controller
{
    protected $influxRepo;

    public function __construct(InfluxSensorRepository $influxRepo)
    {
        $this->influxRepo = $influxRepo;
    }

    public function index(Request $request)
    {
        $from = $request->filled('from_date') ? $request->from_date . ' 00:00:00' : null;
        $to = $request->filled('to_date') ? $request->to_date . ' 23:59:59' : null;
        $status = $request->input('status', 'Semua');

        $data = $this->influxRepo->getReportData($from, $to, $status);

        // Paginate manually for InfluxDB Array mapped data
        $currentPage = Paginator::resolveCurrentPage();
        $perPage = 50;
        $items = array_slice($data->toArray(), ($currentPage - 1) * $perPage, $perPage);
        $readings = new LengthAwarePaginator($items, $data->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return view('laporan.index', compact('readings'));
    }

    public function exportExcel(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        
        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Export Laporan',
            'details' => 'User mengekspor laporan dalam format ' . strtoupper($format)
        ]);

        $filename = 'HERA_Laporan_' . date('Ymd_His') . '.' . $format;
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        return Excel::download(new SensorReadingsExport($request), $filename, $exportFormat);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $from = $request->filled('from_date') ? $request->from_date . ' 00:00:00' : null;
        $to = $request->filled('to_date') ? $request->to_date . ' 23:59:59' : null;
        $status = $request->input('status', 'Semua');

        $data = $this->influxRepo->getReportData($from, $to, $status);
        $readings = $data->take(500);

        $pdf = Pdf::loadView('laporan.pdf', compact('readings'))->setPaper('a4', 'landscape');
        
        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Export Laporan',
            'details' => 'User mengekspor laporan dalam format PDF'
        ]);

        $filename = 'HERA_Laporan_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
