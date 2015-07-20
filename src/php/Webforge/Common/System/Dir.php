<?php

namespace Webforge\Common\System;

use Webforge\Common\String as S;
use Webforge\Common\Preg;
use Webforge\Common\ArrayUtil AS A;
use Webforge\Common\DateTime\DateTime;
use InvalidArgumentException;
use LogicException;

/**
 *
 * translate API
 * Convention: every path has a trailing slash
 */
class Dir {

  const WINDOWS = 'WINDOWS';
  const UNIX = 'UNIX';
  
  const WITHOUT_TRAILINGSLASH = 0x000001;
  const WITHOUT_TRAILING_SLASH = 0x000001;

  /**
   * Represents path like D:\ converted to unix as D:/
   */
  const WINDOWS_DRIVE_WINDOWS_STYLE = 0x000001;

  /**
   * Represents path like D:\ converted to unix as /D:/
   * 
   * (this is an alternative git path style)
   */
  const WINDOWS_DRIVE_UNIX_STYLE = 0x000002;

  /**
   * Represents path like D:\ converted to unix as /cygdrive/d/
   * 
   * (this is an alternative git path style)
   */
  const WINDOWS_WITH_CYGWIN = 0x000004;

  const SORT_ALPHABETICAL = 2;

  const ORDER_ASC = 1024;
  const ORDER_DESC = 2048;

  const RECURSIVE = 2;
  
  const RELATIVE = 'relative';
  
  const ASSERT_EXISTS       = 0x000010;
  const PARENT              = 0x000020;

  /**
   * The default chmod for new directories
   *
   * @var octal $mode
   */
  static $defaultMod = 0744;

  /**
   * Path of the directory
   * 
   * @var array all names of the subdirectories and the name itself
   */
  private $path = array();


  protected $prefix;

  /**
   * The name of the streamwrapper if path is wrapped
   * 
   * @var string 
   */
  protected $wrapper;

  /**
   * @var bool
   */
  protected $cygwin;

  /**
   * Globale ignores for directories
   * 
   * @param array
   * @see getContents()
   */
  public $ignores = array();

  /**
   * Create a new Instance of a directory
   *
   * directories do not have to exist
   * @param string|Dir $path
   */
  public function __construct($path = NULL) {
    if ($path instanceof Dir) {
      $path = $path->getPath();
    }
    
    if (isset($path)) {
      $this->setPath($path);
    }
  }


  /**
   * Returns a new directory with $path
   * 
   * @param string $path with trailing slash
   * @return Dir
   */
  public static function factory($path = NULL) {
    return new static($path);
  }
  
  /**
   * Returns a new directory with $path but $path does not have to be trailing slashed
   *
   * @param string $path does not need to have trailingslash
   */
  public static function factoryTS($path = NULL) {
    if (!isset($path)) {
      return new static(NULL);
    } else {
      return new static(rtrim($path, '\\/').DIRECTORY_SEPARATOR);
    }
  }

   /**
   * Creates the dir from a relative URL in relation to $base
   * 
   * @return Psc\System\Dir
   */
  public static function createFromURL($url, Dir $base = NULL) {
    if (!isset($base)) $base = new Dir(getcwd().DIRECTORY_SEPARATOR);
    
    return $base->sub($url);
  }
  
  /**
   * Creates a temporary Directory
   */
  public static function createTemporary() {
    $file = File::createTemporary();
    $tempname = $file->getName();
    $file->delete();
    
    $dir = $file->getDirectory()->sub($tempname.'/');
    $dir->make();
    return $dir;
  }
  
  /**
   * 
   * @param string $path mit trailin DIRECTORY_SEPERATOR
   */
  public function setPath($path) {
    $path = trim($path);
    
    $lastChar = mb_substr($path,-1);
    if ($lastChar !== '\\' && $lastChar !== '/') {
      throw new Exception($path.' should end with (back)slash.');
    }
    
    if ($this->cygwin = self::isCygwinPath($path)) {
      $parts = explode('/', $this->fixToUnixPath($path));
      $this->prefix = '/cygdrive/'.$parts[2].'/';
      $this->path = array_slice($parts, 3, -1);

    } elseif (self::isWrappedPath($path)) {
      $wrapper = NULL;
      $path = $this->extractWrapper($this->fixToUnixPath($path), $wrapper);
      $parts = explode('/', $path);

      // windows drive as unix path /C:/
      if (mb_strpos($parts[1], ':') === 1) {
        $this->prefix = mb_substr($parts[1], 0, 1).':\\';
        $this->path = array_slice($parts, 2, -1);
        $driveStyle = self::WINDOWS_DRIVE_UNIX_STYLE;
      // windows drive as windows path C:/
      } elseif(mb_strpos($parts[0], ':') === 1) {
        $this->prefix = mb_substr($parts[0], 0, 1).':\\';
        $this->path = array_slice($parts, 1, -1);
        $driveStyle = self::WINDOWS_DRIVE_WINDOWS_STYLE;
      } else {
        $this->path = array_slice($parts, 0, -1); // parts 0 maybe not empty for directory/ as rest of path
        if (mb_strpos($path, '/') === 0) {
          $this->prefix = '/';
        }
        $driveStyle = self::WINDOWS_DRIVE_WINDOWS_STYLE;
      }
      
      $this->wrapWith($wrapper, $driveStyle);
    } elseif (self::isAbsolutePath($path)) {

      if (mb_strpos($path, '\\\\') === 0) {
        $parts = explode('\\', $this->fixToWindowsPath($path));
        $this->prefix = '\\\\'.$parts[1];
        $this->path = array_slice($parts, 1, -1);

      } elseif (DIRECTORY_SEPARATOR === '\\') {
        // windows drive as windows path D:\
        $parts = explode('\\', $this->fixToWindowsPath($path));

        if (mb_strpos($parts[0], ':') === 1) {
          $this->prefix = $parts[0].'\\';
          $this->path = array_slice($parts, 1, -1);

          // windows drive as unix path on windows
        } elseif (mb_strpos($parts[1], ':') === 1) {
          $this->prefix = $parts[1].'\\';
          $this->path = array_slice($parts, 2, -1);

        } else {
          // unix path on windows
          $this->prefix = '/';
          $this->path = array_slice($parts, 0, -1);
        }

      } else {
        $parts = explode('/', $this->fixToUnixPath($path));

        // windows drive as windows path /C:/
        if (mb_strpos($parts[1], ':') === 1) {
          $this->prefix = '/'.mb_substr($parts[1], 0, 1).':/';
          $this->path = array_slice($parts, 2, -1);
        } elseif (mb_strpos($parts[0], ':') === 1) {
          $this->prefix = '/'.mb_substr($parts[0], 0, 1).':/';
          $this->path = array_slice($parts, 1, -1);
        } else {
          $this->prefix = '/';
          $this->path = array_slice($parts, 0, -1);
        }
      }

    } else {
      // relative
      $this->prefix = NULL;

      if (DIRECTORY_SEPARATOR === '\\') {
        $parts = explode('\\', $this->fixToWindowsPath($path));
      } else {
        $parts = explode('/', $this->fixToUnixPath($path));
      }

      $this->path = $parts;
    }

    // cleanup empty elements
    $this->path = array_filter(
      $this->path, 
      function ($part) {
        return (mb_strlen($part) > 0);
      }
    );

    // renumber
    $this->path = array_merge($this->path);

    return $this;
  }

  public static function fixToUnixPath($mixedPath) {
    $bs = preg_quote('\\');
    return Preg::replace($mixedPath, '~'.$bs.'(?!(\s|'.$bs.'))~', '/');
  }

  protected static function fixToWindowsPath($mixedPath) {
    return str_replace('/', '\\', $mixedPath);
  }

  /**
   * @param string &$wrapper
   * @return string $path (verkürzt um den wrapper)
   */
  protected function extractWrapper($path, &$wrapper) {
    $m = array();
    if (Preg::match($path, '|^([a-z\.0-9]+)://(.*)$|', $m) > 0) {
      $wrapper = $m[1];
      $path = rtrim($m[2],'\\/').'/'; // cleanup trailing-backslash
    }

    return $path;
  }
  
  public static function isWrappedPath($path) {
    return Preg::match($path, '|^([a-z\.0-9]+)://(.*)$|') > 0;
  }

  public static function isCygwinPath($path) {
    return Preg::match($path, '|^/cygdrive/[a-z]/|i') > 0;
  }

  public function isCygwin() {
    return $this->cygwin;
  }
  
  /**
   * @return string ohne :// dahinter
   */
  public function getWrapper() {
    return $this->wrapper;
  }
  
  /**
   * Wraps the dir with the wrapper and converts windows paths
   * 
   * @deprecated
   * @param string only the name of the wrapper like file or vfs or phar
   */
  public function setWrapper($wrapperName) {
    return $this->wrapWith($wrapperName);
  }

  /**
   * Wraps the dir with the wrapper and converts windows paths
   * 
   * @param string only the name of the wrapper like file or vfs or phar
   */
  public function wrapWith($wrapperName, $driveStyle = NULL) {
    $this->wrapper = $wrapperName;

    $this->prefix = $this->wrapper.'://'.$this->getOSPrefix(self::UNIX, $driveStyle ?: self::WINDOWS_DRIVE_WINDOWS_STYLE);

    return $this;
  }

  /**
   * @return bool
   */
  public function isWrapped() {
    return $this->wrapper !== NULL;
  }

  /**
   * Resolves relative parts of the path an normalizes
   *
   * if path is relative its resolved to the current working directory
   * 
   * the type of the path of getcwd will be used. So this might change the prefix-type of directory!
   * if dir is not relative this works like realpath with directories that do not exist
   * @uses PHP::getcwd()
   * @chainable
   */
  public function resolvePath() {
    if (count($this->path) == 0) {
      return $this;
    }
    
    if ($this->isRelative()) { 
      /* wir ermitteln das aktuelle working directory und fügen dieses vor unserem bisherigen Pfad hinzu
       * den . am anfang brauchen wir nicht wegmachen, das wird nachher normalisiert
       */
      $cwd = self::factory(getcwd().DIRECTORY_SEPARATOR);
      $this->prefix = $cwd->getPrefix($this);

      $this->path = array_merge(
        $cwd->getPathArray(), 
        $this->path
      ); 
    }

    /* pfad normalisieren */
    $newPath = array();
    foreach ($this->path as $dir) {
      if ($dir !== '.') { // dir2/dir1/./dir4/dir3 den . ignorieren
        if ($dir == '..') {  // ../ auflösen dadurch, dass wir ein verzeichnis zurückgehen
          array_pop($newPath);
        } else {
          $newPath[] = $dir;
        }
      }
    }
        
    $this->path = $newPath;

    return $this;
  }

  /**
   * Löst einen Pfad zu einem absoluten auf
   *
   * der Pfad ist ein string mit forwardslashes. beginnt er mit . oder mit .. wird er relativ zum objekt-path gesehen und angehängt
   * ansonsten wird er absolut interpretiert.
   * @param string $path
   * @return Dir (neue instance)
   */
  public function expand($path) {
    if (mb_strpos($path, '.') === 0) {
      return $this->sub($path);
    } else {
      return self::factory($path);
    }
  }

  /**
   * Verkürzt einen Pfad in Bezug auf ein anderes Verzeichnis
   * 
   * Die Funktion kann z.b. dafür benutzt zu werden aus einem absoluten Verzeichnis ein Relatives zu machen.<br />
   * Die Umwandlung in ein relatives Verzeichnis geschieht in Bezug auf das angegebene Verzeichnis.<br />
   * Wenn das aktuelle Verzeichnis ein Unterverzeichnis des angegebenen ist, wird das Verzeichnis in ein relatives 
   * umgewandelt (sofern es das nicht schon war) und der Pfad bis zum angegeben Verzeichnis verkürzt.
   * @param Dir $dir das Verzeichnis zu welchem Bezug genommen werden soll
   */
  public function makeRelativeTo(Dir $dir) {
    $dir = clone $dir;
    $removePath = (string) $dir->resolvePath();
    $thisPath = (string) $this->resolvePath();
    
    if (!S::startsWith($thisPath,$removePath) || mb_strlen($thisPath) < mb_strlen($removePath))
      throw new Exception('Das Verzeichnis ('.$thisPath.')[1] muss in ('.$removePath.')[2] sein. Kann [1] nicht relatv zu [2] machen, da [2] zu lang ist. Vielleicht Argumente falsch rum?');

    if ($removePath == $thisPath) {
      $this->setPath('.'.DIRECTORY_SEPARATOR); // das Verzeichnis ist relativ gesehen zu sich selbst das aktuelle Verzeichnis
      return $this;
    }

    /* schneidet den zu entfernen pfad vom aktuellen ab */
    $this->path = array_slice($this->path, count($dir->getPathArray()));
    $this->makeRelative();

    /* ./ hinzufügen */
    array_unshift($this->path,'.');

    return $this;
  }

  /**
   * Sets the path to a relative one (no matter if its relative or absolute)
   * 
   * this removes the absolute part of the path which will be:
   * /cygdrive/x for cygwin paths
   * X:\ for windows paths
   * / for unix paths
   * wrapper:// for wrapper-paths
   * @chainable
   */
  public function makeRelative() {
    $this->cygwin = FALSE;
    $this->wrapper = NULL;
    $this->prefix = NULL;
    return $this;
  }
  
  /**
   * Gibt die URL zum Verzeichnis zurück
   *
   * das root Verzeichnis muss angegeben werden
   * URL hat keinen Trailingslash! aber einen slash davor
   */
  public function getURL(Dir $relativeDir = NULL) {
    if (!isset($relativeDir)) $relativeDir = new Dir('.'.DIRECTORY_SEPARATOR);
    
    $rel = clone $this;
    $rel->makeRelativeTo($relativeDir);
    $pa = $rel->getPathArray();
    unset($pa[0]);
    
    return '/'.implode('/',array_map('rawurlencode',$pa)); 
  }
  
  /**
   * Überprüft ob wir Unterverzeichnis eines anderen sind
   *
   * Gibt TRUE zurück wenn $this ein Unterverzeichnis von $parent ist
   * Gibt TRUE zurück wenn $this in $parent enthalten ist
   *
   * gibt FALSE zurück wenn $this und $parent gleich sind
   *
   * ansonsten False
   * @param Dir $parent das Oberverzeichnis zu überprüfen, wenn dies mit $this.equals() wird auch false zurückgegeben
   */
  public function isSubdirectoryOf(Dir $parent) {
    $parentPath = (string) $parent->resolvePath();
    $thisPath = (string) $this->resolvePath();
    
    return S::startsWith($thisPath,$parentPath) && mb_strlen($parentPath) < mb_strlen($thisPath); //das hintere schließt Gleichheit aus
  }

  /**
   * Fügt dem aktuellen Verzeichnis-Pfad ein Unterverzeichnis oder mehrere (einen Pfad) hinzu
   *
   * Wenn $dir der String ".." ist wird ins ParentDir gewechselt (sofern dies möglich ist)
   * Das angegebene Verzeichnis ist ein relatives Verzeichnis und dessen Pfad wird hinzugefügt
   *
   * wenn $dir eine File ist, wird das subverzeichnis angehängt und eine Referenz auf die Datei mit demselben Namen im Verzeichnis zurückgegeben, dabei muss das Verzeichnis von $file ein relatives Dir sein
   * 
   * $dir->append('subdir/');
   * $dir->append('./banane/tanne/apfel/');
   * @param string|Dir $dir das Verzeichnis muss relativ sein
   * @chainable
   */
  public function append($dir) {
    if ($dir == NULL) return $this;
    if ($dir == '..' && count($this->path) >= 1) {
      array_pop($this->path);
      // clearcache
      return $this;
    }
    
    if (!($dir instanceof \Psc\System\Dir)) {
      $dir = (string) $dir;
      if (!s::endsWith($dir,'/')) $dir .= '/';
      
      $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
      $dir = new Dir($dir);
    }

    foreach ($dir->getPathArray() as $part) {
      if ($part == '.') continue;
      $this->path[] = $part;
    }
    return $this;
  }
  
  /**
   * Returns a copy of the instance from a subdirectory
   *
   * @param string $subDirUrl with / at the end and / inbetween (dont' use backslash!)
   * @return Dir
   */
  public function sub($subDirUrl) {
    $sub = clone $this;
    return $sub->append($subDirUrl);
  }
  
  /**
   * Returns a copy of the instance of the parent directory
   *
   * @return Dir
   */
  public function up() {
    $up = clone $this;
    return $up->append('..');
  }
  
  /**
   * Slices parts of the path out (modifies the state)
   *
   * @chainable
   */
  public function slice($start, $length = NULL) {
    if (func_num_args() == 1) {
      $this->path = array_slice($this->path, $start);
    } else {
      $this->path = array_slice($this->path, $start, $length);
    }
    return $this;
  }

  /**
   * Returns a new instance from this directory
   */
  public function clone_() {
    return clone $this;
  }
  
  /**
   * Gibt einen Array über die Verzeichnisse und Dateien im Verzeichnis zurück
   * 
   * Ignores:<br />
   * bei den Ignores gibt es ein Paar Dinge zu beachten: Es ist zu beachten, dass strings in echte Reguläre Ausdrücke umgewandelt werden. Die Delimiter für die Ausdrücke sind // 
   * Der reguläre Ausdruck wird mit ^ und $ ergänzt. D.h. gibt man als Array Eintrag '.svn' wird er umgewandelt in den Ausdruck '/^\.svn$/' besondere Zeichen werden gequotet
   * Wird der Delimiter / am Anfang und Ende angegeben, werden diese Modifikationen nicht gemacht<br />
   * Diese Ignore Funktion ist nicht mit Wildcards zu verwechseln (diese haben in Regulären Ausdrücken andere Funktionen).
   * 
   * Ignores von unserem Verzeichnis werden an die Unterverzeichnisse weitervererbt.
   *
   * Extensions: <br />
   * Wird extensions angegeben (als array oder string) werden nur Dateien (keine Verzeichnisse) mit dieser/n Endungen in den Array gepackt.
   * Ignores werden trotzdem angewandt.
   * 
   * @param array|string $extensions ein Array von Dateiendungen oder eine einzelne Dateiendung
   * @param array $ignores ein Array von Regulären Ausdrücken, die auf den Dateinamen/Verzeichnisnamen (ohne den kompletten Pfad) angewandt werden
   * @param int $sort eine Konstante die bestimmt, wie die Dateien in Verzeichnissen sortiert ausgegeben werden sollen
   * @return Array mit Dir und File
   */
  public function getContents($extensions = NULL, Array $ignores=NULL, $sort = NULL, $subDirs = NULL) {
    if (!$this->exists())
      throw new Exception('Verzeichnis existiert nicht: '.$this);
      
    if (!is_bool($subDirs))
      $subDirs = !isset($extensions); // subDirs werden per Default durchsucht wenn extensions nicht angegeben ist

    $handle = opendir((string) $this);
      
    if ($handle === FALSE) {
      throw new Exception('Fehler beim öffnen des Verzeichnisses mit opendir(). '.$this);
    }

    /* ignore Dirs schreiben */
    if (isset($this->ignores) || $ignores != NULL) {
      $ignores = array_merge($this->ignores, (array) $ignores);

      foreach ($ignores as $key=>$ignore) {
        if (!S::startsWith($ignore,'/') || !S::endsWith($ignore,'/'))
          $ignore = '/^'.$ignore.'$/';
          
        $ignores[$key] = $ignore;
      }

      $callBack = array('Webforge\Common\Preg','match');
    }
    
    $content = array();
    while (FALSE !== ($filename = readdir($handle))) {
      if ($filename != '.' && $filename != '..' && ! (isset($callBack) && count($ignores) > 0 && array_sum(array_map($callBack,array_fill(0,count($ignores),$filename),$ignores)) > 0)) {  // wenn keine ignore regel matched
          
        if (is_file($this->getPath().$filename)) {
          $file = new File(clone $this,$filename);

          if (isset($extensions) && (is_string($extensions) && $file->getExtension() != ltrim($extensions,'.') || is_array($extensions) && !in_array($file->getExtension(), $extensions)))
            continue;

          $content[] = $file;
        }
          
        if (is_dir($this->getPath().$filename) && $subDirs) { // wenn extensions gesetzt ist, keine verzeichnisse, per default
          $directory = new Dir($this->getPath().$filename.$this->getDS());
          $directory->ignores = array_merge($directory->ignores,$ignores); // wir vererben unsere ignores

          $content[] = $directory;
        }
      }
    }
    closedir($handle);

    if ($sort !== NULL) {
        
      if ($sort & self::ORDER_ASC) {
        $order = 'asc';
      } elseif ($sort & self::ORDER_DESC) {
        $order = 'desc';
      } else {
        $order = 'asc';
      }

      /* alphabetisch sortieren */
      if ($sort & self::SORT_ALPHABETICAL) {
          
        if ($order == 'asc') {
          $function = create_function('$a,$b',
                                      'return strcasecmp($a->getName(),$b->getName()); ');
        } else {
          $function = create_function('$a,$b',
                                      'return strcasecmp($b->getName(),$a->getName()); ');
        }

        uasort($content, $function);
      }
    }

    return $content;
  }


  /**
   * Gibt alle Dateien (auch in Unterverzeichnissen) zurück
   * 
   * für andere Parameter siehe getContents()
   * @param bool $subdirs wenn TRUE wird auch in Subverzeichnissen gesucht
   * @see getContents()
   */
  public function getFiles($extensions = NULL, Array $ignores = NULL, $subdirs = TRUE) {
    if (is_string($extensions) && mb_strpos($extensions,'.') === 0)
      $extensions = mb_substr($extensions,1);
    /* wir starten eine Breitensuche (BFS) auf dem Verzeichnis */
    
    $files = array();
    $dirs = array(); // Verzeichnisse die schon besucht wurden
    $queue = array($this);

    while (count($queue) > 0) {
      $elem = array_pop($queue);

      /* dies machen wir deshalb, da wenn extension gesetzt ist, keine verzeichnisse gesetzt werden */
      foreach($elem->getContents(NULL,$ignores) as $item) {
        if ($item instanceof Dir && !in_array((string) $item, $dirs)) { // ist das verzeichnis schon besucht worden?

          if ($subdirs) // wenn nicht wird hier nichts der queue hinzugefügt und wir bearbeiten kein unterverzeichnis
            array_unshift($queue,$item);

          /* besucht markieren */
          $dirs[] = (string) $item;
        }
      }
      
      foreach($elem->getContents($extensions,$ignores) as $item) {
        if ($item instanceof File) {
          $files[] = $item;
        }
      }
    }
    
    return $files;
  }

  /**
   * Gibt alle Unterverzeichnisse (auch in Unterverzeichnissen) zurück
   * 
   * für andere Parameter siehe getContents()
   * @param bool $subdirs wenn TRUE wird auch in Subverzeichnissen gesucht, sonst werden nur verzeichnisse der ebene 1 ausgegeben
   * @see getContents()
   */
  public function getDirectories(Array $ignores = NULL, $subdirs = TRUE) {
    /* wir starten eine Breitensuche (BFS) auf dem Verzeichnis */
    
    $dirs = array(); // Verzeichnisse die schon besucht wurden
    $queue = array($this);

    while (count($queue) > 0) {
      $elem = array_pop($queue);

      /* dies machen wir deshalb, da wenn extension gesetzt ist, keine verzeichnisse gesetzt werden */
      foreach($elem->getContents(NULL,$ignores) as $item) {
        if ($item instanceof Dir && !array_key_exists((string) $item, $dirs)) { // ist das verzeichnis schon besucht worden?

          if ($subdirs) // wenn nicht wird hier nichts der queue hinzugefügt und wir bearbeiten kein unterverzeichnis
            array_unshift($queue,$item);

          /* besucht markieren */
          $dirs[(string) $item] = $item;
        }
      }
    }
    
    return $dirs;
  }

  /**
   * Setzt die Zugriffsrechte des Verzeichnisses
   * 
   * Z.b. $file->chmod(0644);  für // u+rw g+rw a+r
   * @param octal $mode 
   * @param int $flags
   * @chainable
   */
  public function chmod($mode, $flags = NULL) {
    $ret = chmod((string) $this,$mode);
    
    if ($ret === FALSE)
      throw new Exception('chmod für '.$this.' auf '.$mode.' nicht möglich');
      

    if ($flags & self::RECURSIVE) {
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->chmod($mode, $flags);
        }
      }
    }
    
    return $this;
  }

  /**
   * Löscht das Verzeichnis rekursiv
   * 
   * @chainable
   */
  public function delete() {
    if ($this->exists()) {
      
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->delete(); // rekursiver aufruf für Dir
        }
      }
      
      @rmdir((string) $this); // selbst löschen
    }

    return $this;
  }


  /**
   * Löscht die Inhalt des Verzeichnis rekursiv
   * 
   * @chainable
   */
  public function wipe() {
    if ($this->exists()) {
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->delete(); // rekursiver aufruf für Dir
        }
      }
    }

    return $this;
  }

  /**
   * Copies all Files *in* $this to $destination
   * 
   * @param Dir $destination
   * @chainable
   */
  public function copy(Dir $destination, $extensions = NULL, $ignores = NULL, $subDirs = NULL) {
    if ((string) $destination == (string) $this)
      throw new Exception('Kann nicht kopieren: Zielverzeichnis und Quellverzeichns sind gleich.');

    if (!$destination->exists())
      $destination->create();

    foreach ($this->getContents($extensions, $ignores, NULL, $subDirs) as $item) {
      if ($item instanceof File) {
        $destFile = clone $item;
        $destFile->setDirectory($destination);
        $item->copy($destFile); 
      }
        
      if ($item instanceof Dir) {
        $relativeDir = clone $item; 
        $relativeDir->makeRelativeTo($this);
        
        $destDir = clone $destination;
        $destDir->append($relativeDir); // path/to/destination/unterverzeichnis
        $item->copy($destDir); //rekursiver Aufruf
      }
    }
    return $this;
  }



  /**
   * Moves the directory and changes its internal state
   * 
   * @param Dir $destination
   * @chainable
   */
  public function move(Dir $destination) {
    $ret = @rename((string) $this,(string) $destination);

    $errInfo = 'Kann Verzeichnis '.$this.' nicht nach '.$destination.' verschieben / umbenennen.';
    
    if (!$ret) {
      if ($destination->exists())
        throw new Exception($errInfo.' Das Zielverzeichnis existiert.');

      if (!$this->exists())
        throw new Exception($errInfo.' Das Quellverzeichnis existiert nicht.');
      else 
        throw new Exception($errInfo);
    }


    /* wir übernehmen die Pfade von $destination */
    $this->path = $destination->getPathArray();
    return $this;
  }

  /**
   * Creates the full path to the directory, if it does not exist
   * 
   * @chainable
   */
  public function create() {
    $this->make(self::PARENT | self::ASSERT_EXISTS);
    return $this;
  }

  /**
   * Creates the Directory
   * 
   * @param bitmap $options self::PARENT to create the full path of the directory
   * @chainable
   */
  public function make($options=NULL) {
    if (is_int($options)) {
      $parent = ($options & self::PARENT) == self::PARENT;
      $assert = ($options & self::ASSERT_EXISTS) == self::ASSERT_EXISTS;
    } else {
      // legacy option
      $parent = (mb_strpos($options,'-p') !== FALSE);
      $assert = FALSE;
    }
      
    if (!$this->exists()) {
      $ret = @mkdir((string) $this, $this->getDefaultMod(), $parent);
      if ($ret == FALSE) {
        throw new Exception('Fehler beim erstellen des Verzeichnisses: '.$this);
      }
    } else {
      if (!$assert) {
        throw new Exception('Verzeichnis '.$this.' kann nicht erstellt werden, da es schon existiert');
      }
    }

    return $this;
  }

  /**
   * Returns the correct default for mkdir() operations (and such) that respect a umask (if set)
   * 
   * if env WEBFORGE_UMASK_SET is 1 then always 0777 is used, otherwise the content from self::$defaultMod is used
   */
  public function getDefaultMod() {
    if (getenv('WEBFORGE_UMASK_SET') == 1) {
      return 0777;
    } else {
      return self::$defaultMod;
    }
  }

  /**
   * Copy all files (just the files) from this dir into another
   *
   * wenn flat = TRUE ist, werden auch Unterverzeichnisse durchsucht. dies "flatted" die Files dann into $destination
   */
  public function copyFiles($extension, Dir $destination, $flat = FALSE, Array $ignores = NULL) {
    foreach ($this->getFiles($extension, $ignores, $flat) as $f) {
      $f->copy(new File($destination, $f->getName()));
    }
    return $this;
  }

  /**
   * Überprüft ob eine bestimmte Datei im Verzeichnis liegt (und gibt diese zurück)
   * 
   * Wird ein File Objekt übergebeben wird der Name der Datei überprüft.
   * wenn $file ein relatives Verzeichnis hat wird die datei in dem passenden relativen Subverzeichnis zurückgegebeben
   * wenn die Datei nicht existiert, passiert nichts
   * gibt immer eine Datei zurück
   * ist die Datei absolut wird eine InvalidArgumentException geworfen
   * 
   * @param string|File $file
   * @param const $relative
   * @return File
   */
  public function getFile($file) {
    if ($file instanceof File) {
      $fileName = $file->getName();
      $fileDir = $file->getDirectory();
      
      if ($fileDir->isRelative()) {
        $dir = $this->clone_()->append($file->getDirectory());
      } else {
        throw new \InvalidArgumentException('Wenn eine Datei übergeben wird, darf diese nicht absolut sein');
      }
      
    } elseif(is_string($file) && mb_strpos($file, '/') !== FALSE) {
      return File::createFromURL($file, $this);
    } else {
      $fileName = $file;
      $dir = $this;
    }

    $file = new File($fileName,$dir);
    
    return $file;
  }

  /**
   * 
   * @return bool
   */
  public function exists() {
    if (count($this->path) == 0) return FALSE;
    return is_dir((string) $this);
  }
  
  /**
   * Is the directory empty?
   *
   * a directory is empty, if it has no files or directories in it
   * a directory is empty, if it does not exist
   * @return bool
   */
  public function isEmpty() {
    return !$this->exists() || count($this->getContents()) === 0;
  }

  /**
   * @return bool
   */
  public function isWriteable() {
    if (count($this->path) == 0) return FALSE;
    return is_writable((string) $this);
  }

  /**
   * @return bool
   */
  public function isReadable() {
    if (count($this->path) == 0) return FALSE;
    return is_readable((string) $this);
  }
  
  /**
   * @return bool
   */
  public function isRelative() {
    return $this->prefix === NULL;
  }

  /**
   * @return bool
   */
  public function isAbsolute() {
    return $this->prefix !== NULL;
  }

  /**
   * @return bool
   */
  public static function isAbsolutePath($path) {
    return   mb_strpos($path, '/') === 0 // unix
          || mb_strpos($path, ':') === 1 // windows C:\ etc
          || self::isCygwinPath($path)   // /cygdrive/c
          || self::isWrappedPath($path) // phar:// ...
          || mb_strpos($path, '\\\\') === 0;
  }
  
  /**
   * Returns the Path as string
   * 
   * the path is returned for the current Operating System
   * @return string
   */
  public function getPath($flags = 0x000000) {
    $ds = $this->getDS();

    $trail = $flags & self::WITHOUT_TRAILINGSLASH ? '' : $ds;

    return $this->prefix.(empty($this->path) ? '' : implode($ds, $this->path).$trail);
  }

  /**
   * Returns the path without trailing slash
   */
  public function wtsPath() {
    return $this->getPath(self::WITHOUT_TRAILINGSLASH);
  }

  /**
   * Returns the path of the directory converted to specific OS
   * 
   * some edge cases will throw an LogicException because they cannot be converted:
   * 
   * D:\\windows is on unix: /D:/windows/ (like mozilla does it with file://)
   * /var/local/www/ is on windows??
   * 
   * @return string
   * @param const $os self::WINDOWS|self::UNIX
   */
  public function getOSPath($os, $flags = 0x000000) {
    $osDS = $this->getOSDS($os, $flags);

    return $this->getOSPrefix($os,$flags).(empty($this->path) ? '' : implode($osDS, $this->path).$osDS);
  }  

  /**
   * Returns a prefix which is converted to the specific os
   * 
   * if prefix is absolute this ends with a slash or backslash
   * @return string
   */
  protected function getOSPrefix($os, $flags = 0x000000) {

    $letter = NULL;
    if ($this->isWindowsDrivePrefix($letter)) {
      $osPrefix = '';

      if (($flags & self::WINDOWS_WITH_CYGWIN) && $os === self::WINDOWS) {
        $osPrefix .= '/cygdrive/'.mb_strtolower($letter).'/';
      } else {

        if (!($flags & self::WINDOWS_DRIVE_WINDOWS_STYLE) && $os === self::UNIX) {
          $osPrefix  .= '/';
        }

        $osPrefix .= $letter.':'.$this->getOSDS($os);
      }

    } else {
      $osPrefix = $this->prefix;
    }

    return $osPrefix;
  }

  /**
   * Returns the DirectorySeperator
   * 
   * @return \ oder / (bei isWrapped() true ist dies immer / (oder by cygwin paths)
   */
  public function getDS() {
    return ($this->isWrapped() || $this->isCygwin()) ? '/' : DIRECTORY_SEPARATOR;
  }

  /**
   * Returns the DirectorySeperator for a specific operating system
   * 
   * @return \ or /
   */
  public function getOSDS($os, $flags = 0x000000) {
    return ($this->isWrapped() || $this->isCygwin() || ($flags & self::WINDOWS_WITH_CYGWIN) || $os === self::UNIX) ? '/' : '\\';
  }

  /**
   * @return bool
   */
  protected function isWindowsDrivePrefix(&$letter = NULL) {
    if (mb_strpos($this->prefix, ':') === 1) {
      $letter = mb_substr($this->prefix, 0, 1);
      return TRUE;
    } elseif (mb_strpos($this->prefix, ':') === 2) { // like /C:/ 
      $letter = mb_substr($this->prefix, 1, 1);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Returns on Unix an Unix path and on Windows an Cygdrive compatible path
   * 
   * @return string
   */
  public function getUnixOrCygwinPath() {
    return $this->getOSPath($this->getOS(), Dir::WINDOWS_WITH_CYGWIN);
  }
  
  /**
   * Is the path to the other directory the same?
   * @return bool
   */
  public function equals(Dir $dir) {
    return $this->getPath() === $dir->getPath();
  }

  /**
   * @return array
   */
  public function getPathArray() {
    return $this->path;
  }

  /**
   * Returns the basename of the directory
   * 
   * @return string
   */
  public function getName() {
    if (count($this->path) > 0)
      return $this->path[count($this->path)-1];
  }
  
  /**
   * @return DateTime
   */
  public function getModifiedTime() {
    return new Datetime(filemtime((string) $this));
  }

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getAccessTime() {
    return new Datetime(fileatime((string) $this));
  }

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getCreateTime() {
    return new Datetime(filectime((string) $this));
  }

  /**
   * 
   * @return const WINDOWS|UNIX
   */
  public function getOS() {
    if (mb_substr(PHP_OS, 0, 3) == 'WIN') {
      $os = self::WINDOWS;
    } else {
      $os = self::UNIX;
    }
    return $os;
  }

  public function __toString() {
    return $this->getPath();
  }
  
  public function getQuotedString($flags = 0) {
    $str = (string) $this;
    
    if ($flags & self::WITHOUT_TRAILINGSLASH) {
      $str = mb_substr($str, 0, -1);
    }
    
    if (mb_strpos($str, ' ') !== FALSE) {
      return escapeshellarg($str);
    }
    
    return $str;
  }

  /**
   * Extrahiert das Verzeichnis aus einer Angabe zu einer Datei
   * 
   * @param string $string der zu untersuchende string
   * @return Dir
   */
  public static function extract($string) {
    if (mb_strlen($string) == 0) {
      throw new Exception('String ist leer, kann kein Verzeichnis extrahieren');
    }
    
    $path = dirname($string).DIRECTORY_SEPARATOR;
    try {
      $dir = new Dir($path);
    } catch (Exception $e) {
      throw new Exception('kann kein Verzeichnis aus dem extrahierten Verzeichnis "'.$path.'" erstellen: '.$e->getMessage());
    }
    
    return $dir;
  }

  /**
   * 
   * function used from resolvePath()
   * @namespace-only
   */
  public function getPrefix(Dir $getter) {
    return $this->prefix;
  }
}
