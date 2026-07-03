<html lang="ar" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { 
            margin: 110px 30px 60px 30px; 
        }
        
        header {
            position: fixed;
            top: -90px;
            left: 0px;
            right: 0px;
            height: 80px;
            border-bottom: 2px solid #1e3a8a;
        }

        footer {
            position: fixed;
            bottom: -35px;
            left: -10px;
            right: -10px;
            height: 24px;
            background: #1e3a8a;
            color: #fff;
            padding: 4px 10px;
            font-size: 9px;
            text-align: center;
            font-weight: bold;
            border-radius: 4px;
        }

        @font-face {
            font-family: 'Amiri';
            src: url("{{ public_path('fonts/Amiri.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Amiri', 'DejaVu Sans', serif;
            direction: ltr;
            text-align: right;
            font-size: 10px;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        .top-accent {
            position: fixed;
            top: -110px;
            left: -30px;
            right: -30px;
            height: 6px;
            background: #1e3a8a;
            z-index: 2000;
        }

        .premium-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1px solid #cbd5e1;
        }
        .premium-table thead th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 8px 6px;
            font-size: 9px;
            text-align: center;
            border: 1px solid #cbd5e1;
        }
        .premium-table tbody td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            text-align: center;
            color: #334155;
        }
        
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; padding-right: 8px !important; }
        .text-left { text-align: left !important; padding-left: 8px !important; }

    </style>
</head>
<body>

    <div class="top-accent"></div>
    <header>
        <table width="100%" style="border-collapse: collapse; border: none;">
            <tr>
                @if(isset($invoice) && in_array($invoice['type'], ['purchase', 'purchase_return', 'purchase_quotation', 'purchase_order']))
                    <td width="40%" style="text-align: left; vertical-align: middle; font-size: 8px; color: #475569; padding-left: 10px;">
                        <div><strong>VAT No :</strong> {{ $invoice['contact']['tax_number'] ?? 'N/A' }}</div>
                        <div><strong>Phone :</strong> {{ $invoice['contact']['phone'] ?? 'N/A' }}</div>
                        <div><strong>Address :</strong> {{ \App\Helpers\PdfHelper::fixArabic($invoice['contact']['address'] ?? 'N/A') }}</div>
                    </td>
                    <td width="60%" style="text-align: right; vertical-align: middle; padding-right: 15px;">
                        <div style="font-size: 16px; font-weight: bold; color: #1e3a8a;">
                            {{ \App\Helpers\PdfHelper::fixArabic($invoice['contact']['name'] ?? 'مورد غير معروف') }}
                        </div>
                        <div style="font-size: 10px; font-weight: bold; color: #475569; margin-top: 2px;">
                            فاتورة صادرة من المورد / Supplier Invoice
                        </div>
                    </td>
                @else
                    <td width="15%" style="text-align: left; vertical-align: middle;">
                        @php $logoPath = public_path('logo.png'); @endphp
                        @if(file_exists($logoPath))
                            <img src="{{ $logoPath }}" style="max-height: 55px;">
                        @else
                            <div style="color: #0056b3; font-weight: bold; font-size: 16px;">ARAB OPTIMISM</div>
                        @endif
                    </td>
                    <td width="85%" style="text-align: right; vertical-align: middle; padding-right: 15px;">
                        <div style="font-size: 18px; font-weight: bold; color: #0056b3;">
                            {{ \App\Helpers\PdfHelper::fixArabic(\App\Models\Setting::get('company_name', 'شركة التفاؤل العربية للخدمات اللوجستية')) }}
                        </div>
                        <div style="font-size: 11px; font-weight: bold; color: #0056b3; font-family: 'DejaVu Sans', sans-serif; margin-top: 2px;">
                            {{ \App\Models\Setting::get('company_name_en', 'ARAB OPTIMISM for Logistic services Co.') }}
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    </header>

    <footer>
        <table width="100%" style="color: #fff; border: none; font-size: 9px; border-collapse: collapse;">
            <tr>
                <td width="30%" style="text-align: right; border: none;">Email: {{ \App\Models\Setting::get('company_email', 'group@araboptimism.com') }}</td>
                <td width="40%" style="text-align: center; border: none;">Address: {{ \App\Helpers\PdfHelper::fixArabic(\App\Models\Setting::get('company_address', 'الرياض - الملز - شارع الهواري')) }}</td>
                <td width="30%" style="text-align: left; border: none;">Phone: {{ \App\Models\Setting::get('company_phone', '0507086023 - 0549545002') }}</td>
            </tr>
        </table>
    </footer>

    <div class="content">
        @yield('content')
    </div>

</body>
</html>
