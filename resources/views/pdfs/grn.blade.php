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
        .header-table { width: 100%; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 100px; }
        .doc-title-container { text-align: center; }
        .doc-title { font-weight: bold; font-size: 18px; color: #0056b3; border: 2px solid #0056b3; padding: 5px 20px; display: inline-block; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { border: 1px solid #ddd; padding: 8px; }
        .label { font-weight: bold; font-size: 10px; color: #666; }
        .value { font-weight: bold; font-size: 12px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #0056b3; color: white; padding: 10px; font-size: 11px; }
        .items-table td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 12px; }

        .sigs { width: 100%; margin-top: 100px; }
        .sig-box { text-align: center; border-top: 1px solid #000; width: 45%; font-weight: bold; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="30%">
                <img src="{{ public_path('logo.png') }}" class="logo">
            </td>
            <td width="40%" class="doc-title-container">
                <div class="doc-title">سند استلام بضاعة / Goods Receipt</div>
            </td>
            <td width="30%" style="text-align: left; direction: ltr;">
                <p><strong>ARAB OPTIM EST.</strong></p>
                <p>GRN NO: {{ $invoice->invoice_no }}</p>
                <p>DATE: {{ $invoice->invoice_date->format('Y/m/d') }}</p>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td width="60%">
                <div class="label">المورد / Supplier:</div>
                <div class="value">{{ $invoice->contact?->name }}</div>
            </td>
            <td width="40%">
                <div class="label">رقم الفاتورة الأصلية:</div>
                <div class="value">{{ $invoice->invoice_no }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">م</th>
                <th width="20%">رقم الصنف</th>
                <th width="50%">وصف البضاعة المستلمة / Received Goods</th>
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

    <table class="sigs">
        <tr>
            <td class="sig-box" style="float: right;">الفاحص / المستلم<br>Inspected By / Receiver</td>
            <td width="10%"></td>
            <td class="sig-box" style="float: right;">أمين المستودع<br>Store Keeper</td>
        </tr>
    </table>

</body>
</html>
