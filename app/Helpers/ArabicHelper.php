<?php

namespace App\Helpers;

use ArPHP\I18N\Arabic;

class ArabicHelper
{
    /**
     * Shape Arabic text for PDF generation.
     * This handles glyph joining and RTL reversing for DomPDF.
     * 
     * @param string $text
     * @return string
     */
    public static function shape($text)
    {
        // مع محرّك mPDF لم نعد بحاجة لتشكيل النص مسبقاً (utf8Glyphs):
        // mPDF يتولّى تشكيل العربية والاتجاه RTL أصلياً. التشكيل المسبق يُفسد العرض،
        // لذا نُعيد النص كما هو. (أُبقيت الدالة لتفادي تعديل كل القوالب التي تستدعيها.)
        return $text === null ? '' : (string) $text;
    }
}
