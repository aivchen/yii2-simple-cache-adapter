<?php

declare(strict_types=1);

namespace Wearesho\SimpleCache\Tests;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Wearesho\SimpleCache\Bootstrap;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $this->assertFalse(\Yii::$container->has(CacheInterface::class));
        $app = $this->createPartialMock(\yii\console\Application::class, []);
        $bootstrap = new Bootstrap();
        $bootstrap->bootstrap($app);
        $this->assertTrue(\Yii::$container->has(CacheInterface::class));
    }
}
