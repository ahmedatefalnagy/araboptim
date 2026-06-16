<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 10px; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header-table { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 100px; }
        .doc-title-container { text-align: center; }
        .doc-title { font-weight: bold; font-size: 18px; color: #000; border: 2px solid #000; padding: 5px 20px; display: inline-block; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { border: 1px solid #ddd; padding: 8px; }
        .label { font-weight: bold; font-size: 10px; color: #666; }
        .value { font-weight: bold; font-size: 12px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #333; color: white; padding: 10px; font-size: 11px; }
        .items-table td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 12px; }

        .sigs { width: 100%; margin-top: 100px; }
        .sig-box { text-align: center; border-top: 1px solid #000; width: 30%; font-weight: bold; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="30%">
                <img src="{{ public_path('logo.png') }}" class="logo">
            </td>
            <td width="40%" class="doc-title-container">
                <div class="doc-title">{{ $title }}</div>
            </td>
            <td width="30%" style="text-align: left; direction: ltr;">
                <p><strong>ARAB OPTIM EST.</strong></p>
                <p>REF: {{ $invoice->invoice_no }}</p>
                <p>DATE: {{ $invoice->invoice_date->format('Y/m/d') }}</p>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td width="60%">
                <div class="label">مرسل إلى / Customer:</div>
                <div class="value">{{ $invoice->contact?->name }}</div>
            </td>
            <td width="40%">
                <div class="label">طريقة التوصيل / Delivery Info:</div>
                <div class="value">---</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">العنوان / Address:</div>
                <div class="value">{{ $invoice->contact?->address ?? '--' }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">م</th>
                <th width="20%">رقم الصنف</th>
                <th width="50%">وصف البضاعة / Description of Goods</th>
                <th width="20%">الكمية / Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $index => $line)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $line->item?->code ?? '-' }}</td>
                <td style="text-align: right;">{{ $line->item?->name ?? $line->description }}</td>
                <td style="font-weight: bold; font-size: 14px;">{{ number_format($line->quantity, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; min-height: 50px;">
        <strong>ملاحظات / Remarks:</strong><br>
        {{ $invoice->notes ?? 'لا يوجد' }}
    </div>

    <table class="sigs">
        <tr>
            <td class="sig-box" style="float: right;">أمين المستودع<br>Store Keeper</td>
            <td width="5%"></td>
            <td class="sig-box" style="float: right;">المندوب / السائق<br>Driver</td>
            <td width="5%"></td>
            <td class="sig-box" style="float: right;">توقيع المستلم<br>Receiver Signature</td>
        </tr>
    </table>

</body>
</html>
