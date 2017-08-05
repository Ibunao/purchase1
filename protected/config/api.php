<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'name'=>'接口管理',
    'language' => 'zh_cn',

	// preloading 'log' component
	'preload'=>array('log'),

    'defaultController'=>'default/index',
    'timeZone'=>'Asia/Chongqing',

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

    'modules'=>array(
        'api'
	),

	// application components
	'components'=>array(
        'cache'=>array(
            'class'=>'system.caching.CFileCache',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                )
            )
        ),
		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),

        'session' => array(
            'cookieParams' => array(
                'lifetime' => 7200,
            ),
        ),
	),

    'params'=>require(dirname(__FILE__).'/params.php'),
);
