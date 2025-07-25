<?php

namespace Webforge\Common;

use Closure;
use OutOfBoundsException;

class ArrayUtil
{
    public const END = 'end_of_array';

    /**
     * Joins an array to string
     *
     * Aus einem Array ein geschriebenes Array machen:
     * print '$GLOBALS'.A::join(array('conf','default','db'), "['%s']");
     * // $GLOBALS['conf']['default']['db']
     * das erste %s ist der Wert aus dem Array das zweite %s ist der Schlüssel aus dem Array.
     * Diese können mit %1$s (wert) %2$s (schlüssel) addressiert werden
     * @param array<mixed> $pieces die Elemente die zusammengefügt werden sollen
     * @param string $glue der String welcher die Elemente umgeben soll. Er muss %s enthalten. An dieser Stelle wird das Array Element ersetzt
     */
    public static function join(array $pieces, string $glue): ?string {
        $s = null;
        foreach ($pieces as $key => $piece) {
            if (is_array($piece)) {
                $piece = 'Array';
            }

            $s .= sprintf($glue, $piece, $key);
        }
        return $s;
    }

    /**
     * Fügt einen Array zu einem String zusammen (Closure Version)
     *
     * Anders als joinc benutzt diese Funktion immer eine Closure (default ist "return (string) $piece;")
     *
     * Bespiel:
     * Aus einem Array ein geschriebenes Array machen:
     * print '$GLOBALS'.A::join(array('conf','default','db'), "['%s']");
     * // $GLOBALS['conf']['default']['db']
     *
     * das erste %sin $glueFormat ist der Wert aus dem Array das zweite %s ist der Schlüssel aus dem Array.
     * Diese können mit %1$s (wert) %2$s (schlüssel) addressiert werden
     *
     * @param array<mixed> $pieces die Elemente die zusammengefügt werden sollen
     * @param string $glueFormat der String welcher die Elemente umgeben soll. Er muss %s enthalten. An dieser Stelle wird das Array Element ersetzt
     * @param Closure $stringConvert ist dies nicht gesetzt wird (string) $piece benutzt
     */
    public static function joinc(array $pieces, string $glueFormat, ?Closure $stringConvert = null): ?string {
        $s = null;
        if (!isset($stringConvert)) {
            $stringConvert = function ($piece) {
                return (string) $piece;
            };
        }

        foreach ($pieces as $key => $piece) {
            $piece = $stringConvert($piece, $key);
            $s .= sprintf(
                $glueFormat,
                $piece,
                $key
            );
        }

        return $s;
    }

    public static function peek(array $stack): mixed {
        return array_pop($stack);
    }

    /**
     * gibt NULL zurück wenn der Array leer ist
     */
    public static function first(array $stack): mixed {
        return array_shift($stack);
    }

    /**
     * Kopiert die Variablen, fügt einen Wert hinzu (mit array_push()) und gibt den neuen Array zurück
     *
     * $array ist nicht modifiziert!
     */
    public static function push(array $array, mixed $value): array {
        array_push($array, $value);
        return $array;
    }

    /**
     * Fügt in einen numerischen Array innerhalb an einer bestimmten Stelle ein
     *
     * Das element wird eingefügt und die Elemente dahinter nach hinten verschoben<br />
     * ACHTUNG: Die Funktion hat ein undefiniertes verhalten mit assoziativen arrays!
     * offset kann hier auch die Array länge sein, dann wird das Item ans Ende des Arrays eingefügt
     *
     * ist offset A::END wird an der Position NACH dem letzten Element (also ans Ende angefügt)
     * ist offset z.b. -1 wird an der position VOR dem letzten Element eingefügt (dies war eine backwarts incompatible change)
     *
     *   $array = array(0,0,0,0,2,3);
     *   A::insert($array, 1, -2);
     *   => array(0,0,0,0,1,2,3)
     *
     * @param array<mixed> $array der zu modifizierende Array (unbedingt numerisch)
     * @param array<mixed> $item das Item welches eingefügt werden soll
     * @param int $offset von 0 - count($array) und -1 - -count(Array)-1. Das eingefügte Item bekommt dieses Offset. kann -1 für das Ende des Arrays sein. -2 für das Einfügen vor dem letzten Element usw
     */
    public static function insert(array &$array, mixed $item, mixed $offset): array {
        return self::insertArray($array, [$item], $offset);
    }

    /**
     * So wie insert, jedoch wird nicht nur ein Element sondern alle aus $array and der position $offset gemerged
     *
     * undefiniert bei assoziativen arrays.
     * Die Keys in $subject werden verändert
     *
     * siehe tests
     * @param array<mixed> $subject dieses Array wird verändert
     * @param array<mixed> $array dieses Array wird in $subject an Position $offset eingefügt
     * @param int|const $offset kann self::END für das hinzufügen von $array am Ende. Kann negativ sein (z.b. -1 für das letzte elemente) für die position vor dem Element von hinten gezählt
     */
    public static function insertArray(array &$subject, array $array, mixed $offset): array {
        $length = count($subject);

        $offsetArg = $offset;
        if ($offset < 0) {
            $offset = $length + $offset;
        } elseif ($offset === self::END) {
            $offset = $length;
        }

        if ($offset < 0 || $offset > $length) {
            throw new OutOfBoundsException('offset: ' . $offsetArg . ' ist nicht erlaubt. Array-Laenge: ' . $length . ' berechnetes offset: ' . $offset);
        }

        $left = ($offset > 0) ? array_slice($subject, 0, $offset) : [];
        $right = ($offset < $length) ? array_slice($subject, $offset) : [];

        $subject = array_merge($left, $array, $right);
        return $subject;
    }

    /**
     * Remove an element from an array
     *
     * the item is searched and removed, numeric arrays are renumbered
     * only the first item matched will be removed
     * @TODO FIXME: boolean trap
     * @param bool $searchStrict if false only the value will be replace
     * @return array
     */
    public static function remove(array &$array, mixed $item, bool $searchStrict = true): array {
        if (($key = array_search($item, $array, $searchStrict)) !== false) {
            array_splice($array, $key, 1);
        }

        return $array;
    }

    /**
     * Greift auf einen Index im Array zu
     *
     * schmeisst kein notice, wenn der Index nicht gesetzt ist
     */
    public static function index(array $array, mixed $i): mixed {
        return array_key_exists($i, $array) ? $array[$i] : null;
    }

    public static function &indexRef(array &$array, mixed $i)
    {
        return $array[$i];
    }

    public static function set(array &$array, mixed $key, mixed $value): array {
        $array[$key] = $value;
        return $array;
    }

    /**
     * Ähnlich wie joinc allerdings fügt implode ZWISCHEN den Elementen $glue ein
     *
     * @param string $glue ohne %s darin (nicht wie bei join)
     */
    public static function implode(array $array, string $glue, Closure $closure): string {
        return implode($glue, array_map($closure, (array) $array, array_keys($array)));
    }

    public static function shuffle(array $array): array {
        shuffle($array);
        return $array;
    }

    public static function randomValue(array $array): mixed {
        $key = array_rand($array, 1);
        return $array[$key];
    }

    /**
     * Gibt Infos über die Schlüssel des verschachtelten Arrays zurück
     *
     * dies ist besonders Praktisch, wenn man eigentlich nur die info braucht, dass eine Schlüssel-Pfad innerhalb eines Arrays gesetzt ist
     * Es wird nur der Typ des Inhalts vom Eintrag mit dem Schlüssel ausgegeben
     *
     * print_r(A::keys($myComplexArray)); ist eine schöne übersicht des Array-Inhaltes
     */
    public static function keys(mixed $array): array {
        $ret = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $ret[$key] = self::keys($value);
            } else {
                $ret[$key] = gettype($value);
            }
        }

        return $ret;
    }

    /**
     * Führt eine Callback Funktion auf allen Keys (allen Values) des Arrays aus
     *
     * @param Closure $keyClosure modifiziert die Schlüssel
     * @param Closure $valueClosure modifiziert (optional) die Werte
     *
     * danach werden beide in gleichbleibender Reihenfolge zusammengefügt
     * d. h. der modifzierte Schlüssel an Position 1 erhält die (optional modifizierte) value von Position 1
     * @return array
     */
    public static function mapKeys(array $array, Closure $keyClosure, ?Closure $valueClosure = null): array {
        $keys = array_map($keyClosure, array_keys($array));

        $values = array_values($array);
        if (isset($valueClosure)) {
            $values = array_map($valueClosure, $values);
        }

        return array_combine($keys, $values);
    }

    /**
     * Gibt den Typ der Schlüssel des Arrays zurück
     *
     * gibt auch assoc zurück wenn nur ein key ein string ist
     * in numeric Arrays muss die Reihenfolge nicht unbedingt von 0-x sein!
     * @return 'assoc'|'numeric'
     */
    public static function getType(array $array): string {
        return (count(array_filter(array_keys($array), 'is_string')) > 0) ? 'assoc' : 'numeric';
    }

    /**
     * @return bool
     */
    public static function isNumeric(array $array): bool {
        return self::getType($array) === 'numeric';
    }

    /**
     * @return bool
     */
    public static function isAssoc(array $array): bool {
        return self::getType($array) === 'assoc';
    }

    /**
     * Gibt TRUE zurück wenn alle Typen der Values des Arrays auf der ersten Ebene dem angegeben Typ entsprechen
     *
     * @FIXME es muss is_$type existieren
     * @return bool
     */
    public static function isOnlyType(array $array, string $type): bool {
        return (count(array_filter(array_values($array), 'is_' . $type)) === count($array));
    }

    /**
     * Füllt Werte an den angegeben Array an, mit $value als Wert, bis $absLength als Gesamtlänge erreicht ist
     *
     * Der übergebene Array wird nicht modifiziert
     * @param mixed $value
     * @param int $absLength
     * @return array
     */
    public static function fillUp(array $array, mixed $value, int $absLength): array {
        $fillNum = $absLength - count($array);

        /* nichts zu tun, da array schon voll (oder zu voll) */
        if ($fillNum <= 0) {
            return $array;
        }

        return array_merge($array, array_fill(0, $fillNum, $value));
    }

    /**
     * Returns all properties from $items cummulated as a list
     *
     * respects the association keys from $items
     */
    public static function pluck(array $items, mixed $property): array {
        $props = [];

        if (count($items) > 0) {
            $get = Util::castGetterFromSample($property, current($items));

            foreach ($items as $key => $item) {
                $props[$key] = $get($item);
            }
        }

        return $props;
    }

    /**
     * Casts all Strings in the array to a string
     *
     * leaves the original array unmodified
     * returns the stringified array
     * @return array
     */
    public static function stringify(array $items): array {
        return array_map(function ($item) {
            return (string) $item;
        }, $items);
    }

    /**
     * Filters the keys and values from an array
     *
     * there is only one callback allowed that should return truthy if the value SHOULD exist in the new array
     * the first parameter of the callback is the key and the second is the key
     *
     * but this function has the keys passed as second parameter
     * the new array is NOT renumbered
     *
     * @param array<mixed> $array will not be modified
     * @param closure $filter bool function($key, $value)
     * @return array with same keys as the input array (if not filtered)
     */
    public static function filterKeys(array $array, Closure $filter): array {
        $filtered = [];
        //@TODO maybe something with array_filter as callback is faster here? benchmark?

        foreach ($array as $key => $value) {
            if ($filter($key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Returns an array with the index constructred from objects in the collection
     *
     * @param mixed $property see pluck() for details
     * @return array
     */
    public static function indexBy(array $array, mixed $property): array {
        $ret = [];

        if (count($array) > 0) {
            $index = Util::castGetterFromSample($property, current($array));

            foreach ($array as $item) {
                $ret[$index($item)] = $item;
            }
        }

        return $ret;
    }
}
