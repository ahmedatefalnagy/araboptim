<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {        
        $fiscalYears = FiscalYear::orderByDesc('start_date')->get();
        
        $settings = [
            'company_name' => Setting::get('company_name', ''),
            'company_name_en' => Setting::get('company_name_en', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_fax' => Setting::get('company_fax', ''),
            'company_email' => Setting::get('company_email', ''),
            'company_vat_no' => Setting::get('company_vat_no', ''),
            'company_commercial_record' => Setting::get('company_commercial_record', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban' => Setting::get('iban', ''),
            'default_fiscal_year_id' => Setting::get('default_fiscal_year_id', ''),
            'enable_advances' => Setting::get('enable_advances', '1'),
            'enable_vouchers' => Setting::get('enable_vouchers', '1'),
            'enable_invoices' => Setting::get('enable_invoices', '1'),
            'enable_financial_statements' => Setting::get('enable_financial_statements', '1'),
        ];
        
        return Inertia::render('Settings/Index', [
            'auth' => ['user' => auth()->user()],
            'settings' => $settings,
            'fiscalYears' => $fiscalYears,
        ]);
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string',
            'company_name_en' => 'nullable|string',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string',
            'company_fax' => 'nullable|string',
            'company_email' => 'nullable|string',
            'company_vat_no' => 'nullable|string',
            'company_commercial_record' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'iban' => 'nullable|string',
            'default_fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'enable_advances' => 'nullable|string',
            'enable_vouchers' => 'nullable|string',
            'enable_invoices' => 'nullable|string',
            'enable_financial_statements' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }
        
        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }
}