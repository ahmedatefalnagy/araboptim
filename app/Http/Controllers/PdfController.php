<?php

namespace App\Http\Controllers;

use App\Helpers\PdfHelper;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Voucher;
use App\Services\PdfService;
use App\Services\ZatcaService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends Controller
{
    public function __construct(
        protected PdfService $pdf,
        protected ZatcaService $zatca,
    ) {
    }

    public function invoice(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.item', 'creator', 'trips.vehicle']);
        $hasTrips = $invoice->trips()->exists();

        // رمز ZATCA QR (المرحلة الأولى) — بيانات البائع من الإعدادات وليست ثابتة في الكود.
        $qrData = $this->zatca->generateQrCodeBase64(
            Setting::get('company_name_en', 'ARAB OPTIMISM for Logistic services Co.'),
            Setting::get('company_vat_no', '312253166440003'),
            $invoice->created_at->format('Y-m-d\TH:i:s\Z'),
            $invoice->total_amount,
            $invoice->total_tax
        );

        $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($qrData));

        $titles = [
            'sale' => 'فاتورة ضريبية / Tax Invoice',
            'sale_quotation' => 'عرض سعر / Price Quotation',
            'sale_order' => 'أمر بيع / Sales Order',
            'purchase' => 'فاتورة مشتريات / Purchase Invoice',
            'purchase_quotation' => 'طلب شراء / Purchase Request',
            'purchase_order' => 'أمر شراء / Purchase Order',
            'sale_return' => 'إشعار دائن - مردود مبيعات / Credit Note',
            'purchase_return' => 'إشعار مدين - مردود مشتريات / Debit Note',
            'goods_receipt' => 'سند استلام مواد / Goods Receipt Note',
            'goods_issue' => 'سند صرف مواد / Goods Issue Note',
        ];

        $data = PdfHelper::fixArray([
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'title' => $titles[$invoice->type] ?? 'فاتورة / Invoice',
        ]);

        $viewName = $hasTrips ? 'pdfs.truck_invoice' : 'pdfs.invoice';

        return $this->pdf->stream($viewName, $data, "Invoice_{$invoice->invoice_no}.pdf");
    }

    public function deliveryNote(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.item']);

        $data = PdfHelper::fixArray([
            'invoice' => $invoice,
            'title' => 'سند تسليم بضاعة / Delivery Note',
        ]);

        return $this->pdf->stream('pdfs.delivery_note', $data, "DeliveryNote_{$invoice->invoice_no}.pdf");
    }

    public function grn(Invoice $invoice)
    {
        $invoice->load(['contact', 'lines.item']);

        $data = PdfHelper::fixArray([
            'invoice' => $invoice,
            'title' => 'سند استلام بضاعة / Goods Receipt Note',
        ]);

        return $this->pdf->stream('pdfs.grn', $data, "GRN_{$invoice->invoice_no}.pdf");
    }

    public function voucher(Voucher $voucher)
    {
        $voucher->load(['contact', 'debitAccount', 'creditAccount']);

        $titles = [
            'receipt' => 'سند قبض / Receipt Voucher',
            'payment' => 'سند صرف / Payment Voucher',
            'expense' => 'سند صرف مصروف / Expense Voucher',
        ];

        $data = PdfHelper::fixArray([
            'voucher' => $voucher,
            'title' => $titles[$voucher->type] ?? 'سند مالي / Voucher',
        ]);

        return $this->pdf->stream('pdfs.voucher', $data, "Voucher_{$voucher->voucher_no}.pdf");
    }
}
