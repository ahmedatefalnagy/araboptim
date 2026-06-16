@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('ميزان المراجعة'))
@section('doc_title_en', 'TRIAL BALANCE')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

<table class="data-grid">
    <tr>
        <td class="data-card" width="50%">
            <div class="card-label">{{ fx('الفترة من / FROM DATE') }}</div>
            <div class="card-value">{{ $startDate }}</div>
        </td>
        <td class="data-card" width="50%">
            <div class="card-label">{{ fx('الفترة إلى / TO DATE') }}</div>
            <div class="card-value">{{ $endDate }}</div>
        </td>
    </tr>
</table>

<table class="premium-table">
    <thead>
        <tr>
            <th rowspan="2" width="10%">{{ fx('كود') }}<br>CODE</th>
            <th rowspan="2" width="30%">{{ fx('اسم الحساب') }}<br>ACCOUNT NAME</th>
            <th colspan="2" width="30%">{{ fx('المجاميع') }}<br>TOTALS</th>
            <th colspan="2" width="30%">{{ fx('الأرصدة') }}<br>BALANCES</th>
        </tr>
        <tr>
            <th>{{ fx('مدين') }}</th>
            <th>{{ fx('دائن') }}</th>
            <th>{{ fx('مدين') }}</th>
            <th>{{ fx('دائن') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($balances as $row)
        <tr class="{{ isset($row['is_postable']) && !$row['is_postable'] ? 'font-weight-bold' : '' }}">
            <td style="font-family: monospace; font-weight: bold; color: #004a99; {{ isset($row['level']) && $row['level'] > 1 ? 'padding-right: ' . (($row['level']-1)*10) . 'px;' : '' }}">
                {{ $row['code'] }}
            </td>
            <td class="text-right" style="{{ isset($row['is_postable']) && !$row['is_postable'] ? 'font-weight: 900; color: #000;' : 'color: #555;' }}">
                {{ $row['name'] }}
            </td>
            <td style="{{ isset($row['is_postable']) && !$row['is_postable'] ? 'background: #fcfcfc;' : '' }}">{{ number_format($row['debit'], 2) }}</td>
            <td style="{{ isset($row['is_postable']) && !$row['is_postable'] ? 'background: #fcfcfc;' : '' }}">{{ number_format($row['credit'], 2) }}</td>
            <td style="background: #f0f7ff; font-weight: bold;">{{ $row['balance_debit'] > 0 ? number_format($row['balance_debit'], 2) : '-' }}</td>
            <td style="background: #f0f7ff; font-weight: bold;">{{ $row['balance_credit'] > 0 ? number_format($row['balance_credit'], 2) : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #004a99; color: white; font-weight: bold;">
            <td colspan="2" class="text-right">{{ fx('الإجماليات العامـــــة') }}</td>
            <td>{{ number_format($totals['debit'], 2) }}</td>
            <td>{{ number_format($totals['credit'], 2) }}</td>
            <td>{{ number_format($totals['balance_debit'], 2) }}</td>
            <td>{{ number_format($totals['balance_credit'], 2) }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 15px; font-size: 8px; color: #777; border-right: 2px solid #004a99; padding-right: 10px;">
    {{ fx('* ملاحظة: هذا التقرير مستخرج آلياً ويوضح حركة الحسابات والأرصدة الختامية للفترة المحددة.') }}
</div>

@endsection
