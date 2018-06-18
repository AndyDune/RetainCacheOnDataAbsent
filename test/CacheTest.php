<?php
/**
 *
 * PHP version >= 5.6
 *
 * @package andydune/retain-cache-on-data-absent
 * @link  https://github.com/AndyDune/RetainCacheOnDataAbsent for the canonical source repository
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrey Ryzhov  <info@rznw.ru>
 * @copyright 2018 Andrey Ryzhov
 */

namespace AndyDuneTest\RetainCacheOnDataAbsent;

use AndyDune\RetainCacheOnDataAbsent\ForTest\Cache;
use AndyDune\RetainCacheOnDataAbsent\ForTest\DataExtractor;
use AndyDune\RetainCacheOnDataAbsent\ForTest\TemporaryDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CacheTest extends TestCase
{
    public function testCache()
    {
        $tempDir = new TemporaryDirectory(__DIR__);
        $tempDir->name('tmp');
        $tempDir->emptyDir();

        $cacheAdapter = new FilesystemCache('test', 3600, $tempDir->path());

        $dataExtractor = new DataExtractor();

        $cache = new Cache($cacheAdapter, function () use ($dataExtractor) {
            return $dataExtractor->getData();
        });

        $this->assertEquals(0, $dataExtractor->getUsed());
        $date = $cache->get('date');
        $this->assertEquals(1, $dataExtractor->getUsed());
        $this->assertEquals($date, $dataExtractor->getData(false));

        $dataPrev = $dataExtractor->getData(false);
        $dataExtractor->setDate('Y-m-d H:i:s', time() + 23);
        $date = $cache->get('date');
        $this->assertEquals(1, $dataExtractor->getUsed());
        $this->assertEquals($date, $dataPrev);

        $this->assertTrue($cache->has('date'));

        $cacheAdapter->delete($cache->buildMetaDataKey('date'));
        $this->assertTrue($cacheAdapter->has('date'));
        $this->assertFalse($cache->has('date'));

        $date = $cache->get('date');
        $this->assertEquals(2, $dataExtractor->getUsed());
        $this->assertEquals($date, $dataExtractor->getData(false));

        $date = $cache->get('date');
        $this->assertEquals(2, $dataExtractor->getUsed());

        $tempDir->delete();
    }
}