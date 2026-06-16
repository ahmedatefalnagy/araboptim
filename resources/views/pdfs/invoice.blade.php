@extends('layouts.pdf_master')

@section('content')
<style>
    .mockup-meta-table td {
        border: none !important;
    }
</style>

<table class="mockup-meta-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; font-size: 9px; border: none;">
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

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px;">
    <tr>
        <!-- Left Column: Dates -->
        <td width="30%" style="border: 1px solid #000; padding: 0; vertical-align: top;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="40%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Date Issue :<br>تاريخ الإصدار
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold;">
                        {{ $invoice['invoice_date'] }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Due Date :<br>تاريخ الإستحقاق
                    </td>
                    <td style="padding: 5px; text-align: center; font-weight: bold;">
                        {{ $invoice['due_date'] ?? $invoice['invoice_date'] }}
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
        
        <!-- Right Column: Invoice NO. & Buyer No -->
        <td width="30%" style="border: 1px solid #000; padding: 0; vertical-align: top;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr style="border-bottom: 1px solid #000;">
                    <td width="40%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px;">
                        Invoice NO. :<br>رقم الفاتورة
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
    <!-- Buyer Address row -->
    <tr>
        <td colspan="3" style="border: 1px solid #000; padding: 0;">
            <table width="100%" style="border-collapse: collapse; border: none;">
                <tr>
                    <td width="15%" style="padding: 5px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold;">
                        Buyer Address :
                    </td>
                    <td style="padding: 5px; text-align: right; padding-right: 10px; font-weight: bold;">
                        {{ $invoice['contact']['address'] ?? '---' }}
                    </td>
                    <td width="15%" style="padding: 5px; background-color: #f2f2f2; border-right: 1px solid #000; text-align: center; font-weight: bold;">
                        عنوان العميل
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="premium-table" style="width: 100%; border-collapse: collapse; margin-top: 10px; border: 1.5px solid #000;">
    <thead>
        <tr style="background-color: #0056b3; color: #ffffff;">
            <th width="5%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">مسلسل<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">S.N</span></th>
            <th width="45%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">وصف المنتج<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">Product Description</span></th>
            <th width="10%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">الوحدة<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">Unit</span></th>
            <th width="10%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">الكمية<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">Qty.</span></th>
            <th width="15%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">السعر<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">Price</span></th>
            <th width="15%" style="border: 1px solid #000; padding: 5px; font-size: 9px; text-align: center;">الاجمالي<br><span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px;">Total</span></th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice['lines'] as $index => $line)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: right; padding-right: 8px; font-weight: bold;">{{ $line['item']['name'] ?? $line['description'] }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $line['item']['unit']['name'] ?? 'PCS' }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['quantity'], 2) }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['unit_price'], 2) }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ number_format($line['total'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

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
                        الإجمالي ق الضريبة / Before Vat
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
                <tr>
                    <td colspan="2" style="background-color: #f2f2f2; padding: 4px; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                        معلومات الحساب البنكي / Bank Account Info
                    </td>
                </tr>
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
                    <td style="padding: 4px; background-color: #f2f2f2; border-left: 1px solid #000; text-align: center; font-weight: bold;">
                        طريقة السداد
                    </td>
                    <td style="padding: 4px; text-align: center; font-weight: bold;">
                        حوالة بنكية <span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px; font-weight: normal;">( Payment Method )</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

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
        مؤسسة التفاؤل العربية<br>س.ت ١٠١٠٨٠٣٠٤٥<br>المبيعات<br><span style="font-size: 5px; font-family: 'DejaVu Sans', sans-serif;">Arab Optimism Est.</span>
    </div>
</div>

@endsection
