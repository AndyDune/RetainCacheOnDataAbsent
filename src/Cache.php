<?php
/**
 *
 * PHP version >= 5.6
 *
 * For reference:
 * https://github.com/php-fig/simple-cache
 * https://symfony.com/doc/current/components/cache
 *
 * @package andydune/retain-cache-on-data-absent
 * @link  https://github.com/AndyDune/RetainCacheOnDataAbsent for the canonical source repository
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrey Ryzhov  <info@rznw.ru>
 * @copyright 2018 Andrey Ryzhov
 */

namespace AndyDune\RetainCacheOnDataAbsent;

use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    /**
     * @var null|callable
     */
    protected $dataExtractor = null;

    protected $cacheAdapter;

    protected $defaultTtl = 3600;

    protected $ttlOnRetain = 360;

    protected $ttlOnDataAbsent = 200;

    protected $version = 1;

    /**
     * Cache constructor.
     *
     *
     * @param CacheInterface $cacheAdapter
     * @param callable|null $extractor
     */
    public function __construct(CacheInterface $cacheAdapter, callable $extractor = null)
    {
        $this->cacheAdapter = $cacheAdapter;
        $this->dataExtractor = $extractor;
    }


    /**
     * @param callable $extractor
     */
    public function setDataExtractor(callable $extractor)
    {
        $this->dataExtractor = $extractor;
    }


    /**
     * Set default ttl
     *
     * @param int $ttl
     * @return Cache
     */
    public function setDefaultTtl($ttl)
    {
        $this->defaultTtl = $ttl;
        return $this;
    }


    protected function buildMetaDataKey($key)
    {
        return $key . '_' . md5(Cache::class);
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        $hasConditionally = $this->hasConditionally($key);
        $hasReal = $this->hasReal($key);
        if ($hasConditionally and $hasReal) {
            return $this->cacheAdapter->get($key, $default);
        }
        try {
            $data = call_user_func($this->dataExtractor);
        } catch (\Exception $e) {
            $data = false;
        }

        if ($data) {
            $this->set($key, $data);
            return $data;
        }

        if ($hasReal) {
            $this->setConditionally($key, $this->ttlOnRetain);
            return $this->cacheAdapter->get($key, $default);
        }

        $this->set($key, '', $this->ttlOnDataAbsent);
        return $data;

    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$ttl) {
            $ttl = $this->defaultTtl;
        }
        $this->setConditionally($key, $ttl);
        return $this->cacheAdapter->set($key, $value, $ttl * 100);
    }

    protected function setConditionally($key, $ttl)
    {
        $this->cacheAdapter->set($this->buildMetaDataKey($key), $this->version, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        $this->cacheAdapter->delete($this->buildMetaDataKey($key));
        return $this->cacheAdapter->delete($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->cacheAdapter->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->cacheAdapter->getMultiple($keys, $default);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->cacheAdapter->setMultiple($values, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        return $this->cacheAdapter->deleteMultiple($keys);
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        if ($this->hasConditionally($key)) {
            return $this->cacheAdapter->has($key);
        }
        return false;
    }


    protected function hasConditionally($key)
    {
        return $this->cacheAdapter->has($this->buildMetaDataKey($key));
    }

    protected function hasReal($key)
    {
        return $this->cacheAdapter->has($key);
    }

}