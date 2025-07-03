<?php

declare(strict_types=1);

namespace Webforge\Common\Exception;

use Webforge\Common\System\File;

class FileNotFoundException extends \Webforge\Common\Exception
{
    protected $notfoundFile;

    public static function fromFile(File $file, $msg = null, $code = 0): self
    {
        $ex = new self(sprintf($msg ?: 'The file %s cannot be found.', $file, $code));
        $ex->setNotFoundFile($file);

        return $ex;
    }

    public static function fromFileAndExtensions(File $file, $extensions): \Webforge\Common\Exception\FileNotFoundException
    {
        return self::fromFile($file, 'The file %s cannot be found with any of these extensions: ' . implode(',', $extensions));
    }

    public function getNotFoundFile(): Webforge\Common\System\File
    {
        return $this->notfoundFile;
    }

    /**
     * @param Webforge\Common\System\File file
     */
    public function setNotFoundFile(File $file): static
    {
        $this->notfoundFile = $file;
        return $this;
    }
}
