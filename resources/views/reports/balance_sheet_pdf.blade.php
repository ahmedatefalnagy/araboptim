@extends('layouts.pdf_master')

@section('doc_title_ar', \App\Helpers\PdfHelper::fixArabic('قائمة المركز المالي'))
@section('doc_title_en', 'BALANCE SHEET')

@section('content')
@php
    function fx($t) { return \App\Helpers\PdfHelper::fixArabic($t); }
@endphp

<table class="data-grid">
    <tr>
        <td class="data-card" width="100%">
            <div class="card-label">{{ fx('كما في تاريخ / AS OF DATE') }}</div>
            <div class="card-value">{{ $asOfDate }}</div>
        </td>
    </tr>
</table>

<table width="100%" style="border-collapse: collapse;">
    <tr>
        <!-- الأصول -->
        <td width="48%" style="vertical-align: top;">
            <div style="background: #eff6ff; border-right: 4px solid #2563eb; padding: 8px; margin-bottom: 5px; font-weight: bold; color: #1e40af; font-size: 11px;">
                {{ fx('الأصول / ASSETS') }}
            </div>
            <table class="premium-table">
                <thead>
                    <tr>
                        <th width="70%">{{ fx('الحساب') }}</th>
                        <th width="30%">{{ fx('المبلغ') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $row)
                    <tr>
                        <td class="text-center">{{ $row['name'] }}</td>
                        <td class="text-left font-bold">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #1e40af; color: white; font-weight: bold;">
                        <td class="text-right">{{ fx('إجمالي الأصول') }}</td>
                        <td class="text-left">{{ number_format($totalAssets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </td>

        <td width="4%"></td>

        <!-- الخصوم وحقوق الملكية -->
        <td width="48%" style="vertical-align: top;">
            <div style="background: #fef2f2; border-right: 4px solid #dc2626; padding: 8px; margin-bottom: 5px; font-weight: bold; color: #991b1b; font-size: 11px;">
                {{ fx('الخصوم / LIABILITIES') }}
            </div>
            <table class="premium-table">
                <tbody>
                    @foreach($liabilities as $row)
                    <tr>
                        <td width="70%" class="text-center">{{ $row['name'] }}</td>
                        <td width="30%" class="text-left font-bold">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #991b1b; color: white; font-weight: bold;">
                        <td class="text-right">{{ fx('إجمالي الخصوم') }}</td>
                        <td class="text-left">{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="background: #f0fdf4; border-right: 4px solid #16a34a; padding: 8px; margin-top: 15px; margin-bottom: 5px; font-weight: bold; color: #166534; font-size: 11px;">
                {{ fx('حقوق الملكية / EQUITY') }}
            </div>
            <table class="premium-table">
                <tbody>
                    @foreach($equity as $row)
                    <tr>
                        <td width="70%" class="text-center">{{ $row['name'] }}</td>
                        <td width="30%" class="text-left font-bold">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #166534; color: white; font-weight: bold;">
                        <td class="text-right">{{ fx('إجمالي الخصوم والملكبة') }}</td>
                        <td class="text-left">{{ number_format($totalEquity + $totalLiabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </td>
    </tr>
</table>

@php $diff = abs($totalAssets - ($totalLiabilities + $totalEquity)); @endphp
<div style="margin-top: 20px; padding: 12px; text-align: center; border-radius: 8px; {{ $diff < 0.1 ? 'background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' : 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' }}">
    <div style="font-weight: bold; font-size: 12px;">
        {{ $diff < 0.1 ? fx('الميزانية متوازنة / BALANCE SHEET IS BALANCED') : fx('⚠️ تحذير: الميزانية غير متوازنة / UNBALANCED') }}
    </div>
</div>

@endsection
