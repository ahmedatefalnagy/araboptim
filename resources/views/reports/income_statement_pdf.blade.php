@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('قائمة الدخل'))
@section('doc_title_en', 'INCOME STATEMENT')

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

<div style="background: #f0fdf4; border-right: 4px solid #16a34a; padding: 10px; margin-bottom: 5px; font-weight: bold; color: #166534;">
    {{ fx('الإيرادات التشغيلية / REVENUES') }}
</div>
<table class="premium-table">
    <thead>
        <tr>
            <th width="15%">{{ fx('الكود') }}</th>
            <th width="60%">{{ fx('الحساب') }}</th>
            <th width="25%">{{ fx('المبلغ (ر.س)') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($revenues as $row)
        <tr>
            <td>{{ $row['code'] }}</td>
            <td class="text-center">{{ $row['name'] }}</td>
            <td class="text-left font-bold">{{ number_format($row['balance'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #dcfce7; font-weight: bold; color: #166534;">
            <td colspan="2" class="text-right">{{ fx('إجمالي الإيرادات') }}</td>
            <td class="text-left">{{ number_format($totalRevenue, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div style="background: #fef2f2; border-right: 4px solid #dc2626; padding: 10px; margin-top: 20px; margin-bottom: 5px; font-weight: bold; color: #991b1b;">
    {{ fx('المصروفات التشغيلية / EXPENSES') }}
</div>
<table class="premium-table">
    <thead>
        <tr>
            <th width="15%">{{ fx('الكود') }}</th>
            <th width="60%">{{ fx('الحساب') }}</th>
            <th width="25%">{{ fx('المبلغ (ر.س)') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $row)
        <tr>
            <td>{{ $row['code'] }}</td>
            <td class="text-center">{{ $row['name'] }}</td>
            <td class="text-left font-bold">{{ number_format($row['balance'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #fee2e2; font-weight: bold; color: #991b1b;">
            <td colspan="2" class="text-right">{{ fx('إجمالي المصروفات') }}</td>
            <td class="text-left">({{ number_format($totalExpense, 2) }})</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 30px; padding: 20px; text-align: center; border-radius: 12px; {{ $netIncome >= 0 ? 'background: #f0fdf4; border: 2px solid #bbf7d0; color: #166534;' : 'background: #fef2f2; border: 2px solid #fecaca; color: #991b1b;' }}">
    <div style="font-size: 14px; font-weight: bold;">
        {{ $netIncome >= 0 ? fx('صافي الربح للفترة / NET PROFIT') : fx('صافي الخسارة للفترة / NET LOSS') }}
    </div>
    <div style="font-size: 24px; font-weight: bold; margin-top: 10px;">
        {{ number_format($netIncome, 2) }} SAR
    </div>
</div>

@endsection
