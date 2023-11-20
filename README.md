# Yii2 SimpleCache adapter
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-simple-cache-adapter/version)](https://packagist.org/packages/wearesho-team/yii2-simple-cache-adapter)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-simple-cache-adapter/downloads)](https://packagist.org/packages/wearesho-team/yii2-simple-cache-adapter)
[![Build Status](https://travis-ci.org/wearesho-team/yii2-simple-cache-adapter.svg?branch=master)](https://travis-ci.org/wearesho-team/yii2-simple-cache-adapter)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-simple-cache-adapter/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-simple-cache-adapter)

An Adapter for SimpleCache (PSR-16) to Yii2 cache

This library originally developed by [devonliu02](https://github.com/devonliu02) and [Wearesho Team](https://wearesho.com)

## Installation

```bash
composer require wearesho-team/yii2-simple-cache-adapter
```

## Usage

```php
<?php

use Wearesho\SimpleCache;

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
