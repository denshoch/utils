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

## removeControlChars()

Remove Unicode control characters from input text.

```php
$text = "&#x0;&#x1";
\Denshoch\Utils::removeControlChars( $text );
var_dump( $text ); #=> ''
```

## HtmlModifier

```php
$html = "<div><p>Hello, world!</p></div>";
$result = \Denshoch\HtmlModifier::modify($html, 'p', 'my-class');
=> '<div><p class="my-class">Hello, world!</p></div>'


$html = "<div><p>Hello, world!</p></div>";
$tagClassPairs = [
    'p' => 'my-class',
    'div' => 'my-other-class'
];
$result = HtmlModifier::modifyMultiple($html, $tagClassPairs);
=> '<div class="my-other-class"><p class="my-class">Hello, world!</p></div>'
```



Test
-----

```
vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```