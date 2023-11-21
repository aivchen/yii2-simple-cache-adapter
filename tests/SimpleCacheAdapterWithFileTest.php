<?php

namespace Aivchen\SimpleCache\Tests;

use Psr\SimpleCache\CacheInterface;
use Aivchen\SimpleCache;
use yii\caching;

class SimpleCacheAdapterWithFileTest extends BaseTestCase
{
    protected array $skippedTests = [
        'testSetMultipleWithIntegerArrayKey' => '',
    ];

    protected function createSimpleCache(): CacheInterface
    {
        return new SimpleCache\Adapter([
            'cache' => [
                'class' => caching\FileCache::class,
                'cachePath' => __DIR__ . DIRECTORY_SEPARATOR . 'runtime',
            ],
        ]);
    }
}
