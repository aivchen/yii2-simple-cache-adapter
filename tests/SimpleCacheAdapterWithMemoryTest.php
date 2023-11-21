<?php

namespace Aivchen\SimpleCache\Tests;

use Psr\SimpleCache\CacheInterface;
use Aivchen\SimpleCache;
use yii\caching;

class SimpleCacheAdapterWithMemoryTest extends BaseTestCase
{
    protected array $skippedTests = [
        'testSetMultipleWithIntegerArrayKey' => '',
    ];

    protected function createSimpleCache(): CacheInterface
    {
        return new SimpleCache\Adapter([
            'cache' => [
                'class' => caching\ArrayCache::class,
            ],
        ]);
    }
}
