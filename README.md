[![Latest Stable Version](https://poser.pugx.org/delfimov/translate/v/stable)](https://packagist.org/packages/delfimov/translate)
[![Build Status](https://travis-ci.org/delfimov/Translate.svg?branch=master)](https://travis-ci.org/delfimov/Translate)
[![StyleCI](https://styleci.io/repos/3325239/shield?branch=master)](https://styleci.io/repos/3325239)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/26b7cfce-3636-4385-be29-54f51b6dfe42/mini.png)](https://insight.sensiolabs.com/projects/26b7cfce-3636-4385-be29-54f51b6dfe42)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/delfimov/GDImage/blob/master/LICENSE)


# Translate

Easy to use i18n translation PHP class for multi-language websites 
with language auto detection and plurals.

PSR-6 translation containers. PSR-3 logger.

## Requirements

 * [PHP >= 5.6](http://www.php.net/)

## How to install

Add this line to your composer.json file:

```json
"delfimov/translate": "~2.0"
```

or

```sh
composer require delfimov/translate
```

Alternatively, copy the contents of the Translate folder into one of 
your project's directories and `require 'src/Translate.php';`, 
`require 'src/Loader/LoaderInterface.php';`, `require 'src/Loader/PhpFilesLoader.php';`
If you don't speak git or just want a tarball, click the 'zip' button 
at the top of the page in GitHub.

## A Simple Example

See [`example`](example) directory for sources.

`example\example.php`
```php
<pre><?php

use DElfimov\Translate\Translate;
use DElfimov\Translate\Loader\PhpFilesLoader;
use Monolog\Logger; // PSR-3 logger, not required  
use Monolog\Handler\StreamHandler;

$log = new Logger('Translate');
$log->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

$t = new Translate(
    new PhpFilesLoader(__DIR__ . "/messages"),
    [
        "default" => "en",
        "available" => ["en", "ru"],
    ],
    $log // optional
);

$num = rand(0, 100);

$t->setLanguage("en"); // this is not required, language will be auto detected with Accept-Language HTTP header
echo $t->t('some string') . "\n\n"; // or $t('some string');
echo $t->plural('%d liters', $num) . "\n\n";
echo $t->plural("The %s contains %d monkeys", $num, ['tree', $num]) . "\n\n";

$num = rand(0, 100);

$t->setLanguage("ru");
echo $t->t('some string')."\n\n"; // or $t('some string');
echo $t->plural('%d liters', $num) . "\n\n";
echo $t->plural("The %s contains %d monkeys", $num, ['tree', $num]) . "\n\n";

?></pre>
```

`example\messages\en\messages.php`
```php
<?php
return [
    'some string' => 'Some string',
    '%d liters' => '%d liter|%d liters',
    'The %s contains %d monkeys' => 'The %s contains %d monkey|The %s contains %d monkeys',
];
```

`example\messages\ru\messages.php`
```php
<?php
return [
    'some string' => 'Просто строка',
    '%d liters' => '%d литр|%d литра|%d литров',
    'The %s contains %d monkeys' => 'На %s сидит %d обезьяна|На %s сидят %d обезьяны|На %s сидят %d обезьян',
    'tree' => 'дереве'
];
```

## TODO

 * Better code coverage
 

