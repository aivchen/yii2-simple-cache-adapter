<?php

declare(strict_types=1);

namespace Wearesho\SimpleCache;

use yii\base;

/**
 * Class InvalidArgumentException
 * @package Wearesho\SimpleCache
 */
class InvalidArgumentException extends base\Exception implements \Psr\SimpleCache\InvalidArgumentException {}
