# Dir and File

Dir and File help you to do very common, often typed actions with php file utils. Notice: every directory has a trailing slash or backslash at its end per default. You have to construct those paths with trailing slash as well, unless you use `Dir::factoryTS`

## usage
```php
$dir = new Dir('/my/root');

$dir->getFile('index.php')->writeContents('<?php echo "hello world"; ')->copy(new File('index.html', $dir));
```

```php
$dir->copy(Dir::createTemporary());
```

```php
$file->move(new File(...));
$file->copy(new File(...));
$file->copy(Dir::factoryTS(__DIR__));
```

```php
$file->getCreationTime();
$file->getModificationTime()->format('d.m.Y H:i');
$file->getAccessTime()->1i8n_format('d. F H:i');
```

In most of the cases you will work with libraries and other devs that do not trailslash their directoriese

will return the path as string [w]ithout [t]railing [s]lash:
```php
$dir->wtsPath();
```

will return the path as string with trailing slash:
```php
$dir->getPath();
```

## umask and defaultMod

For historical reasons the default permissions for a new Directory created by Webforge are 0644 (octal). To make webforge respekt your umask, configure it and set the environment variable `WEBFORGE_UMASK_SET` to 1. It then will call mkdir() with 0777 (octal) so that your umask is respected.