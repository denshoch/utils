Denshoch Utils
===============

Utility class used in Denshoch softwares.

Install
--------

```
composer install
```

Usage
-------

### removeControlChars()

Remove Unicode control characters from input text.

```php
$text = "&#x0;&#x1";
\Denshoch\Utils::removeControlChars( $text );
var_dump( $text ); #=> ''
```


Test
-----

```
vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```