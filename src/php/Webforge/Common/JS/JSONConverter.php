<?php

namespace Webforge\Common\JS;

use Webforge\Common\System\File;
use Webforge\Common\String AS S;
use Seld\JsonLint\JsonParser;

class JSONConverter {
  
  const EMPTY_ALLOWED = 0x000001;
  const PRETTY_PRINT =  0x000002;

  protected $errors = array(
    JSON_ERROR_NONE => 'Es ist kein Fehler zuvor aufgetreten, aber der Array ist leer. Es kann mit dem 2ten Parameter TRUE umgangen werden, dass der Array überprüft wird',
    JSON_ERROR_DEPTH => 'Die maximale Stacktiefe wurde erreicht',
    JSON_ERROR_CTRL_CHAR => 'Steuerzeichenfehler, möglicherweise fehlerhaft kodiert',
    JSON_ERROR_SYNTAX => 'Syntax Error',
  );
    

  /**
   * @return Webforge\Common\JS\JSONConverter
   */
  public static function create() {
    return new static();
  }
    
  /**
   * @return string
   */
  public function stringify($data, $flags = 0) {
    $json = json_encode($data);
    
    if ($flags === TRUE || $flags & self::PRETTY_PRINT) {
      $json = $this->prettyPrint($json);
    }
    
    return $json;
  }

  public function parse($json, $flags = self::EMPTY_ALLOWED) {
    $data = json_decode($json);

    if (($flags & self::EMPTY_ALLOWED) && (is_array($data) || is_object($data))) {
      return $data;
    }
    
    if (empty($data)) {

      $error = json_last_error();

      if ($error !== JSON_ERROR_DEPTH && $error !== JSON_ERROR_NONE) {
        $parser = new JsonParser();
        $parsingException = $parser->lint($json);

        if ($parsingException instanceof \Seld\JsonLint\ParsingException) {
          throw new JSONParsingException(
            sprintf("JSONConverter: %s", $parsingException->getMessage())
          );
        } else {
          throw new JSONParsingException(
            sprintf("unknown JSON error: %d while parsing string:\n%s", $error, $json)
          );
        }
        
      } elseif (array_key_exists($error, $this->errors)) {
        throw new JSONParsingException(sprintf('JSON error: %s while parsing', $this->errors[$error]));
      } else {
        throw new JSONParsingException(sprintf('JSON error: %d while parsing', $error));
      }
    }
    
    return $data;
  }
  
  /**
   * @return mixed
   */
  public function parseFile(File $file, $flags = self::EMPTY_ALLOWED) {
    return self::parse($file->getContents(), $flags);
  }
  
  /**
   * Indents a flat JSON string to make it more human-readable.
   *
   * http://recursive-design.com/blog/2008/03/11/format-json-with-php/
   * @param string $json The original JSON string to process.
   * @return string Indented version of the original JSON string.
   */
  public function prettyPrint($json) {
    $result      = '';
    $pos         = 0;
    $strLen      = mb_strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
  }
}
