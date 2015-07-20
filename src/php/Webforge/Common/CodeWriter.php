<?php

namespace Webforge\Common;

use stdClass;
use Webforge\Common\ArrayUtil AS A;
use Webforge\Common\String as S;
use Webforge\Common\ClassInterface;
use Webforge\Common\PHPClass;
use RuntimeException;

/**
 * @deprecated dont use this class yet
 * 
 * implements \Webforge\Types\Adapters\CodeWriter it does this but i dont want to pull in dependencies for (only) this
 */
class CodeWriter {  

  public static function create() {
    return new static();
  }
  
  public function exportConstructor(ClassInterface $class, Array $parameters) {
    return $this->writeConstructor($class, $this->exportFunctionParameters($parameters));
  }
  
  public function writeConstructor(ClassInterface $class, $parametersPHPCode) {
    return sprintf('new \%s(%s)', $class->getFQN(), $parametersPHPCode);
  }
  
  public function callGetter($objectVar, $propertyName) {
    return sprintf('%s->get%s()', $this->exportFunctionParameter($objectVar), S::ucfirst($propertyName));
  }
  
  public function callSetter($objectVar, $propertyName, $value) {
    return sprintf('%s->set%s(%s)', $this->exportFunctionParameter($objectVar), S::ucfirst($propertyName), $this->exportFunctionParameter($value));
  }
  
  public function call($objectVar, $functionName, $args) {
    return sprintf('%s->%s(%s)',
                   $this->exportFunctionParameter($objectVar), $functionName, $this->exportFunctionParameter($args));
  }
  
  public function exportFunctionParameters(Array $values) {
    $that = $this;
    return A::implode($values, ',', function ($item) use ($that) {
      return $that->exportFunctionParameter($item);
    });
  }
  
  public function exportFunctionParameter($item) {
    if (is_array($item)) {
      return sprintf('%s', $this->exportKeyList($item));
    } elseif($item instanceof \stdClass) {
      return $this->exportStdClass($item, 'list');
    } elseif($item instanceof \Psc\Code\AST\LVariable) {
      return sprintf('$%s', $item->getName());
    } elseif($item instanceof Expression) {
      return $item->php();
    } else {
      return $this->exportBaseTypeValue($item);
    }
  }
  
  /**
   * Nimmt einen Array und exportiert diesen in einer Zeile
   *
   * es sind nur BasisDatenTypen erlaubt (+ stdClass)
   * also alles was man auch schön exportieren kann. Dies ist für sehr einfache Dinge nur sinnvoll und bietet
   * keine komplexe Export-Möglichkeiten
   *
   * dies ist also ähnliche wie var_export, nur inline
   * @return string
   */
  public function exportList(Array $list) {
    $that = $this;
    return
      sprintf('array(%s)', A::implode($list, ',', function ($item) use($that) {
        if (is_array($item)) {
          return $that->exportList($item);
        } elseif($item instanceof \stdClass) {
          return $that->exportStdClass($item, 'list'); 
        } else {
          return $that->exportValue($item);
        }
      })
    );
  }
  
  public function exportStdClass(stdClass $var, $type = 'keyList') {
    return '(object) '.$this->exportKeyList($this->castStdClassToArray($var), $type);
  }
  
  /**
   * Sowie exportList, jedoch setzt dieses auch die Keys
   */
  public function exportKeyList(Array $array, $type = 'keyList') {
    $that = $this;
    return
      sprintf('array(%s)', A::implode($array, ',', function ($item, $key) use($that, $type) {
        if (is_array($item)) {
          // damit wir wenn wir exportList machen und ein objekt mit hilfsfunktion exportKeyList benutzen zu exportList() wieder zurückgehen können
          $value = $type === 'keyList' ? $that->exportKeyList($item) : $that->exportList($item);
        } elseif($item instanceof \stdClass) {
          $value = $that->exportStdClass($item, 'keyList');
        } else {
          $value = $that->exportValue($item);
        }
        
        return sprintf('%s=>%s', var_export((string) $key,TRUE), $value);
      })
    );
  }
  
  public function castStdClassToArray(stdClass $o) {
    return (array) $o;
  }
  
  /**
   * @return string
   */
  public function exportBaseTypeValue($value) {
    if (!$this->isBaseType($value)) {
      throw new RuntimeException('export kann keine Komplexen Datentypen wie: '.gettype($value).' exportieren');
    }
    
    return var_export($value, TRUE);
  }
  
  public function exportValue($value) {
    // qnd: leere ArrayCollection geht
    if ($value instanceof \Psc\Data\ArrayCollection || $value instanceof \Webforge\Collections\ArrayCollection) {
      return $this->writeConstructor(new PHPClass(get_class($value)), $value->toArray());
    } elseif($value === array()) {
      return 'array()';
    } else {
      return $this->exportBaseTypeValue($value);
    }
  }
  
  /**
   * @return bool
   */
  protected function isBaseType($var) {
    // wir wollen alle php basistypen, aber keine objekte (auch nicht standardclas)
    if (is_object($var)) return FALSE;
    if (is_array($var)) return FALSE;
    
    return TRUE;
  }  
}
