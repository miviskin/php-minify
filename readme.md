php-minify
=====================

## Install via Composer

In composer.json
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/miviskin/php-minify.git"
        }
    ],
    "require": {
        "miviskin/php-minify": "dev-master"
    }
}
```

Update composer

```shell
$ composer update
```

## PHP

```php
$minify = new Miviskin\Minify\Factory(
    new Miviskin\Minify\CompressorResolver(),
    new Illuminate\Filesystem\Filesystem()
);
$minify
    ->addExtension('js', 'JavaScript', function() {
        return new Miviskin\Minify\Compressor\JavaScript();
    })
    ->addExtension('css', 'StyleSheet', function() {
        return new Miviskin\Minify\Compressor\StyleSheet();
    });
```
