@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('تقرير كشف حساب مركز التكلفة'))
@section('doc_title_en', 'COST CENTER STATEMENT REPORT')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

@if(!empty($lines) && count($lines) > 0)
@php
    $revenue = 0;
    $expenses = 0;
    $purchases = 0;
    $custodyIn = 0;
    $custodyOut = 0;
    $contractors = 0;

    foreach($lines as $line) {
        $code = (string)($line['account_code'] ?? '');
        $name = (string)($line['account_name'] ?? '');
        $desc = (string)($line['description'] ?? '');
        $contact = (string)($line['contact_name'] ?? '');

        $debit = (float)($line['debit'] ?? 0);
        $credit = (float)($line['credit'] ?? 0);

        // Check bank/safe/custody
        $isBankOrCash = str_starts_with($code, '1101') || str_starts_with($code, '1102') || str_contains($name, 'بنك') || str_contains($name, 'الراجحي') || str_contains($name, 'الرياض') || str_contains($name, 'صندوق') || str_contains($name, 'خزينة') || str_contains($name, 'خزنه');
        $isSafeOrCustody = str_starts_with($code, '1106') || str_contains($name, 'عهدة') || str_contains($name, 'عهده') || str_contains($name, 'سلفة') || str_contains($name, 'سلفه');
        $isContractor = !$isBankOrCash && !$isSafeOrCustody && (str_contains($contact, 'مقاول') || str_contains($name, 'مقاول') || str_contains($desc, 'مقاول'));

        if ($isBankOrCash) {
            continue;
        }

        if ($isSafeOrCustody) {
            $custodyIn += $debit;
            $custodyOut += $credit;
            continue;
        }

        if ((str_starts_with($code, '4') || str_starts_with($code, '1103')) && !$isContractor) {
            $revenue += ($credit - $debit);
        } else if ($isContractor) {
            $contractors += ($debit - $credit);
        } else if (str_starts_with($code, '5')) {
            $expenses += ($debit - $credit);
        } else if (str_starts_with($code, '3')) {
            $purchases += ($debit - $credit);
        } else {
            if ($debit > 0) {
                $expenses += $debit;
            }
            if ($credit > 0) {
                $revenue += $credit;
            }
        }
    }

    $custodyRemaining = max(0, $custodyIn - $custodyOut);
    $remaining = $revenue - ($expenses + $purchases + $contractors);
@endphp

<table class="data-grid">
    <tr>
        <td class="data-card" width="50%" style="text-align: center">
            <div class="card-label">{{ fx('مركز التكلفة / COST CENTER') }}</div>
            <div class="card-value">{{ $costCenter['code'] }} - {{ $costCenter['name'] }}</div>
        </td>
        <td class="data-card" width="25%" style="text-align: center">
            <div class="card-label">{{ fx('الفترة / PERIOD') }}</div>
            <div class="card-value" style="font-size: 8px;">{{ $startDate }} {{ fx('إلى') }} {{ $endDate }}</div>
        </td>
        <td class="data-card" width="25%" style="text-align: center">
            <div class="card-label">{{ fx('الرصيد السابق / OPENING') }}</div>
            <div class="card-value">{{ number_format($openingBalance, 2) }}</div>
        </td>
    </tr>
</table>

<table class="data-grid" style="margin-top: 10px;">
    <tr>
        <td class="data-card" width="20%" style="text-align: center; background-color: #f6fff9; border: 1px solid #cbfbd5; padding: 6px;">
            <div class="card-label" style="color: #0b7026; font-size: 7px; margin-bottom: 2px;">{{ fx('إجمالي الإيرادات / REVENUE') }}</div>
            <div class="card-value" style="color: #0b7026; font-size: 11px;">{{ number_format($revenue, 2) }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center; background-color: #fff5f5; border: 1px solid #ffd8d8; padding: 6px;">
            <div class="card-label" style="color: #b91c1c; font-size: 7px; margin-bottom: 2px;">{{ fx('المصروفات والمشتريات / EXP') }}</div>
            <div class="card-value" style="color: #b91c1c; font-size: 11px;">{{ number_format($expenses + $purchases, 2) }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center; background-color: #f0fdfa; border: 1px solid #ccfbf1; padding: 6px;">
            <div class="card-label" style="color: #0f766e; font-size: 7px; margin-bottom: 2px;">{{ fx('العهد المالية / CUSTODY') }}</div>
            <div class="card-value" style="color: #0f766e; font-size: 11px;">{{ number_format($custodyRemaining, 2) }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center; background-color: #fffbeb; border: 1px solid #fef3c7; padding: 6px;">
            <div class="card-label" style="color: #b45309; font-size: 7px; margin-bottom: 2px;">{{ fx('دفعات المقاولين / CONTR') }}</div>
            <div class="card-value" style="color: #b45309; font-size: 11px;">{{ number_format($contractors, 2) }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center; background-color: #f5f3ff; border: 1px solid #ddd6fe; padding: 6px;">
            <div class="card-label" style="color: #4c1d95; font-size: 7px; margin-bottom: 2px;">{{ fx('المتبقي للمشروع / NET') }}</div>
            <div class="card-value" style="color: #4c1d95; font-size: 11px; font-weight: bold;">{{ number_format($remaining, 2) }}</div>
        </td>
    </tr>
</table>

@php
    $cashFlows = [];
    foreach($lines as $line) {
        $code = (string)($line['account_code'] ?? '');
        $name = (string)($line['account_name'] ?? '');
        $desc = (string)($line['description'] ?? '');
        $contact = (string)($line['contact_name'] ?? '');

        $debit = (float)($line['debit'] ?? 0);
        $credit = (float)($line['credit'] ?? 0);

        $isBankOrCash = str_starts_with($code, '1101') || str_starts_with($code, '1102') || 
                        str_contains($name, 'بنك') || str_contains($name, 'الراجحي') || str_contains($name, 'الرياض') || 
                        str_contains($name, 'صندوق') || str_contains($name, 'خزينة') || str_contains($name, 'خزنه');

        if ($isBankOrCash) {
            continue;
        }

        $flowType = 'outgoing';
        $categoryText = 'مصروف تشغيلي';
        $amount = $debit - $credit;

        $isContractor = str_contains($contact, 'مقاول') || str_contains($name, 'مقاول') || str_contains($desc, 'مقاول');

        if ((str_starts_with($code, '4') || str_starts_with($code, '1103')) && !$isContractor) {
            $flowType = 'incoming';
            $categoryText = 'إيراد من عميل';
            $amount = $credit - $debit;
        } else if ($isContractor) {
            $categoryText = 'دفعات مقاولين';
            $amount = $debit - $credit;
        } else if (str_starts_with($code, '1106') || str_contains($name, 'عهدة') || str_contains($name, 'عهده') || str_contains($name, 'سلفة') || str_contains($name, 'سلفه') || str_contains($desc, 'عهدة') || str_contains($desc, 'عهده')) {
            $categoryText = 'عهدة موظفين / عهد';
            $amount = $debit - $credit;
        } else if (str_starts_with($code, '3')) {
            $categoryText = 'مشتريات مباشرة';
            $amount = $debit - $credit;
        } else if (str_starts_with($code, '5')) {
            $categoryText = 'مصروف إداري / تشغيلي';
            $amount = $debit - $credit;
        } else {
            if ($credit > $debit) {
                $flowType = 'incoming';
                $categoryText = 'إيراد آخر';
                $amount = $credit - $debit;
            } else {
                $amount = $debit - $credit;
            }
        }

        $cashFlows[] = [
            'date' => $line['date'],
            'entry_no' => $line['entry_no'],
            'flowType' => $flowType,
            'categoryText' => \App\Helpers\PdfHelper::fixArabic($categoryText),
            'amount' => $amount,
            'account' => $code . ' - ' . $name,
            'payee' => $contact ?: ($name ?: 'الصندوق / البنك الدولي للمشروع'),
            'reason' => $desc
        ];
    }
@endphp

<h3 style="font-family: 'Cairo', sans-serif; font-size: 10px; margin-top: 15px; margin-bottom: 5px; color: #004a99; border-bottom: 2px solid #004a99; padding-bottom: 3px;">
    {{ fx('تقرير حركة المشروع التفصيلي (الوارد والمنصرف) / PROJECT CASH FLOW STATEMENT') }}
</h3>
<table class="premium-table" style="margin-bottom: 20px;">
    <thead>
        <tr>
            <th width="12%">{{ fx('التاريخ والبيان') }}<br>DATE</th>
            <th width="18%">{{ fx('نوع الحركة') }}<br>TYPE</th>
            <th width="20%">{{ fx('الحساب المالي') }}<br>ACCOUNT</th>
            <th width="20%">{{ fx('الجهة المستلمة') }}<br>PAYEE</th>
            <th width="20%">{{ fx('السبب والبيان') }}<br>REASON</th>
            <th width="10%">{{ fx('المبلغ') }}<br>AMOUNT</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cashFlows as $flow)
        <tr>
            <td style="font-size: 8px;">{{ date('Y-m-d', strtotime($flow['date'])) }}<br><span style="color: #888;">#{{ $flow['entry_no'] }}</span></td>
            <td style="font-size: 8px; font-weight: bold; color: {{ $flow['flowType'] === 'incoming' ? '#0b7026' : '#b91c1c' }}">
                {{ $flow['flowType'] === 'incoming' ? fx('وارد (+)') : fx('منصرف (-)') }} - {{ $flow['categoryText'] }}
            </td>
            <td class="text-right" style="font-size: 8px;">{{ fx($flow['account']) }}</td>
            <td class="text-right" style="font-size: 8px; font-weight: bold;">{{ fx($flow['payee']) }}</td>
            <td class="text-right" style="font-size: 8px;">{{ fx($flow['reason']) }}</td>
            <td style="font-weight: bold; text-align: left;">{{ number_format($flow['amount'], 2) }}</td>
        </tr>
        @endforeach
        @if(count($cashFlows) === 0)
        <tr>
            <td colspan="6" class="text-center" style="padding: 10px; color: #888;">{{ fx('لا توجد عمليات حركة نقدية للفترة المحددة') }}</td>
        </tr>
        @endif
    </tbody>
</table>

<h3 style="font-family: 'Cairo', sans-serif; font-size: 10px; margin-top: 15px; margin-bottom: 5px; color: #333; border-bottom: 2px solid #333; padding-bottom: 3px;">
    {{ fx('دفتر الأستاذ والعمليات التفصيلية (مدين ودائن) / DETAILED LEDGER STATEMENT') }}
</h3>
<table class="premium-table">
    <thead>
        <tr>
            <th width="10%">{{ fx('التاريخ') }}<br>DATE</th>
            <th width="10%">{{ fx('المرجع') }}<br>REF</th>
            <th width="12%">{{ fx('رمز الحساب') }}<br>ACC CODE</th>
            <th width="18%">{{ fx('اسم الحساب') }}<br>ACC NAME</th>
            <th width="22%">{{ fx('البيان') }}<br>DESCRIPTION</th>
            <th width="10%">{{ fx('مدين') }}<br>DEBIT</th>
            <th width="10%">{{ fx('دائن') }}<br>CREDIT</th>
            <th width="12%">{{ fx('الرصيد') }}<br>BALANCE</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $runningBalance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;
        @endphp
        @foreach($lines as $line)
            @php
                $debit = (float)($line['debit'] ?? 0);
                $credit = (float)($line['credit'] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;
                $runningBalance += $debit - $credit;
            @endphp
            <tr>
                <td style="font-size: 8px;">{{ date('Y-m-d', strtotime($line['date'])) }}</td>
                <td style="font-family: monospace; font-size: 8px;">{{ $line['entry_no'] }}</td>
                <td style="font-family: monospace; font-size: 8px;">{{ $line['account_code'] }}</td>
                <td class="text-right" style="font-size: 8px;">{{ fx($line['account_name']) }}</td>
                <td class="text-right" style="font-size: 8px;">{{ fx($line['description']) }}</td>
                <td>{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                <td>{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                <td style="background: #f9fbff; font-weight: bold;">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        @endforeach
        
        @if(count($lines) < 3)
            @for($i=0; $i < (3 - count($lines)); $i++)
            <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
            </tr>
            @endfor
        @endif
    </tbody>
    <tfoot>
        <tr style="background: #004a99; color: #ffffff; font-weight: bold;">
            <td colspan="8" class="text-center" style="padding: 8px;">
                <span style="font-size: 12px;">{{ number_format($runningBalance, 2) }}</span>
                <span style="font-size: 8px; margin-right: 5px;">SAR</span>
                <span style="font-size: 10px; opacity: 0.8; margin-left: 10px;">{{ fx('الإجمالي للفترة / TOTAL FOR PERIOD') }}</span>
            </td>
        </tr>
    </tfoot>
</table>

<div class="signature-section">
    <table width="100%">
        <tr>
            <td width="33%" style="text-align: center;">
                <div style="font-weight: bold; font-size: 10px; margin-bottom: 30px;">{{ fx('المحاسب') }}</div>
                <div style="border-top: 1px solid #333; width: 100px; margin: 0 auto;"></div>
            </td>
            <td width="33%" style="text-align: center;">
                <div style="font-weight: bold; font-size: 10px; margin-bottom: 30px;">{{ fx('المدير المالي') }}</div>
                <div style="border-top: 1px solid #333; width: 100px; margin: 0 auto;"></div>
            </td>
            <td width="33%" style="text-align: center;">
                <div style="font-weight: bold; font-size: 10px; margin-bottom: 30px;">{{ fx('المدير العام') }}</div>
                <div style="border-top: 1px solid #333; width: 100px; margin: 0 auto;"></div>
            </td>
        </tr>
    </table>
</div>
@endif

@endsection
