<?php

declare(strict_types=1);

namespace Aivchen\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use yii\base;

final class Bootstrap implements base\BootstrapInterface
{
    public function bootstrap($app): void
    {
        $app->setContainer([
            'singletons' => [
                CacheInterface::class => Adapter::class,
            ],
        ]);
    }
}
