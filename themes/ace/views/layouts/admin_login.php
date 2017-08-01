<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo Yii::app()->params['web_sites_title']; ?>后台</title>
    <meta name="keywords" content="<?php echo Yii::app()->params['web_sites_title']; ?>后台" />
    <meta name="description" content="<?php echo Yii::app()->params['web_sites_title']; ?>后台" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- basic styles -->

    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/font-awesome.min.css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!-- page specific plugin styles -->

    <!-- fonts -->

    <!--
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" />
    -->

    <!-- ace styles -->

    <link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/ace.min.css" />
    <link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/ace-rtl.min.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/js/html5shiv.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/js/respond.min.js"></script>
    <![endif]-->
</head>
<script type="text/javascript">
    var isChrome = window.navigator.userAgent.indexOf("Chrome") !== -1;
    if (!isChrome) {
        alert("不是Chrome浏览器");
        location.href='/';
    }
</script>
<body class="login-layout" style="background-color: #478fca !important;">
<div class="main-container">
<div class="main-content">
<div class="row">
<div class="col-sm-10 col-sm-offset-1">
<div class="login-container">
<div class="center">
    <h1>
        <i class="icon-leaf green"></i>
        <span class="red">十月妈咪</span>
        <span class="white">订货会后台</span>
    </h1>
    <h4 class="white">&copy; Company 上海有喜</h4>
</div>

<div class="space-6"></div>

<div class="position-relative">
    <div id="login-box" class="login-box visible widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header blue lighter bigger">
                    <i class="icon-coffee green"></i>
                    请输入你的登录信息
                </h4>
                <div class="space-6"></div>
                <form action="?r=admin/login" method="post">
                    <fieldset>
                        <label class="block clearfix">
                            <span class="block input-icon input-icon-right">
                                <input type="text" class="form-control" name="Desktopusers[name]"/>
                                <i class="icon-user"></i>
                            </span>
                        </label>
                        <label class="block clearfix">
                            <span class="block input-icon input-icon-right">
                                <input type="password" class="form-control" name="Desktopusers[password]/>
                                <i class="icon-lock"></i>
                            </span>
                        </label>

                        <div class="space"></div>

                        <div class="clearfix">
                            <button type="submit" class="width-35 pull-right btn btn-sm btn-primary">
                                <i class="icon-key"></i>
                                登录
                            </button>
                        </div>

                        <div class="space-4"></div>
                    </fieldset>
                </form>

            </div><!-- /widget-main -->

            <div class="toolbar clearfix">
                <div>
                    <a href="#" onclick="show_box('forgot-box'); return false;" class="forgot-password-link">
                        <i class="icon-arrow-left"></i>
                        访问首页
                    </a>
                </div>

            </div>
        </div><!-- /widget-body -->
    </div><!-- /login-box -->

</div><!-- /position-relative -->
</div>
</div><!-- /.col -->
</div><!-- /.row -->
</div>
</div><!-- /.main-container -->

<!-- basic scripts -->

<script type="text/javascript">
    window.jQuery || document.write("<script src='<?php echo Yii::app()->theme->baseUrl; ?>/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='<?php echo Yii::app()->theme->baseUrl; ?>/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<!-- inline scripts related to this page -->
<script type="text/javascript">
    function show_box(id) {
        jQuery('.widget-box.visible').removeClass('visible');
        jQuery('#'+id).addClass('visible');
    }
</script>
</body>
</html>
