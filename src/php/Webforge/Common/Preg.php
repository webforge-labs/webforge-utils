<?php

namespace Webforge\Common;

use \Psc\Code\Code;

class Preg {

  const THROW_EXCEPTION = 'throwException';

  /**
   * Sucht innerhalb eines Strings anhand eines regulären Ausdruckes.
   * 
   * Sucht innerhalb eines Strings anhand eines regulären Ausdruckes. Wrapper für die PHP-Funktionen preg_match und preg_match_all. 
   * Modifier für UTF-8 wird automatisch gesetzt.
   * Der (in PHP nicht bekannte) Modifier "g" unterscheidet, ob preg_match oder preg_match_all ausgeführt wird. 
   * Bei preg_match_all wird PREG_SET_ORDER als default eingesetzt (dies ist ein anderer Default als bei PHP!).
   * @param string $subject Der String, in dem gesucht werden wird
   * @param mixed $pattern Der reguläre Ausdruck (string oder array aus strings)
   * @param mixed &$matches Die Ergebnisse der Suche
   * @param int $flags Flags (vgl. http://www.php.net/manual/en/function.preg-match-all.php)
   * @param int $offset An diesem offset wird begonnen zu matchen.
   * @return mixed Entweder int mit Anzahl der Matches oder FALSE im Fehlerfall
   * @see PHP::preg_match
   * @see PHP::preg_match_all
   */
  public static function match ($subject, $pattern, &$matches=NULL, $flags=NULL, $offset=0) {
    $pattern = self::set_u_modifier($pattern, TRUE);
    
    $delimiter = mb_substr($pattern,0 ,1);
    $modifiers = mb_substr($pattern, mb_strrpos($pattern, $delimiter));
    if (mb_strpos ($modifiers, 'g') !== FALSE) {
      // remove g modifier but use preg_match_all
      $modifiers = str_replace('g', '',$modifiers);

      $pattern = mb_substr($pattern, 0, mb_strrpos($pattern, $delimiter)) . $modifiers;
      
      if ($flags === NULL) $flags = PREG_SET_ORDER; // default to PREG_SET_ORDER
      $ret = preg_match_all($pattern, $subject, $matches, $flags, $offset);
    } else {
      $ret = preg_match($pattern, $subject, $matches, $flags, $offset);
    }

    if ($ret === FALSE) {
      throw new Exception('Pattern Syntax Error: '.$pattern.' '.self::getError(preg_last_error()));
    }

    return $ret;
  }
  
  // @codeCoverageIgnoreStart
  public static function getError($error) {
    
    switch ($error) {
      case PREG_NO_ERROR:
        return 'There was no error.';
      
      case PREG_INTERNAL_ERROR:
        return 'Internal Error in Preg Library.';
      
      case PREG_BACKTRACK_LIMIT_ERROR:
        return 'Backtrack limit exhausted.';
      
      case PREG_RECURSION_LIMIT_ERROR:
        return 'Recursion limit exhausted.';
      
      case PREG_BAD_UTF8_ERROR:
        return 'Subject contains Bad UTF8';
      
      case PREG_BAD_UTF8_OFFSET_ERROR:
        return 'Bad UTF8 Offset.';
    }
    return NULL;
  }
  // @codeCoverageIgnoreEnd

  /**
   * Führt eine Ersetzung anhand eines regulären Ausdruckes durch 
   * 
   * Wrapper für die PHP-Funktion preg_replace. Modifier für UTF-8 wird automatisch gesetzt.
   * @param string $subject Der String, in dem ersetzt werden wird
   * @param mixed $pattern Der reguläre Ausdruck (string oder array aus strings)
   * @param mixed $replace Ersetzungen (string oder array aus strings)
   * @param int $limit Optionale maximale Ersetzungsanzahl (-1 == No limit)
   * @param int $count Wird mit der Anzahl der Ersetzungsaktionen befüllt werden
   * @return string|array
   * @see PHP::preg_replace
   */
  public static function replace ($subject, $pattern, $replace, $limit = -1, &$count = NULL) {
    $pattern = self::set_u_modifier($pattern, TRUE);
    return (preg_replace($pattern, $replace, $subject, $limit, $count));
  }


  /**
   * So wie replace allerdings mit einer Callback Funktion mit einem Parameter
   * 
   * Callback bekommt als Parameter1 den Array des Matches von $pattern welches ersetzt werden soll
   * Der Rückgabestring wird dann in $subject ersetzt. 
   * <code>
   * function callback(Array $matches) {
   *  // as usual: $matches[0] is the complete match
   *  // $matches[1] the match for the first subpattern
   *  // enclosed in '(...)' and so on
   *  return $matches[1].($matches[2]+1);
   * }
   * </code>
   * @see PHP::preg_replace_callback
   */
  public static function replace_callback ($subject, $pattern, $callback, $limit = -1) {
    return preg_replace_callback(self::set_u_modifier($pattern, TRUE), $callback, $subject, $limit);
  }


  /**
   * Fügt einem Regex-Pattern den u-Modifier hinzu oder entfernt ihn
   * 
   * Gibt das Regex-Pattern mit u modifier zurück oder ohne u modifier zurück
   * @param string $pattern Das Regex-Pattern
   * @param bool $add wenn true wird der modifier hinzugefügt, ansonsten entfernt
   * @return string 
   */
  protected static function set_u_modifier($pattern, $add = TRUE) {
    return self::setModifier($pattern, 'u', $add);
  }
  
  public static function setModifier($pattern, $modifier, $add = TRUE) {
    /* aufsplitten */
    $delimiter = mb_substr($pattern, 0, 1);
    $modifiers = mb_substr($pattern, mb_strrpos($pattern, $delimiter)+1);
  
    if ($add) {
      if (mb_strpos ($modifiers, $modifier) === FALSE)
        $pattern .= $modifier; // modifier hinzufügen, da er nicht existiert
    } else {
      if (mb_strpos($modifiers, $modifier) !== FALSE) {
        $modifiers = str_replace($modifier, '',$modifiers);
        $pattern = mb_substr($pattern, 0, mb_strrpos($pattern, $delimiter)+1) . $modifiers;
      }
    }
    
    return $pattern;
  }
  
  /**
   * Überprüft ob der Ausdruck $rx auf $string passt und gibt dann vom Ergebnis das Set mit dem Offset $set zurück
   *
   * @param int|int[] $set ist dies ein array von ints werden die offset aus dem matching zurückgegeben (nicht gecheckt)
   * @return string|NULL|array  gibt immer dann NULL ($default) zurück wenn nichts gematched hat
   */
  public static function qmatch($string, $rx, $set = 1, $default = NULL) {
    $m = array();
    if (self::match($string, $rx, $m) > 0) {
      if (is_array($set)) {
        return array_merge(array_intersect_key($m, array_flip($set))); // merge: renumbered
      } else {
        return $m[$set];
      }
    }
    return $default;
  }
  
  
  
  /**
   * Überprüft einen Wert anhand eines Regexp-Array
   * 
   * Ein rxArray ist ein Array der als Schlüssel Reguläre Ausdrücke (fertig escaped etc) hat und als werte den Zielwert
   *
   * @param rxArray $rxArray
   * @return mixed
   */
  public static function matchArray($rxArray, $value, $do = self::THROW_EXCEPTION) {
    foreach ($rxArray as $rx => $target) {
      if (self::match($value, $rx) > 0) {
        return $target;
      }
    }
    
    if ($do === self::THROW_EXCEPTION) {
      throw new NoMatchException('Konnte kein Matching für '.Util::varInfo($value).' in '.Util::varInfo($rxArray, Util::INFO_PLAIN_ARRAY).' finden.');
    }
    
    return $do;
  }
  
  /**
   * So wie matchArray jedoch stoppt dies nicht nach der ersten Bedingung
   *
   * returns the corrosponding values of all matching keys
   * @return array
   */
  public static function matchFullArray($rxArray, $value, $do = self::THROW_EXCEPTION) {
    $targets = array();
    foreach ($rxArray as $rx => $target) {
      if (self::match($value, $rx) > 0) {
        $targets[] = $target;
      }
    }
    
    if (count($targets) === 0) {
      if ($do === self::THROW_EXCEPTION) {
        throw new NoMatchException('Keins der Matchings für '.Util::varInfo($value).' konnte in '.Util::varInfo($rxArray).' gefunden werden.');
      }
    
      return $do;
    }
    
    return $targets;
  }
}
?>
