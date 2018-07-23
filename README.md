# RetainCacheOnDataAbsent

[![Build Status](https://travis-ci.org/AndyDune/RetainCacheOnDataAbsent.svg?branch=master)](https://travis-ci.org/AndyDune/RetainCacheOnDataAbsent)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/andydune/retain-cache-on-data-absent.svg?style=flat-square)](https://packagist.org/packages/andydune/retain-cache-on-data-absent)
[![Total Downloads](https://img.shields.io/packagist/dt/andydune/retain-cache-on-data-absent.svg?style=flat-square)](https://packagist.org/packages/andydune/retain-cache-on-data-absent)


Code allow restore data in cache if new data can not be retrieved.

Installation
------------

Installation using composer:

```
composer require andydune/retain-cache-on-data-absent
```
Or if composer was not installed globally:
```
php composer.phar require andydune/retain-cache-on-data-absent
```
Or edit your `composer.json`:
```
"require" : {
     "andydune/retain-cache-on-data-absent": "^1"
}

```
And execute command:
```
php composer.phar update
```

Problem
----------

Yor script gets data from external API. For example currency rate from [crr](https://github.com/AndyDune/CurrencyRateCbr).

There is no need to extract data every time it is needed. 
So we are using cache. But what will be if cache get old, but no data appears from api? 
There is network error or bank site breakage.

This little library helps to avoid data absent from retriever. 
Cached data do not expires and if no new data appears it uses old data from cache. 

Using
-------

```php
use Symfony\Component\Cache\Simple\FilesystemCache;
use AndyDune\RetainCacheOnDataAbsent\Cache;

$cacheAdapter = new FilesystemCache('test', 3600, '<root cache dir>');
$cache = new Cache($cacheAdapter, function () {
    /*
        $data = false; // no data - return data from old cache
        $data = 'very useful data to cache and use'; // update cache and return this data
    */
    return $data;
});
$result = $cache->get('data'); // use any key - it is used only for cache key 
```      
