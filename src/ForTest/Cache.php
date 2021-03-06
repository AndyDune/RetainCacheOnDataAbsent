<?php
/**
 *
 * PHP version >= 5.6
 *
 *
 * @package andydune/retain-cache-on-data-absent
 * @link  https://github.com/AndyDune/RetainCacheOnDataAbsent for the canonical source repository
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrey Ryzhov  <info@rznw.ru>
 * @copyright 2018 Andrey Ryzhov
 */

namespace AndyDune\RetainCacheOnDataAbsent\ForTest;


class Cache extends \AndyDune\RetainCacheOnDataAbsent\Cache
{
    public function buildMetaDataKey($key)
    {

        return parent::buildMetaDataKey($key);
    }

}