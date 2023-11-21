<?php

declare(strict_types=1);

namespace Wearesho\SimpleCache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

abstract class BaseTestCase extends TestCase
{
    private CacheInterface $cache;

    abstract protected function createSimpleCache(): CacheInterface;

    protected function setUp(): void
    {
        $this->cache = $this->createSimpleCache();
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    /**
     * Data provider for invalid keys.
     *
     * @return list<list{string}>
     */
    public static function invalidKeys(): array
    {
        return [
            [''],
            ['{str'],
            ['rand{'],
            ['rand{str'],
            ['rand}str'],
            ['rand(str'],
            ['rand)str'],
            ['rand/str'],
            ['rand\\str'],
            ['rand@str'],
            ['rand:str'],
        ];
    }

    /**
     * Data provider for valid keys.
     *
     * @return list<list{string}>
     */
    public static function validKeys(): array
    {
        return [
            ['AbC19_.'],
            ['1234567890123456789012345678901234567890123456789012345678901234'],
        ];
    }

    /**
     * Data provider for valid data to store.
     *
     */
    public static function validData(): array
    {
        return [
            ['AbC19_.'],
            [4711],
            [47.11],
            [true],
            [null],
            [['key' => 'value']],
            [new \stdClass()],
        ];
    }

    public function testSet(): void
    {
        $result = $this->cache->set('key', 'value');
        $this->assertTrue($result, 'set() must return true if success');
        $this->assertEquals('value', $this->cache->get('key'));
    }

    public function testSetTtl(): void
    {
        $result = $this->cache->set('key1', 'value', 1);
        $this->assertTrue($result, 'set() must return true if success');
        $this->assertEquals('value', $this->cache->get('key1'));
        sleep(2);
        $this->assertNull($this->cache->get('key1'), 'Value must expire after ttl.');

        $this->cache->set('key2', 'value', new \DateInterval('PT1S'));
        $this->assertEquals('value', $this->cache->get('key2'));
        sleep(2);
        $this->assertNull($this->cache->get('key2'), 'Value must expire after ttl.');
    }

    public function testSetExpiredTtl(): void
    {
        $this->cache->set('key0', 'value');
        $this->cache->set('key0', 'value', 0);
        $this->assertNull($this->cache->get('key0'));
        $this->assertFalse($this->cache->has('key0'));

        $this->cache->set('key1', 'value', -1);
        $this->assertNull($this->cache->get('key1'));
        $this->assertFalse($this->cache->has('key1'));
    }

    public function testGet(): void
    {
        $this->assertNull($this->cache->get('key'));
        $this->assertEquals('foo', $this->cache->get('key', 'foo'));

        $this->cache->set('key', 'value');
        $this->assertEquals('value', $this->cache->get('key', 'foo'));
    }

    public function testDelete(): void
    {
        $this->assertTrue($this->cache->delete('key'), 'Deleting a value that does not exist should return true');
        $this->cache->set('key', 'value');
        $this->assertTrue($this->cache->delete('key'), 'Delete must return true on success');
        $this->assertNull($this->cache->get('key'), 'Values must be deleted on delete()');
    }

    public function testClear(): void
    {
        $this->assertTrue($this->cache->clear(), 'Clearing an empty cache should return true');
        $this->cache->set('key', 'value');
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->assertTrue($this->cache->clear(), 'Delete must return true on success');
        $this->assertNull($this->cache->get('key'), 'Values must be deleted on clear()');
    }

    public function testSetMultiple(): void
    {
        $result = $this->cache->setMultiple(['key0' => 'value0', 'key1' => 'value1']);
        $this->assertTrue($result, 'setMultiple() must return true if success');
        $this->assertEquals('value0', $this->cache->get('key0'));
        $this->assertEquals('value1', $this->cache->get('key1'));

        $result = $this->cache->setMultiple(['k0' => 'value0']);
        $this->assertTrue($result, 'setMultiple() must return true if success');
        $this->assertEquals('value0', $this->cache->get('k0'));
    }

    public function testSetMultipleTtl(): void
    {
        $this->cache->setMultiple(['key2' => 'value2', 'key3' => 'value3'], 1);
        $this->assertEquals('value2', $this->cache->get('key2'));
        $this->assertEquals('value3', $this->cache->get('key3'));
        sleep(2);
        $this->assertNull($this->cache->get('key2'), 'Value must expire after ttl.');
        $this->assertNull($this->cache->get('key3'), 'Value must expire after ttl.');

        $this->cache->setMultiple(['key4' => 'value4'], new \DateInterval('PT1S'));
        $this->assertEquals('value4', $this->cache->get('key4'));
        sleep(2);
        $this->assertNull($this->cache->get('key4'), 'Value must expire after ttl.');
    }

    public function testSetMultipleExpiredTtl(): void
    {
        $this->cache->setMultiple(['key0' => 'value0', 'key1' => 'value1'], 0);
        $this->assertNull($this->cache->get('key0'));
        $this->assertNull($this->cache->get('key1'));
    }

    public function testSetMultipleWithGenerator(): void
    {
        $gen = static function (): \Generator {
            yield 'key0' => 'value0';
            yield 'key1' => 'value1';
        };

        $this->cache->setMultiple($gen());
        $this->assertEquals('value0', $this->cache->get('key0'));
        $this->assertEquals('value1', $this->cache->get('key1'));
    }

    public function testGetMultiple(): void
    {
        $result = $this->cache->getMultiple(['key0', 'key1']);
        $keys = [];
        foreach ($result as $i => $r) {
            $keys[] = $i;
            $this->assertNull($r);
        }
        sort($keys);
        $this->assertSame(['key0', 'key1'], $keys);

        $this->cache->set('key3', 'value');
        $result = $this->cache->getMultiple(['key2', 'key3', 'key4'], 'foo');
        $keys = [];
        foreach ($result as $key => $r) {
            $keys[] = $key;
            if ($key === 'key3') {
                $this->assertEquals('value', $r);
            } else {
                $this->assertEquals('foo', $r);
            }
        }
        sort($keys);
        $this->assertSame(['key2', 'key3', 'key4'], $keys);
    }

    public function testGetMultipleWithGenerator(): void
    {
        $gen = static function (): \Generator {
            yield 1 => 'key0';
            yield 1 => 'key1';
        };

        $this->cache->set('key0', 'value0');
        $result = $this->cache->getMultiple($gen());
        $keys = [];
        foreach ($result as $key => $r) {
            $keys[] = $key;
            if ($key === 'key0') {
                $this->assertEquals('value0', $r);
            } elseif ($key === 'key1') {
                $this->assertNull($r);
            } else {
                $this->assertFalse(true, 'This should not happend');
            }
        }
        sort($keys);
        $this->assertSame(['key0', 'key1'], $keys);
        $this->assertEquals('value0', $this->cache->get('key0'));
        $this->assertNull($this->cache->get('key1'));
    }

    public function testDeleteMultiple(): void
    {
        $this->assertTrue($this->cache->deleteMultiple([]), 'Deleting a empty array should return true');
        $this->assertTrue(
            $this->cache->deleteMultiple(['key']),
            'Deleting a value that does not exist should return true'
        );

        $this->cache->set('key0', 'value0');
        $this->cache->set('key1', 'value1');
        $this->assertTrue($this->cache->deleteMultiple(['key0', 'key1']), 'Delete must return true on success');
        $this->assertNull($this->cache->get('key0'), 'Values must be deleted on deleteMultiple()');
        $this->assertNull($this->cache->get('key1'), 'Values must be deleted on deleteMultiple()');
    }

    public function testDeleteMultipleGenerator(): void
    {
        $gen = static function (): \Generator {
            yield 1 => 'key0';
            yield 1 => 'key1';
        };
        $this->cache->set('key0', 'value0');
        $this->assertTrue($this->cache->deleteMultiple($gen()), 'Deleting a generator should return true');

        $this->assertNull($this->cache->get('key0'), 'Values must be deleted on deleteMultiple()');
        $this->assertNull($this->cache->get('key1'), 'Values must be deleted on deleteMultiple()');
    }

    public function testHas(): void
    {
        $this->assertFalse($this->cache->has('key0'));
        $this->cache->set('key0', 'value0');
        $this->assertTrue($this->cache->has('key0'));
    }

    #[DataProvider('invalidKeys')]
    public function testGetInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->get($key);
    }

    #[DataProvider('invalidKeys')]
    public function testGetMultipleInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->getMultiple(['key1', $key, 'key2']);
    }

    #[DataProvider('invalidKeys')]
    public function testSetInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->set($key, 'foobar');
    }

    #[DataProvider('invalidKeys')]
    public function testSetMultipleInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $values = static function () use ($key): \Generator {
            yield 'key1' => 'foo';
            yield $key => 'bar';
            yield 'key2' => 'baz';
        };
        $this->cache->setMultiple($values());
    }

    #[DataProvider('invalidKeys')]
    public function testHasInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->has($key);
    }

    #[DataProvider('invalidKeys')]
    public function testDeleteInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->delete($key);
    }

    #[DataProvider('invalidKeys')]
    public function testDeleteMultipleInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->deleteMultiple(['key1', $key, 'key2']);
    }

    public function testNullOverwrite(): void
    {
        $this->cache->set('key', 5);
        $this->cache->set('key', null);

        $this->assertNull($this->cache->get('key'), 'Setting null to a key must overwrite previous value');
    }

    public static function byTypeCases(): array
    {
        return [
            [null],
            ['5'],
            [5],
            [5],
            [1.23456789],
            [true],
            [false],
            ['a' => 'foo', 2 => 'bar'],
            [(object)['a' => 'foo']],
        ];
    }

    #[DataProvider('byTypeCases')]
    public function testByType(mixed $value): void
    {
        $this->cache->set('key', $value);
        $has = $this->cache->has('key');
        $actual = $this->cache->get('key');

        $this->assertTrue($has, sprintf('has() should return true when %s is stored.', gettype($value)));
        $this->assertEquals($value, $actual, 'Wrong data type. If we store null we must get null back.');
    }

    #[DataProvider('validKeys')]
    public function testSetValidKeys(string $key): void
    {
        $this->cache->set($key, 'foobar');
        $this->assertEquals('foobar', $this->cache->get($key));
    }

    #[DataProvider('validKeys')]
    public function testSetMultipleValidKeys(string $key): void
    {
        $this->cache->setMultiple([$key => 'foobar']);
        $result = $this->cache->getMultiple([$key]);
        $keys = [];
        foreach ($result as $i => $r) {
            $keys[] = $i;
            $this->assertEquals($key, $i);
            $this->assertEquals('foobar', $r);
        }
        $this->assertSame([$key], $keys);
    }

    #[DataProvider('validData')]
    public function testSetValidData(mixed $data): void
    {
        $this->cache->set('key', $data);
        $this->assertEquals($data, $this->cache->get('key'));
    }

    #[DataProvider('validData')]
    public function testSetMultipleValidData(mixed $data): void
    {
        $this->cache->setMultiple(['key' => $data]);
        $result = $this->cache->getMultiple(['key']);
        $keys = [];
        foreach ($result as $i => $r) {
            $keys[] = $i;
            $this->assertEquals($data, $r);
        }
        $this->assertSame(['key'], $keys);
    }

    public function testObjectAsDefaultValue(): void
    {
        $obj = new \stdClass();
        $obj->foo = 'value';
        $this->assertEquals($obj, $this->cache->get('key', $obj));
    }

    public function testObjectDoesNotChangeInCache(): void
    {
        $obj = new \stdClass();
        $obj->foo = 'value';
        $this->cache->set('key', $obj);
        $obj->foo = 'changed';

        $cacheObject = $this->cache->get('key');
        $this->assertEquals('value', $cacheObject->foo, 'Object in cache should not have their values changed.');
    }
}
