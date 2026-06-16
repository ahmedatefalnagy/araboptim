<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

/**
 * خدمة موحّدة لتوليد ملفات PDF عبر mPDF.
 *
 * مزايا mPDF مقابل DomPDF:
 *  - تشكيل العربية والاتجاه RTL بشكل أصلي (لا حاجة لـ utf8Glyphs أو قلب الاتجاه يدوياً).
 *  - دعم خوارزمية BiDi لخلط العربي والإنجليزي والأرقام بشكل سليم.
 *
 * ملاحظة: النصوص العربية تُمرَّر "خام" (بدون تشكيل مسبق). أي تشكيل مسبق
 * عبر ArPHP::utf8Glyphs سيُفسد العرض في mPDF، لذا تم تحييد PdfHelper::fixArabic.
 */
class PdfService
{
    /** إعدادات mPDF الأساسية المشتركة بين كل المستندات. */
    protected array $baseConfig;

    public function __construct()
    {
        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $this->baseConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            // تسجيل خط Amiri العربي مع دعم OTL (التشكيل) والكشيدة.
            'fontDir' => array_merge($defaultConfig['fontDir'], [public_path('fonts')]),
            // ملاحظة: لا نُفعّل useOTL لخط Amiri لأن بعض إصداراته تحتوي جداول
            // GPOS متقدمة (Lookup Type 5, Format 3) لا يدعمها مُحلّل خطوط mPDF.
            // مُشكِّل العربية المدمج في mPDF يكفي لعرض النص متصلاً وبشكل صحيح.
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'amiri' => [
                    'R' => 'Amiri.ttf',
                ],
            ],
            'default_font' => 'amiri',
            'default_font_size' => 10,
            // اكتشاف اللغة تلقائياً واختيار الخط المناسب لها.
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];
    }

    /**
     * بناء كائن mPDF من قالب Blade.
     */
    public function make(string $view, array $data = [], array $config = []): Mpdf
    {
        $mpdf = new Mpdf(array_merge($this->baseConfig, $config));

        $html = View::make($view, $data)->render();
        $mpdf->WriteHTML($html);

        return $mpdf;
    }

    /**
     * عرض الـ PDF داخل المتصفح (inline).
     */
    public function stream(string $view, array $data, string $filename, array $config = []): Response
    {
        return $this->toResponse($this->make($view, $data, $config), $filename, 'inline');
    }

    /**
     * تنزيل الـ PDF كملف (attachment).
     */
    public function download(string $view, array $data, string $filename, array $config = []): Response
    {
        return $this->toResponse($this->make($view, $data, $config), $filename, 'attachment');
    }

    /**
     * إرجاع محتوى الـ PDF كنص خام (لحفظه أو إرفاقه — مفيد لاحقاً لتكامل ZATCA).
     */
    public function raw(string $view, array $data, array $config = []): string
    {
        return $this->make($view, $data, $config)->Output('', Destination::STRING_RETURN);
    }

    protected function toResponse(Mpdf $mpdf, string $filename, string $disposition): Response
    {
        $content = $mpdf->Output('', Destination::STRING_RETURN);

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }
}
