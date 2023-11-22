<?php

declare(strict_types=1);

namespace Aivchen\SimpleCache\Tests;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Aivchen\SimpleCache\Bootstrap;

final class BootstrapTest extends TestCase
{
    public function testBootstrap(): void
    {
        $this->assertFalse(\Yii::$container->has(CacheInterface::class));
        $app = $this->createPartialMock(\yii\console\Application::class, []);
        $bootstrap = new Bootstrap();
        $bootstrap->bootstrap($app);
        $this->assertTrue(\Yii::$container->has(CacheInterface::class));
    }
}
