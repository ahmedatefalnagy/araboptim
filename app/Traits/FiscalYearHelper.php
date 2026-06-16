<?php

namespace App\Traits;

use App\Models\Setting;
use App\Models\FiscalYear;

trait FiscalYearHelper
{
    protected function getDefaultFiscalYear(): ?FiscalYear
    {
        $defaultYearId = Setting::get('default_fiscal_year_id');
        return FiscalYear::find($defaultYearId);
    }

    protected function getFiscalYearPeriod(): array
    {
        $year = $this->getDefaultFiscalYear();
        
        if ($year) {
            return [
                'start_date' => $year->start_date->format('Y-m-d'),
                'end_date' => $year->end_date->format('Y-m-d'),
            ];
        }
        
        return [
            'start_date' => date('Y-01-01'),
            'end_date' => date('Y-12-31'),
        ];
    }

    protected function getAllFiscalYears(): \Illuminate\Database\Eloquent\Collection
    {
        return FiscalYear::orderByDesc('start_date')->get(['id', 'name', 'start_date', 'end_date']);
    }
}