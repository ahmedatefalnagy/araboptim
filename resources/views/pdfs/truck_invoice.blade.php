@extends('layouts.pdf_master')

@section('content')
<style>
    .mockup-meta-table td {
        border: none !important;
    }
    .truck-banner {
        background-color: #0056b3;
        color: #ffffff;
        text-align: center;
        padding: 6px 15px;
        font-weight: bold;
        font-size: 11px;
        margin-top: 10px;
        margin-bottom: 5px;
    }
</style>

<table class="mockup-meta-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; font-size: 9px; border: none;">
    <!-- Row 1: C.R. & VAT + Tax Invoice Box -->
    <tr>
        <td width="30%" style="text-align: left; vertical-align: middle; padding: 2px; border: none;">
            <div><strong>C.R :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
            <div><strong>VAT No :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
        </td>
        <td width="40%" style="text-align: center; vertical-align: middle; border: none;">
            <div style="border: 1.5px solid #000; background-color: #f2f2f2; padding: 6px 15px; display: inline-block; font-weight: bold; font-size: 11px; line-height: 1.3; text-align: center;">
                {!! $title !!}
            </div>
        </td>
        <td width="30%" style="text-align: right; vertical-align: middle; padding: 2px; border: none;">
            <div><strong>سجل تجاري :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
            <div><strong>الرقم الضريبي :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
        </td>
    </tr>
</table>

{{-- Meta Information Boxes --}}
<table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9px;">
    <tr>
        <!-- Left Column: Dates -->
        <td width="30%" style="border: 1px solid #000; padding: 0; vertical-align: top;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="40%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Due Date :<br>التاريخ
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold;">
                        {{ $invoice['invoice_date'] }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Date Issue :<br>الموافق
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold;">
                        @php
                            $hijriDate = \App\Helpers\PdfHelper::gregorianToHijri($invoice['invoice_date']);
                        @endphp
                        {{ $hijriDate }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Middle Column: Buyer Name & VAT -->
        <td width="40%" style="border: 1px solid #000; padding: 0; vertical-align: top;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="30%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Buyer Name :<br>اسم العميل
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold;">
                        {{ $invoice['contact']['name'] ?? '--' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Buyer Vat :<br>رقم ضريبي
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold; font-family: monospace;">
                        {{ $invoice['contact']['tax_number'] ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Right Column: Claim No & Buyer No -->
        <td width="30%" style="border: 1px solid #000; padding: 0; vertical-align: top;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="40%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Claim No :<br>رقم الفاتورة
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold; font-family: monospace; font-size: 11px;">
                        {{ $invoice['invoice_no'] }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Buyer No :<br>رقم العميل
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold; font-family: monospace;">
                        {{ $invoice['contact_id'] }}
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
<table class="premium-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; border: 1.5px solid #000;">
    <thead>
        <tr style="background-color: #0056b3; color: #ffffff;">
            <th width="5%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">م<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">S.N</span></th>
            <th width="10%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">التاريخ<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">Date</span></th>
            <th width="15%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">من<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">From</span></th>
            <th width="15%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">إلى<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">To</span></th>
            <th width="20%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">اسم المورد<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">Supplier Name</span></th>
            <th width="12%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">السعر<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">Price</span></th>
            <th width="12%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">الضريبة<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">VAT</span></th>
            <th width="11%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">الاجمالي<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 7px;">Total</span></th>
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
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 8px;">{{ $tripDate }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['destination'] ?? '' }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['origin'] ?? '' }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: right; padding-right: 6px; font-weight: bold; font-size: 8px;">{{ $trip['end_customer_name'] ?? '' }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($price, 2) }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($vatAmount, 2) }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
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
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 8px;">{{ $invoice['invoice_date'] }}</td>
                    <td colspan="3" style="border: 1px solid #000; padding: 5px; text-align: right; padding-right: 8px; font-weight: bold; font-size: 8px;">{{ $line['item_name'] ?? $line['item']['name'] ?? '' }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['subtotal'], 2) }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['tax_amount'], 2) }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['total'], 2) }}</td>
                </tr>
            @endforeach
        @endif

        {{-- Totals Row --}}
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td colspan="5" style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 9px;"></td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 10px;">{{ number_format($totalPrice, 2) }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 10px;">{{ number_format($totalVat, 2) }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 10px;">{{ number_format($totalAll, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- Bottom Section: Totals + QR Code + Bank Info --}}
<table width="100%" style="margin-top: 15px; border-collapse: collapse; border: none;">
    <tr>
        <!-- Totals Table -->
        <td width="35%" style="vertical-align: top; padding: 0; border: none;">
            <table width="100%" style="border-collapse: collapse; border: 1px solid #000; font-size: 9px;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="55%" style="padding: 4px; text-align: center; font-weight: bold; background-color: #f2f2f2; border-left: 1px solid #000;">
                        المجموع الفرعي / Sub Total
                    </td>
                    <td style="padding: 4px; text-align: center; font-weight: bold;">
                        {{ number_format($invoice['total_base'], 2) }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; text-align: center; font-weight: bold; background-color: #f2f2f2; border-left: 1px solid #000;">
                        الخصم / Discount
                    </td>
                    <td style="padding: 4px; text-align: center; font-weight: bold;">
                        0.00
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; text-align: center; font-weight: bold; background-color: #f2f2f2; border-left: 1px solid #000;">
                        الإجمالي ق الضريبة / Vat Before
                    </td>
                    <td style="padding: 4px; text-align: center; font-weight: bold;">
                        {{ number_format($invoice['total_base'], 2) }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; text-align: center; font-weight: bold; background-color: #f2f2f2; border-left: 1px solid #000;">
                        ضريبة مضافة 15 % / Tax 15 %
                    </td>
                    <td style="padding: 4px; text-align: center; font-weight: bold;">
                        {{ number_format($invoice['total_tax'], 2) }}
                    </td>
                </tr>
                <tr style="background-color: #f2f2f2;">
                    <td style="padding: 5px; text-align: center; font-weight: bold; border-left: 1px solid #000; font-size: 10px;">
                        المستحق دفعة / Payable
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold; font-size: 10px;">
                        {{ number_format($invoice['total_amount'], 2) }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- QR Code Block -->
        <td width="25%" style="vertical-align: middle; text-align: center; padding: 0 10px; border: none;">
            @if(isset($qrCode))
                <img src="data:image/png;base64,{{ $qrCode }}" style="width: 100px; height: 100px; border: 1.5px solid #000; padding: 2px;">
            @endif
        </td>
        
        <!-- Bank Info Block -->
        <td width="40%" style="vertical-align: top; padding: 0; border: none;">
            <table width="100%" style="border-collapse: collapse; border: 1px solid #000; font-size: 9px;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="30%" style="padding: 4px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold;">
                        الاسم / Name
                    </td>
                    <td style="padding: 4px; text-align: right; padding-right: 8px; font-weight: bold; font-size: 8px;">
                        {{ \App\Models\Setting::get('company_name', 'شركة التفاؤل العربية للخدمات اللوجستية') }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold;">
                        البنك / Bank
                    </td>
                    <td style="padding: 4px; text-align: right; padding-right: 8px; font-weight: bold;">
                        {{ \App\Models\Setting::get('bank_name', 'مصرف الراجحي') }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold;">
                        الإيبان / IBAN
                    </td>
                    <td style="padding: 4px; text-align: right; padding-right: 8px; font-family: monospace; font-size: 9px; font-weight: bold;">
                        {{ \App\Models\Setting::get('iban', 'SA7880000511608016212237') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 4px; text-align: center; font-weight: bold; background-color: #f2f2f2;">
                        طريقة السداد <span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px; font-weight: normal;">( Payment Method )</span>
                        &nbsp;&nbsp; حوالة بنكية
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Remarks --}}
<table width="100%" style="margin-top: 10px; border-collapse: collapse; border: 1px solid #000; font-size: 9px;">
    <tr>
        <td width="15%" style="background-color: #f2f2f2; border-left: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
            Remarks :
        </td>
        <td style="padding: 4px; text-align: right; padding-right: 8px; font-weight: bold;">
            {{ $invoice['notes'] ?? '---' }}
        </td>
        <td width="15%" style="background-color: #f2f2f2; border-right: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
            ملاحظات
        </td>
    </tr>
</table>

<!-- Stamp area -->
<div style="margin-top: 15px; text-align: left; padding-left: 30px;">
    <div style="width: 90px; height: 90px; border: 2.5px double #0056b3; border-radius: 50%; display: inline-block; text-align: center; padding-top: 15px; color: #0056b3; font-size: 7px; font-weight: bold; line-height: 1.4;">
        مؤسسة التفاؤل العربية<br>س.ت ١٠٠٩٠٣٧٩٤٢<br>المبيعات<br><span style="font-size: 5px; font-family: 'DejaVu Sans', sans-serif;">Arab Optimism Est.</span>
    </div>
</div>

@endsection
