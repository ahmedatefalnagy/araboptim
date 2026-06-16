<?php

namespace App\Helpers;

use ArPHP\I18N\Arabic;

class PdfHelper
{
    /**
     * وظيفة ذكية لتحويل النصوص العربية المتقطعة إلى نصوص متصلة وجاهزة للـ PDF
     */
    public static function fixArabic($text)
    {
        // مع محرّك mPDF لم نعد بحاجة إلى تشكيل النص مسبقاً (utf8Glyphs):
        // فـ mPDF يتولّى تشكيل العربية والاتجاه RTL أصلياً. أي تشكيل مسبق هنا
        // سيؤدي إلى عرض معكوس/مكسور، لذا نُعيد النص كما هو (pass-through).
        // تم الإبقاء على الدالة لتفادي تعديل كل القوالب التي تستدعيها.
        return $text;
    }

    /**
     * تحويل المبلغ إلى كلمات باللغة العربية (تفقيط)
     */
    public static function amountInWords($amount)
    {
        if (empty($amount) || $amount <= 0) return '';
        
        try {
            $amount = round($amount, 2);
            $formatted = number_format($amount, 2, '.', '');
            $parts = explode('.', $formatted);
            $riyal = intval($parts[0]);
            $halala = intval($parts[1]);
            
            $Arabic = new Arabic();
            $riyalWords = $Arabic->int2str($riyal);
            
            $result = "فقط " . $riyalWords . " ريال سعودي";
            
            if ($halala > 0) {
                $halalaWords = $Arabic->int2str($halala);
                $result .= " و" . $halalaWords . " هللة";
            }
            
            $result .= " لا غير";
            return self::fixArabic($result);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * معالجة البيانات بشكل تكراري (تصلح للمصفوفات والكائنات)
     */
    public static function fixArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $value = self::fixArray($value);
            }
        } elseif (is_object($data)) {
            // تحويل الكائن إلى مصفوفة لضمان التوافق مع قوالب Blade
            if (method_exists($data, 'toArray')) {
                $array = $data->toArray();
            } else {
                $array = (array) $data;
            }
            return self::fixArray($array);
        } elseif (is_string($data)) {
            return self::fixArabic($data);
        }
        return $data;
    }

    /**
     * تحويل تاريخ ميلادي إلى هجري
     */
    public static function gregorianToHijri($date)
    {
        if (empty($date)) return '';
        $time = is_numeric($date) ? $date : strtotime($date);
        if (!$time) return '';

        $year = date('Y', $time);
        $month = date('m', $time);
        $day = date('d', $time);

        $jd = gregoriantojd($month, $day, $year);
        $l = $jd - 1948440 + 10632;
        $n = intval(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (intval((10985 - $l) / 5316)) * (intval((50 * $l) / 17719)) + (intval($l / 5670)) * (intval((43 * $l) / 15238));
        $l = $l - (intval((30 - $j) / 15)) * (intval((17719 * $j) / 50)) - (intval($j / 16)) * (intval((15238 * $j) / 43)) + 29;
        $m = intval((24 * $l) / 709);
        $d = $l - intval((709 * $m) / 24);
        $y = 30 * $n + $j - 30;

        return sprintf('%04d/%02d/%02d', $y, $m, $d);
    }

    /**
     * اسم الشهر بالعربي
     */
    public static function arabicMonthName($monthNumber)
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        return $months[intval($monthNumber)] ?? '';
    }
}
