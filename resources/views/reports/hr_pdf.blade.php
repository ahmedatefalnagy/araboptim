<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>التقرير المالي للكوادر البشرية</title>
    <style>
        @page { margin: 20px; }
        * { font-family: 'DejaVu Sans', sans-serif !important; }
        body { 
            direction: rtl; 
            background-color: #fff; 
            margin: 0; 
            padding: 0; 
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #1e293b; padding-bottom: 10px; }
        .company-name { font-size: 22px; font-weight: bold; color: #1e293b; }
        .report-title { font-size: 18px; color: #475569; margin-top: 5px; }
        .filter-info { font-size: 11px; color: #64748b; margin-top: 5px; }
        
        .stats-grid { width: 100%; margin-bottom: 25px; }
        .stat-card { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; text-align: center; border-radius: 8px; }
        .stat-label { font-size: 10px; color: #64748b; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; }
        .stat-value { font-size: 15px; font-weight: bold; color: #0f172a; }

        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; margin-bottom: 10px; padding-right: 5px; border-right: 4px solid #1e293b; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; table-layout: fixed; direction: rtl; }
        th { background-color: #f1f5f9; color: #475569; font-size: 11px; font-weight: bold; padding: 10px 5px; border: 1px solid #e2e8f0; text-align: right; }
        td { font-size: 10px; color: #1e293b; padding: 8px 5px; border: 1px solid #e2e8f0; text-align: right; word-wrap: break-word; }
        .text-right { text-align: right; padding-right: 10px; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f8fafc; }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-orange { background-color: #ffedd5; color: #9a3412; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $companyName }}</div>
        <div class="report-title">{{ \App\Helpers\ArabicHelper::shape('تقرير تحليل الموارد البشرية والعهد') }}</div>
        <div class="filter-info">
            {{ \App\Helpers\ArabicHelper::shape('الفترة من') }}: {{ $filters['start_date'] }} {{ \App\Helpers\ArabicHelper::shape('إلى') }}: {{ $filters['end_date'] }} 
            | {{ \App\Helpers\ArabicHelper::shape('تم الاستخراج في') }}: {{ date('Y-m-d H:i') }}
        </div>
    </div>

    <table class="stats-grid">
        <tr>
            <td class="stat-card">
                <div class="stat-label">{{ \App\Helpers\ArabicHelper::shape('إجمالي الرواتب') }}</div>
                <div class="stat-value">{{ number_format($totals['salaries'], 2) }}</div>
            </td>
            <td style="width: 10px; border: none;"></td>
            <td class="stat-card">
                <div class="stat-label">{{ \App\Helpers\ArabicHelper::shape('أرصدة السلف') }}</div>
                <div class="stat-value">{{ number_format($totals['advances_remaining'], 2) }}</div>
            </td>
            <td style="width: 10px; border: none;"></td>
            <td class="stat-card">
                <div class="stat-label">{{ \App\Helpers\ArabicHelper::shape('أرصدة العهد') }}</div>
                <div class="stat-value">{{ number_format($totals['custodies_remaining'], 2) }}</div>
            </td>
            <td style="width: 10px; border: none;"></td>
            <td class="stat-card">
                <div class="stat-label">{{ \App\Helpers\ArabicHelper::shape('المكافآت') }}</div>
                <div class="stat-value">{{ number_format($totals['bonuses'], 2) }}</div>
            </td>
            <td style="width: 10px; border: none;"></td>
            <td class="stat-card">
                <div class="stat-label">{{ \App\Helpers\ArabicHelper::shape('مدفوعات الفترة') }}</div>
                <div class="stat-value">{{ number_format($totals['payroll_period'], 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">{{ \App\Helpers\ArabicHelper::shape('ملخص أرصدة الموظفين') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">{{ \App\Helpers\ArabicHelper::shape('الموظف') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('الراتب') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('رصيد السلف') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('رصيد العهد') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('إجمالي المكافآت') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('صافي المدفوع') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
            <tr>
                <td class="text-right font-bold">{{ $row['name'] }}</td>
                <td>{{ number_format($row['basic_salary'], 2) }}</td>
                <td class="font-bold" style="color: #9a3412">{{ number_format($row['advances']['remaining'], 2) }}</td>
                <td class="font-bold" style="color: #166534">{{ number_format($row['custodies']['remaining'], 2) }}</td>
                <td>{{ number_format($row['bonuses']['total'], 2) }}</td>
                <td class="font-bold bg-gray">{{ number_format($row['total_payroll_period'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ \App\Helpers\ArabicHelper::shape('سجل الحركات التفصيلي') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">{{ \App\Helpers\ArabicHelper::shape('التاريخ') }}</th>
                <th style="width: 20%;">{{ \App\Helpers\ArabicHelper::shape('الموظف') }}</th>
                <th style="width: 10%;">{{ \App\Helpers\ArabicHelper::shape('النوع') }}</th>
                <th style="width: 20%;">{{ \App\Helpers\ArabicHelper::shape('البيان / الغرض') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('المبلغ') }}</th>
                <th>{{ \App\Helpers\ArabicHelper::shape('المتبقي') }}</th>
                <th style="width: 20%;">{{ \App\Helpers\ArabicHelper::shape('الملاحظات') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $tx)
            <tr>
                <td style="color: #64748b">{{ $tx['date'] }}</td>
                <td class="text-right font-bold">{{ $tx['employee_name'] }}</td>
                <td>
                    @if($tx['type'] == 'advance') {{ \App\Helpers\ArabicHelper::shape('سلفة') }}
                    @elseif($tx['type'] == 'custody') {{ \App\Helpers\ArabicHelper::shape('عهدة') }}
                    @else {{ \App\Helpers\ArabicHelper::shape('مكافأة') }} @endif
                </td>
                <td class="text-right">{{ $tx['purpose'] }}</td>
                <td class="font-bold">{{ number_format($tx['amount'], 2) }}</td>
                <td style="color: #94a3b8">{{ number_format($tx['remaining'], 2) }}</td>
                <td class="text-right" style="font-size: 9px; color: #475569 italic;">{{ $tx['notes'] ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ \App\Helpers\ArabicHelper::shape('هذا التقرير تم توليده آلياً من النظام المحاسبي') }} - {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
