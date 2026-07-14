<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RqBatch;
use App\Models\RqAnalysis;
use App\Services\RQCalculationService;
use App\Models\ActivityLog;
use App\Imports\RqAnalysisImport;
use App\Imports\RqMultiSheetImport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class RqBatchController extends Controller
{
    protected RQCalculationService $rqService;

    public function __construct(RQCalculationService $rqService)
    {
        $this->rqService = $rqService;
    }

    /**
     * Tampilkan daftar batch penelitian.
     */
    public function index(Request $request)
    {
        $query = RqBatch::withCount('analyses')
            ->with('user');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', "%" . $request->input('search') . "%");
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('analisis.batch.index', compact('batches'));
    }

    /**
     * Simpan batch penelitian baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rq_batches,name',
        ]);

        $batch = RqBatch::create([
            'name' => $validated['name'],
            'user_id' => auth()->id() ?? 1,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Buat Batch Analisis',
            'details' => 'Membuat batch analisis baru: ' . $batch->name
        ]);

        return redirect()
            ->route('analisis.batch.show', $batch->id)
            ->with('success', 'Batch analisis "' . $batch->name . '" berhasil dibuat.');
    }

    /**
     * Perbarui nama batch penelitian.
     */
    public function update(Request $request, $id)
    {
        $batch = RqBatch::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rq_batches,name,' . $batch->id,
        ]);

        $oldName = $batch->name;
        $batch->update([
            'name' => $validated['name'],
        ]);

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Edit Batch Analisis',
            'details' => 'Mengubah nama batch dari "' . $oldName . '" menjadi "' . $batch->name . '"'
        ]);

        return redirect()
            ->route('analisis.index')
            ->with('success', 'Nama batch analisis berhasil diubah menjadi "' . $batch->name . '".');
    }

    /**
     * Hapus batch penelitian beserta seluruh datanya.
     */
    public function destroy($id)
    {
        $batch = RqBatch::findOrFail($id);
        $batchName = $batch->name;
        
        $batch->delete(); // Karena cascadeOnDelete, data rq_analyses otomatis terhapus

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Hapus Batch Analisis',
            'details' => 'Menghapus batch analisis: ' . $batchName
        ]);

        return redirect()
            ->route('analisis.index')
            ->with('success', 'Batch "' . $batchName . '" beserta seluruh data di dalamnya berhasil dihapus.');
    }

    /**
     * Tampilkan detail batch berdasarkan pollutant aktif.
     */
    public function show(Request $request, $id, $pollutant = 'dashboard')
    {
        $batch = RqBatch::findOrFail($id);
        $allowedPollutants = array_keys(RqAnalysis::$pollutantLabels);

        // Jika pollutant adalah dashboard (halaman utama ringkasan)
        if ($pollutant === 'dashboard') {
            $populationGroup = $request->input('population_group', 'all');

            // Bangun query dasar terfilter
            $baseQuery = RqAnalysis::where('rq_batch_id', $batch->id);
            if ($populationGroup === 'adult') {
                $baseQuery->where('umur', '>=', 18);
            } elseif ($populationGroup === 'child') {
                $baseQuery->where('umur', '<', 18);
            }

            // Hitung total responden berdasarkan jumlah baris logam terbanyak (agar nama duplikat tidak tereliminasi)
            $totalCount = (clone $baseQuery)
                ->groupBy('pollutant_type')
                ->selectRaw('count(*) as count')
                ->pluck('count')
                ->max() ?? 0;

            $metals = ['chromium', 'pb', 'nickel', 'arsenic', 'cd'];
            $summaryData = [];
            $trends = [];

            foreach ($metals as $metal) {
                $analyses = (clone $baseQuery)
                    ->where('pollutant_type', $metal)
                    ->get();

                $count = $analyses->count();
                $avgC = $count > 0 ? $analyses->avg('c') : 0;
                $avgRqRealtime = $count > 0 ? $analyses->avg('rq_realtime') : 0;
                $avgRq30th = $count > 0 ? $analyses->avg('rq_30th') : 0;

                // Persentase risiko (RQ > 1) lintas waktu
                $pctRealtime = $count > 0 ? ($analyses->where('rq_realtime', '>', 1)->count() / $count) * 100 : 0;
                $pct5th      = $count > 0 ? ($analyses->where('rq_5th', '>', 1)->count() / $count) * 100 : 0;
                $pct10th     = $count > 0 ? ($analyses->where('rq_10th', '>', 1)->count() / $count) * 100 : 0;
                $pct15th     = $count > 0 ? ($analyses->where('rq_15th', '>', 1)->count() / $count) * 100 : 0;
                $pct20th     = $count > 0 ? ($analyses->where('rq_20th', '>', 1)->count() / $count) * 100 : 0;
                $pct25th     = $count > 0 ? ($analyses->where('rq_25th', '>', 1)->count() / $count) * 100 : 0;
                $pct30th     = $count > 0 ? ($analyses->where('rq_30th', '>', 1)->count() / $count) * 100 : 0;

                $summaryData[$metal] = [
                    'count'             => $count,
                    'avg_c'             => $avgC,
                    'avg_rq_realtime'   => $avgRqRealtime,
                    'avg_rq_30th'       => $avgRq30th,
                    'risk_pct_realtime' => $pctRealtime,
                ];

                $trends[$metal] = [
                    round($pctRealtime, 2),
                    round($pct5th, 2),
                    round($pct10th, 2),
                    round($pct15th, 2),
                    round($pct20th, 2),
                    round($pct25th, 2),
                    round($pct30th, 2)
                ];
            }

            // Cari logam paling berbahaya
            $highestRiskMetal = 'Tidak Ada';
            $highestRiskPct = 0;
            foreach ($summaryData as $m => $data) {
                if ($data['risk_pct_realtime'] > $highestRiskPct) {
                    $highestRiskPct = $data['risk_pct_realtime'];
                    $highestRiskMetal = RqAnalysis::$pollutantLabels[$m];
                }
            }

            // Komposisi risiko batch secara umum (berisiko di salah satu logam pada realtime)
            $atRiskCount = (clone $baseQuery)
                ->where('rq_realtime', '>', 1)
                ->distinct()
                ->count('no_responden');
            
            $safeCount = max(0, $totalCount - $atRiskCount);

            return view('analisis.batch.show', compact(
                'batch',
                'pollutant',
                'totalCount',
                'atRiskCount',
                'safeCount',
                'highestRiskMetal',
                'highestRiskPct',
                'summaryData',
                'trends',
                'populationGroup'
            ));
        }

        // Validasi parameter logam
        if (!in_array($pollutant, $allowedPollutants)) {
            return redirect()->route('analisis.batch.show', [$batch->id, 'dashboard']);
        }

        $query = RqAnalysis::where('rq_batch_id', $batch->id)
            ->where('pollutant_type', $pollutant);

        // Filter Pencarian Responden (Case-Insensitive untuk PostgreSQL/MySQL/SQLite)
        if ($request->filled('search')) {
            $search = strtolower(trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"]);
                if (is_numeric($search)) {
                    $q->orWhere('no_responden', (int)$search);
                }
            });
        }

        // Filter Status Risiko (berdasarkan rq_realtime)
        if ($request->filled('risk_status')) {
            $status = $request->input('risk_status');
            if ($status === 'berisiko') {
                $query->where('rq_realtime', '>', 1);
            } elseif ($status === 'aman') {
                $query->where('rq_realtime', '<=', 1);
            }
        }

        // Ambil data analisis responden untuk batch dan polutan aktif
        $records = $query->orderBy('created_at', 'desc')->paginate(20);

        // Ambil data koordinat responden untuk pemetaan spasial Leaflet
        $mapRecords = RqAnalysis::where('rq_batch_id', $batch->id)
            ->where('pollutant_type', $pollutant)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['nama', 'no_responden', 'umur', 'wb', 'rq_realtime', 'latitude', 'longitude']);

        $rfdDefault = RqAnalysis::$rfdDefaults[$pollutant] ?? 1.0;

        return view('analisis.batch.show', compact('batch', 'records', 'pollutant', 'rfdDefault', 'mapRecords'));
    }

    /**
     * Simpan data responden baru secara manual di dalam batch.
     */
    public function storeManual(Request $request, $id)
    {
        $batch = RqBatch::findOrFail($id);

        $validated = $request->validate([
            'pollutant_type' => 'required|in:chromium,pb,nickel,arsenic,cd',
            'nama'           => 'required|string|max:255',
            'umur'           => 'required|numeric|min:0',
            'wb'             => 'required|numeric|min:0.1',
            'f'              => 'required|numeric|min:1',
            'c'              => 'required|numeric|min:0',
            'r'              => 'required|numeric|min:0',
            'rfd'            => 'nullable|numeric|min:0',
            'tavg'           => 'required|numeric|min:1',
            'dt_input'       => 'required|numeric|min:0.1',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        $pollutant = $validated['pollutant_type'];
        $rfd = $validated['rfd'] ?? RqAnalysis::$rfdDefaults[$pollutant] ?? 1.0;
        $validated['rfd'] = $rfd;

        $calculations = $this->rqService->calculate($validated);

        // Cari nomor responden berikutnya dalam batch & polutan yang sama
        $nextNo = RqAnalysis::where('rq_batch_id', $batch->id)
            ->where('pollutant_type', $pollutant)
            ->count() + 1;

        RqAnalysis::create(array_merge($validated, $calculations, [
            'rq_batch_id'  => $batch->id,
            'user_id'      => auth()->id() ?? 1,
            'source'       => 'manual',
            'no_responden' => $nextNo,
        ]));

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Input Manual Analisis',
            'details' => 'Menambahkan data responden secara manual untuk logam ' . $pollutant . ' pada batch ' . $batch->name
        ]);

        return redirect()
            ->route('analisis.batch.show', [$batch->id, $pollutant])
            ->with('success', 'Data responden berhasil disimpan secara manual.');
    }

    /**
     * Mengimpor data dari Excel/CSV ke dalam batch.
     */
    public function import(Request $request, $id)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $batch = RqBatch::findOrFail($id);

        $request->validate([
            'file'           => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'import_type'    => 'required|in:active_tab,all_sheets',
            'pollutant_type' => 'required_if:import_type,active_tab|in:chromium,pb,nickel,arsenic,cd',
        ]);

        $file = $request->file('file');
        $importType = $request->input('import_type');
        $activePollutant = $request->input('pollutant_type', 'chromium');

        try {
            if ($importType === 'all_sheets' && $file->getClientOriginalExtension() !== 'csv') {
                // Membaca daftar nama sheet secara cepat menggunakan PhpSpreadsheet
                $filePath = $file->getRealPath();
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                $sheetNames = $reader->listWorksheetNames($filePath);

                Excel::import(new RqMultiSheetImport($batch->id, $sheetNames), $file);
                
                ActivityLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'action'  => 'Import Master Excel Analisis',
                    'details' => 'Mengimpor seluruh sheet logam berat pada batch ' . $batch->name
                ]);

                return redirect()
                    ->route('analisis.batch.show', [$batch->id, 'chromium'])
                    ->with('success', 'File Master Excel berhasil di-import untuk semua sheet logam berat sekaligus.');
            } else {
                Excel::import(new RqAnalysisImport($activePollutant, $batch->id), $file);

                ActivityLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'action'  => 'Import Excel Analisis',
                    'details' => 'Mengimpor file data responden untuk logam ' . $activePollutant . ' pada batch ' . $batch->name
                ]);

                return redirect()
                    ->route('analisis.batch.show', [$batch->id, $activePollutant])
                    ->with('success', 'Data responden untuk logam aktif berhasil di-import.');
            }
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal memproses file. Pastikan struktur kolom atau format file sesuai dengan template: ' . $e->getMessage());
        }
    }

    /**
     * Edit baris responden individu.
     */
    public function editRecord($id)
    {
        $record = RqAnalysis::findOrFail($id);
        return view('analisis.batch.edit_record', compact('record'));
    }

    /**
     * Update baris responden individu.
     */
    public function updateRecord(Request $request, $id)
    {
        $record = RqAnalysis::findOrFail($id);

        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'umur'     => 'required|numeric|min:0',
            'wb'       => 'required|numeric|min:0.1',
            'f'        => 'required|numeric|min:1',
            'c'        => 'required|numeric|min:0',
            'r'        => 'required|numeric|min:0',
            'rfd'      => 'required|numeric|min:0',
            'tavg'     => 'required|numeric|min:1',
            'dt_input' => 'required|numeric|min:0.1',
        ]);

        $calculations = $this->rqService->calculate($validated);

        $record->update(array_merge($validated, $calculations));

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Update Analisis',
            'details' => 'Memperbarui data responden ' . $record->nama . ' untuk logam ' . $record->pollutant_type
        ]);

        return redirect()
            ->route('analisis.batch.show', [$record->rq_batch_id, $record->pollutant_type])
            ->with('success', 'Data responden berhasil diupdate.');
    }

    /**
     * Hapus baris responden individu.
     */
    public function destroyRecord($id)
    {
        $record = RqAnalysis::findOrFail($id);
        $batchId = $record->rq_batch_id;
        $pollutant = $record->pollutant_type;
        $name = $record->nama;

        $record->delete();

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'Hapus Analisis',
            'details' => 'Menghapus data responden ' . $name . ' untuk logam ' . $pollutant
        ]);

        return redirect()
            ->route('analisis.batch.show', [$batchId, $pollutant])
            ->with('success', 'Data responden "' . $name . '" berhasil dihapus.');
    }

}
