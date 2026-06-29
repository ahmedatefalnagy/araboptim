@extends('layouts.pdf_master')

@section('content')
<style>
    .mockup-meta-table td {
        border: none !important;
    }
    .meta-card-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
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
    .meta-label-full {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: bold;
        font-size: 8px;
        text-align: center;
        width: 15%;
    }
    .meta-value-full {
        text-align: right;
        padding-right: 12px;
        font-weight: bold;
        color: #1e293b;
    }
</style>

<table class="mockup-meta-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 12px; font-size: 9px; border: none;">
    <!-- Row 1: C.R. & VAT + Tax Invoice Box -->
    <tr>
        <td width="30%" style="text-align: left; vertical-align: middle; padding: 2px; border: none; color: #475569;">
            <div><strong>C.R :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
            <div><strong>VAT No :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
        </td>
        <td width="40%" style="text-align: center; vertical-align: middle; border: none;">
            <div style="border: 1px solid #1e3a8a; background-color: #f1f5f9; color: #1e3a8a; padding: 8px 18px; display: inline-block; font-weight: bold; font-size: 11px; line-height: 1.4; text-align: center; border-radius: 4px;">
                {!! $title !!}
            </div>
        </td>
        <td width="30%" style="text-align: right; vertical-align: middle; padding: 2px; border: none; color: #475569;">
            <div><strong>سجل تجاري :</strong> {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}</div>
            <div><strong>الرقم الضريبي :</strong> {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}</div>
        </td>
    </tr>
</table>

<table class="meta-card-table">
    <tr>
        <!-- Left Column: Dates -->
        <td width="33%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label">
                        Date Issue :<br>تاريخ الإصدار
                    </td>
                    <td class="meta-value">
                        {{ $invoice['invoice_date'] }}
                    </td>
                </tr>
                <tr>
                    <td class="meta-label">
                        Due Date :<br>تاريخ الإستحقاق
                    </td>
                    <td class="meta-value">
                        {{ $invoice['due_date'] ?? $invoice['invoice_date'] }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Middle Column: Buyer Name & VAT -->
        <td width="34%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label" style="width: 30%;">
                        Buyer Name :<br>اسم العميل
                    </td>
                    <td class="meta-value">
                        {{ $invoice['contact']['name'] ?? '--' }}
                    </td>
                </tr>
                <tr>
                    <td class="meta-label" style="width: 30%;">
                        Buyer Vat :<br>رقم ضريبي
                    </td>
                    <td class="meta-value" style="font-family: monospace;">
                        {{ $invoice['contact']['tax_number'] ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </td>
        
        <!-- Right Column: Invoice NO. & Cost Center -->
        <td width="33%">
            <table class="meta-inner-table">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td class="meta-label" style="width: 40%;">
                        Invoice NO. :<br>رقم الفاتورة
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
    <!-- Buyer Address row -->
    <tr>
        <td colspan="3" style="border-top: 1px solid #e2e8f0;">
            <table class="meta-inner-table">
                <tr>
                    <td class="meta-label-full" style="width: 12%; border-left: 1px solid #e2e8f0 !important;">
                        Buyer Address :
                    </td>
                    <td class="meta-value-full">
                        {{ $invoice['contact']['address'] ?? '---' }}
                    </td>
                    <td class="meta-label-full" style="width: 12%; border-right: 1px solid #e2e8f0 !important;">
                        عنوان العميل
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="premium-table">
    <thead>
        <tr>
            <th width="5%">S.N<br>مسلسل</th>
            <th width="45%">Product Description<br>وصف المنتج</th>
            <th width="10%">Unit<br>الوحدة</th>
            <th width="10%">Qty.<br>الكمية</th>
            @if(!in_array($invoice['type'], ['work_order', 'goods_receipt', 'goods_issue']))
            <th width="15%">Price<br>السعر</th>
            <th width="15%">Total<br>الاجمالي</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($invoice['lines'] as $index => $line)
        <tr>
            <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
            <td style="text-align: right; padding-right: 8px; font-weight: bold;">{{ $line['item']['name'] ?? $line['description'] }}</td>
            <td style="text-align: center;">{{ $line['item']['unit']['name'] ?? 'PCS' }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($line['quantity'], 2) }}</td>
            @if(!in_array($invoice['type'], ['work_order', 'goods_receipt', 'goods_issue']))
            <td style="text-align: center; font-weight: bold;">{{ number_format($line['unit_price'], 2) }}</td>
            <td style="text-align: center; font-weight: bold; color: #1e293b;">{{ number_format($line['total'], 2) }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<table width="100%" style="margin-top: 15px; border-collapse: collapse; border: none;">
    <tr>
        <!-- Totals Table -->
        @if(!in_array($invoice['type'], ['work_order', 'goods_receipt', 'goods_issue']))
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
                        الإجمالي ق الضريبة / Before Vat
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
        @else
        <td width="38%" style="border: none;"></td>
        @endif
        
        <!-- QR Code Block -->
        <td width="22%" style="vertical-align: middle; text-align: center; padding: 0 10px; border: none;">
            @if(!in_array($invoice['type'], ['work_order', 'goods_receipt', 'goods_issue']) && isset($qrCode))
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 95px; height: 95px; border: 1px solid #cbd5e1; padding: 4px; background-color: #fff; border-radius: 4px;">
            @endif
        </td>
        
        <!-- Bank Info Block -->
        <td width="40%" style="vertical-align: top; padding: 0; border: none;">
            <table width="100%" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9px; background-color: #f8fafc; border-radius: 4px;">
                <tr>
                    <td colspan="2" style="background-color: #f1f5f9; padding: 6px; text-align: center; font-weight: bold; border-bottom: 1px solid #cbd5e1; color: #1e3a8a;">
                        معلومات الحساب البنكي / Bank Account Info
                    </td>
                </tr>
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
                    <td style="padding: 6px; background-color: #f1f5f9; border-left: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #475569;">
                        طريقة السداد
                    </td>
                    <td style="padding: 6px; text-align: center; font-weight: bold; color: #334155;">
                        حوالة بنكية <span style="font-family: 'DejaVu Sans', sans-serif; font-size: 8px; font-weight: normal; color: #64748b;">( Payment Method )</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

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

<!-- Stamp area -->
<div style="margin-top: 15px; text-align: left; padding-left: 30px;">
    <div style="width: 90px; height: 90px; border: 2px double #1e3a8a; border-radius: 50%; display: inline-block; text-align: center; padding-top: 15px; color: #1e3a8a; font-size: 7px; font-weight: bold; line-height: 1.4; background-color: #fff;">
        مؤسسة التفاؤل العربية<br>س.ت ١٠١٠٨٠٣٠٤٥<br>المبيعات<br><span style="font-size: 5px; font-family: 'DejaVu Sans', sans-serif;">Arab Optimism Est.</span>
    </div>
</div>

@endsection
