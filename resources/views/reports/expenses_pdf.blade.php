@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('تقرير المصروفات التشغيلية'))
@section('doc_title_en', 'OPERATIONAL EXPENSES REPORT')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

<table class="data-grid">
    <tr>
        <td class="data-card" width="20%" style="text-align: center;font-size:10px">
            <div class="card-label" style="font-size:9px">{{ fx('الفترة الزمنية / PERIOD') }}</div>
            <div class="card-value">{{ $endDate }}  {{ fx('إلى') }} {{ $startDate }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center;">
            <div class="card-label" style="font-size:9px">{{ fx('تاريخ الاستخراج / PRINT DATE') }}</div>
            <div class="card-value">{{ date('Y-m-d') }}</div>
        </td>
        <td class="data-card" width="20%" style="text-align: center;">
            <div class="card-label" style="font-size:9px">{{ fx('رقم المرجع / REF NO') }}</div>
            <div class="card-value">EXP-{{ date('Ymd') }}</div>
        </td>
    </tr>
</table>

<table class="premium-table">
    <thead>
        <tr>
            <th width="8%">{{ fx('م') }}<br>S.N</th>
            <th width="17%">{{ fx('كود الحساب') }}<br>CODE</th>
            <th width="50%">{{ fx('بيان المصروف') }}<br>DESCRIPTION</th>
            <th width="25%">{{ fx('المبلغ (ر.س)') }}<br>AMOUNT (SAR)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($balances as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td style="font-family: monospace; font-weight: bold; color: #004a99;">{{ $row['code'] }}</td>
            <td class="text-center" style="vertical-align: middle;">{{ $row['name'] }}</td>
            <td class="text-center" style="font-weight: bold;">{{ number_format($row['balance'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div style="width: 100%; margin-top: 15px;">
    <table width="100%">
        <tr>
            <td width="50%" style="text-align: center; vertical-align: middle;">
                <div style="display: inline-block; text-align: center;">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 5px; color: #444;">{{ fx('يعتمد ،،،') }}</div>
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 35px; color: #444;">{{ fx('المدير المالي') }}</div>
                    <div style="border-top: 1px solid #333; width: 120px; margin: 0 auto;"></div>
                </div>
            </td>

            <td width="50%" style="vertical-align: middle;">
                <div style="border: 1px solid #eee; border-radius: 6px; padding: 12px; background: #fafafa;">
                    <div style="font-size: 8px; font-weight: bold; color: #888; margin-bottom: 5px;">{{ fx('ملخص مالي / FINANCIAL SUMMARY') }}</div>
                    <table width="100%" style="font-size: 11px;">
                        <tr>
                            <td style="text-align: left; font-weight: bold; color: #d00;">{{ number_format($totalExpenses, 2) }} SAR</td>
                            <td style="text-align: right; font-weight: bold;">{{ fx('إجمالي المصروفات عن الفترة :') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

@endsection
