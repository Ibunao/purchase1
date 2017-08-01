<?php
/**
 * 用户导入
 *
 * @author       zangmiao <838881690@qq.com>
 * @copyright    Copyright (c) 2011-2016 octmami.All Rights Reserved.
 * @link         http://mall.octmami.com
 * @version      v1.2
 */
$this->breadcrumbs=array(
    '内容管理',
    '客户管理'=>'/admin.php?r=order/manage/index',
    '批量导入客户',
);
?>
<style>
    .headers p{
        font-size: 16px;
    }
    a{
        font-size: 16px;
    }
</style>
<h3>代理导入</h3>
<div class="row" style="margin-top: 20px">
    <form method="post" enctype="multipart/form-data" action="/admin.php?r=order/manage/ImportAgent">
        <div class="form-group">
            <label for="name">上传文件</label>
            <input type="file" class="btn btn-sm btn-info" value="上传文件" name="file">
        </div>
        <div class="form-group">
            <button class="btn btn-sm btn-danger">立即导入</button>
        </div>
    </form>
</div>
