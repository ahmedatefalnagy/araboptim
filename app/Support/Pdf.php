<?php

namespace App\Support;

/**
 * جسر متوافق مع واجهة barryvdh/laravel-dompdf لكنه يولّد عبر mPDF.
 *
 * الهدف: توحيد كل توليد الـ PDF في المشروع على محرّك واحد (mPDF) بدعم عربي/RTL أصلي،
 * مع إبقاء نفس أسلوب الاستدعاء (Pdf::loadView(...)->download(...)) في المتحكمات
 * لتقليل التغييرات. ما عليك سوى تبديل سطر الاستيراد:
 *   use Barryvdh\DomPDF\Facade\Pdf;  →  use App\Support\Pdf;
 */
class Pdf
{
    public static function loadView(string $view, array $data = []): PdfDocument
    {
        return new PdfDocument($view, $data);
    }
}
