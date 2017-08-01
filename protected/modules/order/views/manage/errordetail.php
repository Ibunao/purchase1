<?php
/**
 *
 * @author        zangmiao <838881690@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       Manage.controller
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
$this->breadcrumbs = array(
    '客户管理',
    '客户管理'=>'/admin.php?r=order/manage/index',
    '客户批量导入检查结果',
);
?>
<style>
    .info p{
        font-size: 15px;
    }
    .info p b{
        font-size: 18px;
        color: red;
    }
    .info span{
        padding-right: 20px;
    }
</style>
<div class="info">
    <h3>下面是错误的问题，请根据下面提示修改EXCEL文件后再试</h3>
    <a href="/admin.php?r=order/manage/import"><button class="btn btn-sm btn-danger">点我重试</button></a>
    <?php if($error){  echo $error;  } ?>
</div>