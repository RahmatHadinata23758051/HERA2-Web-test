<?php

namespace App\Exports;

use App\Models\RqAnalysis;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RqAnalysisExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function collection()
    {
        return RqAnalysis::ofType($this->type)->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            // Responden
            'No Responden',
            'Nama',
            'Umur',
            'Wb',
            
            // Variabel
            'f',
            'C',
            'R',
            'RfD',
            'tavg',
            'Dt Realtime',

            // Intake (Kalkulasi)
            'Intake Realtime',
            'Intake 5th',
            'Intake 10th',
            'Intake 15th',
            'Intake 20th',
            'Intake 25th',
            'Intake 30th',

            // RQ (Kalkulasi)
            'RQ Realtime',
            'RQ 5th',
            'RQ 10th',
            'RQ 15th',
            'RQ 20th',
            'RQ 25th',
            'RQ 30th',
            
            // Meta
            'Sistem Tanggal Input',
        ];
    }

    public function map($row): array
    {
        return [
            $row->no_responden,
            $row->nama,
            $row->umur,
            $row->wb,
            
            $row->f,
            $row->c,
            $row->r,
            $row->rfd,
            $row->tavg,
            $row->dt_input,

            $row->intake_realtime,
            $row->intake_5th,
            $row->intake_10th,
            $row->intake_15th,
            $row->intake_20th,
            $row->intake_25th,
            $row->intake_30th,

            $row->rq_realtime,
            $row->rq_5th,
            $row->rq_10th,
            $row->rq_15th,
            $row->rq_20th,
            $row->rq_25th,
            $row->rq_30th,

            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
