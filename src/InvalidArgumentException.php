<?php

declare(strict_types=1);

namespace Aivchen\SimpleCache;

use yii\base;

final class InvalidArgumentException extends base\Exception implements \Psr\SimpleCache\InvalidArgumentException {}
