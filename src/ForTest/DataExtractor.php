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


class DataExtractor
{

    protected $used = 0;

    protected $date;

    public function __construct()
    {
        $this->date = date('Y-m-d H:i:s');
    }


    public function getData($increment = true)
    {
        if ($increment) {
            $this->used++;
        }
        return $this->date;
    }

    /**
     * @param false|string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }



    /**
     * @return int
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * @param int $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }



}