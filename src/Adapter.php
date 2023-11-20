<?php

declare(strict_types=1);

namespace Wearesho\SimpleCache;

use yii\base;
use yii\caching;
use yii\di;
use Psr\SimpleCache;

/**
 * Class Adapter
 * @package Wearesho\SimpleCache
 */
class Adapter extends base\Component implements SimpleCache\CacheInterface
{
    public const INVALID_KEY_CHARACTER = '{}()/\@:';

    public string|array $componentId = 'cache';

    public caching\CacheInterface $cache;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        /** @var caching\CacheInterface */
        $this->cache = di\Instance::ensure($this->componentId, caching\CacheInterface::class);
    }

    /**
     * Cache::get() return false if the value is not in the cache or expired, but PSR-16 return $default(null)
     *
     * @return bool|mixed|null
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertValidKey($key);

        $data = $this->cache->get($key);

        if ($data === false) {
            return $default;
        }

        return $data ?? false;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->assertValidKey($key);

        if (($duration = $this->toSeconds($ttl)) === false) {
            return $this->delete($key);
        }

        if ($value === null) {
            return $this->delete($key);
        }

        // case FALSE to null so we can detect that if
        // the cache miss/expired or it did set the FALSE value into cache
        $value = $value === false ? null : $value;
        return $this->cache->set($key, $value, $duration);
    }

    public function delete(string $key): bool
    {
        $this->assertValidKey($key);

        return $this->has($key) ? $this->cache->delete($key) : true;
    }

    public function clear(): bool
    {
        return $this->cache->flush();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $pairs = [];
        foreach ($values as $key => $value) {
            $this->assertValidKey($key);
            $pairs[$key] = $value;
        }

        $res = true;
        foreach ($pairs as $key => $value) {
            $res = $res && $this->set($key, $value, $ttl);
        }

        return $res;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys, false);
        }

        array_walk($keys, $this->assertValidKey(...));

        $res = true;
        array_map(function ($key) use (&$res) {
            $res = $res && $this->delete($key);
        }, $keys);

        return $res;
    }

    public function has(string $key): bool
    {
        $this->assertValidKey($key);

        return $this->cache->exists($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidKey(string $key): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Invalid key. Key should not be empty.');
        }

        // valid key according to PSR-16 rules
        $invalid = preg_quote(static::INVALID_KEY_CHARACTER, '/');
        if (preg_match('/[' . $invalid . ']/', $key)) {
            throw new InvalidArgumentException(
                'Invalid key: ' . $key . '. Contains (a) character(s) reserved ' .
                'for future extension: ' . static::INVALID_KEY_CHARACTER
            );
        }
    }

    private function toSeconds(null|int|\DateInterval $ttl): false|int
    {
        if ($ttl === null) {
            // 0 means forever in Yii 2 cache
            return 0;
        }

        if (is_int($ttl)) {
            $sec = $ttl;
        } else {
            $sec = ((new \DateTime())->add($ttl))->getTimestamp() - (new \DateTime())->getTimestamp();
        }

        return $sec > 0 ? $sec : false;
    }
}
