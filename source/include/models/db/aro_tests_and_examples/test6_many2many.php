<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'tests');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';

$user = AROUser::finder()->byPk(5);
$user->bff;
$user->friends; // MANY_TO_MANY
$user->friends2; // FROM_FUNCTION
print_r($user);

print_r( AROUser::finder()->findFirst('id > ?', rand(0, 2)) );

?>