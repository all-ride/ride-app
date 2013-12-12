## Read A Absolute File

Use the _File_ object to read a absolute file or a file relative to the running script.

    use zibo\library\filesystem\File;
    ...
    $file = new File('data/text.txt');
    if ($file->exists()) {
        $contents = $file->read();
    }

## Read A Public File

Use _Zibo_ to look for a file relative to the public directory structure. 
Zibo will look first in the configured public directory, then as a relative file in the _public_ directory. 
When your file is found, Zibo will stop looking and return the file. 

    $file = $zibo->getPublicFile('img/someimage.png');
    if ($file) {
        // ...
    }

## Read A Relative File

Use _Zibo_ to look for a file relative to the directory structure. 
Zibo will look first in the _application_ directory, then in the installed modules. 
When your file is found, Zibo will stop looking and return the file. 
The directories of the modules are processed alphabetically.

    $file = $zibo->getFile('data/text.txt');
    if ($file) {
        $contents = $file->read();
    }

## Read Multiple Relative Files

Use _Zibo_ to look for all files relative to the _application_ directory or a module. 
Zibo will look first in the _application_ directory, then in the installed modules.

    $files = $zibo->getFiles('data/text.txt');
    foreach ($files as $file) {
        $contents = $file->read();
        ...
    }
    
## Write A File

When you need to write a file, it should be in the _public_ or the _application_ directory. 
You can obtain these from Zibo, create your absolute file and write the contents to it.

    use zibo\library\filesystem\File;
    ...
    $content = 'disable = 1';
    
    $applicationDirectory = $zibo->getApplicationDirectory();
    
    $file = new File($applicationDirectory, 'config/minify.ini');
    $file->write($content);
    
_Note: Don't write parameters like this, use $zibo->setParameter($key, $value)._