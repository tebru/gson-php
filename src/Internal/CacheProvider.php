<?php

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\ChainCache;
use Symfony\Component\Cache\Simple\NullCache;
use Symfony\Component\Cache\Simple\PhpFilesCache;

final class CacheProvider
{
    /**
     * @var bool
     */
    private $symfonyGte43;

    public function __construct()
    {
        $this->symfonyGte43 = class_exists('Symfony\Component\Cache\Psr16Cache');
    }

    /**
     * @return CacheInterface
     */
    public function provideNullCacheAsSimpleCache()
    {
        if ($this->symfonyGte43) {
            return new Psr16Cache(new NullAdapter());
        }

        return new NullCache();
    }

    /**
     * @param int $defaultLifetime
     * @param bool $storeSerialized Disabling serialization can lead to cache corruptions when storing mutable values but increases performance otherwise
     * @return CacheInterface
     */
    public function provideArrayCacheAsSimpleCache(int $defaultLifetime = 0, bool $storeSerialized = true)
    {
        if ($this->symfonyGte43) {
            return new Psr16Cache(new ArrayAdapter($defaultLifetime, $storeSerialized));
        }

        return new ArrayCache($defaultLifetime, $storeSerialized);
    }

    /**
     * @param int $defaultLifetime
     * @param bool $storeSerialized Disabling serialization can lead to cache corruptions when storing mutable values but increases performance otherwise
     * @return CacheItemPoolInterface|CacheInterface
     */
    public function providerArrayCacheAsCacheItemPool(int $defaultLifetime = 0, bool $storeSerialized = true)
    {
        if ($this->symfonyGte43) {
            return new ArrayAdapter($defaultLifetime, $storeSerialized);
        }

        return new ArrayCache($defaultLifetime, $storeSerialized);
    }

    /**
     * @param CacheItemPoolInterface[] $caches The ordered list of caches used to fetch cached items
     * @param int $defaultLifetime The lifetime of items propagated from lower caches to upper ones
     * @return CacheInterface
     */
    public function provideChainCacheAsSimpleCache(array $caches, int $defaultLifetime = 0)
    {
        if ($this->symfonyGte43) {
            return new Psr16Cache(new ChainAdapter($caches, $defaultLifetime));
        }

        return new ChainCache($caches, $defaultLifetime);
    }

//    /**
//     * @param string $namespace
//     * @param int $defaultLifetime
//     * @param string|null $directory
//     * @param bool $appendOnly Set to `true` to gain extra performance when the items stored in this pool never expire.
//     *                    Doing so is encouraged because it fits perfectly OPcache's memory model.
//     *
//     * @return CacheInterface
//     * @throws CacheException
//     */
//    public function providePhpFilesCacheAsSimpleCache(string $namespace = '', int $defaultLifetime = 0, string $directory = null, bool $appendOnly = false)
//    {
//        if ($this->symfonyGte43) {
//            return new Psr16Cache(new PhpFilesAdapter($namespace, $defaultLifetime, $directory, $appendOnly));
//        }
//
//        return new PhpFilesCache($namespace, $defaultLifetime, $directory, $appendOnly);
//    }

    /**
     * @param string $namespace
     * @param int $defaultLifetime
     * @param string|null $directory
     * @param bool $appendOnly Set to `true` to gain extra performance when the items stored in this pool never expire.
     *                    Doing so is encouraged because it fits perfectly OPcache's memory model.
     *
     * @return CacheItemPoolInterface|CacheInterface
     * @throws CacheException
     */
    public function providePhpFilesCacheAsCacheItemPool(string $namespace = '', int $defaultLifetime = 0, string $directory = null, bool $appendOnly = false)
    {
        if ($this->symfonyGte43) {
            return new PhpFilesAdapter($namespace, $defaultLifetime, $directory, $appendOnly);
        }

        return new PhpFilesCache($namespace, $defaultLifetime, $directory, $appendOnly);
    }
}
