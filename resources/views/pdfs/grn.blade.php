<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 15px; }
        body {
            font-family: 'DejaVu Sans', 'Amiri', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #334155;
            margin: 0;
            padding: 15px;
        }
        .header-table { 
            width: 100%; 
            border-bottom: 2px solid #1e3a8a; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header-table td {
            vertical-align: middle;
            border: none;
        }
        .logo { max-height: 55px; }
        .doc-title-container { text-align: center; }
        .doc-title { 
            font-weight: bold; 
            font-size: 16px; 
            color: #1e3a8a; 
            border: 1px solid #1e3a8a; 
            background-color: #f1f5f9;
            padding: 6px 20px; 
            display: inline-block; 
            border-radius: 4px;
        }

        .meta-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 6px;
        }
        .meta-table td { 
            border: 1px solid #e2e8f0; 
            padding: 8px 12px; 
        }
        .label { 
            font-weight: bold; 
            font-size: 10px; 
            color: #64748b; 
            margin-bottom: 3px;
        }
        .value { 
            font-weight: bold; 
            font-size: 12px; 
            color: #1e293b;
        }

        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            border: 1px solid #cbd5e1;
        }
        .items-table th { 
            background: #1e3a8a; 
            color: white; 
            padding: 8px 10px; 
            font-size: 11px; 
            border: 1px solid #cbd5e1;
        }
        .items-table td { 
            border: 1px solid #e2e8f0; 
            padding: 8px 10px; 
            text-align: center; 
            font-size: 11px; 
            color: #334155;
        }

        .sigs { 
            width: 100%; 
            margin-top: 50px; 
            border-collapse: collapse;
        }
        .sigs td {
            border: none;
            vertical-align: top;
        }
        .sig-box { 
            text-align: center; 
            border-top: 1px solid #cbd5e1; 
            width: 45%; 
            font-weight: bold; 
            padding-top: 8px;
            color: #475569;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="30%">
                @php $logoPath = public_path('logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo">
                @else
                    <div style="color: #1e3a8a; font-weight: bold; font-size: 16px;">ARAB OPTIMISM</div>
                @endif
            </td>
            <td width="40%" class="doc-title-container">
                <div class="doc-title">سند استلام بضاعة / Goods Receipt</div>
            </td>
            <td width="30%" style="text-align: left; direction: ltr; color: #475569;">
                <p style="margin: 2px 0;"><strong>ARAB OPTIM EST.</strong></p>
                <p style="margin: 2px 0;">GRN NO: {{ $invoice->invoice_no }}</p>
                <p style="margin: 2px 0;">DATE: {{ $invoice->invoice_date->format('Y/m/d') }}</p>
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
                <td style="text-align: right; padding-right: 8px;">{{ $line->item?->name ?? $line->description }}</td>
                <td style="font-weight: bold; font-size: 12px; color: #1e293b;">{{ number_format($line->quantity, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sigs">
        <tr>
            <td class="sig-box">الفاحص / المستلم<br>Inspected By / Receiver</td>
            <td width="10%"></td>
            <td class="sig-box">أمين المستودع<br>Store Keeper</td>
        </tr>
    </table>

</body>
</html>
