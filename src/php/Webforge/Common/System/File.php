<?php

namespace Webforge\Common\System;

use Webforge\Common\Util AS Code;
use BadMethodCallException;
use InvalidArgumentException;
use Webforge\Common\DateTime\DateTime;
use Webforge\Common\Preg;
use Webforge\Common\Exception\FileNotFoundException;

/**
 * @todo refactor exceptions to be multi-lingual (english / german)
 */
class File {

  const EXCLUSIVE = 0x000001;

  const WITHOUT_EXTENSION = FALSE;
  const WITH_EXTENSION = TRUE;

  const WINDOWS = Dir::WINDOWS;
  const UNIX = Dir::UNIX;

  /**
   * Name of File without Extension
   * @var string 
   */
  protected $name;

  /**
   * File Ending (if any) without .
   * @var string
   */
  protected $extension;

  /**
   * @var Webforge\Common\System\Dir
   */
  protected $directory;

  /**
   * @var string
   */
  protected $mimeType;

  /**
   * Cache for hash (sha1)
   */
  protected $sha1;

  
  public function __construct($arg1, $arg2 = NULL) {
    if ($arg1 instanceof Dir) {
      $this->constructDefault($arg2, $arg1);
    } elseif ($arg2 instanceof Dir) {
      $this->constructDefault($arg1, $arg2);
    } elseif (is_string($arg1) && $arg2 === NULL) {
      $this->constructString($arg1);
    } else {
      $signatur = array();
      foreach (func_get_args() as $arg) {
        $signatur[] = Code::getType($arg);
      }

      throw new BadMethodCallException('No Constructor defined for '.get_class($this).' with: '.implode(', ',$signatur));
    }
  }
  
  /**
   * Creates a temporary File in the system temp directory
   * 
   * @return Webforge\Common\System\File
   */
  public static function createTemporary($extension = NULL) {
    $tmpfile = tempnam(sys_get_temp_dir(), mb_substr(uniqid(),0,3));

    if ($extension) {
      rename($tmpfile, $tmpfile = $tmpfile.'.'.ltrim($extension, '.'));
    }

    return new static($tmpfile);
  }
  
  /**
   * Creates the file from a relative URL in relation to $base
   * 
   * @return Webforge\Common\System\File
   */
  public static function createFromURL($url, Dir $base = NULL) {
    if (!isset($base)) $base = new Dir('.'.DIRECTORY_SEPARATOR);
    
    // lets do it dirty
    $url = str_replace('/', DIRECTORY_SEPARATOR, ltrim($url, '/'));
    return $base->getFile(new File('.'.DIRECTORY_SEPARATOR.$url));
  }

  /**
   * Creates a new File instance from single string
   * 
   * @param string $file
   */
  protected function constructString($file) {
    if (mb_strlen($file) == 0)
      throw new Exception('keine Datei angegeben');
    
    $dir = NULL;
    try {
      $dir = Dir::extract($file);
    } catch (Exception $e) {
      /* kein Verzeichnis vorhanden, vll ein Notice? (aber eigentlich sollte dies ja auch okay sein */
    }
    
    $filename = self::extractFilename($file);

    $this->constructDefault($filename,$dir);
  }

  /**
   * Creates a new File from filename and Directory Object
   * 
   * @param string $filename der Name der Datei
   * @param Dir $directory das Verzeichnis in dem die Datei liegt
   */
  protected function constructDefault($filename, Dir $directory=NULL) {
    $this->setName($filename);
    
    if (isset($directory)) 
      $this->setDirectory($directory);
  }

  /**
   * @return Webforge\Common\System\File
   */
  public static function factory($arg1, $arg2 = NULL) {
    return new static($arg1, $arg2);
  }

  /**
   * Writes the contents of the file from a string
   * 
   * With File::EXCLUSIVE a temporary file will be written and then moved to $this to prevent race conditions
   * 
   * @param string $contents
   * @param int $flags File::EXCLUSIVE
   * @chainable
   */
  public function writeContents($contents, $flags = NULL) {
    if ($this->exists() && !$this->isWriteable()) {
      throw new Exception('Dateiinhalt kann nicht geschrieben werden. (exists/writeable) '.$this);
    }

    if ($flags & self::EXCLUSIVE) {
      
      $tmpFile = new File(tempnam('/tmp', 'filephp'));
      $tmpFile->writeContents($contents);

      $ret = $tmpFile->move($this,TRUE); // 2ter Parameter ist overwrite
      
      if ($ret === FALSE)
        throw new Exception('Dateiinhalt konnte nicht geschrieben werden move() gab FALSE zurück. '.$this);

    } else {
      $ret = @file_put_contents((string) $this,$contents);

      if ($ret === FALSE) {
        $info = '';
        if (!$this->getDirectory()->exists()) {
          $info = "\n".'Das Verzeichnis: '.$this->getDirectory().' existiert nicht und muss zuerst erstellt werden.';
        }
        throw new Exception("Dateiinhalt konnte nicht geschrieben werden PHP::file_put_contents gab FALSE zurück.".$this.$info);
      }
    }
    $this->sha1 = NULL;
    
    return $this;
  }
  
  /**
   * Returns the contents of the file
   * 
   * @param int $maxlength to read in bytes
   * @return string
   */
  public function getContents($maxLength = NULL) {

    if (isset($maxLength)) {
      $ret = @file_get_contents(
        (string) $this,
        NULL, // flags
        NULL, // context
        NULL, // offset
        $maxLength
      );
    } else {
      $ret = @file_get_contents((string) $this);
    }

    if ($ret === FALSE) {
      $reason = '';
      if (!$this->exists()) {
        $reason = '. The file does not exist';
      } elseif(!$this->isReadable()) {
        $reason = '. The file is not readable';
      }

      throw new Exception(
        sprintf("dateiinhalt von '%s' konnte nicht ausgelesen werden PHP::file_get_contents gab FALSE zurück%s", $this, $reason)
      );
    }

    return $ret;
  }

  /**
   * Moves the File to another file and changes the internal state to the destination
   *
   * after this command
   * (string) $fileDestination-> === (string) $this
   * is true
   *
   * @param File $fileDestination
   * @return bool
   */
  public function move(File $fileDestination, $overwrite = FALSE) {
    if (!$this->exists())
      throw new Exception('Quelle von move existiert nicht. '.$this);

    if ($fileDestination->exists() && !$overwrite) {
      throw new Exception('Das Ziel von move existiert bereits'.$fileDestination);
    }

    if (!$fileDestination->getDirectory()->exists()) {
      throw new Exception('Das ZielVerzeichnis von move existiert nicht: '.$fileDestination->getDirectory());
    }
   
    if ($fileDestination->exists() && $overwrite && Util::isWindows()) {
      $fileDestination->delete();
    }
    $ret = rename((string) $this, (string) $fileDestination);
    
    if ($ret) {
      $this->setDirectory($fileDestination->getDirectory());
      $this->setName($fileDestination->getName());
    }

    return $ret;
  }

  /**
   * Copys the file to another file or into an directory
   * 
   * @param File|Dir $fileDestination
   * @chainable
   */
  public function copy($destination) {
    
    if (!$this->exists()) {
      throw new Exception('Source from copy does not exist: '.$this);
    }
    
    if ($destination instanceof Dir) {
      $destination = $destination->getFile($this->getName());
    } elseif (!($destination instanceof File)) {
      throw new InvalidArgumentException('Invalid Argument. $destination must be file or dir');
    }

    if (!$destination->getDirectory()->exists()) {
      throw new Exception('The directory from the destination file does not exist: '.$destination);
    }

    $ret = @copy((string) $this, (string) $destination);

    if (!$ret) {
      throw new Exception('PHP Error while copying '.$this.' onto '.$destination);
    }
    
    return $this;
  }
  
  /**
   * Deletes the file (if exists)
   *
   * returns true if the file could be deleted
   * @return bool
   */
  public function delete() {
    if (!$this->isWriteable()) return FALSE; // performace: does check exist, too

    @unlink((string) $this);
    
    return !$this->exists();
  }

  /**
   * Sets the file access rights
   * 
   * $file->chmod(0644);  for: u+rw g+rw a+r
   * @param octal $mode 
   * @chainable
   */
  public function chmod($mode) {
    $ret = chmod((string) $this, $mode);
    
    if ($ret === FALSE)
      throw new Exception('chmod für '.$this.' auf '.$mode.' nicht möglich');

    return $this;
  }

  /**
   * Does the file exist physically?
   * @return bool
   */
  public function exists() {
    return mb_strlen($this->getName()) > 0 && is_file((string) $this);
  }

  /**
   * Überprüft ob eine Datei lesbar ist
   * @return bool
   */
  public function isReadable() {
    return is_readable((string) $this);
  }

  /**
   * @return bool
   */
  public function isWriteable() {
    return $this->exists() && is_writable((string) $this);
  }

  /**
   * @return bool
   */
  public function isRelative() {
    return $this->directory->isRelative();
  }

  /**
   * Sets the name of the file
   *
   * If extension is NOT found in $name extension won't be replaced
   * 
   * @param string $name filename with extension
   * @chainable
   */
  public function setName($name) {
    if (($pos = mb_strrpos($name,'.')) !== FALSE) {
      $this->name = mb_substr($name, 0, $pos);
      $this->extension = mb_substr($name, $pos+1);
    } else {
      $this->name = $name;
    }
    
    return $this;
  }
  
  /**
   * Returns the full name of the file (with or without extension)
   * 
   * @return string
   */
  public function getName($extension = self::WITH_EXTENSION) {
    if (isset($this->extension) && $extension == self::WITH_EXTENSION)
      return $this->name.'.'.$this->extension;
    else
      return $this->name;
  }



  /**
   * Sets the directory of the file
   * @param Dir $directory
   * @chainable
   */
  public function setDirectory(Dir $directory) {
    $this->directory = $directory;
    return $this;
  }

  /**
   * @return Dir
   */
  public function getDirectory() {
    return $this->directory;
  }
  
  /**
   * Returns the filename with quotes if dir or file have whitespace in their names
   *
   * @return string
   */
  public function getQuotedString() {
    $str = (string) $this;
    
    if (mb_strpos($str, ' ') !== FALSE) {
      return escapeshellarg($str);
    }
    
    return $str;
  }

  /**
   * Returns on Unix an Unix path and on Windows an Cygdrive compatible path
   * 
   * @return string
   */
  public function getUnixOrCygwinPath() {
    return $this->getOSPath($this->directory->getOS(), Dir::WINDOWS_WITH_CYGWIN);
  }

  public function getOSPath($os, $flags = 0x000000) {
    $dir = $this->getDirectory()->getOSPath($os, $flags);

    return $dir.$this->getName();
  }

  /**
   * @return tring
   */
  public function __toString() {
    $dir = (string) $this->getDirectory();
    
    return $dir.$this->getName();
  }
  
  /**
   * @return DateTime
   */
  public function getModifiedTime() {
    if (!$this->exists()) throw new Exception('Kann keine mtime für eine Datei zurückgeben die nicht existiert. '.$this);
    
    return new Datetime(filemtime((string) $this));
  }

  /**
   * @return DateTime
   */
  public function getAccessTime() {
    if (!$this->exists()) throw new Exception('Kann keine atime für eine Datei zurückgeben die nicht existiert. '.$this);
    return new Datetime(fileatime((string) $this));
  }

  /**
   * @discouraged
   * @return DateTime
   */
  public function getCreateTime() {
    return $this->getCreationTime();
  }

  /**
   * @return DateTime
   */
  public function getCreationTime() {
    if (!$this->exists()) throw new Exception('Kann keine ctime für eine Datei zurückgeben die nicht existiert. '.$this);
    return new Datetime(filectime((string) $this));
  }

  /**
   * Ersetzt (wenn vorhanden) die Extension des angegebenen Dateinamens mit einer Neuen
   * 
   * Ist keine Extension vorhanden, wird die zu ersetzende angehängt
   * ist <var>$extension</var> === NULL wird die Extension gelöscht
   * 
   * @param string $extension die Erweiterung (der . davor ist optional)
   * @chainable
   */
  public function setExtension($extension=NULL) {
    if ($extension != NULL && mb_strpos($extension,'.') === 0) 
      $this->extension = mb_substr($extension,1);
    else 
      $this->extension = $extension;

    return $this;
  }

  /**
   * Returns the extension (if any) of a filename without the .
   *
   * @return string|NULL
   */
  public static function extractExtension($name) {
    if (($pos = mb_strrpos($name,'.')) !== FALSE) {
      return mb_substr($name, $pos+1);
    }
  }

  /**
   * Returns the first file that exists with one of the given extensions
   * 
   * Dir contents:
   *   thefile.js
   *   thefile.php
   *   thefile.csv
   * 
   * $dir->getFile('thefile')->findExtension(array('php', 'js', 'csv')); // returns thefile.php
   * $dir->getFile('thefile')->findExtension(array('js', 'csv')); // returns thefile.js
   * $dir->getFile('thefile')->findExtension(array('html', 'csv')); // returns thefile.csv
   */
  public function findExtension(Array $possibleExtensions) {
    foreach ($possibleExtensions as $extension) {
      $file = clone $this;
      $file->setExtension($extension);

      if ($file->exists()) {
        return $file;
      }
    }

    throw FileNotFoundException::fromFileAndExtensions($this, $possibleExtensions);
  }

  /**
   * Extrahiert eine Datei aus einer Pfadangabe
   * 
   * Der Pfad wird nicht in die Informationen in File mit aufgenommen
   * @param string $string der Pfad zur Datei
   * @return File die extrahierte Datei aus dem Pfad
   */
  public function extract($string) {
    return new static(self::extractFilename($string));
  }

  /**
   * Extrahiert eine Datei als String aus einer Pfadangabe
   * 
   * @param string $string ein Pfad zu einer Datei
   * @return string name der Datei
   */
  public static function extractFilename($string) {
    if (mb_strlen($string) == 0) 
      throw new Exception('Cannot extract filename from empty string');
    
    $file = basename($string);

    if (mb_strlen($file) == 0) 
      throw new Exception('PHP could not extract the basename of the file: '.$file);
    
    return $file;
  }
  
  
  public function __clone() {
    $this->directory = clone $this->directory;
  }
  
  /**
   * Resolves relative parts of the path to absolute ones
   *
   * the getcwd() directory is used as "."
   * @chainable
   */
  public function resolvePath() {
    $this->directory->resolvePath();
    return $this;
  }
  
  /**
   * Modifies the directory to be a relative directory in relation to $dir
   * 
   * @chainable
   */
  public function makeRelativeTo(Dir $dir) {
    $this->directory->makeRelativeTo($dir);
    return $this;
  }
  
  /**
   * @return int in bytes
   */
  public function getSize() {
    return filesize((string) $this);
  }

  /**
   * Returns the URL for the File relative to a directory
   *
   * @return string with forward slashes and the names rawurlencoded
   */
  public function getURL(Dir $relativeDir = NULL) {
    // rtrim entfernt den TrailingSlash der URL (der eigentlich nie da sein sollte, außer falls directoryURL nur "/" ist)
    return rtrim($this->directory->getURL($relativeDir),'/').'/'.rawurlencode($this->getName(self::WITH_EXTENSION));
  }
  
  public function getHTMLEscapedUrl(Dir $relativeDir = NULL) {
    return rtrim($this->directory->getURL($relativeDir),'/').'/'.\Psc\HTML\HTML::esc(rawurlencode($this->getName(self::WITH_EXTENSION)));
  }
  
  /**
   * returns the SHA1 of the file
   * 
   * Attention: the file must exist
   * the hash is cached
   * @return string
   */
  public function getSha1() {
    if (!isset($this->sha1)) {
      $this->sha1 = sha1_file((string) $this);
    }
    
    return $this->sha1;
  }
  
  /**
   * Overwrites the sha1 hash from File
   */
  public function setSha1($sha1) {
    $this->sha1 = $sha1;
    return $this;
  }
  
  /**
   * Returns the Extension of the file (if any) without the .
   * 
   * @returns string
   */
  public function getExtension() {
    return $this->extension;
  }

  
  /**
   * Returns a safeName for a file
   *
   * does dump search/replace for some nonAlpha
   * filters all other characters
   * 
   * @param string $name
   * @param bool $lower return name in low
   * @param int $maxlen default is 250, cuts the filename at maxlen
   * @return string
   */
  public static function safeName($name, $lower=FALSE,$maxlen=250) {
    $noalpha = array ('Á','É','Í','Ó','Ú','Ý','á','é','í','ó','ú','ý','Â','Ê','Î','Ô','Û','â','ê','î','ô','û','À','È','Ì','Ò','Ù','à','è','ì','ò','ù','Ä','Ë','Ï','Ö','Ü','ä','ë','ï','ö','ü','ÿ','Ã','ã','Õ','õ','Å','å','Ñ','ñ','Ç','ç','@','°','º','ª', 'ß');
    $alpha   = array ('A','E','I','O','U','Y','a','e','i','o','u','y','A','E','I','O','U','a','e','i','o','u','A','E','I','O','U','a','e','i','o','u','Ae','E','I','Oe','Ue','ae','e','i','oe','ue','y','A','a','O','o','A','a','N','n','C','c','a','o','o','a', 'ss');
   
    $name = mb_substr($name, 0, $maxlen);
    $name = str_replace($noalpha, $alpha, $name);
    $name = Preg::replace($name, '/[^a-zA-Z0-9,._\+\()\-]/', '_');
    if ($lower)
      $name = mb_strtolower($name);
  
    return $name;
  }
}
?>