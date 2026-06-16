<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign keys to clear everything safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate transaction tables and accounts to start fresh
        DB::table('journal_entry_lines')->truncate();
        DB::table('journal_entries')->truncate();
        DB::table('vouchers')->truncate();
        DB::table('opening_balances')->truncate();
        DB::table('invoice_lines')->truncate();
        DB::table('invoices')->truncate();
        DB::table('contacts')->truncate();
        DB::table('accounts')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $asset = AccountType::where('code', 'asset')->firstOrFail()->id;
        $liability = AccountType::where('code', 'liability')->firstOrFail()->id;
        $equity = AccountType::where('code', 'equity')->firstOrFail()->id;
        $revenue = AccountType::where('code', 'revenue')->firstOrFail()->id;
        $expense = AccountType::where('code', 'expense')->firstOrFail()->id;

        $accountsData = [
            // 1. Assets / الأصول
            ['code' => '1', 'name' => 'الأصول', 'type' => $asset, 'parent' => null, 'postable' => false],
            ['code' => '11', 'name' => 'الأصول المتداولة', 'type' => $asset, 'parent' => '1', 'postable' => false],
            ['code' => '1101', 'name' => 'الصندوق', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1102', 'name' => 'البنك', 'type' => $asset, 'parent' => '11', 'postable' => false],
            ['code' => '110201', 'name' => 'بنك الراجحي الرئيسي', 'type' => $asset, 'parent' => '1102', 'postable' => true],
            ['code' => '110202', 'name' => 'بنك الراجحي الإدارة', 'type' => $asset, 'parent' => '1102', 'postable' => true],
            ['code' => '110203', 'name' => 'بنك الراجحي المشتريات', 'type' => $asset, 'parent' => '1102', 'postable' => true],
            ['code' => '1103', 'name' => 'العملاء (ذمم مدينة)', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1104', 'name' => 'المخزون', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1105', 'name' => 'مصروفات مدفوعة مقدماً', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1106', 'name' => 'عهد وسلف الموظفين', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1107', 'name' => 'ضريبة القيمة المضافة مدينة (مدخلات)', 'type' => $asset, 'parent' => '11', 'postable' => true],
            ['code' => '1108', 'name' => 'أوراق القبض', 'type' => $asset, 'parent' => '11', 'postable' => true],
            
            ['code' => '12', 'name' => 'الأصول غير المتداولة', 'type' => $asset, 'parent' => '1', 'postable' => false],
            ['code' => '1201', 'name' => 'الأصول الثابتة', 'type' => $asset, 'parent' => '12', 'postable' => false],
            ['code' => '120101', 'name' => 'سيارات', 'type' => $asset, 'parent' => '1201', 'postable' => true],
            ['code' => '120102', 'name' => 'أجهزة ومعدات', 'type' => $asset, 'parent' => '1201', 'postable' => true],
            ['code' => '120103', 'name' => 'أثاث', 'type' => $asset, 'parent' => '1201', 'postable' => true],
            ['code' => '1202', 'name' => 'مجمع الإهلاك', 'type' => $asset, 'parent' => '12', 'postable' => true],
            ['code' => '1203', 'name' => 'أصول غير ملموسة', 'type' => $asset, 'parent' => '12', 'postable' => false],
            ['code' => '120301', 'name' => 'برامج وأنظمة', 'type' => $asset, 'parent' => '1203', 'postable' => true],

            // 2. Liabilities / الخصوم
            ['code' => '2', 'name' => 'الخصوم', 'type' => $liability, 'parent' => null, 'postable' => false],
            ['code' => '21', 'name' => 'الخصوم المتداولة', 'type' => $liability, 'parent' => '2', 'postable' => false],
            ['code' => '2101', 'name' => 'الموردون', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2102', 'name' => 'دائنون متنوعون', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2103', 'name' => 'ضريبة القيمة المضافة مستحقة (مخرجات)', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2104', 'name' => 'رواتب وأجور مستحقة', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2105', 'name' => 'مصروفات مستحقة', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2106', 'name' => 'إيرادات مقدمة (سلف العملاء)', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2107', 'name' => 'أوراق الدفع', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '2108', 'name' => 'الجزء المتداول من القروض', 'type' => $liability, 'parent' => '21', 'postable' => true],
            ['code' => '22', 'name' => 'الخصوم غير المتداولة', 'type' => $liability, 'parent' => '2', 'postable' => false],
            ['code' => '2201', 'name' => 'قروض طويلة الأجل', 'type' => $liability, 'parent' => '22', 'postable' => true],
            ['code' => '2202', 'name' => 'التزامات طويلة الأجل أخرى', 'type' => $liability, 'parent' => '22', 'postable' => true],

            // 3. Equity / حقوق الملكية
            ['code' => '3', 'name' => 'حقوق الملكية', 'type' => $equity, 'parent' => null, 'postable' => false],
            ['code' => '3101', 'name' => 'رأس المال', 'type' => $equity, 'parent' => '3', 'postable' => true],
            ['code' => '3201', 'name' => 'الأرباح المبقاة', 'type' => $equity, 'parent' => '3', 'postable' => true],
            ['code' => '3301', 'name' => 'مسحوبات الشركاء', 'type' => $equity, 'parent' => '3', 'postable' => true],
            ['code' => '3401', 'name' => 'احتياطي نظامي', 'type' => $equity, 'parent' => '3', 'postable' => true],
            ['code' => '3402', 'name' => 'احتياطي اختياري', 'type' => $equity, 'parent' => '3', 'postable' => true],

            // 4. Revenues / الإيرادات
            ['code' => '4', 'name' => 'الإيرادات', 'type' => $revenue, 'parent' => null, 'postable' => false],
            ['code' => '4101', 'name' => 'المبيعات', 'type' => $revenue, 'parent' => '4', 'postable' => true],
            ['code' => '4102', 'name' => 'مبيعات خدمات', 'type' => $revenue, 'parent' => '4', 'postable' => true],
            ['code' => '4103', 'name' => 'خصومات المبيعات', 'type' => $revenue, 'parent' => '4', 'postable' => true],
            ['code' => '4201', 'name' => 'إيرادات أخرى', 'type' => $revenue, 'parent' => '4', 'postable' => true],
            ['code' => '4202', 'name' => 'فروقات أسعار', 'type' => $revenue, 'parent' => '4', 'postable' => true],

            // 5. Expenses / المصروفات
            ['code' => '5', 'name' => 'المصروفات', 'type' => $expense, 'parent' => null, 'postable' => false],
            ['code' => '5101', 'name' => 'تكلفة البضاعة المباعة', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5102', 'name' => 'رواتب وأجور', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5103', 'name' => 'إيجارات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5104', 'name' => 'كهرباء ومياه', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5105', 'name' => 'إنترنت واتصالات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5106', 'name' => 'محروقات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5107', 'name' => 'صيانة وإصلاحات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5108', 'name' => 'نقل وشحن', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5109', 'name' => 'ضيافة وسفر', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5201', 'name' => 'مصاريف مكتبية', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5202', 'name' => 'رسوم حكومية', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5203', 'name' => 'رسوم بنكية', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5204', 'name' => 'استشارات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5301', 'name' => 'إهلاك سيارات', 'type' => $expense, 'parent' => '5', 'postable' => true],
            ['code' => '5302', 'name' => 'إهلاك أجهزة ومعدات', 'type' => $expense, 'parent' => '5', 'postable' => true],
        ];

        // We build the tree by resolving parent IDs
        $insertedAccounts = [];

        foreach ($accountsData as $data) {
            $parentId = null;
            if ($data['parent'] !== null) {
                $parentId = $insertedAccounts[$data['parent']] ?? null;
            }

            $len = strlen($data['code']);
            $level = $len <= 2 ? $len : ($len / 2) + 1;

            $account = Account::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'account_type_id' => $data['type'],
                'parent_id' => $parentId,
                'level' => $level,
                'is_postable' => $data['postable'],
                'is_active' => true,
            ]);

            $insertedAccounts[$data['code']] = $account->id;
        }
    }
}