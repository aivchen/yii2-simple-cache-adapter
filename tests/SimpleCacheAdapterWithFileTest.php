<?php

namespace Aivchen\SimpleCache\Tests;

use Psr\SimpleCache\CacheInterface;
use Aivchen\SimpleCache;
use yii\caching;

final class SimpleCacheAdapterWithFileTest extends BaseTestCase
{
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
