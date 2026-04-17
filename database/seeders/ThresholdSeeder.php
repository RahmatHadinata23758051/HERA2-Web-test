<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Threshold;

class ThresholdSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Threshold::DEFAULTS as $key => $data) {
            Threshold::updateOrCreate(
                ['key' => $key],
                [
                    'value'      => $data['value'],
                    'unit'       => $data['unit'],
                    'label'      => $data['label'],
                    'updated_by' => null,
                ]
            );
        }
    }
}
