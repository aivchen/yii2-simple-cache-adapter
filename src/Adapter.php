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

    public string|array|caching\CacheInterface $cache = 'cache';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->cache = di\Instance::ensure($this->cache, caching\CacheInterface::class);
    }

    /**
     * Cache::get() return false if the value is not in the cache or expired, but PSR-16 return $default(null)
     *
     * @param string $key
     * @param null $default
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
        if (!$keys instanceof \Traversable && !is_array($keys)) {
            throw new InvalidArgumentException(
                'Invalid keys: ' . var_export($keys, true) . '. Keys should be an array or Traversable of strings.'
            );
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        if (!$values instanceof \Traversable && !is_array($values)) {
            throw new InvalidArgumentException(
                'Invalid keys: ' . var_export($values, true) . '. Keys should be an array or Traversable of strings.'
            );
        }

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
     * @param $key
     * @throws InvalidArgumentException
     */
    private function assertValidKey($key): void
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Invalid key: ' . var_export($key, true) . '. Key should be a string.');
        }

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

    /**
     * @param $ttl
     * @throws InvalidArgumentException
     */
    private function assertValidTtl($ttl): void
    {
        if ($ttl !== null && !is_int($ttl) && !$ttl instanceof \DateInterval) {
            $error = 'Invalid time: ' . serialize($ttl) . '. Must be integer or instance of DateInterval.';
            throw new InvalidArgumentException($error);
        }
    }

    /**
     * @param $ttl
     * @return false|int
     * @throws InvalidArgumentException
     */
    private function toSeconds($ttl): false|int
    {
        $this->assertValidTtl($ttl);

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
