@extends('layouts.pdf_master')

@section('content')
<style>
    .mockup-meta-table td {
        border: none !important;
    }
    .truck-banner {
        background-color: #1e3a8a;
        color: #ffffff;
        text-align: center;
        padding: 8px 15px;
        font-weight: bold;
        font-size: 11px;
        margin-top: 10px;
        margin-bottom: 5px;
        border-radius: 4px;
    }
    .meta-card-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: 9px;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        border-radius: 6px;
    }
    .meta-card-table td {
        border: 1px solid #e2e8f0;
        padding: 0;
        vertical-align: top;
    }
    .meta-inner-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .meta-inner-table td {
        border: none !important;
        padding: 6px 8px;
    }
    .meta-label {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: bold;
        font-size: 8px;
        text-align: center;
        width: 35%;
        border-left: 1px solid #e2e8f0 !important;
    }
    .meta-value {
        text-align: center;
        font-weight: bold;
        color: #1e293b;
    }
</style>

<table class="mockup-meta-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; font-size: 9px; border: none;">
    <!-- Row 1: C.R. & VAT + Tax Invoice Box -->
@php
    $isPurchase = in_array($invoice['type'], ['purchase', 'purchase_return', 'purchase_quotation', 'purchase_order']);
    $buyerName = $isPurchase ? \App\Models\Setting::get('company_name', 'شركة التفاؤل العربية للخدمات اللوجستية') : ($invoice['contact']['name'] ?? '--');
    $buyerVat = $isPurchase ? \App\Models\Setting::get('company_vat_no', '312253166440003') : ($invoice['contact']['tax_number'] ?? 'N/A');
@endphp

<table class="mockup-meta-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 12px; font-size: 9px; border: none;">
    <!-- Row 1: C.R. & VAT + Tax Invoice Box -->
    <tr>
        <td width="30%" style="text-align: left; vertical-align: middle; padding: 2px; border: none; color: #475569;">
            @if($isPurchase)
                <div><strong>VAT No :</strong> {{ $invoice['contact']['tax_number'] ?? 'N/A' }}</div>
            @else
                <div><strong>C.R :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
                <div><strong>VAT No :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
            @endif
        </td>
        <td width="40%" style="text-align: center; vertical-align: middle; border: none;">
            <div style="border: 1px solid #1e3a8a; background-color: #f1f5f9; color: #1e3a8a; padding: 8px 18px; display: inline-block; font-weight: bold; font-size: 11px; line-height: 1.4; text-align: center; border-radius: 4px;">
                {!! $title !!}
            </div>
        </td>
        <td width="30%" style="text-align: right; vertical-align: middle; padding: 2px; border: none; color: #475569;">
            @if($isPurchase)
                <div><strong>الرقم الضريبي :</strong> {{ $invoice['contact']['tax_number'] ?? 'N/A' }}</div>
            @else
                <div><strong>سجل تجاري :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
                <div><strong>الرقم الضريبي :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- Meta Information Boxes --}}
<table class="meta-card-table">
    <tr>
        <!-- Left Column: Dates -->
        <td width="33%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label">
                        Due Date :<br>التاريخ
                    </td>
                    <td class="meta-value">
                        {{ date('d-m-Y', strtotime($invoice['invoice_date'])) }}
                    </td>
                </tr>
                <tr>
                    <td class="meta-label">
                        Date Issue :<br>الموافق
                    </td>
                    <td class="meta-value">
                        @php
                            $hijriDate = \App\Helpers\PdfHelper::gregorianToHijri($invoice['invoice_date']);
                        @endphp
                        {{ $hijriDate }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Middle Column: Buyer Name & VAT -->
        <td width="34%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label" style="width: 30%;">
                        Buyer Name :<br>اسم العميل/المشتري
                    </td>
                    <td class="meta-value">
                        {{ $buyerName }}
                    </td>
                </tr>
                <tr>
                    <td class="meta-label" style="width: 30%;">
                        Buyer Vat :<br>رقم ضريبي للمشتري
                    </td>
                    <td class="meta-value" style="font-family: monospace;">
                        {{ $buyerVat }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Right Column: Claim No & Cost Center -->
        <td width="33%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label" style="width: 40%;">
                        Claim No :<br>رقم الفاتورة
                    </td>
                    <td class="meta-value" style="font-family: monospace; font-size: 10px; color: #1e3a8a;">
                        {{ $invoice['invoice_no'] }}
                    </td>
                </tr>
                <tr>
                    <td class="meta-label" style="width: 40%;">
                        Cost Center :<br>مركز التكلفة
                    </td>
                    <td class="meta-value" style="color: #0d9488;">
                        {{ $invoice['costCenter']['name'] ?? '--' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Truck Banner with Plate Number and Month --}}
@php
    $firstTrip = isset($invoice['trips']) && count($invoice['trips']) > 0 ? $invoice['trips'][0] : null;
    $plateNo = $firstTrip && isset($firstTrip['vehicle']) ? $firstTrip['vehicle']['plate_no'] : '---';
    $invoiceMonth = date('n', strtotime($invoice['invoice_date']));
    $invoiceYear = date('Y', strtotime($invoice['invoice_date']));
    $monthName = \App\Helpers\PdfHelper::arabicMonthName($invoiceMonth);
@endphp

<div class="truck-banner">
    الشاحنة رقم {{ $plateNo }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; شهر {{ $monthName }} {{ $invoiceYear }}
</div>

{{-- Trips Details Table --}}
<table class="premium-table">
    <thead>
        <tr>
            <th width="5%">S.N<br>م</th>
            <th width="10%">Date<br>التاريخ</th>
            <th width="15%">From<br>من</th>
            <th width="15%">To<br>إلى</th>
            <th width="20%">Supplier Name<br>اسم المورد</th>
            <th width="12%">Price<br>السعر</th>
            <th width="12%">VAT<br>الضريبة</th>
            <th width="11%">Total<br>الاجمالي</th>
        </tr>
    </thead>
    <tbody>
        @php $totalPrice = 0; $totalVat = 0; $totalAll = 0; @endphp
        @if(isset($invoice['trips']) && count($invoice['trips']) > 0)
            @foreach($invoice['trips'] as $index => $trip)
                @php
                    $price = floatval($trip['broker_price'] ?? 0);
                    $vatAmount = $price * 0.15;
                    $lineTotal = $price + $vatAmount;
                    $totalPrice += $price;
                    $totalVat += $vatAmount;
                    $totalAll += $lineTotal;
                    $tripDate = isset($trip['created_at']) ? date('d-M', strtotime($trip['created_at'])) : '---';
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-size: 8px;">{{ $tripDate }}</td>
                    <td style="text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['destination'] ?? '' }}</td>
                    <td style="text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['origin'] ?? '' }}</td>
                    <td style="text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['end_customer_name'] ?? '' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($price, 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($vatAmount, 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        @else
            {{-- Fallback to standard invoice lines --}}
            @foreach($invoice['lines'] as $index => $line)
                @php
                    $totalPrice += $line['subtotal'];
                    $totalVat += $line['tax_amount'];
                    $totalAll += $line['total'];
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-size: 8px;">{{ date('d-m-Y', strtotime($invoice['invoice_date'])) }}</td>
                    <td colspan="3" style="text-align: right; padding-right: 8px; font-weight: bold; font-size: 8px;">{{ $line['item_name'] ?? $line['item']['name'] ?? '' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($line['subtotal'], 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($line['tax_amount'], 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($line['total'], 2) }}</td>
                </tr>
            @endforeach
        @endif

        {{-- Totals Row --}}
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="5" style="text-align: center; font-weight: bold; font-size: 9px; color: #475569;">الإجمالي / Total</td>
            <td style="text-align: center; font-weight: bold; font-size: 10px; color: #1e293b;">{{ number_format($totalPrice, 2) }}</td>
            <td style="text-align: center; font-weight: bold; font-size: 10px; color: #1e3a8a;">{{ number_format($totalVat, 2) }}</td>
            <td style="text-align: center; font-weight: bold; font-size: 10px; color: #1e293b;">{{ number_format($totalAll, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Bottom Section: Totals + QR Code + Bank Info --}}
<table width="100%" style="margin-top: 15px; border-collapse: collapse; border: none;">
    <tr>
        <!-- Totals Table -->
        <td width="38%" style="vertical-align: top; padding: 0; border: none;">
            <table width="100%" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9px; background-color: #f8fafc; border-radius: 4px;">
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td width="55%" style="padding: 6px; text-align: center; font-weight: bold; background-color: #f1f5f9; border-left: 1px solid #cbd5e1;">
                        المجموع الفرعي / Sub Total
                    </td>
                    <td style="padding: 6px; text-align: center; font-weight: bold; color: #334155;">
                        {{ number_format($invoice['total_base'], 2) }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 6px; text-align: center; font-weight: bold; background-color: #f1f5f9; border-left: 1px solid #cbd5e1;">
                        الخصم / Discount
                    </td>
                    <td style="padding: 6px; text-align: center; font-weight: bold; color: #64748b;">
                        0.00
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 6px; text-align: center; font-weight: bold; background-color: #f1f5f9; border-left: 1px solid #cbd5e1;">
                        الإجمالي ق الضريبة / Vat Before
                    </td>
                    <td style="padding: 6px; text-align: center; font-weight: bold; color: #334155;">
                        {{ number_format($invoice['total_base'], 2) }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 6px; text-align: center; font-weight: bold; background-color: #f1f5f9; border-left: 1px solid #cbd5e1;">
                        ضريبة مضافة 15 % / Tax 15 %
                    </td>
                    <td style="padding: 6px; text-align: center; font-weight: bold; color: #1e3a8a;">
                        {{ number_format($invoice['total_tax'], 2) }}
                    </td>
                </tr>
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-align: center; font-weight: bold; border-left: 1px solid #cbd5e1; font-size: 10px; color: #1e3a8a;">
                        المستحق دفعة / Payable
                    </td>
                    <td style="padding: 8px; text-align: center; font-weight: bold; font-size: 11px; color: #1e3a8a;">
                        {{ number_format($invoice['total_amount'], 2) }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- QR Code Block -->
        <td width="22%" style="vertical-align: middle; text-align: center; padding: 0 10px; border: none;">
            @if(in_array($invoice['type'], ['sale', 'sale_return']) && isset($qrCode))
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 95px; height: 95px; border: 1px solid #cbd5e1; padding: 4px; background-color: #fff; border-radius: 4px;">
            @endif
        </td>
        
        <!-- Bank Info Block -->
        <td width="40%" style="vertical-align: top; padding: 0; border: none;">
            <table width="100%" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9px; background-color: #f8fafc; border-radius: 4px;">
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td width="30%" style="padding: 6px; background-color: #f1f5f9; border-left: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #475569;">
                        الاسم / Name
                    </td>
                    <td style="padding: 6px; text-align: right; padding-right: 8px; font-weight: bold; font-size: 8px; color: #334155;">
                        {{ \App\Models\Setting::get('company_name', 'شركة التفاؤل العربية للخدمات اللوجستية') }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 6px; background-color: #f1f5f9; border-left: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #475569;">
                        البنك / Bank
                    </td>
                    <td style="padding: 6px; text-align: right; padding-right: 8px; font-weight: bold; color: #334155;">
                        {{ \App\Models\Setting::get('bank_name', 'مصرف الراجحي') }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 6px; background-color: #f1f5f9; border-left: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #475569;">
                        الإيبان / IBAN
                    </td>
                    <td style="padding: 6px; text-align: right; padding-right: 8px; font-family: monospace; font-size: 9px; font-weight: bold; color: #334155;">
                        {{ \App\Models\Setting::get('iban', 'SA7880000511608016212237') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 6px; text-align: center; font-weight: bold; background-color: #f1f5f9; color: #334155;">
                        طريقة السداد <span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px; font-weight: normal; color: #64748b;">( Payment Method )</span>
                        &nbsp;&nbsp; حوالة بنكية
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Remarks --}}
<table width="100%" style="margin-top: 12px; border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9px; background-color: #f8fafc; border-radius: 4px;">
    <tr>
        <td width="15%" style="background-color: #f1f5f9; border-left: 1px solid #cbd5e1; padding: 6px; text-align: center; font-weight: bold; color: #475569;">
            Remarks :
        </td>
        <td style="padding: 6px; text-align: right; padding-right: 8px; font-weight: bold; color: #334155;">
            {{ $invoice['notes'] ?? '---' }}
        </td>
        <td width="15%" style="background-color: #f1f5f9; border-right: 1px solid #cbd5e1; padding: 6px; text-align: center; font-weight: bold; color: #475569;">
            ملاحظات
        </td>
    </tr>
</table>

@endsection
