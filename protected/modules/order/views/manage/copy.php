<?php
/**
 * 客户修改的VIEW视图
 *
 * @author        zangmiao <838881690@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       Manage.controller
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
$this->breadcrumbs=array(
    '内容管理',
    '客户管理'=>'/admin.php?r=order/manage/manage',
    '客户复制',
);
echo $this->renderPartial('_form',array('insert_option'=>$insert_option, 'action'=>'copy','param'=>$select_result));
