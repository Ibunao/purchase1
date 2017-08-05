<?php
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'十月妈咪订货会',
    'language' => 'zh_cn',

	// preloading 'log' component
	'preload'=>array('log'),

    'defaultController'=>'default/index',

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),
    'theme'=>'b2c',

	// application components
	'components'=>array(
        'cache'=>array(
            'class'=>'system.caching.CFileCache',
        ),

		// uncomment the following to enable URLs in path-format
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'urlSuffix'=>'.html',//搭车加上.html后缀
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),

		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

        'session' => array(
            'cookieParams' => array(
                'lifetime' => 3600*24,
            ),
        ),

	),
    'params'=>require(dirname(__FILE__).'/params.php'),
);
