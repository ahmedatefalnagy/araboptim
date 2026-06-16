<?php
function gregorianToHijri($date)
{
    if (empty($date)) return '';
    $time = is_numeric($date) ? $date : strtotime($date);
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

echo "Date: 2026-05-12 -> Hijri: " . gregorianToHijri('2026-05-12') . "\n";
echo "Date: 2026-04-02 -> Hijri: " . gregorianToHijri('2026-04-02') . "\n";
