@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('تقرير كشف حساب (دفتر الأستاذ)'))
@section('doc_title_en', 'GENERAL LEDGER REPORT')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

@if(!empty($lines) && count($lines) > 0)
<table class="data-grid">
    <tr>
        <td class="data-card" width="50%" style="text-align: center">
            <div class="card-label">{{ fx('الحساب / ACCOUNT') }}</div>
            <div class="card-value">{{ $account['code'] }} - {{ $account['name'] }}</div>
            @if($contact)
                <div style="font-size: 8px; color: #004a99; margin-top: 3px; font-weight: bold;">
                    {{ fx('الجهة / CONTACT:') }} {{ $contact['name'] }}
                </div>
            @endif
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

<div style="height: 10px;"></div>

<table class="premium-table">
    <thead>
        <tr>
            <th width="10%">{{ fx('التاريخ') }}<br>DATE</th>
            <th width="10%">{{ fx('المرجع') }}<br>REF</th>
            <th width="40%">{{ fx('البيان') }}<br>DESCRIPTION</th>
            <th width="12%">{{ fx('مدين') }}<br>DEBIT</th>
            <th width="12%">{{ fx('دائن') }}<br>CREDIT</th>
            <th width="16%">{{ fx('الرصيد') }}<br>BALANCE</th>
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

                $isDebitNormal = ($account['type']['normal_balance'] ?? 'debit') === 'debit';
                if ($isDebitNormal) {
                    $runningBalance += $debit - $credit;
                } else {
                    $runningBalance += $credit - $debit;
                }
            @endphp
            <tr>
                <td style="font-size: 8px;">{{ date('Y-m-d', strtotime($line['journal_entry']['entry_date'])) }}</td>
                <td style="font-family: monospace; font-size: 8px;">{{ $line['journal_entry']['entry_no'] }}</td>
                <td class="text-right">{{ $line['description'] ?: ($line['journal_entry']['description'] ?? '-') }}</td>
                <td>{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                <td>{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                <td style="background: #f9fbff; font-weight: bold;">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        @endforeach
        
        {{-- إضافة أسطر فارغة لزيادة المسافة إذا كان التقرير قصيراً --}}
        @if(count($lines) < 3)
            @for($i=0; $i < (3 - count($lines)); $i++)
            <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
            </tr>
            @endfor
        @endif
    </tbody>
    <tfoot>
        <!-- <tr style="background: #f5f5f5; font-weight: bold;">
            <td colspan="3" class="text-right" style="padding: 8px;">{{ fx('الإجمالي للفترة / TOTAL FOR PERIOD') }}</td>
            <td class="text-center">{{ number_format($totalDebit, 2) }}</td>
            <td class="text-center">{{ number_format($totalCredit, 2) }}</td>
            <td class="text-center" style="background: #f0f0f0;">{{ number_format($runningBalance, 2) }}</td>
        </tr> -->
        <tr style="background: #004a99; color: #ffffff; font-weight: bold;">
            <td colspan="6" class="text-center" style="padding: 8px;">
                <span style="font-size: 12px;">{{ number_format($runningBalance, 2) }}</span>
                <span style="font-size: 8px; margin-right: 5px;">SAR</span>
                <!-- <span style="font-size: 10px; opacity: 0.8; margin-left: 10px;">{{ $endDate }} {{ fx('الرصيد الختامي في') }}</span> -->
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
