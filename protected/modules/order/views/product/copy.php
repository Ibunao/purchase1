<?php
/**
 * add 添加复制  主要是商品复制功能
 *
 * @author        zangmiao <838881690@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       Manage.controller
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
$this->breadcrumbs=array(
    '商品管理',
    '商品管理'=>'/admin.php?r=order/product/index',
    '商品复制',
);
echo $this->renderPartial('_form',array('selectFilter'=>$selectFilter, 'action'=>'copy','param'=>$param));
?>