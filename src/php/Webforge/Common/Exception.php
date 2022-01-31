<?php

namespace Webforge\Common;

use Webforge\Common\Exception\InExceptionExportable;

class Exception extends \Exception implements Exception\MessageException
{
  /**
   * @chainable
   */
    public function setMessage($msg)
    {
        $this->message = $msg;
        return $this;
    }

    /**
     * @chainable
     */
    public function appendMessage($msg)
    {
        $this->message .= $msg;
        return $this;
    }

    /**
     * @chainable
     */
    public function prependMessage($msg)
    {
        $this->message = $msg.$this->message;
        return $this;
    }

    /**
     * Returns a nicer string Representation for the Exception
     *
     * @param string $format text|html
     * @return string
     */
    public function toString($format = 'text', $relativeDir = null, $relativeDirLabel = null)
    {
        return self::getExceptionText($this, $format, $relativeDir, $relativeDirLabel);
    }

    public static function getExceptionText(\Exception $e, $format = 'html', $relativeDir = null, $relativeDirLabel = 'ROOT')
    {
        $cr = $format == 'html' ? "<br />\n" : "\n";

        if (isset($relativeDir)) {
            // versuche die Pfade übersichtlich zu machen
            $trace = str_replace(
                array((string) $relativeDir,"\n"),
                array('{'.$relativeDirLabel.'}'.DIRECTORY_SEPARATOR, $cr),
                $e->getTraceAsString()
            );
            $file = str_replace(
                array((string) $relativeDir),
                array('{'.$relativeDirLabel.'}'.DIRECTORY_SEPARATOR),
                $e->getFile()
            );
        } else {
            $trace = $e->getTraceAsString();
            $file = $e->getFile();
        }

        $text = null;
        if ($format == 'html') {
            $text = '<pre class="php-error">'."\n";
            $text .= $cr.'<b>Fatal Error:</b> ';
        }

        $text .= 'Uncaught exception \''.get_class($e).'\' with message:'.$cr;
        if ($e instanceof InExceptionExportable) {
            $text .= str_replace("\n", $cr, wordwrap($e->exportExceptionText(), 140, "\n")).$cr;
        } else {
            $text .= "'".str_replace("\n", $cr, wordwrap($e->getMessage(), 140, "\n"))."'".$cr;
        }

        $text .= 'in '.$file.':'.$e->getLine().$cr;
        $text .= 'StackTrace: '.$cr.$trace.$cr;

        if ($format == 'html') {
            $text .= 'in <b>'.$file.':'.$e->getLine().'</b>'.'</pre>';
        } else {
            $text .= 'in '.$file.':'.$e->getLine();
        }

        if ($e->getPrevious() instanceof \Exception) {
            $text .= $cr.'Previous Exception:'.$cr;
            $text .= self::getExceptionText($e->getPrevious(), $format).$cr;
        }

        return $text;
    }
}
