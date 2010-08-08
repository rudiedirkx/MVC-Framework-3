<?php

$g_fStartUtc = microtime(true);

// Include config
require_once( 'cfg_toplevel.php' );
session_start();


function __autoload( $f_szClass ) {
	$class = strtolower($f_szClass);
	foreach ( array(PROJECT_MODELS, PROJECT_INCLUDE, PROJECT_CONTROLLERS) AS $dir ) {
		if ( file_exists($dir . '/inc.cls.' . $class . '.php') ) {
			require_once($dir . '/inc.cls.' . $class . '.php');
			break;
		}
	}
}


require_once( PROJECT_CONTROLLERS.'/inc.cls.__topmodule.php' );


// Fetch request URI
$g_szRequestUri = __TopModule::getRequestUri();

// Connect to db
require_once( 'cfg_db.php' );
require_once( PROJECT_INC_DB . '/inc.cls.db_mysqli.php' );
$db = new db_mysqli( SQL_HOST, SQL_USER, SQL_PASS, SQL_DB );

// Save db layer
require_once( PROJECT_INC_DB . '/inc.cls.activerecordobject.php' );
ActiveRecordObject::setDbObject($db);


// Define general functions
require_once( PROJECT_INCLUDE . '/inc.functions.php' );


// Assign new class name to existing superclass
//template::$class = 'mytemplate';

try {
	$application = __TopModule::run( $g_szRequestUri );
	$application->exec();
}
catch ( InvalidURIException $ex ) {
	exit('['.date('Y-m-d H:i:s').'] Page not found: '.$g_szRequestUri);
}
catch ( AROException $ex ) {
	exit('['.date('Y-m-d H:i:s').'] Model error: '.$ex->getMessage());
}
catch ( DBException $ex ) {
	exit('['.date('Y-m-d H:i:s').'] Database error: '.$ex->getMessage());
}

