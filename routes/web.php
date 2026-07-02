<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\HRReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Journal\JournalEntryController;
use App\Http\Controllers\Journal\JournalImportController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['admin'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });


    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/next-code', [AccountController::class, 'getNextCode'])->name('accounts.next-code');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::prefix('fixed-assets')->name('fixed-assets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FixedAssetController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\FixedAssetController::class, 'store'])->name('store');
        Route::put('/{account}', [\App\Http\Controllers\FixedAssetController::class, 'update'])->name('update');
        Route::post('/depreciate', [\App\Http\Controllers\FixedAssetController::class, 'depreciate'])->name('depreciate');
    });

Route::prefix('journal')->name('journal.')->group(function () {
        Route::get('/entries/export', [JournalEntryController::class, 'exportExcel'])->name('entries.export');
        Route::get('/entries/template', [JournalImportController::class, 'downloadTemplate'])->name('entries.template');
        Route::post('/entries/import/preview', [JournalImportController::class, 'preview'])->name('entries.import.preview');
        Route::get('/entries/import/review', [JournalImportController::class, 'showReview'])->name('entries.import.review');
        Route::post('/entries/import/confirm', [JournalImportController::class, 'confirm'])->name('entries.import.confirm');
        Route::get('/entries', [JournalEntryController::class, 'index'])->name('entries.index');
        Route::get('/entries/create', [JournalEntryController::class, 'create'])->name('entries.create');
        Route::post('/entries', [JournalEntryController::class, 'store'])->name('entries.store');
        Route::get('/entries/{entry}', [JournalEntryController::class, 'show'])->name('entries.show');
        Route::get('/entries/{entry}/edit', [JournalEntryController::class, 'edit'])->name('entries.edit');
        Route::put('/entries/{entry}', [JournalEntryController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{entry}', [JournalEntryController::class, 'destroy'])->name('entries.destroy');
        Route::post('/entries/{entry}/post', [JournalEntryController::class, 'post'])->name('entries.post');
        Route::post('/entries/{entry}/unpost', [JournalEntryController::class, 'unpost'])->name('entries.unpost');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        
        Route::get('/ledger', [\App\Http\Controllers\ReportController::class, 'ledger'])->name('ledger');
        Route::get('/ledger/excel', [\App\Http\Controllers\ReportController::class, 'exportLedger'])->name('ledger.excel');
        Route::get('/ledger/pdf', [\App\Http\Controllers\ReportController::class, 'exportLedgerPdf'])->name('ledger.pdf');
        
        Route::get('/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('trialBalance');
        Route::get('/trial-balance/excel', [\App\Http\Controllers\ReportController::class, 'exportTrialBalance'])->name('trialBalance.excel');
        Route::get('/trial-balance/pdf', [\App\Http\Controllers\ReportController::class, 'exportTrialBalancePdf'])->name('trialBalance.pdf');
        
        Route::get('/income-statement', [\App\Http\Controllers\ReportController::class, 'incomeStatement'])->name('incomeStatement');
        Route::get('/income-statement/excel', [\App\Http\Controllers\ReportController::class, 'exportIncomeStatement'])->name('incomeStatement.excel');
        Route::get('/income-statement/pdf', [\App\Http\Controllers\ReportController::class, 'exportIncomeStatementPdf'])->name('incomeStatement.pdf');
        
        Route::get('/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('balanceSheet');
        Route::get('/balance-sheet/excel', [\App\Http\Controllers\ReportController::class, 'exportBalanceSheet'])->name('balanceSheet.excel');
        Route::get('/balance-sheet/pdf', [\App\Http\Controllers\ReportController::class, 'exportBalanceSheetPdf'])->name('balanceSheet.pdf');
        
        Route::get('/tax', [\App\Http\Controllers\ReportController::class, 'taxReport'])->name('tax');
        Route::get('/tax/excel', [\App\Http\Controllers\ReportController::class, 'exportTax'])->name('tax.excel');
        Route::get('/tax/pdf', [\App\Http\Controllers\ReportController::class, 'exportTaxPdf'])->name('tax.pdf');
        
        Route::get('/expenses', [\App\Http\Controllers\ReportController::class, 'expenses'])->name('expenses');
        Route::get('/expenses/excel', [\App\Http\Controllers\ReportController::class, 'exportExpenses'])->name('expenses.excel');
        Route::get('/expenses/pdf', [\App\Http\Controllers\ReportController::class, 'exportExpensesPdf'])->name('expenses.pdf');
        
        Route::get('/fixed-assets', [\App\Http\Controllers\ReportController::class, 'fixedAssets'])->name('fixedAssets');
        Route::get('/fixed-assets/excel', [\App\Http\Controllers\ReportController::class, 'exportFixedAssets'])->name('fixedAssets.excel');
        Route::get('/fixed-assets/pdf', [\App\Http\Controllers\ReportController::class, 'exportFixedAssetsPdf'])->name('fixedAssets.pdf');
        
        Route::get('/cost-center', [\App\Http\Controllers\ReportController::class, 'costCenterReport'])->name('costCenter');
        Route::get('/cost-center/excel', [\App\Http\Controllers\ReportController::class, 'exportCostCenter'])->name('costCenter.excel');
        Route::get('/cost-center/pdf', [\App\Http\Controllers\ReportController::class, 'exportCostCenterPdf'])->name('costCenter.pdf');
        Route::get('/cost-center-cashflow', [\App\Http\Controllers\ReportController::class, 'costCenterCashflowReport'])->name('costCenterCashflow');
        
        Route::get('/hr', [HRReportController::class, 'index'])->name('hr');
        Route::get('/hr/pdf', [HRReportController::class, 'exportPdf'])->name('hr.pdf');
        Route::get('/hr/excel', [HRReportController::class, 'exportExcel'])->name('hr.excel');
    });

    Route::resource('contacts', \App\Http\Controllers\ContactController::class);
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('items/quick-store', [\App\Http\Controllers\ItemController::class, 'quickStore'])->name('items.quick-store');
    Route::resource('items', \App\Http\Controllers\ItemController::class);
    Route::resource('item-categories', \App\Http\Controllers\ItemCategoryController::class);
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::get('vouchers/cash-register', [\App\Http\Controllers\VoucherController::class, 'cashRegister'])->name('vouchers.cash-register');
    Route::resource('vouchers', \App\Http\Controllers\VoucherController::class);
    Route::resource('cost-centers', \App\Http\Controllers\CostCenterController::class);
    Route::post('warehouses/quick-store', [\App\Http\Controllers\WarehouseController::class, 'quickStore'])->name('warehouses.quick-store');

    // HR Routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/employees', [\App\Http\Controllers\HRController::class, 'employees'])->name('employees');
        Route::post('/employees', [\App\Http\Controllers\HRController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}', [\App\Http\Controllers\HRController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [\App\Http\Controllers\HRController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::post('/employees/{employee}/toggle-status', [\App\Http\Controllers\HRController::class, 'toggleEmployeeStatus'])->name('employees.toggle-status');

        Route::get('/advances', [\App\Http\Controllers\HRController::class, 'advances'])->name('advances');
        Route::post('/advances', [\App\Http\Controllers\HRController::class, 'storeAdvance'])->name('advances.store');
        Route::get('/advances/{advance}/settlement', [\App\Http\Controllers\HRController::class, 'showSettlement'])->name('advances.settlement');
        Route::post('/advances/{advance}/settlement', [\App\Http\Controllers\HRController::class, 'processSettlement'])->name('advances.settlement.process');
        Route::put('/settlement/{settlement}', [\App\Http\Controllers\HRController::class, 'updateSettlement'])->name('advances.settlement.update');
        Route::delete('/settlement/{settlement}', [\App\Http\Controllers\HRController::class, 'destroySettlement'])->name('advances.settlement.destroy');
        Route::delete('/advances/{advance}', [\App\Http\Controllers\HRController::class, 'destroyAdvance'])->name('advances.destroy');
        Route::post('/advances/{advance}/convert-to-custody', [\App\Http\Controllers\HRController::class, 'convertToCustody'])->name('advances.convert-to-custody');
        Route::post('/advances/{advance}/deduct-from-salary', [\App\Http\Controllers\HRController::class, 'deductFromSalary'])->name('advances.deduct-from-salary');

        Route::get('/payroll', [\App\Http\Controllers\HRController::class, 'payroll'])->name('payroll');
        Route::post('/payroll', [\App\Http\Controllers\HRController::class, 'storePayroll'])->name('payroll.store');

        Route::get('/government-expenses', [\App\Http\Controllers\HRController::class, 'governmentExpenses'])->name('government-expenses');
        Route::post('/government-expenses', [\App\Http\Controllers\HRController::class, 'storeGovernmentExpense'])->name('government-expenses.store');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::delete('/', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('destroy');
    });

    // Logistics & Fleet Management
    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Logistics\LogisticsDashboardController::class, 'index'])->name('dashboard');
        
        // Workshop & Maintenance
        Route::prefix('workshop')->name('workshop.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Logistics\WorkshopController::class, 'index'])->name('index');
            Route::get('/orders', [\App\Http\Controllers\Logistics\WorkshopController::class, 'orders'])->name('orders');
            Route::post('/orders', [\App\Http\Controllers\Logistics\WorkshopController::class, 'storeOrder'])->name('orders.store');
            Route::post('/orders/{order}/add-part', [\App\Http\Controllers\Logistics\WorkshopController::class, 'addPart'])->name('orders.addPart');
            Route::post('/orders/{order}/complete', [\App\Http\Controllers\Logistics\WorkshopController::class, 'completeOrder'])->name('orders.complete');
            
            Route::get('/tires', [\App\Http\Controllers\Logistics\WorkshopController::class, 'tires'])->name('tires');
            Route::post('/tires', [\App\Http\Controllers\Logistics\WorkshopController::class, 'storeTire'])->name('tires.store');
        });

        // Driver Portal
        Route::prefix('driver')->name('driver.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Logistics\DriverPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/trips', [\App\Http\Controllers\Logistics\DriverPortalController::class, 'trips'])->name('trips');
        });

        Route::get('vehicles/assignments', [\App\Http\Controllers\Logistics\VehicleController::class, 'assignments'])->name('vehicles.assignments');
        Route::post('vehicles/{vehicle}/assignment', [\App\Http\Controllers\Logistics\VehicleController::class, 'updateAssignment'])->name('vehicles.updateAssignment');
        Route::resource('vehicles', \App\Http\Controllers\Logistics\VehicleController::class);
        Route::resource('routes', \App\Http\Controllers\Logistics\TripRouteController::class);
        Route::get('trips/monthly-billing', [\App\Http\Controllers\Logistics\TripController::class, 'monthlyBilling'])->name('trips.monthly-billing');
        Route::post('trips/generate-monthly-invoice', [\App\Http\Controllers\Logistics\TripController::class, 'generateMonthlyInvoice'])->name('trips.generate-monthly-invoice');
        Route::post('trips/quick-store-supplier', [\App\Http\Controllers\Logistics\TripController::class, 'quickStoreSupplier'])->name('trips.quick-store-supplier');
        Route::post('trips/quick-store-subclient', [\App\Http\Controllers\Logistics\TripController::class, 'quickStoreSubClient'])->name('trips.quick-store-subclient');
        Route::post('trips/quick-store-maincompany', [\App\Http\Controllers\Logistics\TripController::class, 'quickStoreMainCompany'])->name('trips.quick-store-maincompany');

        Route::resource('trips', \App\Http\Controllers\Logistics\TripController::class);
        Route::post('trips/{trip}/status', [\App\Http\Controllers\Logistics\TripController::class, 'updateStatus'])->name('trips.status');
        Route::post('trips/{trip}/stops', [\App\Http\Controllers\Logistics\TripController::class, 'addStop'])->name('trips.stops.add');
        Route::post('trips/{trip}/events', [\App\Http\Controllers\Logistics\TripController::class, 'addEvent'])->name('trips.events');
        Route::post('trips/{trip}/diesel', [\App\Http\Controllers\Logistics\TripController::class, 'addDiesel'])->name('trips.diesel');
        
        // Reports
        Route::get('reports/driver-statement', [\App\Http\Controllers\Logistics\DriverReportController::class, 'statement'])->name('reports.driver-statement');
        Route::get('reports/trips', [\App\Http\Controllers\Logistics\TripReportController::class, 'index'])->name('reports.trips');
    });

    // AI Assistant
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/chat', [\App\Http\Controllers\AiController::class, 'index'])->name('chat');
        Route::post('/chat', [\App\Http\Controllers\AiController::class, 'chat'])->name('chat.process');
    });
});

Route::middleware('auth')->group(function () {
    // PDF Exports
    Route::get('/invoices/{invoice}/pdf', [PdfController::class, 'invoice'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/delivery-note', [PdfController::class, 'deliveryNote'])->name('invoices.delivery-note');
    Route::get('/invoices/{invoice}/grn', [PdfController::class, 'grn'])->name('invoices.grn');
    Route::get('/vouchers/{voucher}/pdf', [PdfController::class, 'voucher'])->name('vouchers.pdf');
});

require __DIR__.'/auth.php';