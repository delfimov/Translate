# Translate

Easy to use PHP class for multi-language websites with language auto detection and plurals.

## Requirements

 * [PHP >= 5.4](http://www.php.net/)

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
your project's directories and require '/path/to/Translate/Translate.php';
If you don't speak git or just want a tarball, click the 'zip' button at the top of the page in GitHub.

## A Simple Example

`example.php`
```php
<?php
require "src/Translate.php"; // better use Composer, see README.md
$t = new delfimov\Translate(
    [
        "default" => "en",
        "available" => ["en", "ru"],
        "path" => __DIR__ . "/messages"
    ]
);


$num = rand(0, 100);

echo $t->t('some string'); // or $t('some string');
echo $t->plural('%d liters', $num);
echo $t->plural("The %s contains %d monkeys", $num, ['tree', $num]);

$t->setLanguage("ru");

echo $t->t('some string'); // or $t('some string');
echo $t->plural('%d liters', $num);
echo $t->plural("The %s contains %d monkeys", $num, ['tree', $num]);
```

`messages\en\messages.php`
```php
<?php
return [
    'some string' => 'Some string',
    '%d liters' => '%d liter|%d liters',
    'The %s contains %d monkeys' => 'The %s contains %d monkey|The %s contains %d monkeys',
]
```

`messages\ru\messages.php`
```php
<?php
return [
    'some string' => 'Просто строка',
    '%d liters' => '%d литр|%d литра|%d литров',
    'The %s contains %d monkeys' => 'На %s сидит %d обезьяна|На %s сидят %d обезьяны|На %s сидят %d обезьян',
    'tree' => 'дереве'
]
```

## TODO

 * Unit tests
 * php-cs
 * `Translate->getMessages()` and `Translate->getMessage()`. Must use a message loader to be able to load translation messages from any source, not just php files.
 * `Translate->getLanguage()`. Rewrite, simplify and add 3 letters language codes support.  
 