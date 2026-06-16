@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('جدول الأصول الثابتة والإهلاك'))
@section('doc_title_en', 'FIXED ASSETS & DEPRECIATION SCHEDULE')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

<div style="margin-bottom: 20px;">
    <table class="data-grid">
        <tr>
            <td class="data-card" width="50%">
                <div class="card-label">{{ fx('التاريخ المستهدف / AS OF DATE') }}</div>
                <div class="card-value">{{ $asOfDate }}</div>
            </td>
            <td class="data-card" width="50%">
                <div class="card-label">{{ fx('الفترة المالية / FISCAL PERIOD') }}</div>
                <div class="card-value">{{ fx('عام 2025م') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="premium-table">
    <thead>
        <tr>
            <th width="5%">{{ fx('م') }}</th>
            <th width="25%">{{ fx('بيان الأصل') }}</th>
            <th width="12%">{{ fx('تكلفة 01-01') }}</th>
            <th width="12%">{{ fx('مجمع 01-01') }}</th>
            <th width="12%">{{ fx('صافي 01-01') }}</th>
            <th width="8%">{{ fx('النسبة') }}</th>
            <th width="12%">{{ fx('إهلاك السنة') }}</th>
            <th width="14%">{{ fx('صافي 31-12') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($schedule as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="text-right" style="padding-right: 10px;">{{ $row['name'] }}</td>
            <td class="text-center">{{ number_format($row['opening_asset'], 2) }}</td>
            <td class="text-center">{{ number_format($row['opening_acc_dep'], 2) }}</td>
            <td class="text-center" style="background: #f8f9fa;">{{ number_format($row['nbv_opening'], 2) }}</td>
            <td class="text-center" style="color: #004a99;">{{ $row['rate'] }}%</td>
            <td class="text-center" style="color: #d00;">{{ number_format($row['dep_for_year'], 2) }}</td>
            <td class="text-center" style="font-weight: bold; background: #eef2f7;">{{ number_format($row['nbv_closing'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: white;">
            <td colspan="2" class="text-center">{{ fx('الإجماليات') }}</td>
            <td class="text-center">{{ number_format($totals['opening_asset'], 2) }}</td>
            <td class="text-center">{{ number_format($totals['opening_acc_dep'], 2) }}</td>
            <td class="text-center">{{ number_format($totals['nbv_opening'], 2) }}</td>
            <td class="text-center">---</td>
            <td class="text-center">{{ number_format($totals['dep_for_year'], 2) }}</td>
            <td class="text-center">{{ number_format($totals['nbv_closing'], 2) }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
    <table width="100%">
        <tr>
            <td width="33%" class="text-center">
                <div style="font-weight: bold; margin-bottom: 30px;">{{ fx('المحاسب') }}</div>
                <div>_________________</div>
            </td>
            <td width="33%" class="text-center">
                <div style="font-weight: bold; margin-bottom: 30px;">{{ fx('المدير المالي') }}</div>
                <div>_________________</div>
            </td>
            <td width="33%" class="text-center">
                <div style="font-weight: bold; margin-bottom: 30px;">{{ fx('يعتمد،،') }}</div>
                <div>_________________</div>
            </td>
        </tr>
    </table>
</div>

<div style="position: absolute; bottom: 0; width: 100%; font-size: 8px; color: #999; text-align: center;">
    {{ fx('تم استخراج هذا التقرير آلياً من نظام التقوى المحاسبي') }} - {{ date('Y-m-d H:i') }}
</div>
@endsection
