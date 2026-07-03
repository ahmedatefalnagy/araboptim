<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Amiri';
            src: url("{{ public_path('fonts/Amiri.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        
        @page {
            margin: 20px;
        }

        body {
            font-family: 'Amiri', 'DejaVu Sans', serif;
            direction: ltr;
            text-align: right;
            font-size: 13px;
            color: #334155;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .voucher-container {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            min-height: 520px;
            background-color: #ffffff;
        }

        /* Header Styles */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
        }
        .logo-img {
            max-height: 65px;
        }
        .company-title-ar {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            text-align: right;
        }
        .company-title-en {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            margin-top: 4px;
            line-height: 1.3;
            text-align: right;
        }
        .header-line {
            border-bottom: 3px solid #1e3a8a;
            margin-top: 6px;
            margin-bottom: 14px;
            width: 100%;
        }

        /* Top Grid Info */
        .top-info-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 12px;
        }
        .top-info-table td {
            border: none;
            vertical-align: middle;
        }
        .tax-info-left {
            font-size: 11px;
            text-align: left;
            line-height: 1.4;
            color: #475569;
        }
        .tax-info-right {
            font-size: 11px;
            text-align: right;
            line-height: 1.4;
            color: #475569;
        }
        .voucher-title-box {
            border: 1px solid #1e3a8a;
            border-radius: 6px;
            background-color: #f1f5f9;
            text-align: center;
            padding: 8px 12px;
            display: block;
            width: 150px;
            margin: 0 auto;
        }
        .voucher-title-ar {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1.2;
            margin: 0;
        }
        .voucher-title-en {
            font-size: 10px;
            font-weight: bold;
            color: #475569;
            line-height: 1.0;
            font-family: 'DejaVu Sans', sans-serif;
            margin-top: 3px;
        }

        /* Row of Boxes (Date, Amount, No) */
        .boxes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .boxes-table > tr > td {
            width: 33.33%;
            padding: 0 5px;
            border: none;
        }
        .inner-box-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: #f8fafc;
        }
        .inner-box-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
        }
        .label-cell-vertical {
            background-color: #f1f5f9;
            width: 40px;
            font-weight: bold;
            font-size: 9px !important;
            line-height: 1.1;
            color: #475569;
        }
        .label-cell-horizontal {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 10px;
            width: 65px;
            color: #475569;
        }
        .value-cell {
            background-color: #fff;
            font-weight: bold;
            font-size: 13px;
            color: #1e293b;
        }

        /* Main Form Rows (Received from, Amount, Being) */
        .main-form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .main-form-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            vertical-align: middle;
        }
        .form-label-left {
            background-color: #f1f5f9;
            width: 130px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            color: #475569;
        }
        .form-label-right {
            background-color: #f1f5f9;
            width: 130px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            color: #475569;
        }
        .form-value-center {
            background-color: #fff;
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            color: #1e293b;
        }

        /* Signatures Section */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .signatures-table td {
            width: 25%;
            border: 1px solid #e2e8f0;
            padding: 0;
            vertical-align: top;
        }
        .sig-header {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e3a8a;
        }
        .sig-box {
            height: 75px;
            background-color: #fff;
            padding: 15px 12px;
            text-align: right;
            vertical-align: top;
            font-weight: normal;
            font-size: 11px;
            line-height: 2.2;
        }

        /* Footer Bar */
        .footer-bar-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1e3a8a;
            color: #fff;
            margin-top: 15px;
            border-radius: 4px;
        }
        .footer-bar-table td {
            border: none;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: bold;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="voucher-container">
    
    <!-- Top Header -->
    <table class="header-table">
        <tr>
            <!-- Logo -->
            <td width="25%">
                @php $logoPath = public_path('logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo-img">
                @else
                    <div style="color: #1e3a8a; font-weight: bold; font-size: 18px; font-family: 'DejaVu Sans', sans-serif;">ARAB OPTIMISM</div>
                @endif
            </td>
            <!-- Center Company Titles -->
            <td width="75%" style="text-align: right; padding-right: 15px; padding-left: 25px;">
                <h1 class="company-title-ar">{{ \App\Helpers\PdfHelper::fixArabic(\App\Models\Setting::get('company_name', 'شركة التفاؤل العربية للخدمات اللوجستية')) }}</h1>
                <div class="company-title-en">{{ \App\Models\Setting::get('company_name_en', 'ARAB OPTIMISM for Logistic services Co.') }}</div>
            </td>
        </tr>
    </table>
    
    <div class="header-line"></div>

    <!-- C.R and VAT info + Title Box -->
    <table class="top-info-table">
        <tr>
            <!-- Left Info (English) -->
            <td width="35%" class="tax-info-left">
                C.R: {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}<br>
                VAT No: {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}
            </td>
            <!-- Center Title Box -->
            <td width="30%" style="text-align: center; vertical-align: middle;">
                <div class="voucher-title-box">
                    @if(in_array($voucher['type'], ['receipt', 'petty_cash_receipt']))
                        <div class="voucher-title-ar">{{ \App\Helpers\PdfHelper::fixArabic('سند قبض') }}</div>
                        <div class="voucher-title-en">Receipt Voucher</div>
                    @else
                        <div class="voucher-title-ar">{{ \App\Helpers\PdfHelper::fixArabic('سند صرف') }}</div>
                        <div class="voucher-title-en">Payment Voucher</div>
                    @endif
                </div>
            </td>
            <!-- Right Info (Arabic) -->
            <td width="35%" class="tax-info-right">
                {{ \App\Helpers\PdfHelper::fixArabic('سجل تجاري:') }} {{ \App\Models\Setting::get('company_commercial_record', '1009037942') }}<br>
                {{ \App\Helpers\PdfHelper::fixArabic('الرقم الضريبي:') }} {{ \App\Models\Setting::get('company_vat_no', '312253166440003') }}
            </td>
        </tr>
    </table>

    <!-- Row of Boxes (Date, Amount, No) -->
    <table class="boxes-table">
        <tr>
            <!-- Box 1: Issue Date -->
            <td>
                <table class="inner-box-table">
                    <tr>
                        <td class="label-cell-vertical">Issue<br>Date:</td>
                        <td class="value-cell" style="font-family: 'DejaVu Sans', sans-serif;">{{ \Carbon\Carbon::parse($voucher['date'])->format('d-m-Y') }}</td>
                        <td class="label-cell-vertical">{{ \App\Helpers\PdfHelper::fixArabic('تاريخ') }}<br>{{ \App\Helpers\PdfHelper::fixArabic('السند') }}</td>
                    </tr>
                </table>
            </td>
            <!-- Box 2: Amount -->
            <td>
                @php
                    $amountVal = floatval($voucher['amount']);
                    $integerPart = floor($amountVal);
                    $decimalPart = str_pad(round(($amountVal - $integerPart) * 100), 2, '0', STR_PAD_LEFT);
                @endphp
                <table class="inner-box-table">
                    <tr>
                        <td class="label-cell-horizontal" style="width: 50px;">Amount</td>
                        <td class="value-cell" style="font-size: 15px; font-family: 'DejaVu Sans', sans-serif; color: #1e3a8a;">{{ $integerPart }}</td>
                        <td class="value-cell" style="width: 25px; font-family: 'DejaVu Sans', sans-serif; color: #1e3a8a;">{{ $decimalPart }}</td>
                        <td class="label-cell-horizontal" style="width: 45px;">{{ \App\Helpers\PdfHelper::fixArabic('المبلغ') }}</td>
                    </tr>
                </table>
            </td>
            <!-- Box 3: Voucher Number -->
            <td>
                <table class="inner-box-table">
                    <tr>
                        <td class="label-cell-horizontal" style="font-size: 9px; width: 45px;">Voucher<br>No.:</td>
                        <td class="value-cell" style="font-family: 'DejaVu Sans', sans-serif; color: #1e3a8a;">{{ $voucher['voucher_no'] }}</td>
                        <td class="label-cell-horizontal" style="font-size: 9px; width: 50px;">
                            @if(in_array($voucher['type'], ['receipt', 'petty_cash_receipt']))
                                {{ \App\Helpers\PdfHelper::fixArabic('رقم سند') }}<br>{{ \App\Helpers\PdfHelper::fixArabic('القبض') }}
                            @else
                                {{ \App\Helpers\PdfHelper::fixArabic('رقم سند') }}<br>{{ \App\Helpers\PdfHelper::fixArabic('الصرف') }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Main Content Fields (Name, Amount in words, Being) -->
    <table class="main-form-table">
        
        <!-- Row 1: Pay To / Received From -->
        <tr>
            <td class="form-label-left">
                @if(in_array($voucher['type'], ['receipt', 'petty_cash_receipt']))
                    Received From Mrs.
                @else
                    Pay To M / S
                @endif
            </td>
            <td class="form-value-center" style="color: #1e3a8a;">
                {{ $voucher['contact']['name'] ?? '--' }}
            </td>
            <td class="form-label-right">
                @if(in_array($voucher['type'], ['receipt', 'petty_cash_receipt']))
                    {{ \App\Helpers\PdfHelper::fixArabic('استلمنا من السادة') }}
                @else
                    {{ \App\Helpers\PdfHelper::fixArabic('إصرفوا إلى السيد / السادة') }}
                @endif
            </td>
        </tr>

        <!-- Row 2: Amount in Words -->
        <tr>
            <td class="form-label-left">Amount Cash</td>
            <td class="form-value-center" style="font-size: 12px; color: #0f766e;">
                {{ \App\Helpers\PdfHelper::amountInWords($voucher['amount']) }}
            </td>
            <td class="form-label-right">{{ \App\Helpers\PdfHelper::fixArabic('مبلغ نقداً وقدره') }}</td>
        </tr>

        <!-- Row 3: Being / Description -->
        <tr>
            <td class="form-label-left">Being</td>
            <td class="form-value-center" style="font-size: 12px; font-weight: normal;">
                {{ $voucher['description'] }}
            </td>
            <td class="form-label-right">{{ \App\Helpers\PdfHelper::fixArabic('وذلك مقابل') }}</td>
        </tr>

        <!-- Row 4: Cost Center / مركز التكلفة (Optional) -->
        @if(!empty($voucher['cost_center_id']) && isset($voucher['costCenter']))
        <tr>
            <td class="form-label-left">Cost Center</td>
            <td class="form-value-center" style="font-size: 12px; font-weight: bold; color: #0d9488;">
                {{ $voucher['costCenter']['name'] }}
            </td>
            <td class="form-label-right">{{ \App\Helpers\PdfHelper::fixArabic('مركز التكلفة') }}</td>
        </tr>
        @endif
    </table>

    <!-- Bottom Signatures Grid -->
    <!-- ترتيب الأعمدة بحيث يكون أقصى اليمين للمستلم (اتجاه القراءة العربي): المستلم ← مدير المشاريع/الصندوق ← الحسابات ← المدير العام -->
    <table class="signatures-table">
        <tr>
            <!-- General Manager (leftmost) -->
            <td>
                <div class="sig-header">{{ \App\Helpers\PdfHelper::fixArabic('المدير العام') }} / General Manager</div>
                <div class="sig-box" style="line-height: 1.8; padding: 10px;">
                    <div style="margin-bottom: 6px;">{{ \App\Helpers\PdfHelper::fixArabic('الاسم:') }} ...................................</div>
                    <div>{{ \App\Helpers\PdfHelper::fixArabic('التوقيع:') }} ................................</div>
                </div>
            </td>

            <!-- Accounting -->
            <td>
                <div class="sig-header">{{ \App\Helpers\PdfHelper::fixArabic('الحسابات') }} / Accounting</div>
                <div class="sig-box" style="line-height: 1.8; padding: 10px;">
                    <div style="margin-bottom: 6px;">{{ \App\Helpers\PdfHelper::fixArabic('الاسم:') }} ...................................</div>
                    <div>{{ \App\Helpers\PdfHelper::fixArabic('التوقيع:') }} ................................</div>
                </div>
            </td>

            <!-- Cash or Projects Manager -->
            <td>
                @if(in_array($voucher['type'], ['receipt', 'petty_cash_receipt']))
                    <div class="sig-header">{{ \App\Helpers\PdfHelper::fixArabic('مدير المشاريع') }} / Projects Manager</div>
                @else
                    <div class="sig-header">{{ \App\Helpers\PdfHelper::fixArabic('الصندوق') }} / Cash</div>
                @endif
                <div class="sig-box" style="line-height: 1.8; padding: 10px;">
                    <div style="margin-bottom: 6px;">{{ \App\Helpers\PdfHelper::fixArabic('الاسم:') }} ...................................</div>
                    <div>{{ \App\Helpers\PdfHelper::fixArabic('التوقيع:') }} ................................</div>
                </div>
            </td>

            <!-- Receiver (rightmost) -->
            <td>
                <div class="sig-header">{{ \App\Helpers\PdfHelper::fixArabic('المستلم') }} / Receiver</div>
                <div class="sig-box" style="line-height: 1.8; padding: 10px;">
                    <div style="margin-bottom: 6px;">{{ \App\Helpers\PdfHelper::fixArabic('الاسم:') }} ...................................</div>
                    <div>{{ \App\Helpers\PdfHelper::fixArabic('التوقيع:') }} ................................</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Blue Footer Bar -->
    <table class="footer-bar-table">
        <tr>
            <!-- Left Info -->
            <td width="35%" style="text-align: left;">
                Email: {{ \App\Models\Setting::get('company_email', 'accounts@araboptim.com') }}
            </td>
            <!-- Center Info -->
            <td width="40%" style="text-align: center;">
                {{ \App\Helpers\PdfHelper::fixArabic(\App\Models\Setting::get('company_address', 'الرياض - المغرزات - شارع الامير مقرن بن عبدالعزيز')) }}
            </td>
            <!-- Right Info -->
            <td width="25%" style="text-align: right; font-family: 'DejaVu Sans', sans-serif;">
                Phone: {{ \App\Models\Setting::get('company_phone', '0507086023 - 0536268865') }}
            </td>
        </tr>
    </table>

</div>

</body>
</html>
