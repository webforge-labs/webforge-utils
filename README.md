# webforge-utils


## whats included

- DateTime and Time classes for an advanced (and more concise) interface for the PHP::DateTime classes
- A full functional File and Directory implementation that abstracts a lot of PHP file funtions
  - supports nearly every way to write a file path (and convert): 
    - windows D:\www\something.php
    - unix /some/path
    - wrappers: vfs://some/file/topath
    - windows cygin /cydrive/d/www/someting.php
    - windows shares \\\\psc-laptop\shared\www\something.php
    - unix style windows paths (sublime and others): /D/www/something.php
  - copy dirs and files recursively
  - find files recursively
- Commonly used Exceptions with better semantics and debug output (FileNotFound, NotImplemented, Deprecated)
- Some simple Utils to debug and dump variables
- A bunch of useful String and Array functions

## usage 

  - [Dir and File API](docs/dir-and-file.md)

## installation

Use [Composer](http://getcomposer.org) to install.
```
composer require webforge/utils
```

## migrate to 2.0.x

- use php 8.1

## migrate to 1.1.x

`Webforge\Common\String` was renamed to `Webforge\Common\StringUtil`
