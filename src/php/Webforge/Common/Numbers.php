<?php

declare(strict_types=1);

namespace Webforge\Common;

class Numbers
{
    public const USE_LOCALE = 0x000001;

    public static function parseFloat($floatString, $thousandsSep = self::USE_LOCALE, $decimalPoint = self::USE_LOCALE): float
    {
        $locale = localeconv();
        if ($thousandsSep === self::USE_LOCALE) {
            $thousandsSep = $locale['mon_thousands_sep'];
        }
        if ($decimalPoint === self::USE_LOCALE) {
            $decimalPoint = $locale['mon_decimal_point'];
        }

        if (mb_substr_count($floatString, ',') > 1) {
            throw new Exception('Could not parse ' . $floatString . ' as float. Found more than one colon (,)');
        }

        $floatString = str_replace([$thousandsSep, $decimalPoint], ['', '.'], $floatString);
        return floatval($floatString);
    }
}
