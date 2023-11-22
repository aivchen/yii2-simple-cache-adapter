# Yii2 SimpleCache adapter
[![Latest Stable Version](http://poser.pugx.org/aivchen/yii2-simple-cache-adapter/v)](https://packagist.org/packages/aivchen/yii2-simple-cache-adapter) [![Total Downloads](http://poser.pugx.org/aivchen/yii2-simple-cache-adapter/downloads)](https://packagist.org/packages/aivchen/yii2-simple-cache-adapter) [![Latest Unstable Version](http://poser.pugx.org/aivchen/yii2-simple-cache-adapter/v/unstable)](https://packagist.org/packages/aivchen/yii2-simple-cache-adapter) [![License](http://poser.pugx.org/aivchen/yii2-simple-cache-adapter/license)](https://packagist.org/packages/aivchen/yii2-simple-cache-adapter) [![PHP Version Require](http://poser.pugx.org/aivchen/yii2-simple-cache-adapter/require/php)](https://packagist.org/packages/aivchen/yii2-simple-cache-adapter)

An Adapter for SimpleCache (PSR-16) to Yii2 cache

This library originally developed by [devonliu02](https://github.com/devonliu02) and [Wearesho Team](https://wearesho.com)

## Installation

```bash
composer require aivchen/yii2-simple-cache-adapter
```

## Usage

```php
<?php

use Aivchen\SimpleCache;

$adapter = new SimpleCache\Adapter; // will use \Yii::$app->cache by default

$customAdapter = new SimpleCache\Adapter([
    'cache' => [
        'class' => \yii\caching\ArrayCache::class, // or your custom \yii\caching\CacheInterface implementation
    ],
]);

```

## Contributors
- [Andrew Ivchenkov](mailto:and.ivchenkov@gmail.com)
- [Alexander Letnikow](mailto:reclamme@gmail.com)
- [devonliu02](https://github.com/devonliu02)

## License
[MIT](./LICENSE.md)
