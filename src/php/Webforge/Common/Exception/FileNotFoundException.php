<?php

namespace Webforge\Common\Exception;

use Webforge\Common\System\File;

class FileNotFoundException extends \Webforge\Common\Exception
{
    protected $notfoundFile;

    public static function fromFile(File $file, $msg = null, $code = 0)
    {
        $ex = new static(sprintf($msg ?: 'The file %s cannot be found.', $file, $code));
        $ex->setNotFoundFile($file);

        return $ex;
    }

    public static function fromFileAndExtensions(File $file, $extensions)
    {
        return self::fromFile($file, 'The file %s cannot be found with any of these extensions: ' . implode(',', $extensions));
    }

    /**
     * @return Webforge\Common\System\File
     */
    public function getNotFoundFile()
    {
        return $this->notfoundFile;
    }

    /**
     * @param Webforge\Common\System\File file
     * @chainable
     */
    public function setNotFoundFile(File $file)
    {
        $this->notfoundFile = $file;
        return $this;
    }
}
