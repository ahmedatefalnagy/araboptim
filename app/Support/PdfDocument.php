<?php

namespace App\Support;

use App\Services\PdfService;
use Symfony\Component\HttpFoundation\Response;

/**
 * يمثّل مستند PDF قيد البناء — يخزّن القالب والبيانات حتى لحظة الإخراج،
 * ثم يولّد عبر PdfService (mPDF). يدعم نفس دوال DomPDF الشائعة.
 */
class PdfDocument
{
    protected array $config = [];

    public function __construct(
        protected string $view,
        protected array $data = [],
    ) {
    }

    /**
     * ضبط حجم الورق والاتجاه (متوافق مع DomPDF: setPaper('a4', 'landscape')).
     */
    public function setPaper(string $size = 'a4', string $orientation = 'portrait'): static
    {
        $this->config['format'] = strtoupper($size);
        $this->config['orientation'] = strtolower($orientation) === 'landscape' ? 'L' : 'P';

        return $this;
    }

    public function download(string $filename = 'document.pdf'): Response
    {
        return app(PdfService::class)->download($this->view, $this->data, $filename, $this->config);
    }

    public function stream(string $filename = 'document.pdf'): Response
    {
        return app(PdfService::class)->stream($this->view, $this->data, $filename, $this->config);
    }

    public function output(): string
    {
        return app(PdfService::class)->raw($this->view, $this->data, $this->config);
    }
}
