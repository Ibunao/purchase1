<?php
/**
 * 数据缓存类
 * @author chenfenghua <843958575@qq.com>
 */
class Cache {

    /**
     * 左侧分类
     */
    public static function cateList()
    {
        $cagBigSmall = new CatBigSmall();
        $list = $cagBigSmall->items();
        return $list;
    }
}