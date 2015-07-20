# Files for tests

## access
You can access those files in every test, which extends `Webforge\Common\TestCase` with `$this->getFile('subDir/thename.txt');`
you can get directories with: `$this->getTestDirectory('subDir/')`

## short description

- htdocs: a directory with files in it, with subdirectories, with other files
- some.json a valid .json file
- some.phar.gz a small phar.gz (not executable), contents unknown!