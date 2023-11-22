<?php

namespace Aivchen\SimpleCache\Tests;

use Psr\SimpleCache\CacheInterface;
use Aivchen\SimpleCache;
use yii\caching;

final class SimpleCacheAdapterWithMemoryTest extends BaseTestCase
{
    protected function createSimpleCache(): CacheInterface
    {
        return new SimpleCache\Adapter([
            'cache' => [
                'class' => caching\ArrayCache::class,
            ],
        ]);
    }
}
