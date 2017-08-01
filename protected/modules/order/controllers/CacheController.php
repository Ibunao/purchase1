<?php
/**
 * 缓存管理
 *
 * @author     chenfenghua<843958575@qq.com>
 * @copyright  Copyright 2008-2013 mall.octmami.com
 * @version    1.0
 */

class CacheController extends BaseController
{
    /**
     * 缓存管理
     */
    public function actionIndex()
    {
        echo md5(md5(1598));
        die;
        $this->render('index');
    }

    /**
     * 删除缓存
     */
    public function actionDelete()
    {
        $res = Yii::app()->params['flush_cache_url'];
        foreach($res as $val){
            file_get_contents($val.'/user/cache');
        }
        $this->redirect('?r=order/cache/index');
    }
} 