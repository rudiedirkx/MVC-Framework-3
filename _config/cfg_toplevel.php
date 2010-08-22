<?php

define( 'UTC_START',			microtime(true) );



define( 'SCRIPT_ROOT',			str_replace('\\', '/', dirname(dirname(__FILE__))) );

define( 'PROJECT_PUBLIC',		SCRIPT_ROOT . '/public_html' );
define( 'PROJECT_INCLUDE',		SCRIPT_ROOT . '/source/include' );
define( 'PROJECT_MODELS',		PROJECT_INCLUDE . '/models' );			// M
define( 'PROJECT_VIEWS',		PROJECT_INCLUDE . '/views' );			// V
define( 'PROJECT_CONTROLLERS',	SCRIPT_ROOT . '/source/controllers' );	// C
define( 'PROJECT_RESOURCES',	SCRIPT_ROOT . '/source/resources' );
define( 'PROJECT_RUNTIME',		SCRIPT_ROOT . '/source/runtime' );
define( 'PROJECT_CRONJOBS',		SCRIPT_ROOT . '/source/cronjobs' );


# include paths (3dparty apps and global apps) #
define( 'PROJECT_INC_TPL',		PROJECT_INCLUDE . '/smarty' );
define( 'PROJECT_INC_DB',		PROJECT_INCLUDE . '/models/db' );


# runtime paths (logs, tmp for suckureAdmin, etc) #
define( 'RUNTIME_LOGS',			PROJECT_RUNTIME . '/logs/' );


# session vars #
define( 'SESSION_NAME',			'pj_3_1' );


# project version from VERSION file #
define( 'PROJECT_VERSION',		trim(file_get_contents(PROJECT_PUBLIC.'/VERSION')) );


