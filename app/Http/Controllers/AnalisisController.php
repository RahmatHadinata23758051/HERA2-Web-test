<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RqAnalysis;
use App\Services\RQCalculationService;
use App\Exports\RqAnalysisExport;
use App\Imports\RqAnalysisImport;
use Maatwebsite\Excel\Facades\Excel;

class AnalisisController extends Controller
{
    protected RQCalculationService $rqService;

    public function __construct(RQCalculationService $rqService)
    {
        $this->rqService = $rqService;
    }

    // =========================================================
    // Helper: tampilkan halaman analisis per polutan
    // =========================================================
    private function showAnalisis(Request $request, string $type)
    {
        $records = RqAnalysis::ofType($type)
            ->orderBy('created_at', 'desc')
            ->get();

        $label   = RqAnalysis::$pollutantLabels[$type] ?? strtoupper($type);
        $rfdDefault = RqAnalysis::$rfdDefaults[$type] ?? 0;

        return view('analisis.index', compact('records', 'type', 'label', 'rfdDefault'));
    }

    // =========================================================
    // METODE PER POLUTAN
    // =========================================================
    public function rqNitrat(Request $request)
    {
        return $this->showAnalisis($request, 'nitrat');
    }

    public function rqPb(Request $request)
    {
        return $this->showAnalisis($request, 'pb');
    }

    public function rqCd(Request $request)
    {
        return $this->showAnalisis($request, 'cd');
    }

    public function rqPh(Request $request)
    {
        return $this->showAnalisis($request, 'ph');
    }

    public function rqF(Request $request)
    {
        return $this->showAnalisis($request, 'f');
    }

    // =========================================================
    // INPUT DATA MANUAL
    // =========================================================
    public function inputData(Request $request)
    {
        // Tampilkan form input data manual
        return view('analisis.input');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pollutant_type' => 'required|in:nitrat,pb,cd,ph,f',
            'nama'           => 'required|string|max:255',
            'umur'           => 'required|numeric|min:0',
            'wb'             => 'required|numeric|min:0.1',
            'f'              => 'required|numeric|min:0',
            'c'              => 'required|numeric|min:0',
            'r'              => 'required|numeric|min:0',
            'rfd'            => 'required|numeric|min:0.000001',
            'tavg'           => 'required|numeric|min:1',
            'dt_input'       => 'required|numeric|min:0',
        ]);

        // Hitung Intake & RQ untuk semua periode
        $calculations = $this->rqService->calculate($validated);

        RqAnalysis::create(array_merge($validated, $calculations, [
            'user_id' => auth()->id(),
            'source'  => 'manual',
        ]));

        return redirect()
            ->route('analisis.rq.' . $validated['pollutant_type'])
            ->with('success', 'Data berhasil disimpan dan dihitung.');
    }

    // =========================================================
    // IMPORT EXCEL
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'file'           => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'pollutant_type' => 'required|in:nitrat,pb,cd,ph,f',
        ]);

        Excel::import(
            new RqAnalysisImport($request->pollutant_type, $this->rqService),
            $request->file('file')
        );

        return redirect()
            ->route('analisis.rq.' . $request->pollutant_type)
            ->with('success', 'File Excel berhasil diimpor dan data telah dihitung.');
    }

    // =========================================================
    // EXPORT EXCEL
    // =========================================================
    public function export(Request $request, string $type)
    {
        $label    = RqAnalysis::$pollutantLabels[$type] ?? strtoupper($type);
        $filename = 'HERA_RQ_' . strtoupper($type) . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new RqAnalysisExport($type), $filename);
    }

    // =========================================================
    // DELETE
    // =========================================================
    public function destroy(Request $request, int $id)
    {
        $record = RqAnalysis::findOrFail($id);
        $type   = $record->pollutant_type;
        $record->delete();

        return redirect()
            ->route('analisis.rq.' . $type)
            ->with('success', 'Data berhasil dihapus.');
    }
}
