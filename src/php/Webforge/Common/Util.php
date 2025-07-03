<?php

declare(strict_types=1);

namespace Webforge\Common;

use Closure;
use Doctrine\Common\Collections\Collection;
use Traversable;
use Webforge\Common\ArrayUtil as A;

class Util
{
    public const INFO_PLAIN_ARRAY = 'plain_array';

    /**
     * Verbose as much as possible for this var
     *
     * use this function for debug purposes and dont rely on the output
     * it's slow!
     *
     * @param mixed $var
     */
    public static function varInfo($var, $type = null): string
    {
        if ($var === null) {
            return 'NULL';
        }

        $type = self::getType($var);

        if ($type == 'string') {
            return sprintf(
                'string(%d) "%s"',
                mb_strlen($var),
                (string) $var
            );
        } elseif ($type == 'array') {
            return sprintf(
                'array(%s)',
                A::implode($var, ', ', fn ($item): string => Util::varInfo($item))
            );
        } elseif ($var instanceof Info) {
            return $var->getVarInfo();
        } elseif ($var instanceof \stdClass) {
            return '(object) ' . json_encode($var);
        } elseif (is_object($var)) {
            return sprintf('%s(%s)', method_exists($var, '__toString') ? $var->__toString() : 'not converted to string', self::typeInfo($var));
        } elseif (is_bool($var)) {
            return sprintf('boolean(%s)', $var ? 'true' : 'false');
        } else {
            return sprintf('%s(%s)', self::typeInfo($var), (string) $var);
        }
    }

    /**
     * return-Values sind:
     *
     * - unknown type
     * - bool
     * - int
     * - float (auch für double)
     * - string
     * - array
     * - resource:$resourcetype
     * - object:$class
     */
    public static function getType(mixed $var): string {
        $type = gettype($var);

        if ($type == 'object') {
            return 'object:' . $var::class;
        }
        if ($type == 'boolean') {
            return 'bool';
        }
        if ($type == 'double') {
            return 'float';
        }
        if ($type == 'integer') {
            return 'int';
        }
        if ($type == 'resource') {
            return 'resource:' . get_resource_type($var);
        }

        return $type;
    }

    /**
     * Gibt Verbose-Informationen über einen Variablen-Typ aus
     *
     * Diese Funktion wirklich nur zu Debug-Zwecken benutzen (in Exceptions), da sie sehr langsam ist
     *
     * Gbit den Typ und weitere Informationen zum Typ zurück
     */
    public static function typeInfo(mixed $var): string {
        $string = gettype($var);

        if ($string == 'object') {
            $string .= ' (' . $var::class . ')';
        }

        if ($string == 'array') {
            $string .= ' (' . count($var) . ')';
        }

        if ($string == 'resource') {
            $string .= ' (' . get_resource_type($var) . ')';
        }

        return $string;
    }

    public static function castGetterFromSample(mixed $getter, mixed $sampleObject): Closure {
        if (!($getter instanceof Closure)) {
            if (mb_strpos($getter, 'get') !== 0) {
                if (ClassUtil::hasPublicProperty($sampleObject, $getter)) {
                    $prop = $getter;
                    $getter = (fn ($o) => $o->$prop);
                } else {
                    $get = 'get' . ucfirst($getter);
                    $getter = (fn ($o) => $o->$get());
                }
            } else {
                $get = $getter;
                $getter = (fn ($o) => $o->$get());
            }
        }

        return $getter;
    }

    /**
     * @param array|Traversable|mixed $collection
     */
    public static function castArray(mixed $collection): array
    {
        if ($collection instanceof Collection) {
            return $collection->toArray();
        } elseif (is_array($collection)) {
            return $collection;
        } elseif ($collection instanceof Traversable) {
            return iterator_to_array($collection);
        } else {
            return (array) $collection;
        }
    }
}
