## Read A Public File

Use the file browser to look for a file relative to the public directory. 
It will look first in the configured public directory, then as a relative file in the _public_ directory of your modules. 
When your file is found, the file browser will stop looking and return the file. 

```php
<?php

use ride\library\system\file\browser\FileBrowser;

function foo(FileBrowser $fileBrowser) {
    $file = $fileBrowser->getPublicFile('img/someimage.png');
    if ($file) {
        // ...
    }
}
```

## Read A Relative File

Use the file browser to look for a file relative to the directory structure. 
It will look first in the _application_ directory, then in the installed modules according to their level. 
When your file is found, the file browser will stop looking and return the file. 

```php
<?php

use ride\library\system\file\browser\FileBrowser;

function foo(FileBrowser $fileBrowser) {
    $file = $fileBrowser->getFile('data/text.txt');
    if ($file) {
        $contents = $file->read();
    }
}
```

## Read Multiple Relative Files

Use the file browser to look for all files relative to the _application_ directory or a module. 
It will look first in the _application_ directory, then in the installed modules according to their level.

```php
<?php

use ride\library\system\file\browser\FileBrowser;

function foo(FileBrowser $fileBrowser) {
    $files = $fileBrowser->getFiles('data/text.txt');
    foreach ($files as $file) {
        $contents = $file->read();
        ...
    }
}
```

## Write A File

When you need to write a file, it should be in the _public_ or the _application_ directory. 
You can obtain these from the file browser, create your file and write the contents to it.

```php
<?php

use ride\library\system\file\browser\FileBrowser;

function foo(FileBrowser $fileBrowser) {
    $applicationDirectory = $fileBrowser->getApplicationDirectory();
    
    $file = $applicationDirectory->getChild('data/dummy.ini');
    $file->write('enable = 1');
}
```
