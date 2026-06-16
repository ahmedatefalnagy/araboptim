<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $defaultDate = date('Y-m-d');
        $activeYearId = \App\Models\Setting::get('default_fiscal_year_id');
        if ($activeYearId) {
            $year = \App\Models\FiscalYear::find($activeYearId);
            if ($year) {
                // If current date is outside the fiscal year, use the fiscal year start date
                $currentDate = now();
                if ($currentDate->lt($year->start_date) || $currentDate->gt($year->end_date)) {
                    $defaultDate = $year->start_date->format('Y-m-d');
                }
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'default_date' => $defaultDate,
            'active_fiscal_year_id' => $activeYearId,
            'settings' => [
                'company_name' => \App\Models\Setting::get('company_name', 'مؤسسة عرب أوبتيما للتجارة'),
                'company_address' => \App\Models\Setting::get('company_address', 'الرياض - حي المروج - طريق الملك فهد'),
                'company_phone' => \App\Models\Setting::get('company_phone', '0500000000'),
                'company_vat_no' => \App\Models\Setting::get('company_vat_no', '300000000000003'),
                'company_commercial_record' => \App\Models\Setting::get('company_commercial_record', ''),
            ],
        ];
    }
}
