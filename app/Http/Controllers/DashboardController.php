<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Item;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. Total Sales (Current Month)
        $salesThisMonth = Invoice::where('type', 'sale')
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->sum('total_amount');

        // 2. Pending Purchases
        $pendingPurchases = Invoice::where('type', 'purchase_order')
            ->sum('total_amount');

        // 3. Inventory Value (Estimated by cost price)
        $inventoryValue = DB::table('inventory_stocks')
            ->join('items', 'items.id', '=', 'inventory_stocks.item_id')
            ->selectRaw('SUM(inventory_stocks.quantity * items.cost_price) as total_value')
            ->value('total_value') ?? 0;

        // 4. Cash/Bank Balance (Total of accounts of type Asset with code starting with '11' for cash/bank)
        $cashBalance = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->where('a.code', 'like', '11%') // Common pattern for cash/bank
            ->selectRaw('SUM(jel.debit) - SUM(jel.credit) as balance')
            ->value('balance') ?? 0;

        // 5. Recent Activities
        $recentInvoices = Invoice::with('contact')->latest()->take(5)->get()->map(function($inv) {
            return [
                'time' => $inv->created_at->diffForHumans(),
                'desc' => "تم إصدار " . $this->getTypeLabel($inv->type) . " رقم " . $inv->invoice_no . " لـ " . ($inv->contact->name ?? 'عميل'),
                'user' => 'النظام',
                'type' => in_array($inv->type, ['sale', 'sale_return']) ? 'sale' : 'purchase'
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => [
                'sales' => number_format($salesThisMonth, 2),
                'purchases' => number_format($pendingPurchases, 2),
                'inventory' => number_format($inventoryValue, 2),
                'cash' => number_format($cashBalance, 2),
            ],
            'recentActivities' => $recentInvoices
        ]);
    }

    private function getTypeLabel($type) {
        $labels = [
            'sale' => 'فاتورة مبيعات',
            'sale_return' => 'مردود مبيعات',
            'purchase' => 'فاتورة مشتريات',
            'purchase_return' => 'مردود مشتريات',
            'work_order' => 'أمر شغل',
        ];
        return $labels[$type] ?? $type;
    }
}
