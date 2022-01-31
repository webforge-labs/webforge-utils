<?php

namespace Webforge\Common;

class StringUtil
{
    public const END = 'end';
    public const START = 'start';

    public const DOUBLE_QUOTE = '"';
    public const SINGLE_QUOTE = "'";
    public const BACKSLASH = '\\';

    /**
     * Does string start with prefix?
     *
     * @param string $string
     * @param string $prefix
     * @return bool
     */
    public static function startsWith($string, $prefix)
    {
        return (mb_strpos($string, $prefix) === 0);
    }

    /**
     * Does string end with suffix?
     *
     * @param string $string
     * @param string $suffix
     */
    public static function endsWith($string, $suffix)
    {
        return (mb_strrpos($string, $suffix) === mb_strlen($string) - mb_strlen($suffix));
    }

    /**
     * Inserts whitespace before every new line (after every EOL)
     *
     * @param string $code  code with $br as EOL
     * @param string $br    the EOL in $code, its unexpected if its not right
     * @return string
     */
    public static function indent($code, $indent=2, $br = "\n")
    {
        // for performance reasons prefixLines is not used
        if ($indent == 0 || $code == '') {
            return $code;
        }
        $cutEnd = self::endsWith($code, $br);
        $code = str_repeat(' ', $indent).str_replace($br, $br.str_repeat(' ', $indent), $code);
        if ($cutEnd) {
            $code = mb_substr($code, 0, -$indent);
        } // letzte weißzeichen vom str_replace entfernen
        return $code;
    }

    /**
     * Adds a prefix before every new line (after every EOL)
     *
     * this is the generic function for indent()
     *
     * @return string
     */
    public static function prefixLines($msg, $prefix, $eol = "\n")
    {
        if (static::endsWith($msg, $eol)) {
            return $prefix.str_replace($eol, $eol.$prefix, mb_substr($msg, 0, -1)).$eol;
        } else {
            return $prefix.str_replace($eol, $eol.$prefix, $msg);
        }
    }

    /**
     * Number the lines in the string
     *
     * @param string $code
     * @param string $eol the eol from $code
     * @param int $begin the number of the first line found
     * @return string
     */
    public static function lineNumbers($code, $eol = "\n", $begin = 1)
    {
        $cut = false;
        if (!static::endsWith($code, $eol)) {
            $code .= $eol;
            $cut = -mb_strlen($eol);
        }
        $lines = mb_substr_count($code, $eol);
        $padWhite = mb_strlen((string) $lines); // darstellung der größten zeilen-nummer als string
        $cnt = $begin;
        $linedCode = Preg::replace_callback(
            $code,
            '/(.*?)'.$eol.'/',
            function ($match) use (&$cnt, $padWhite) {
          return sprintf('%s %s', StringUtil::padRight((string) $cnt++, $padWhite, ' '), $match[0]);
      }
        );

        if ($cut !== false) {
            return mb_substr($linedCode, 0, $cut);
        } else {
            return $linedCode;
        }
    }

    /**
     * Cuts the text (hard) at the given position, if its longer than given length
     *
     * it appends (when cut) the $ender to the string
     * @return string
     */
    public static function cut($string, $length, $ender = '…')
    {
        $length = (int) $length;
        if (mb_strlen($string) > $length) {
            $string = mb_substr($string, 0, $length).$ender;
        }
        return $string;
    }

    /**
     * Cuts at a specific char if string is longer than given length
     *
     * acts like cut, but when the string is longer then $length it is search backwards
     * until $atChar is found and cut at this position.
     *
     * the common case is to wordwrap at whitespace (inbetween two words)
     *
     * $teaser = S::cutAtLast($longText, 300, " ");
     */
    public static function cutAtLast($string, $length, $atChar, $ender = '…')
    {
        $length = (int) $length;
        if (($slength = mb_strlen($string)) > $length) {
            $string = mb_substr($string, 0, mb_strrpos($string, $atChar, $length-$slength)).$ender; // -x means: search backwards from x of end of string
        }
        return $string;
    }

    /**
     * Generates a Random string from specific length
     *
     * the range of chars [a-z0-9] is used
     * @return string
     */
    public static function random($length)
    {
        if ($length <= 0) {
            return '';
        }
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $rand = rand(0, 35);
            if ($rand >= 10) {
                $str .= chr(ord('a') + $rand-10);
            } // a - z
            else {
                $str .= chr(ord('0') + $rand);
            }    // 0 - 9
        }

        return $str;
    }

    /**
     * Upcase the first letter
     *
     * @return string
     */
    public static function ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }

    /**
     * Lowercase the first letter
     *
     * @return string
     */
    public static function lcfirst($string)
    {
        return mb_strtolower(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }

    /**
     * Show EOLs in Text (for debugging)
     *
     * @return string
     */
    public static function eolVisible($string)
    {
        return str_replace(
            array("\r", "\n", "-r-\r-n-\n"),
            array("-r-\r", "-n-\n", "-rn-\r\n"),
            $string
        );
    }

    /**
     * Normalize Text EOLs to unix lineends
     *
     * @return string
     */
    public static function fixEOL($string)
    {
        return str_replace(array("\r\n", "\r"), "\n", $string);
    }

    /**
     * More visible equals verbose for two strings
     *
     * often unified diffs are hard to read. Use something like this:
     * $test->assertEquals($expected, $actual, S::debugEquals($expected, $actual));
     * @return string
     */
    public static function debugEquals($expected, $actual)
    {
        return sprintf("expected>>>\n%s<<<\n\nactual>>>\n%s<<<\n", $expected, $actual);
    }

    /**
     * Fills the string with $fill on its left until $length
     *
     * @return string
     */
    public static function padLeft($string, $length, $fill = ' ')
    {
        return str_pad($string, $length, $fill, STR_PAD_LEFT);
    }

    /**
     * Fills the string with $fill on its right until $length
     *
     * @return string
     */
    public static function padRight($string, $length, $fill = ' ')
    {
        return str_pad($string, $length, $fill, STR_PAD_RIGHT);
    }

    /**
     * Returns a substring $from position $to position
     *
     * the use of this function is discouraged
     * @deprecated
     * @return string
     */
    public static function substring($string, $from, $to)
    {
        return mb_substr($string, $from, abs($from-$to));
    }

    /**
     * Adds $withString to the end|begining of string when string does not end|begin with $withString
     *
     * expand withString: "Class", self::END
     * Some => SomeClass
     * SomeClass => SomeClass
     *
     * expand withString: "Custom", self::START
     * String => CustomString
     * CustomString => CustomString
     *
     * @return string
     */
    public static function expand($string, $withString, $type = self::END)
    {
        if ($type === self::END) {
            if (!self::endsWith($string, $withString)) {
                $string .= $withString;
            }
        } elseif ($type === self::START) {
            if (!self::startsWith($string, $withString)) {
                $string = $withString.$string;
            }
        }
        return $string;
    }

    /**
     * Wrap the string with a char or chars
     */
    public static function wrap($string, $wrap)
    {
        return $wrap.$string.$wrap;
    }

    /**
     * Symmetric wrap
     *
     * wrap the string with a matching symmetric char.
     * if char has not a defined symmetric, the same char is returned (you could use
     * wrap instead for better performance)
     *
     * [ => ]
     * ( => )
     */
    public static function swrap($string, $symmetricWrapper)
    {
        if ($symmetricWrapper === '[') {
            return '['.$string.']';
        } elseif ($symmetricWrapper === '(') {
            return '('.$string.')';
        } else {
            return $symmetricWrapper.$string.$symmetricWrapper;
        }
    }

    /**
     * Replace %var% with values in the string
     *
     * @return string
     */
    public static function miniTemplate($string, array $vars)
    {
        $string = str_replace(
      // replace %key%
      array_map(create_function('$a', 'return "%".$a."%"; '), array_keys($vars)),
      // with values
      array_values($vars),
      // in
      $string
        );
        return $string;
    }

    /**
     * @return string
     */
    public static function dashToCamelCase($string)
    {
        return ucfirst(Preg::replace_callback($string, '/\-([a-zA-Z])/', function ($match) {
            return mb_strtoupper($match[1]);
        }));
    }

    /**
     * @return string
     */
    public static function camelCaseToDash($camelName)
    {
        if (Preg::match($camelName, '/^[A-Z0-9]+$/')) {
            return mb_strtolower($camelName);
        }

        $specials = preg_quote(implode("", array('.','@','\\',' ','[',']','(',')')), '/');

        $dashed = Preg::replace(
      // in
      $camelName,
      // what
      sprintf(
          '/%s|[%s]/',
          "(?<=\w)([A-Z]|[0-9])",
          $specials
      ),
      // with
      '-\\1'
        );

        $dashed = mb_strtolower($dashed);

        return $dashed;
    }
}
