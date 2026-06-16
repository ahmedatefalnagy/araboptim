@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('إقرار ضريبة القيمة المضافة'))
@section('doc_title_en', 'VAT REPORT - FORM 2025')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

<table class="data-grid">
    <tr>
        <td class="data-card" width="50%">
            <div class="card-label">{{ fx('السنة / YEAR') }}</div>
            <div class="card-value">{{ $year }}</div>
        </td>
        <td class="data-card" width="50%">
            <div class="card-label">{{ fx('الربع / QUARTER') }}</div>
            <div class="card-value">Q{{ $quarter }}</div>
        </td>
    </tr>
</table>

<div style="background: #004a99; color: white; padding: 10px; font-weight: bold; border-radius: 8px 8px 0 0;">
    {{ fx('ملخص الإقرار الضريبي / VAT SUMMARY') }}
</div>
<table class="premium-table" style="margin-top: 0;">
    <thead>
        <tr>
            <th width="50%">{{ fx('التفاصيل / DETAILS') }}</th>
            <th width="25%">{{ fx('المبلغ الخاضع للضريبة') }}</th>
            <th width="25%">{{ fx('مبلغ الضريبة (15%)') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-right">{{ fx('صافي المبيعات (المخرجات الضريبية)') }}</td>
            <td class="text-left">{{ number_format($totals['output_base'], 2) }}</td>
            <td class="text-left font-bold" style="color: #2563eb;">{{ number_format($totals['output_tax'], 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">{{ fx('صافي المشتريات (المدخلات الضريبية)') }}</td>
            <td class="text-left">{{ number_format($totals['input_base'], 2) }}</td>
            <td class="text-left font-bold" style="color: #dc2626;">{{ number_format($totals['input_tax'], 2) }}</td>
        </tr>
    </tbody>
</table>

<div style="margin: 20px 0; padding: 15px; text-align: center; border-radius: 12px; {{ $totals['net_vat'] > 0 ? 'background: #fef2f2; border: 2px solid #fecaca; color: #991b1b;' : 'background: #f0fdf4; border: 2px solid #bbf7d0; color: #166534;' }}">
    <div style="font-size: 11px; font-weight: bold;">
        {{ $totals['net_vat'] > 0 ? fx('صافي الضريبة المستحقة للهيئة / NET VAT PAYABLE') : fx('صافي الضريبة المستردة / NET VAT REFUNDABLE') }}
    </div>
    <div style="font-size: 20px; font-weight: bold; margin-top: 5px;">
        {{ number_format(abs($totals['net_vat']), 2) }} SAR
    </div>
</div>

<div style="font-weight: bold; color: #004a99; margin-top: 20px; margin-bottom: 5px;">
    {{ fx('تفاصيل مبيعات الفترة / SALES DETAILS') }}
</div>
<table class="premium-table">
    <thead>
        <tr>
            <th width="12%">{{ fx('التاريخ') }}</th>
            <th width="10%">{{ fx('القيد') }}</th>
            <th width="53%">{{ fx('البيان / الوصف') }}</th>
            <th width="25%">{{ fx('الضريبة') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salesTaxEntries as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td style="font-family: monospace;">#{{ $row['entry_no'] }}</td>
            <td class="text-center">{{ $row['description'] }}</td>
            <td class="text-left font-bold">{{ number_format($row['tax_amount'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div style="font-weight: bold; color: #004a99; margin-top: 20px; margin-bottom: 5px;">
    {{ fx('تفاصيل مشتريات الفترة / PURCHASE DETAILS') }}
</div>
<table class="premium-table">
    <thead>
        <tr>
            <th width="12%">{{ fx('التاريخ') }}</th>
            <th width="10%">{{ fx('القيد') }}</th>
            <th width="53%">{{ fx('البيان / الوصف') }}</th>
            <th width="25%">{{ fx('الضريبة') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseTaxEntries as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td style="font-family: monospace;">#{{ $row['entry_no'] }}</td>
            <td class="text-center">{{ $row['description'] }}</td>
            <td class="text-left font-bold">{{ number_format($row['tax_amount'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
